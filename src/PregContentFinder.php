<?php
declare(strict_types=1);
namespace SL5\PregContentFinder;

// PSR-3 Log Interfaces importieren
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use SL5\PregContentFinder\Tests\FilenameProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Formatter\LineFormatter;


enum SearchMode: string
{
    case LAZY_WHITESPACE = 'lazyWhiteSpace';
    case DONT_TOUCH_THIS = 'dontTouchThis';
    case USE_BACKREFERENCE = 'use_BackReference'; // Ggf. den alten String-Wert beibehalten für Kompatibilität mit alten setSearchMode-Aufrufen
    case SIMPLE_STRING_NO_NESTING = 'simpleString';
}

class PregContentFinder
{
    public readonly string $content;

    private string $userProvidedBeginDelimiter;
    private string $userProvidedEndDelimiter;

    private string $effectiveBeginDelimiter;
    private string $effectiveEndDelimiter;

    private SearchMode $currentSearchMode;
    private int $nextSearchPosition = 0;

    private bool $stopOnMissingEndBorder = false;
    private bool $stopOnMissingBothBorders = true;
    private bool $updateInstanceSearchPositionOnMatch = true;

    private array $borderMatchCache = [];
    private array $foundSegmentsList = [];
    private ?int $currentSegmentId = null;
    protected string $logFilePath = ''; 
    protected Logger $logger; 


    public function __construct(
        string $content,
        string|array $beginRegexOrArray = "[",
        ?string $endRegex = "]",
        SearchMode|string $initialSearchMode = SearchMode::LAZY_WHITESPACE
    ) {

        $this->loggerSetUp();
        $this->logger->info("Logger setup complete. Logging to: {$this->logFilePath}");

        $this->logger->info('greetings from PregContentFinder :) ');

        $this->content = $content;

        $this->setSearchMode($initialSearchMode); // Setzt $this->currentSearchMode und ruft prepareEffectiveDelimiters

        // setBeginEndDelimiters muss mit den Standardwerten umgehen können,
        // wenn $beginRegexOrArray und $endRegex ihre Defaults haben.
        if (is_array($beginRegexOrArray)) {
            // Wenn ein Array übergeben wird, ignoriere den $endRegex Parameter
            $this->setBeginEndDelimiters($beginRegexOrArray);
        } else {
            // Wenn $beginRegexOrArray ein String ist (oder der Default "["),
            // und $endRegex der Default "]" ist (oder explizit übergeben wurde).
            // Wichtig: Wenn $endRegex null ist, soll er den Wert von $beginRegexOrArray annehmen.
            $actualEndRegex = ($endRegex === null && !is_array($beginRegexOrArray)) ? $beginRegexOrArray : $endRegex;
            $this->setBeginEndDelimiters($beginRegexOrArray, $actualEndRegex);
        }

        $this->nextSearchPosition = 0;
    }

    protected function loggerSetUp(): void
    {
        $channelName = (new \ReflectionClass($this))->getShortName();
        $this->logger = new Logger($channelName);

        $logDir = '/app/logs'; 
        $shortFileName = str_replace('.php','',__FILE__);
        $logFileName = $shortFileName.'.log';
        $this->logFilePath = $logDir . '/' . $logFileName;
        // Handler für die Log-Datei
        $fileHandler = new StreamHandler($this->logFilePath, Level::Info); 
        // Formatter für die Log-Ausgabe
        // Format: Filename:Line[FunctionName()]LEVEL: Message Context Extra
        // %extra.file% und %extra.line% kommen vom IntrospectionProcessor
        $outputFormat = $shortFileName . ":%extra.line%[%extra.function%()]%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($outputFormat, null, true, true); // allowInlineLineBreaks, ignoreEmptyContextAndExtra
        $fileHandler->setFormatter($formatter);
        $this->logger->pushHandler($fileHandler);
    }



    /**
     * Sets the regular expressions for the beginning and end delimiters.
     *
     * @deprecated This method is deprecated and will be removed in a future version.
     *             Use setBeginEndDelimiters() instead.
     * @param string|array|null $begin
     * @param string|null $end
     * @return void
     */
    public function setBeginEnd_RegEx(string|array|null $begin = null, ?string $end = null): void
    {
        $this->logger->warning(__METHOD__ . ' is deprecated. Use setBeginEndDelimiters() instead.');
        $this->setBeginEndDelimiters($begin, $end);
        // Die alte Methode gab true zurück, aber void ist für Setter besser.
        // Um die alte API exakt nachzubilden (falls Tests das prüfen): return true;
        // Aber da die neue `void` ist, ist Konsistenz vielleicht besser.
        // Da Ihre alte Methode laut PHPDoc `bool always returns true - no meaning` war, ist `void` hier okay.
    }

    public function setBeginEndDelimiters(string|array $begin, ?string $end = null): void
    {
        $this->logger->debug("setBeginEndDelimiters called.", ['begin' => $begin, 'end' => $end]);
        if (is_array($begin)) {
            if (count($begin) !== 2 || !is_string($begin[0]) || !is_string($begin[1])) {
                $this->logger->error("Invalid array structure for delimiters.", ['array_delimiters' => $begin]);
                throw new \InvalidArgumentException("Array delimiter must contain two strings: [begin, end].");
            }
            $this->userProvidedBeginDelimiter = $begin[0];
            $this->userProvidedEndDelimiter = $begin[1];
        } else {
            if (!is_string($begin)) {
                $this->logger->error("Begin delimiter is not a string.", ['begin_type' => gettype($begin)]);
                throw new \InvalidArgumentException("Begin delimiter must be a string.");
            }
            // Wenn $end null ist, soll es den Wert von $begin annehmen.
            $this->userProvidedBeginDelimiter = $begin;
            $this->userProvidedEndDelimiter = $end ?? $begin;
        }
        $this->logger->info("User provided delimiters set.", [
            'begin' => $this->userProvidedBeginDelimiter,
            'end' => $this->userProvidedEndDelimiter
        ]);

        // Ensure prepareEffectiveDelimiters is called if currentSearchMode is already set
        if (isset($this->currentSearchMode)) {
            $this->prepareEffectiveDelimiters();
        }
        $this->clearCacheAndResults();
    }

    // In setSearchMode, der $oldModeValue Check muss angepasst werden, da currentSearchMode im Konstruktor gesetzt wird:
    public function setSearchMode(SearchMode|string $mode): void
    {
        $oldModeValue = null;
        if (isset($this->currentSearchMode)) { // Prüfen, ob es schon initialisiert wurde
            $oldModeValue = $this->currentSearchMode->value;
        }

        if (is_string($mode)) {
            # echo "DEBUG setSearchMode: Attempting to resolve string mode: "; var_dump($mode);
            # echo "DEBUG setSearchMode: Known Enum Cases/Values: "; var_dump(SearchMode::cases()); // ALLE BEKANNTEN CASES AUSGEBEN

            $resolvedMode = SearchMode::tryFrom($mode);
            if ($resolvedMode === null) {
                $validModes = implode(', ', array_map(fn($case) => $case->value, SearchMode::cases()));
                $this->logger->error("Invalid search mode string provided.", ['mode_string' => $mode, 'valid_modes' => $validModes]);
                throw new \InvalidArgumentException("Invalid search mode string: '{$mode}'. Valid values are: {$validModes}");
            }
            $this->currentSearchMode = $resolvedMode;
        } elseif ($mode instanceof SearchMode) {
            $this->currentSearchMode = $mode;
        } else {
            $this->logger->error("Invalid type for search mode.", ['mode_type' => gettype($mode)]);
            throw new \InvalidArgumentException("Invalid type for search mode. Expected string or SearchMode enum instance.");
        }

        if ($oldModeValue !== $this->currentSearchMode->value) {
            $this->logger->info("Search mode changed.", [
                'old_mode' => $oldModeValue,
                'new_mode' => $this->currentSearchMode->value
            ]);
            // prepareEffectiveDelimiters wird nur aufgerufen, wenn userProvidedDelimiters schon gesetzt sind
            if (isset($this->userProvidedBeginDelimiter)) {
                $this->prepareEffectiveDelimiters();
            }
            $this->clearCacheAndResults();
        }
    }

    private function prepareEffectiveDelimiters(): void
    {
        if (!isset($this->userProvidedBeginDelimiter) || !isset($this->userProvidedEndDelimiter)) {
            $this->logger->critical("Cannot prepare effective delimiters: userProvidedDelimiters are not set. This indicates an internal logic error or incorrect initialization order.");
            // This state should ideally be prevented by constructor logic or setters.
            // Forcing an error or setting to a known "broken" state might be appropriate.
            // For now, we'll assume they get set properly before this is critically needed.
            // If they are null, an error will occur later anyway.
            $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter ?? ''; // Fallback to avoid error, but it's a problem
            $this->effectiveEndDelimiter = $this->userProvidedEndDelimiter ?? '';
            return;
        }

        switch ($this->currentSearchMode) {
            case SearchMode::LAZY_WHITESPACE:
                $this->effectiveBeginDelimiter = $this->escapeRegexForDelimiter($this->userProvidedBeginDelimiter, true);
                $this->effectiveEndDelimiter = $this->escapeRegexForDelimiter($this->userProvidedEndDelimiter, true);
                break;
            case SearchMode::SIMPLE_STRING_NO_NESTING:
                $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter;
                $this->effectiveEndDelimiter = $this->userProvidedEndDelimiter;
                break;
            case SearchMode::DONT_TOUCH_THIS:
            case SearchMode::USE_BACKREFERENCE:
                $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter;
                $this->effectiveEndDelimiter = $this->userProvidedEndDelimiter;
                break;
        }
        $this->logger->debug("Effective delimiters prepared.", [
            'mode' => $this->currentSearchMode->value,
            'effective_begin' => $this->effectiveBeginDelimiter,
            'effective_end' => $this->effectiveEndDelimiter
        ]);
    }

    private function escapeRegexForDelimiter(string $string, bool $makeWhitespaceFlexible = false, string $delimiterChar = '~'): string
    {
        $escaped = preg_quote($string, $delimiterChar);
        if ($makeWhitespaceFlexible) {
            $escaped = preg_replace('/\s+/s', '\s+', $escaped);
        }
        return $escaped;
    }

    public function getSearchMode(): string { return $this->currentSearchMode->value; }
    public function setPosOfNextSearch(int $position): void {
        if ($position < 0 || $position > strlen($this->content)) {
            $this->logger->error("Invalid search position set.", ['position' => $position, 'content_length' => strlen($this->content)]);
            throw new \InvalidArgumentException("Invalid search position: {$position}. Must be between 0 and content length.");
        }
        $this->nextSearchPosition = $position;
        $this->logger->debug("Next search position set.", ['position' => $this->nextSearchPosition]);
    }
    public function getPosOfNextSearch(): int { return $this->nextSearchPosition; }
    private function clearCacheAndResults(): void {
        $this->borderMatchCache = [];
        $this->foundSegmentsList = [];
        $this->currentSegmentId = null;
        $this->logger->debug("Cache and results list cleared.");
    }

    public function getEffectiveBeginDelimiter(): string { return $this->effectiveBeginDelimiter; }
    public function getEffectiveEndDelimiter(): string { return $this->effectiveEndDelimiter; }
    public function getUserProvidedBeginDelimiter(): string { return $this->userProvidedBeginDelimiter; }
    public function getUserProvidedEndDelimiter(): string { return $this->userProvidedEndDelimiter; }

    private function findFirstSimpleStringMatch(int $searchOffset): ?array
    {
        $beginRegex = $this->effectiveBeginDelimiter;
        $endRegex = $this->effectiveEndDelimiter;

        $this->logger->debug("Attempting SIMPLE_STRING_NO_NESTING match.", [
            'begin_delim' => $beginRegex, 'end_delim' => $endRegex, 'offset' => $searchOffset
        ]);

        if ($searchOffset >= strlen($this->content)) {
            $this->logger->debug("Simple search: offset beyond content length.", ['offset' => $searchOffset]);
            return null;
        }
        if ($beginRegex === '' || $endRegex === '') {
            $this->logger->warning("Simple search: called with empty delimiter(s).", ['begin' => $beginRegex, 'end' => $endRegex]);
            return null;
        }

        $beginPos = strpos($this->content, $beginRegex, $searchOffset);
        if ($beginPos === false) {
            $this->logger->debug("Simple search: Begin delimiter not found.", ['begin_delim' => $beginRegex]);
            return null;
        }
        $this->logger->debug("Simple search: Found begin delimiter.", ['position' => $beginPos]);

        $beginEndPos = $beginPos + strlen($beginRegex);
        $endPos = strpos($this->content, $endRegex, $beginEndPos);

        if ($endPos === false) {
            $this->logger->debug("Simple search: End delimiter not found after begin.", ['end_delim' => $endRegex, 'searched_from' => $beginEndPos]);
            if ($this->stopOnMissingEndBorder) {
                $this->logger->info("Simple search: Stopping due to missing end border (flag active).");
                return null;
            }
            $this->logger->info("Simple search: End border not found, no valid block.");
            return null;
        }
        $this->logger->debug("Simple search: Found end delimiter.", ['position' => $endPos]);

        $endEndPos = $endPos + strlen($endRegex);

        return [
            'begin_begin' => $beginPos, 'begin_end'   => $beginEndPos,
            'end_begin'   => $endPos,   'end_end'     => $endEndPos,
            'matches'     => null
        ];
    }

    // #########################################################################
    // # KERNLOGIK: findNextSegmentRegex und buildEndRegexWithBackreferences #
    // #########################################################################

    /**
     * Builds the end regex by substituting backreferences with captured values.
     */
    private static function buildEndRegexWithBackreferences(
        array $startMatchCapturingGroups, // Values of captured groups from begin regex
        string $originalEndRegexWithPlaceholders,
        string $pcreDelimiter = '~'
    ): string {
        $modifiedEndRegex = $originalEndRegexWithPlaceholders;
        foreach ($startMatchCapturingGroups as $index => $capturedValue) {
            $groupIndex = $index + 1; // Backreferences are 1-indexed
            $quotedValue = preg_quote((string) $capturedValue, $pcreDelimiter);

            // Replace all common backreference syntaxes
            $patternsToReplace = [
                '\\'. $groupIndex,  // \1
                '$'. $groupIndex,   // $1 (common but sometimes needs care with \b)
                '${'. $groupIndex .'}' // ${1}
            ];
            // Need to be careful with $1 vs $10. Replace longer ones first or use regex for replacement.
            // For simplicity, direct str_replace, but preg_replace might be more robust for $1 vs $10.
            // Example for $1 vs $10 with preg_replace:
            // $modifiedEndRegex = preg_replace('/(?<![0-9])\$' . $groupIndex . '(?![0-9])/', $quotedValue, $modifiedEndRegex);
            // $modifiedEndRegex = preg_replace('/(?<![0-9])\$\{' . $groupIndex . '\}(?![0-9])/', $quotedValue, $modifiedEndRegex);
            // $modifiedEndRegex = preg_replace('/\\\\' . $groupIndex . '(?![0-9])/', $quotedValue, $modifiedEndRegex);
            // For now, simple str_replace:
            $modifiedEndRegex = str_replace($patternsToReplace, $quotedValue, $modifiedEndRegex);
        }
        return $modifiedEndRegex;
    }

    /**
     * Finds the next segment using regular expressions, handling nesting and search modes.
     */
    private function findNextSegmentRegex(int $searchOffset): ?array
    {
        $this->logger->info("REGEX_PATH: Starting regex segment search.", [
            'offset' => $searchOffset, 'mode' => $this->currentSearchMode->value,
            'eff_begin_regex' => $this->effectiveBeginDelimiter,
            'eff_end_regex_tpl' => $this->effectiveEndDelimiter // This is the template for USE_BACKREFERENCE
        ]);

        $txt = $this->content;
        $strLenTxt = strlen($txt);

        if ($searchOffset >= $strLenTxt) {
            $this->logger->debug("REGEX_PATH: Offset beyond content length.");
            return null;
        }

        $activeBeginRegexForLoop = $this->effectiveBeginDelimiter;
        // For USE_BACKREFERENCE, this will be substituted after the first begin match.
        // For DONT_TOUCH_THIS and LAZY_WHITESPACE, it's already the final effective end regex.
        $activeEndRegexForLoop = $this->effectiveEndDelimiter;

        $findPos = ['begin_begin' => null, 'begin_end' => null, 'end_begin' => null, 'end_end' => null];
        $matchesReturn = ['begin_begin' => null, 'end_begin' => null]; // For potential capturing groups

        $count_begin = 0;
        $count_end = 0;
        $emergency_Stop = 0;
        $currentSearchPositionInLoop = $searchOffset;
        $mainLoopPattern = null;

        while (($count_begin === 0 || $count_begin > $count_end) && $emergency_Stop < 1000) {
            $emergency_Stop++;

            if ($count_begin === 0) { // Find the initial opening delimiter
                $this->logger->debug("REGEX_PATH: Iter #{$emergency_Stop} - Searching initial begin.", ['regex' => $activeBeginRegexForLoop, 'pos' => $currentSearchPositionInLoop]);
                if (!preg_match('~' . $activeBeginRegexForLoop . '~sm', $txt, $matches_begin, PREG_OFFSET_CAPTURE, $currentSearchPositionInLoop)) {
                    $this->logger->info("REGEX_PATH: Initial begin delimiter not found.");
                    if (preg_match('~' . $activeEndRegexForLoop . '~sm', $txt, $matches_end_only, PREG_OFFSET_CAPTURE, $currentSearchPositionInLoop)) {
                        $findPos['end_begin'] = $matches_end_only[0][1]; // Store if standalone end found
                        $this->logger->debug("REGEX_PATH: Standalone end delimiter found.", ['pos' => $findPos['end_begin']]);
                    }
                    break;
                }

                $findPos['begin_begin'] = $matches_begin[0][1];
                $findPos['begin_end'] = $findPos['begin_begin'] + strlen($matches_begin[0][0]);
                $currentSearchPositionInLoop = $findPos['begin_end'];
                $count_begin++;
                $this->logger->debug("REGEX_PATH: Initial begin found.", ['match' => $matches_begin[0][0], 'start' => $findPos['begin_begin'], 'end' => $findPos['begin_end']]);

                if (count($matches_begin) > 1) {
                    $capturedGroups = array_slice($matches_begin, 1); // All captured groups
                    $matchesReturn['begin_begin'] = []; // Store as [index => [value, offset]]
                    foreach($capturedGroups as $group) { $matchesReturn['begin_begin'][] = $group; }
                    $this->logger->debug("REGEX_PATH: Captured groups from begin_match.", ['groups' => $matchesReturn['begin_begin']]);
                }

                if ($this->currentSearchMode === SearchMode::USE_BACKREFERENCE && !empty($matchesReturn['begin_begin'])) {
                    $groupValues = array_map(fn($g) => $g[0], $matchesReturn['begin_begin']);
                    // Use $this->userProvidedEndDelimiter as template because $this->effectiveEndDelimiter might be quoted
                    $activeEndRegexForLoop = self::buildEndRegexWithBackreferences($groupValues, $this->userProvidedEndDelimiter, '~');
                    $this->logger->info("REGEX_PATH: End regex updated with backreferences.", ['new_end_regex' => $activeEndRegexForLoop]);
                }
                // After the first begin is found (and end regex potentially updated), build the main loop pattern
                $mainLoopPattern = '~(' . $activeBeginRegexForLoop . '|' . $activeEndRegexForLoop . ')(.*)~sm';
                $this->logger->debug("REGEX_PATH: Main loop pattern constructed.", ['pattern' => $mainLoopPattern]);
            } // End of if ($count_begin === 0)

            if ($mainLoopPattern === null) { // Should not happen if count_begin > 0
                $this->logger->error("REGEX_PATH: mainLoopPattern is unexpectedly null in loop iteration > 1.");
                break;
            }

            $this->logger->debug("REGEX_PATH: Iter #{$emergency_Stop} - Loop search.", ['pattern' => $mainLoopPattern, 'pos' => $currentSearchPositionInLoop]);
            if (preg_match($mainLoopPattern, $txt, $matches_loop, PREG_OFFSET_CAPTURE, $currentSearchPositionInLoop)) {
                $matchedDelimiterFull = $matches_loop[1][0];
                $matchedDelimiterOffset = $matches_loop[1][1];
                $this->logger->debug("REGEX_PATH: Loop: Matched delimiter.", ['string' => $matchedDelimiterFull, 'offset' => $matchedDelimiterOffset]);

                $currentSearchPositionInLoop = $matchedDelimiterOffset + strlen($matchedDelimiterFull);

                if (preg_match('~^' . preg_quote($activeEndRegexForLoop, '~') . '$~s', $matchedDelimiterFull) ||  // If end regex was simple
                    ($activeEndRegexForLoop !== $this->effectiveEndDelimiter && preg_match('~^' . $activeEndRegexForLoop . '$~s', $matchedDelimiterFull)) // If end regex was complex (backref)
                ) {
                    $findPos['end_begin'] = $matchedDelimiterOffset;
                    $findPos['end_end'] = $currentSearchPositionInLoop;
                    $count_end++;
                    $this->logger->debug("REGEX_PATH: Loop: Matched end delimiter.", ['end_pos' => $findPos['end_begin'], 'count' => $count_end]);
                } else {
                    $count_begin++;
                    $this->logger->debug("REGEX_PATH: Loop: Matched begin (or non-end) delimiter.", ['count' => $count_begin]);
                }
            } else {
                $this->logger->info("REGEX_PATH: Loop: No further delimiters by main pattern.");
                break;
            }
        } // End of while loop

        if ($emergency_Stop >= 1000) {
            $this->logger->warning("REGEX_PATH: Emergency stop triggered.", ['iterations' => $emergency_Stop]);
        }

        if ($findPos['begin_begin'] === null) {
            $this->logger->info("REGEX_PATH: Final: No segment found (no begin).");
            return null;
        }

        if ($count_begin > $count_end) { // Unbalanced
            $this->logger->info("REGEX_PATH: Final: Unbalanced delimiters.", ['begins' => $count_begin, 'ends' => $count_end]);
            if ($this->stopOnMissingEndBorder) {
                $this->logger->debug("REGEX_PATH: stopOnMissingEndBorder is true, returning null for unbalanced.");
                return null;
            }
            $findPos['end_begin'] = $strLenTxt;
            $findPos['end_end'] = $strLenTxt;
            $this->logger->debug("REGEX_PATH: Unbalanced, taking content to end of string.", ['end_pos' => $strLenTxt]);
        } elseif ($findPos['end_begin'] === null) { // Begin found, but no end delimiter ever matched by loop's end-part
            $this->logger->info("REGEX_PATH: Final: Begin found, but no end delimiter was matched in loop.");
            if ($this->stopOnMissingEndBorder) return null;
            $findPos['end_begin'] = $strLenTxt;
            $findPos['end_end'] = $strLenTxt;
        }

        // Final check for validity
        if ($findPos['end_begin'] === null || ($findPos['end_begin'] < $findPos['begin_end']) ) {
            $this->logger->warning("REGEX_PATH: Invalid state, end_begin is null or before begin_end.", ['findPos' => $findPos]);
            return null; // Or handle as unclosed if begin_begin is not null
        }

        $findPos['matches'] = $matchesReturn; // Contains 'begin_begin' captures
        $this->logger->info("REGEX_PATH: Regex segment search successful.", ['found' => $findPos]);
        return $findPos;
    }


    public function getBorders(
        ?string $beginRegexParam = null,
        ?string $endRegexParam = null,
        ?int $startPositionParam = null,
        SearchMode|string|null $searchModeParam = null
    ): ?array {



        // Store original instance settings to restore them later if params temporarily override them
        $originalInstanceSearchMode = $this->currentSearchMode;
        $originalInstanceUserBeginRegex = $this->userProvidedBeginDelimiter;
        $originalInstanceUserEndRegex = $this->userProvidedEndDelimiter;
        $wereSettingsTemporarilyChanged = false;

        // Apply temporary settings if parameters are provided
        if ($searchModeParam !== null) {
            $this->setSearchMode($searchModeParam); // This also calls prepareEffectiveDelimiters and clearCache
            $wereSettingsTemporarilyChanged = true;
        }
        if ($beginRegexParam !== null || $endRegexParam !== null) {
            // If only one is provided, use instance default for the other
            $effectiveBegin = $beginRegexParam ?? $this->userProvidedBeginDelimiter;
            $effectiveEnd = $endRegexParam ?? $this->userProvidedEndDelimiter;
            $this->setBeginEndDelimiters($effectiveBegin, $effectiveEnd); // This also calls prepareEffectiveDelimiters and clearCache
            $wereSettingsTemporarilyChanged = true;
        }

        $effectiveSearchPos = $startPositionParam ?? $this->nextSearchPosition;

    
        // --- Cache Lookup ---
        $cacheKey = hash('sha256', $this->currentSearchMode->value . $this->effectiveBeginDelimiter . $this->effectiveEndDelimiter . $effectiveSearchPos);
        if (isset($this->borderMatchCache[$cacheKey])) {
            $this->logger->debug("Cache hit for getBorders.", ['key' => $cacheKey]);
            $cachedResult = $this->borderMatchCache[$cacheKey];
            if ($this->updateInstanceSearchPositionOnMatch && $cachedResult !== null && isset($cachedResult['end_end'])) {
                $this->setPosOfNextSearch($cachedResult['end_end']);
            }
            if ($wereSettingsTemporarilyChanged) {
                $this->setBeginEndDelimiters($originalInstanceUserBeginRegex, $originalInstanceUserEndRegex);
                $this->setSearchMode($originalInstanceSearchMode);
            }
            return $cachedResult;
        }
        $this->logger->debug("Cache miss for getBorders.", ['key' => $cacheKey]);

        // --- Delegate to specific find method ---
        $foundMatchArray = null;
        if ($this->currentSearchMode === SearchMode::SIMPLE_STRING_NO_NESTING) {
            $foundMatchArray = $this->findFirstSimpleStringMatch($effectiveSearchPos);
        } else {
            $foundMatchArray = $this->findNextSegmentRegex($effectiveSearchPos);
        }

        // --- Process result ---
        $finalResultForReturn = null;
        if ($foundMatchArray !== null) {
            $this->logger->debug("Match found by delegated find method.", ['details' => $foundMatchArray]);
            $newSegmentId = $this->addFoundSegmentToList($foundMatchArray);
            $finalResultForReturn = $this->foundSegmentsList[$newSegmentId]; // Return the stored segment
            $this->borderMatchCache[$cacheKey] = $finalResultForReturn;

            if ($this->updateInstanceSearchPositionOnMatch) {
                $this->setPosOfNextSearch($foundMatchArray['end_end']);
            }
        } else {
            $this->logger->info("No match found by delegated find method.");
            // stopOnMissingBothBorders is handled by findFirstSimpleStringMatch or findNextSegmentRegex if they return null
            $this->borderMatchCache[$cacheKey] = null; // Cache "not found"
            $finalResultForReturn = null;
        }

        if ($wereSettingsTemporarilyChanged) {
            $this->logger->debug("Restoring original instance settings after getBorders call.");
            $this->setBeginEndDelimiters($originalInstanceUserBeginRegex, $originalInstanceUserEndRegex);
            $this->setSearchMode($originalInstanceSearchMode);
        }
        return $finalResultForReturn;
    }

    private function addFoundSegmentToList(array $segmentData): int
    {
        $this->foundSegmentsList[] = $segmentData;
        $newId = count($this->foundSegmentsList) - 1;
        $this->currentSegmentId = $newId;
        $this->logger->debug("Segment added to list.", ['id' => $newId, 'data_preview' => array_slice($segmentData, 0, 2)]);
        return $newId;
    }

    // --- `getContent` and `getContent_user_func_recursive` still need full implementation ---
    public function getContent(
        ?string $beginRegex = null, ?string $endRegex = null,
        ?int $startPosition = null, SearchMode|string|null $searchMode = null
    ): string|false {
        $this->logger->debug("getContent called.", ['begin' => $beginRegex, 'end' => $endRegex, 'pos' => $startPosition, 'mode' => $searchMode]);
        $segmentData = $this->getBorders($beginRegex, $endRegex, $startPosition, $searchMode);

        if ($segmentData === null || !isset($segmentData['begin_end']) || !isset($segmentData['end_begin'])) {
            $this->logger->info("getContent: getBorders returned no valid segment.");
            return false;
        }
        if ($segmentData['end_begin'] < $segmentData['begin_end']) {
            $this->logger->warning("getContent: end_begin is before begin_end.", ['segment' => $segmentData]);
            return "";
        }
        $content = substr($this->content, $segmentData['begin_end'], $segmentData['end_begin'] - $segmentData['begin_end']);
        $this->logger->info("getContent extracted.", ['content_length' => strlen($content)]);
        return $content;
    }

    public function getContent_user_func_recursive(callable $userCallback): string|false
    {
        $this->logger->info("getContent_user_func_recursive called. Implementation pending full refactor.");
        // This method needs a complete rewrite to use the new findNextSegmentRegex in a truly recursive manner,
        // applying callbacks and re-assembling the string. It's the most complex part.
        // The old getContent_user_func_recursivePRIV gives hints but is hard to adapt directly.

        // For now, a conceptual placeholder that might work for non-nested simple cases:
        $output = "";
        $currentPos = $this->getPosOfNextSearch(); // Start from current position
        $originalNextSearchPos = $this->nextSearchPosition; // Backup

        while(true) {
            // Use instance's current delimiters and mode for this pass
            $segmentData = $this->getBorders(null, null, $currentPos, null); // Pass currentPos

            if ($segmentData === null) {
                $output .= substr($this->content, $currentPos); // Add rest of the string
                break;
            }

            // Text before the current match
            $output .= substr($this->content, $currentPos, $segmentData['begin_begin'] - $currentPos);

            // Prepare $cut array for the callback (simplified, adapt to your old structure if needed)
            $middleContent = substr($this->content, $segmentData['begin_end'], $segmentData['end_begin'] - $segmentData['begin_end']);
            $cut = [
                // 'before' and 'behind' in the callback context are tricky for a flat iteration.
                // The original callback expected $cut['before'] to be the part of the *current segment's* before-delimiter string.
                // This simplified loop doesn't easily provide that context without more state.
                // For now, let's assume the callback primarily works on 'middle'.
                'before' => $this->userProvidedBeginDelimiter, // Simplification: provide original delimiters
                'middle' => $middleContent,
                'behind' => $this->userProvidedEndDelimiter   // Simplification
            ];

            // TODO: How to pass $deepCount, $callsCount, $posList0, $originalSegmentContent correctly?
            // This requires a true recursive parsing approach, not just a flat loop.
            // The current $segmentData is $posList0 for this level.
            // $originalSegmentContent for the callback was $C->content in the old code, which was $content['middle']
            // passed to a new PregContentFinder instance. This is complex to replicate here without true recursion.

            // For a basic test of the callback mechanism on a flat structure:
            // We pass $segmentData as $posList0, and the $middleContent as $originalSegmentContent.
            // $deepCount and $callsCount would need to be managed by a truly recursive wrapper.
            // This is a placeholder and NOT a full recursive implementation.
            $transformedCut = $userCallback($cut, 0, 0, $segmentData, $middleContent);

            if (is_array($transformedCut) && isset($transformedCut['middle'])) {
                // Assuming callback returns modified $cut array, and we just use the middle.
                // Or if it's more complex, the callback might return a fully formed string segment.
                // Original code: $return = $content['before'] . $r1_cut; (where r1_cut was $cut['before'].$cut['middle'])
                // This implies callback should modify $cut['before'] and $cut['middle'] and $cut['behind']
                // and the class reassembles it.
                $output .= ($transformedCut['before'] ?? '') . $transformedCut['middle'] . ($transformedCut['behind'] ?? '');

            } elseif (is_string($transformedCut)) { // If callback returns just the string
                $output .= $transformedCut;
            } else {
                $this->logger->error("Callback in getContent_user_func_recursive returned unexpected type.", ['return_type' => gettype($transformedCut)]);
                $output .= $this->userProvidedBeginDelimiter . $middleContent . $this->userProvidedEndDelimiter; // Fallback
            }

            $currentPos = $segmentData['end_end'];
            if ($currentPos >= strlen($this->content)) {
                break;
            }
        }
        $this->nextSearchPosition = $originalNextSearchPos; // Restore original search position for instance
        return $output;
    }


    // TODO: Implement getContent_Before, getContent_Behind, getID, getContent_ByID
    // These will depend on how $this->foundSegmentsList and $this->currentSegmentId are managed.
    // Example for getContent_Before (needs $this->currentSegmentId to be set by getBorders/getContent)
    public function getContent_Before(): string|false
    {
        if ($this->currentSegmentId === null || !isset($this->foundSegmentsList[$this->currentSegmentId])) {
            // Attempt to find the "current" segment if not explicitly set,
            // by calling getBorders with current instance settings from $nextSearchPosition
            $this->logger->debug("getContent_Before: currentSegmentId not set, attempting to find current segment.");
            $segment = $this->getBorders(null, null, $this->nextSearchPosition, null);
            if ($segment === null || $this->currentSegmentId === null || !isset($this->foundSegmentsList[$this->currentSegmentId])) {
                $this->logger->info("getContent_Before: No current segment found to get 'before' content from.");
                return false; // Or empty string, depending on desired API behavior
            }
        }

        $currentSegment = $this->foundSegmentsList[$this->currentSegmentId];
        // 'begin_begin' is the start of the opening delimiter of the current segment
        // We need text from the end of the *previous* segment (or start of content)
        // up to the beginning of the *current* segment's opening delimiter.

        $previousSegmentEndPos = 0; // Start of content
        if ($this->currentSegmentId > 0 && isset($this->foundSegmentsList[$this->currentSegmentId - 1])) {
            $previousSegment = $this->foundSegmentsList[$this->currentSegmentId - 1];
            $previousSegmentEndPos = $previousSegment['end_end'];
        }

        $textBefore = substr($this->content, $previousSegmentEndPos, $currentSegment['begin_begin'] - $previousSegmentEndPos);
        $this->logger->debug("getContent_Before returning.", ['text_before' => $textBefore]);
        return $textBefore;
    }
    // Similarly for getContent_Behind, getID, etc.

}
