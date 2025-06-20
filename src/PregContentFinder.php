<?php
declare(strict_types=1);
namespace SL5\PregContentFinder;

// PSR-3 Log Interfaces importieren
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use SL5\PregContentFinder\Tests\PHPUnit\FilenameProcessor;
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
    public bool $isUniqueSignUsed = false; 
    public readonly string $content;

    public ?string $userProvidedBeginDelimiter;
    public ?string $userProvidedEndDelimiter;

    private string|array|null $beginRegexOrArray; 
    private ?string $endRegex; 

    public ?string $effectiveBeginDelimiter;
    public string $effectiveEndDelimiter;

    public SearchMode $currentSearchMode;
    private int $nextSearchPosition = 0;

    private bool $stopOnMissingEndBorder = false;
    private bool $stopOnMissingBothBorders = true;
    private bool $updateInstanceSearchPositionOnMatch = true;

    

    private array $borderMatchCache = [];
    private array $foundSegmentsList = [];
    private ?int $currentSegmentId = null;
    protected string $logFilePath = ''; 
    protected Logger $logger; 
    protected ?StreamHandler $logFileHandler = null; // Property to store the handler

    public function __construct(
        string $content,
        string|array|null $beginRegexOrArray = '' ,
        ?string $endRegex = '',
        SearchMode|string $initialSearchMode = SearchMode::LAZY_WHITESPACE
    ) {

        $this->loggerSetUp();
        $this->logger->info("Logger setup complete. Logging to: {$this->logFilePath}");

        // $this->logger->info('greetings from PregContentFinder :) ');

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
        /* $shortFileName = str_replace('.php','', __FILE__); */
        $shortFileName = basename(__FILE__, '.php');
        $fileDir = dirname(__FILE__);


        $logFileName = $fileDir . '/' . $shortFileName.'.log';
        $this->logFilePath = $logDir . '/' . $logFileName;
        // Handler für die Log-Datei
        // $fileHandler = new StreamHandler($this->logFilePath, Level::Info); 
        // Create the handler and store it in the property

        $this->logFileHandler = new StreamHandler($this->logFilePath, Level::Info);

        // $fileHandler->setLevel(Level::Info);
        // $fileHandler->setLevel(Level::Debug);
        // $fileHandler->setLevel(Level::Error);

        // Formatter für die Log-Ausgabe
        // Format: Filename:Line[FunctionName()]LEVEL: Message Context Extra
        // %extra.file% und %extra.line% kommen vom IntrospectionProcessor
        $outputFormat = $shortFileName . ":%extra.line%[%extra.function%()]%level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($outputFormat, null, true, true); // allowInlineLineBreaks, ignoreEmptyContextAndExtra

        $this->logFileHandler->setFormatter($formatter); 
        $this->logFileHandler->setLevel(Level::Info); // Level vor dem pushHandler setzen ist gute Praxis

        // Übergeben Sie die Klassen-Eigenschaft an den Logger
        $this->logger->pushHandler($this->logFileHandler); 
        $this->logger->pushProcessor(new IntrospectionProcessor(Level::Info));

        $this->logger->info("---------------");
        // Die Zeile $this->logFileHandler->setLevel(Level::Info); wurde nach oben verschoben, kann aber auch hier bleiben.
        $this->logger->info("================ ");


    }


    public function helloWorld(): string
    {
        return 'Hello World!';
    }



    // Füge diese Methode zur Klasse hinzu
    public function getUniqueSignExtreme(): ?string
    {
        if (!$this->isUniqueSignUsed) {
            // Funktion ist nicht aktiviert, um keine unnötige Performance zu kosten.
            $this->logger->warning("getUniqueSignExtreme called but isUniqueSignUsed is false.");
            return null;
        }

        // Wir testen eine Reihe unwahrscheinlicher ASCII-Steuerzeichen.
        // Das erste, das nicht im String vorkommt, wird unser "sicheres" Zeichen.
        for ($i = 1; $i <= 31; $i++) {
            $char = chr($i);
            if (strpos($this->content, $char) === false) {
                $this->logger->info("Found unique sign for protection.", ['char_code' => $i]);
                return $char;
            }
        }
        
        // Fallback, falls der String alle Steuerzeichen enthält (extrem unwahrscheinlich)
        $this->logger->error("Could not find a unique sign in the content string.");
        return null; 
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
    public function setBeginEnd_RegEx(string|array|null $begin = '', ?string $end = ''): void
    {
        $this->logger->warning(__METHOD__ . ' is deprecated. Use setBeginEndDelimiters() instead.');
        $this->setBeginEndDelimiters( $begin, $end);
        // Die alte Methode gab true zurück, aber void ist für Setter besser.
        // Um die alte API exakt nachzubilden (falls Tests das prüfen): return true;
        // Aber da die neue `void` ist, ist Konsistenz vielleicht besser.
        // Da Ihre alte Methode laut PHPDoc `bool always returns true - no meaning` war, ist `void` hier okay.
    }

    public function setBeginEndDelimiters(string|array|null $begin = '', ?string $end = ''): void
    {
        $this->logger->info("setBeginEndDelimiters called.", ['begin' => $begin, 'end' => $end]);
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
        // $fileHandler->setLevel(Level::Error);
        
        
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

            // *** THE FIX IS HERE ***
            // If the user has already provided delimiters, we must re-prepare them
            // now that the search mode has changed.
            if (isset($this->userProvidedBeginDelimiter)) {
                $this->prepareEffectiveDelimiters();
            }

            $this->clearCacheAndResults();
        }
    }

private function prepareEffectiveDelimiters(): void
{
    if (!isset($this->userProvidedBeginDelimiter) || !isset($this->userProvidedEndDelimiter)) {
        // This is a critical state, but for now we ensure properties exist to avoid errors.
        $this->effectiveBeginDelimiter = '';
        $this->effectiveEndDelimiter = '';
        $this->logger->critical("Cannot prepare effective delimiters: user-provided delimiters are not set.");
        return;
    }

    // Use local variables for preparation
    $begin = $this->userProvidedBeginDelimiter;
    $end = $this->userProvidedEndDelimiter;

    switch ($this->currentSearchMode) {
        case SearchMode::LAZY_WHITESPACE:
            $this->effectiveBeginDelimiter = $this->escapeRegexForDelimiter($begin, true);
            $this->effectiveEndDelimiter = $this->escapeRegexForDelimiter($end, true);
            break;
        
// in prepareEffectiveDelimiters:

        case SearchMode::SIMPLE_STRING_NO_NESTING:
            // You only set effectiveBeginDelimiter
            $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter;
            // But you never set effectiveEndDelimiter for this case!
            $this->logger->info("effectiveBeginDelimiter: $this->effectiveBeginDelimiter, effectiveEndDelimiter: $this->effectiveEndDelimiter");
            break; // The break happens before the logger call at the end

        case SearchMode::DONT_TOUCH_THIS:
        case SearchMode::USE_BACKREFERENCE:
            // Use the provided regex patterns as-is.
            $this->effectiveBeginDelimiter = $begin;
            $this->effectiveEndDelimiter = $end;
            break;
    }

    // This log call is now safe because all cases above initialize both properties.
    $this->logger->debug("Effective delimiters prepared.", [
        'mode' => $this->currentSearchMode->value,
        'effective_begin' => $this->effectiveBeginDelimiter,
        'effective_end' => $this->effectiveEndDelimiter
    ]);
}


    private function escapeRegexForDelimiter(string|null $string = '', bool $makeWhitespaceFlexible = false, string $delimiterChar = '~'): string
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
        $this->logger->info("Cache and results list cleared.");
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
        $this->logger->info("Content: $this->content, EndRegex: $endRegex, BeginEndPos: $beginEndPos");

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
    // Part 1: The Guard Clause for Empty Delimiters. This is essential hygiene.
    if (empty($this->effectiveBeginDelimiter) && empty($this->effectiveEndDelimiter)) {
        return null;
    }

    $this->logger->info("DEFINITIVE_ENGINE: Starting unified search.", [
        'offset' => $searchOffset, 'begin' => $this->effectiveBeginDelimiter, 'end' => $this->effectiveEndDelimiter
    ]);

    $txt = $this->content;
    $strLenTxt = strlen($txt);

    // Part 2: The Initial Find. This is standard and correct.
    if (!preg_match('~' . $this->effectiveBeginDelimiter . '~sm', $txt, $matches_begin, PREG_OFFSET_CAPTURE, $searchOffset)) {
        return null;
    }

    $findPos = [
        'begin_begin' => $matches_begin[0][1],
        'begin_end'   => $matches_begin[0][1] + strlen($matches_begin[0][0]),
        'end_begin'   => null,
        'end_end'     => null,
        'matches'     => ['begin_matches' => $matches_begin]
    ];
    
    $balance = 1;
    $currentSearchPosition = $findPos['begin_end'];

    // *** Part 3: The Definitive Balancing Loop ***
    // This loop solves both the greedy matching and infinite loop problems.
    while ($balance > 0 && $currentSearchPosition < $strLenTxt) {
        // Find the position of the very next available opening delimiter.
        $foundBegin = preg_match('~' . $this->effectiveBeginDelimiter . '~sm', $txt, $match_b, PREG_OFFSET_CAPTURE, $currentSearchPosition);
        $posBegin = $foundBegin ? $match_b[0][1] : PHP_INT_MAX;

        // Find the position of the very next available closing delimiter.
        $foundEnd = preg_match('~' . $this->effectiveEndDelimiter . '~sm', $txt, $match_e, PREG_OFFSET_CAPTURE, $currentSearchPosition);
        $posEnd = $foundEnd ? $match_e[0][1] : PHP_INT_MAX;

        // If we can't find any more delimiters at all, we're done.
        if (!$foundBegin && !$foundEnd) {
            break;
        }

        // Compare the positions to see which delimiter comes next chronologically.
        if ($posBegin < $posEnd) {
            // The next delimiter is an opening one.
            $balance++;
            // CRITICAL: Advance the pointer robustly.
            $currentSearchPosition = $match_b[0][1] + max(1, strlen($match_b[0][0]));
        } else {
            // The next delimiter is a closing one.
            $balance--;
            // CRITICAL: Advance the pointer robustly.
            $currentSearchPosition = $match_e[0][1] + max(1, strlen($match_e[0][0]));
            if ($balance === 0) {
                // We found our matching closing delimiter.
                $findPos['end_begin'] = $match_e[0][1];
                $findPos['end_end'] = $currentSearchPosition;
            }
        }
    }

    // Part 4: Handle unclosed blocks using our previously established, correct logic.
    if ($balance > 0) {
        if ($this->stopOnMissingEndBorder) return null;
        
        $this->logger->info("DEFINITIVE_ENGINE: Unbalanced block detected, applying special rule for NIXNIX case.");
        
        $subContent = substr($txt, $findPos['begin_end']);
        if (preg_match_all('~' . $this->effectiveEndDelimiter . '~sm', $subContent, $all_ends, PREG_OFFSET_CAPTURE)) {
            $last_end_match = end($all_ends[0]);
            $absolute_offset = $findPos['begin_end'] + $last_end_match[1];
            
            // This defines the content's end *before* the last found '}'
            $findPos['end_begin'] = $absolute_offset; 
            $findPos['end_end'] = $absolute_offset + strlen($last_end_match[0]);
        } else {
            // If no closing delimiter is found at all, then the content goes to the end.
            $findPos['end_begin'] = $strLenTxt;
            $findPos['end_end'] = $strLenTxt;
        }
    }

    return $findPos;
}




     


















protected function getBorders(
    ?string $beginRegexParam = null,
    ?string $endRegexParam = null,
    ?int $startPositionParam = null,
    SearchMode|string|null $searchModeParam = null
): ?array {
    
    // Temporäre Einstellungen speichern
    $originalSearchMode = $this->currentSearchMode;
    $originalBeginDelim = $this->userProvidedBeginDelimiter;
    $originalEndDelim = $this->userProvidedEndDelimiter;
    $settingsChanged = false;

    // *** KORREKTE, SAUBERE LOGIK ***
    // 1. Zuerst den Modus setzen, falls ein neuer übergeben wird.
    if ($searchModeParam !== null && ($searchModeParam instanceof SearchMode || $this->currentSearchMode->value !== $searchModeParam)) {
        $this->setSearchMode($searchModeParam);
        $settingsChanged = true;
    }
    
    // 2. Danach die Delimiter setzen, falls neue übergeben werden.
    // Das ruft intern prepareEffectiveDelimiters im Kontext des NEUEN Modus auf.
    if ($beginRegexParam !== null || $endRegexParam !== null) {
        $this->setBeginEndDelimiters($beginRegexParam, $endRegexParam);
        $settingsChanged = true;
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
            $this->logger->debug("return cachedResult");    
            return $cachedResult;
        }
        $this->logger->debug("Cache miss for getBorders.", ['key' => $cacheKey]);

        // --- Delegate to specific find method ---
        $foundMatchArray = null;
        if ($this->currentSearchMode === SearchMode::SIMPLE_STRING_NO_NESTING) {
            $foundMatchArray = $this->findFirstSimpleStringMatch($effectiveSearchPos);
        } else {
            $this->logger->debug("findNextSegmentRegex called.");
            $foundMatchArray = $this->findNextSegmentRegex($effectiveSearchPos);
            $this->logger->debug(var_export($foundMatchArray, true));

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


        // Am Ende, den Originalzustand wiederherstellen, falls er geändert wurde
        if ($settingsChanged) {
            $this->setSearchMode($originalSearchMode);
            $this->setBeginEndDelimiters($originalBeginDelim, $originalEndDelim);
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

public function getContent(
    ?string $beginRegex = null, 
    ?string $endRegex = null,
    ?int $startPosition = null, 
    SearchMode|string|null $searchMode = null
): string|false {

    $isTemporarySearch = ($beginRegex !== null || $endRegex !== null || $searchMode !== null);


    $this->logger->info("getContent called. Delegating to getBorders.", [
        'begin' => $beginRegex, 'end' => $endRegex, 'pos' => $startPosition, 'mode' => $searchMode
    ]);
    
    if ($isTemporarySearch) {
        $this->logger->info("getContent: Starting temporary search. Saving instance state.");
        $originalSearchMode = $this->currentSearchMode;
        $originalBeginDelim = $this->userProvidedBeginDelimiter;
        $originalEndDelim = $this->userProvidedEndDelimiter;
        $originalNextPos = $this->nextSearchPosition;
    }

    // KORRIGIERTE LOGIK:
    // Wenn ein temporärer Modus oder temporäre Delimiter übergeben werden,
    // müssen wir sicherstellen, dass sie in der richtigen Reihenfolge verarbeitet werden.
    // getBorders ist dafür ausgelegt, dies zu handhaben. Wir müssen nur die Parameter durchreichen.


    // Zuerst den Modus setzen, falls ein temporärer übergeben wurde.
    if ($searchMode !== null) {
        $this->setSearchMode($searchMode);
    }
    // Danach die Delimiter setzen, die den korrekten Modus nutzen.
    if ($beginRegex !== null || $endRegex !== null) {
        $this->setBeginEndDelimiters($beginRegex, $endRegex);
    }

    
    $effectiveStartPosition = $startPosition ?? $this->getPosOfNextSearch();
    if($startPosition !== null) {
        $this->setPosOfNextSearch($startPosition);
    }
    
    // Wir übergeben die Parameter direkt an getBorders.
    // getBorders wird zuerst den Modus setzen (falls vorhanden) und dann die Delimiter.
    // Dadurch wird sichergestellt, dass die Delimiter im Kontext des richtigen Modus vorbereitet werden.
    $segmentData = $this->getBorders($beginRegex, $endRegex, $effectiveStartPosition, $searchMode);

    if ($segmentData === null) {
        $this->logger->info("getContent: getBorders returned no segment.");
        return false;
    }

    $content = substr(
        $this->content, 
        $segmentData['begin_end'], 
        $segmentData['end_begin'] - $segmentData['begin_end']
    );
    
    $this->logger->info("getContent: Found content.", ['content' => $content]);
    return $content;
}







public function getContent_user_func_recursive(callable $userCallback, int $currentDepth = 0): string
{
    // Use a local finder for the iteration at this depth to prevent state conflicts.
    $localFinder = new self($this->content);
    $localFinder->setSearchMode($this->currentSearchMode);
    $localFinder->setBeginEndDelimiters($this->userProvidedBeginDelimiter, $this->userProvidedEndDelimiter);
    
    $resultParts = [];
    $lastPosition = 0;

    // The loop finds all top-level segments in the content for the current depth.
    while (($segmentData = $localFinder->getBorders(null, null, $lastPosition, null)) !== null) {
        
        // 1. Add the plain text part BEFORE the current segment.
        $resultParts[] = substr($this->content, $lastPosition, $segmentData['begin_begin'] - $lastPosition);

        // 2. Isolate the raw content of the current segment.
        $rawSegmentContent = substr($this->content, $segmentData['begin_end'], $segmentData['end_begin'] - $segmentData['begin_end']);

        // 3. *** THE CONTEXT-AWARE RECURSION STEP ***
        $recursivelyProcessedContent = '';
        
        // The "circuit breaker" for infinite loops.
        if ($rawSegmentContent === $this->content) {
            $this->logger->debug("Recursive function circuit breaker: inner content is identical to outer. Halting recursion for this branch.");
            $recursivelyProcessedContent = $rawSegmentContent;
        } else {
            // It's safe to dive deeper. Create a new instance for the inner content.
            $innerFinderRecursive = new self($rawSegmentContent);
            $innerFinderRecursive->setSearchMode($this->currentSearchMode);
            $innerFinderRecursive->setBeginEndDelimiters($this->userProvidedBeginDelimiter, $this->userProvidedEndDelimiter);
            
            // Pass the incremented depth to the recursive call.
            $recursivelyProcessedContent = $innerFinderRecursive->getContent_user_func_recursive($userCallback, $currentDepth + 1);
        }
        

        // 4. Apply the user's callback to the fully resolved inner content.
        $cutForCallback = ['middle' => $recursivelyProcessedContent];
        $callbackResult = $userCallback($cutForCallback, $currentDepth, 0, $segmentData, $recursivelyProcessedContent);


        // FLEXIBLE RETURN TYPE HANDLING 
        // Check if the callback returned the array or just the string.
        // Handle flexible return types from the callback.
        if (is_array($callbackResult) && isset($callbackResult['middle'])) {
            $finalTransformedContent = $callbackResult['middle'];
        } else {
            $finalTransformedContent = $callbackResult;
        }

        
        // 5. Add the final, transformed part to our result.
        $resultParts[] = $finalTransformedContent;

        // 6. Update our position to search for the NEXT segment.
        $lastPosition = $segmentData['end_end'];
    }

    // 7. Add the final trailing part of the string.
    $resultParts[] = substr($this->content, $lastPosition);

    // 8. Join all pieces back together.
    return implode('', $resultParts);
}










// Füge diese Methoden in die PregContentFinder-Klasse ein

public function getContent_Next(): string|false
{
    // getBorders nutzt intern $this->nextSearchPosition, wenn kein Startpunkt übergeben wird.
    // Nach einem erfolgreichen getContent() steht dieser Wert genau hinter dem letzten Treffer.
    $segmentData = $this->getBorders(); 

    if ($segmentData === null) {
        return false;
    }

    return substr(
        $this->content,
        $segmentData['begin_end'],
        $segmentData['end_begin'] - $segmentData['begin_end']
    );
}

public function getContent_Prev(): string|false
{
    if ($this->currentSegmentId === null || $this->currentSegmentId < 1) {
        return false; // Es gibt kein vorheriges Segment
    }

    // Gehe zum vorherigen Segment
    $this->currentSegmentId--;
    $segmentData = $this->foundSegmentsList[$this->currentSegmentId];

    // WICHTIG: Setze die globale Suchposition zurück, damit zukünftige
    // Aufrufe von getContent() oder getContent_Next() wieder von hier starten.
    $this->setPosOfNextSearch($segmentData['end_end']);

    return substr(
        $this->content,
        $segmentData['begin_end'],
        $segmentData['end_begin'] - $segmentData['begin_end']
    );
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

    public function getContent_Behind(): string|false
    {
        // Schritt 1: Prüfen, ob bereits ein Segment gefunden wurde.
        // Wenn nicht, müssen wir die Suche nach dem ersten Segment selbst anstoßen.
        if ($this->currentSegmentId === null) {
            $this->logger->debug("getContent_Behind: No current segment set. Searching for the first segment now.");
            
            // getBorders() ohne Parameter nutzt die aktuellen Instanz-Einstellungen
            // und sucht ab der Position $this->nextSearchPosition (die anfangs 0 ist).
            $segment = $this->getBorders();
            
            // Wenn selbst die Suche nichts findet, gibt es auch nichts,
            // worauf etwas folgen könnte.
            if ($segment === null) {
                $this->logger->info("getContent_Behind: No segment found, so no 'behind' content exists.");
                return false;
            }
        }
        
        // Schritt 2: Die Koordinaten des aktuellen Segments abrufen.
        // An dieser Stelle ist garantiert, dass ein Segment gefunden wurde (entweder
        // durch einen vorherigen Aufruf oder durch uns in Schritt 1).
        $currentSegment = $this->foundSegmentsList[$this->currentSegmentId];
        
        // Die Eigenschaft 'end_end' enthält die Position direkt NACH dem schließenden Delimiter.
        $startPositionOfBehindContent = $currentSegment['end_end'];
        
        // Schritt 3: Den Teilstring ab dieser Position extrahieren.
        $behindContent = substr($this->content, $startPositionOfBehindContent);
        
        $this->logger->info("getContent_Behind: Successfully extracted 'behind' content.", ['content' => $behindContent]);
        
        return $behindContent;
    }


    // Similarly for getContent_Behind, getID, etc.

}
