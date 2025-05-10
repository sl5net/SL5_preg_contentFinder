<?php
declare(strict_types=1); // Strikte Typen für die neue Klasse!

namespace SL5\PregContentFinder; // Beibehaltung des Namespaces für Kompatibilität

// Das SearchMode Enum (kann in derselben Datei oder einer separaten Datei sein)
// Wenn separat, dann: use SL5\PregContentFinder\SearchMode;
enum SearchMode: string
{
  case LAZY_WHITESPACE = 'lazyWhiteSpace';
  case DONT_TOUCH_THIS = 'dontTouchThis';
  case USE_BACKREFERENCE = 'use_BackReference_IfExists_()$1${1}';
  case SIMPLE_STRING_NO_NESTING = 'simpleStringNoNesting';
}

class PregContentFinderV2 // Oder Sie nennen sie weiterhin PregContentFinder und arbeiten im neuen Branch
{
  // PHP 8.0+ Constructor Property Promotion für unveränderliche Werte
  // PHP 8.1+ readonly für $content, wenn es nach dem Konstruktor nicht mehr geändert wird.
  // Wenn $content modifizierbar sein soll (z.B. durch interne Operationen, was aber
  // die alte Klasse nicht primär tat), dann nicht readonly.
  // Für den Moment gehen wir davon aus, dass der ursprüngliche Content unverändert bleibt
  // und Operationen einen neuen String oder Ergebnisobjekte zurückgeben.

  private ?string $currentBeginRegex = null;
  private ?string $currentEndRegex = null;
  private SearchMode $currentSearchMode; // Wird im Konstruktor gesetzt
  private int $nextSearchPosition = 0;

  // Original-Delimiter, falls der Fast-Path-Check sie unmodifiziert braucht
  private ?string $originalUnescapedBeginDelimiter = null;
  private ?string $originalUnescapedEndDelimiter = null;

  // Zukünftige Properties für Ergebnisse/Cache (noch nicht in Phase 1 implementiert)
  // private array $foundItems = [];
  // private array $borderCache = [];

  public function __construct(
    public readonly string $content, // Readonly, wird nicht mehr geändert
    string|array|null $beginRegexOrArray = null,
    ?string $endRegex = null,
    SearchMode|string $initialSearchMode = SearchMode::LAZY_WHITESPACE // Standardmodus
  ) {
    $this->setSearchMode($initialSearchMode); // Setzt $this->currentSearchMode
    $this->setBeginEnd_RegEx($beginRegexOrArray, $endRegex);
    // $this->pos_of_next_search wurde früher im Konstruktor nicht explizit gesetzt,
    // sondern durch getPosOfNextSearch() mit 0 initialisiert.
    // Wir initialisieren es hier mit 0 als Property-Default oder hier.
    $this->nextSearchPosition = 0;
  }

  /**
   * Sets the regular expressions for the beginning and end delimiters.
   * Can accept an array [begin, end] as the first argument.
   */
  public function setBeginEnd_RegEx(string|array|null $begin = null, ?string $end = null): void
  {
    $resolvedBegin = null;
    $resolvedEnd = null;

    if (is_array($begin)) {
      if (count($begin) !== 2) {
        throw new \InvalidArgumentException(
          "If begin regex is an array, it must contain exactly two elements: [beginRegex, endRegex]."
        );
      }
      $resolvedBegin = $begin[0];
      $resolvedEnd = $begin[1];
    } else {
      $resolvedBegin = $begin;
      $resolvedEnd = $end;
    }

    // Store original unescaped delimiters if they are strings (for SIMPLE_STRING_NO_NESTING checks)
    if (is_string($resolvedBegin)) {
      $this->originalUnescapedBeginDelimiter = $resolvedBegin;
    }
    if (is_string($resolvedEnd)) {
      $this->originalUnescapedEndDelimiter = $resolvedEnd;
    }

    // Set the potentially regex-interpreted delimiters
    // In der alten Klasse gab es setRegEx_begin/end, die $this->regEx_begin/end setzten.
    // Wir weisen hier direkt zu, wenn sie nicht null sind.
    if ($resolvedBegin !== null) {
      if (!is_string($resolvedBegin)) { // Früher in setRegEx geprüft
        throw new \InvalidArgumentException("Begin delimiter must be a string.");
      }
      $this->currentBeginRegex = $resolvedBegin;
    }
    if ($resolvedEnd !== null) {
      if (!is_string($resolvedEnd)) { // Früher in setRegEx geprüft
        throw new \InvalidArgumentException("End delimiter must be a string.");
      }
      $this->currentEndRegex = $resolvedEnd;
    }
  }

  /**
   * Sets the search mode.
   */
  public function setSearchMode(SearchMode|string $mode): void
  {
    if (is_string($mode)) {
      $resolvedMode = SearchMode::tryFrom($mode);
      if ($resolvedMode === null) {
        $validModes = implode(', ', array_map(fn($case) => $case->value, SearchMode::cases()));
        throw new \InvalidArgumentException(
          "Invalid search mode string provided: '{$mode}'. Valid values are: {$validModes}"
        );
      }
      $this->currentSearchMode = $resolvedMode;
    } elseif ($mode instanceof SearchMode) {
      $this->currentSearchMode = $mode;
    } else {
      throw new \InvalidArgumentException("Invalid type for search mode provided. Expected string or SearchMode enum instance.");
    }
  }

  /**
   * Gets the current search mode as a string.
   */
  public function getSearchMode(): string
  {
    return $this->currentSearchMode->value;
  }

  /**
   * Gets the currently set beginning regular expression.
   * (Getter für Tests und interne Konsistenz)
   */
  public function getRegEx_begin(): ?string // Name beibehalten für Testkompatibilität
  {
    return $this->currentBeginRegex;
  }

  /**
   * Gets the currently set ending regular expression.
   */
  public function getRegEx_end(): ?string // Name beibehalten
  {
    return $this->currentEndRegex;
  }


  /**
   * Sets the starting position for the next search.
   */
  public function setPosOfNextSearch(int $position): void
  {
    if ($position < 0) {
      throw new \InvalidArgumentException("Search position cannot be negative. Given: " . $position);
    }
    if ($position > strlen($this->content)) {
      // Optional: Fehler werfen oder Position auf strlen setzen?
      // Ihre alte Klasse hat das nicht explizit geprüft.
      // Für Robustheit:
      throw new \InvalidArgumentException("Search position ({$position}) cannot be beyond content length (" . strlen($this->content) . ").");
    }
    $this->nextSearchPosition = $position;
  }

  /**
   * Gets the starting position for the next search.
   */
  public function getPosOfNextSearch(): int
  {
    return $this->nextSearchPosition;
  }

  // --- Placeholder für Kernmethoden (Phase 2-4) ---
  public function getContent(
    ?string $beginRegex = null,
    ?string $endRegex = null,
    ?int $startPosition = null,
    SearchMode|string|null $searchMode = null
  ): string|false {
    // TODO: Implement logic using getBorders
    // For Phase 1, this can just return false or throw NotImplementeException
    // to make tests that call it fail explicitly until implemented.
    // Or, for initial tests focusing on setters/getters, this won't be called yet.
    throw new \LogicException(__METHOD__ . " not yet implemented for V2.");
    // return false;
  }

  public function getBorders(
    ?string $beginRegex = null,
    ?string $endRegex = null,
    ?int $startPosition = null,
    SearchMode|string|null $searchMode = null
  ): ?array {
    // TODO: Core logic to be implemented
    throw new \LogicException(__METHOD__ . " not yet implemented for V2.");
    // return null;
  }

  public function getContent_user_func_recursive(
    callable $userCallback
    // Die alte Version hatte mehr optionale Parameter hier.
    // Für Phase 1 lassen wir die komplexe Implementierung weg.
  ): string|false {
    // TODO: Core recursive logic
    throw new \LogicException(__METHOD__ . " not yet implemented for V2.");
    // return false;
  }

  // ... (getContent_Before, getContent_Behind etc. als Placeholder)

}
