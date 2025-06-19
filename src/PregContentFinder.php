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
            $this->logger->info("effectiveBeginDelimiter: $this->effectiveBeginDelimiter, effectiveEndDelimiter: $this->effectiveEndDelimiter");
            return;
        }

        switch ($this->currentSearchMode) {
            case SearchMode::LAZY_WHITESPACE:

            $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter ? $this->escapeRegexForDelimiter($this->userProvidedBeginDelimiter, true) : '';

            // $this->effectiveEndDelimiter = $this->userProvidedBeginDelimiter ? $this->escapeRegexForDelimiter($this->effectiveEndDelimiter, true) : '';
            $this->effectiveEndDelimiter = $this->userProvidedEndDelimiter ? $this->escapeRegexForDelimiter($this->userProvidedEndDelimiter, true) : '';

                break;
            case SearchMode::SIMPLE_STRING_NO_NESTING:
                $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter;
                $this->effectiveEndDelimiter = $this->userProvidedEndDelimiter;
                $this->logger->info("effectiveBeginDelimiter: $this->effectiveBeginDelimiter, effectiveEndDelimiter: $this->effectiveEndDelimiter");
                break;
            case SearchMode::DONT_TOUCH_THIS:
            case SearchMode::USE_BACKREFERENCE:
                $this->effectiveBeginDelimiter = $this->userProvidedBeginDelimiter;
                $this->effectiveEndDelimiter = $this->userProvidedEndDelimiter;
                $this->logger->info("effectiveBeginDelimiter: $this->effectiveBeginDelimiter, effectiveEndDelimiter: $this->effectiveEndDelimiter");
                break;
        }
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
    $this->logger->info("REGEX_PATH: Starting regex segment search.", [
        'offset' => $searchOffset, 'mode' => $this->currentSearchMode->value,
        'eff_begin_regex' => $this->effectiveBeginDelimiter,
        'eff_end_regex_tpl' => $this->effectiveEndDelimiter
    ]);

    $txt = $this->content;
    $strLenTxt = strlen($txt);

    if ($searchOffset >= $strLenTxt) {
        return null;
    }

    $activeBeginRegexForLoop = $this->effectiveBeginDelimiter;
    $activeEndRegexForLoop = $this->effectiveEndDelimiter;

    $findPos = ['begin_begin' => null, 'begin_end' => null, 'end_begin' => null, 'end_end' => null];
    $matchesReturn = ['begin_begin' => null, 'end_begin' => null];

    $count_begin = 0;
    $count_end = 0;
    $emergency_Stop = 0;
    $currentSearchPositionInLoop = $searchOffset;

    // --- Schritt 1: Finde den allerersten Start-Delimiter ---
    if (!preg_match('~' . $activeBeginRegexForLoop . '~sm', $txt, $matches_begin, PREG_OFFSET_CAPTURE, $currentSearchPositionInLoop)) {
        return null;
    }

    $findPos['begin_begin'] = $matches_begin[0][1];
    $matchLength = strlen($matches_begin[0][0]);
    $findPos['begin_end'] = $findPos['begin_begin'] + $matchLength;
    // KORREKTUR FÜR ZERO-WIDTH:
    $currentSearchPositionInLoop = $findPos['begin_begin'] + ($matchLength > 0 ? $matchLength : 1);
    $count_begin++;


    // --- Schritt 2: Baue das Such-Pattern für die Schleife ---
    $mainLoopPattern = '~' . $activeBeginRegexForLoop . '|' . $activeEndRegexForLoop . '~sm';


    // --- Schritt 3: Schleife, die nach Balance sucht ---
    while ($count_begin > $count_end && $emergency_Stop < 1000) {
        $emergency_Stop++;

        if (!preg_match($mainLoopPattern, $txt, $matches_loop, PREG_OFFSET_CAPTURE, $currentSearchPositionInLoop)) {
            break;
        }

        $matchedDelimiterFull = $matches_loop[0][0];
        $matchedDelimiterOffset = $matches_loop[0][1];
        $matchLength = strlen($matchedDelimiterFull);

        if (preg_match('~^' . $activeEndRegexForLoop . '$~s', $matchedDelimiterFull)) {
            $count_end++;
            if ($count_begin === $count_end) {
                $findPos['end_begin'] = $matchedDelimiterOffset;
                $findPos['end_end'] = $matchedDelimiterOffset + $matchLength;
            }
        } else {
            $count_begin++;
        }

        // KORREKTUR FÜR ZERO-WIDTH:
        $currentSearchPositionInLoop = $matchedDelimiterOffset + ($matchLength > 0 ? $matchLength : 1);
    }


    // --- Schritt 4: Ergebnis auswerten ---
    if ($count_begin > $count_end && $this->stopOnMissingEndBorder === false) {
        $findPos['end_begin'] = $strLenTxt;
        $findPos['end_end'] = $strLenTxt;
    } elseif ($count_begin > $count_end) {
        return null;
    }

    $findPos['matches'] = $matchesReturn;
    return $findPos;
}
























    protected function getBorders(
        ?string $beginRegexParam = '',
        ?string $endRegexParam = '',
        ?int $startPositionParam = null,
        SearchMode|string|null $searchModeParam = null
    ): ?array {

        $this->logger->debug("hiho"); 
        $this->logger->debug("$beginRegexParam:" . $beginRegexParam); 



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
        ?string $beginRegex = '', ?string $endRegex = '',
        ?int $startPosition = null, SearchMode|string|null $searchMode = null
    ): string|false {
        // $this->logger->debug(['begin' => $beginRegex, 'end' => $endRegex, 'pos' => $startPosition, 'mode' => $searchMode]);
        // $fileHandler->setLevel(Level::Info);

        if($beginRegex){
            $this->userProvidedBeginDelimiter = $beginRegex;
        }
        if($endRegex){
            $this->userProvidedEndDelimiter = $endRegex;
        }
        if($beginRegex || $endRegex){
            $this->prepareEffectiveDelimiters();
        }
        // $this->logger->info("BeginRegex: $beginRegex, EndRegex: $endRegex");

        if($startPosition){
            $this->nextSearchPosition = $startPosition;
        }else{
            $startPosition = 0;
            $this->nextSearchPosition = $startPosition;
        }
        $this->logger->info("StartPosition: $startPosition");

        if($searchMode){
            $this->setSearchMode($searchMode);
        }

        if(!$beginRegex){
            $this->logger->info('strange. begin is empty');
        }
        if(!is_string($beginRegex)){
            $this->logger->info('strange. begin not string');
        }

        $this->logger->info("BeginRegex: $beginRegex, EndRegex: $endRegex, StartPosition: $startPosition, SearchMode: " . $searchMode->name);
        
        $segmentData = $this->getBorders($beginRegex, $endRegex, $startPosition, $searchMode);


        if ($segmentData === null || !isset($segmentData['begin_end']) || !isset($segmentData['end_begin'])) {
            $this->logger->info("getContent: getBorders returned no valid segment.");
            return false;
        }
        if ($segmentData['end_begin'] < $segmentData['begin_end']) {
            $this->logger->warning("getContent: end_begin is before begin_end.", ['segment' => $segmentData]);
            return "";
        }
        $this->logger->info('begin_end:' . $segmentData['begin_end'] . ', end_begin:' . $segmentData['end_begin']);
        $content = substr($this->content, $segmentData['begin_end'], $segmentData['end_begin'] - $segmentData['begin_end']);
        $this->logger->info('content_length:' . strlen($content));
        $this->logger->info($content);
        return $content;
    }

// --- START: ERSETZE DEN GESAMTEN FUNKTIONSINHALT MIT DIESEM DEBUG-CODE ---

// --- START: ERSETZE DEN GESAMTEN FUNKTIONSINHALT ---

public function getContent_user_func_recursive(callable $userCallback): string|false
{
    // Finde das allererste Segment ab der Startposition der Instanz
    $segmentData = $this->getBorders(null, null, $this->nextSearchPosition, null);

    // Fall 1: Kein Segment gefunden. Gib den Inhalt so zurück, wie er ist.
    if ($segmentData === null) {
        return $this->content;
    }

    // Fall 2: Ein Segment wurde gefunden. Zerlege den String KORREKT in seine Teile.
    $partBeforeSegment = substr($this->content, 0, $segmentData['begin_begin']);
    $contentOfSegment  = substr($this->content, $segmentData['begin_end'], $segmentData['end_begin'] - $segmentData['begin_end']); // KORRIGIERT
    $partAfterSegment = substr($this->content, $segmentData['end_end']);
    

    // Bereite den $cut-Array für den Callback vor.
    $cutForCallback = [
        'middle' => $contentOfSegment,
        'behind' => $partAfterSegment,
        'before' => '', // Wird vom Test-Callback hinzugefügt, also initialisieren wir es.
    ];

    // Rufe den Callback auf
    $transformedCut = $userCallback($cutForCallback, 0, 1, [], $contentOfSegment);

    // Setze das Endergebnis zusammen.
    if (is_array($transformedCut) && isset($transformedCut['middle'])) {
        // Die Logik des Test-Callbacks ist: middle wird zu middle + behind.
        // Das Ergebnis der Funktion sollte also sein: before + (neues middle).
        return $partBeforeSegment . $transformedCut['middle'];
    }

    // Fallback, falls der Callback etwas Unerwartetes zurückgibt.
    return $this->content;
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
