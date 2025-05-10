<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder; // Die refactorte Klasse
use SL5\PregContentFinder\SearchMode;

class PregContentFinderPhase1Test extends \PHPUnit\Framework\TestCase
{
    public function testConstructorSetsContent(): void
    {
        $content = "Hello World";
        // MINDESTENS ZWEI Argumente für den Konstruktor benötigt
        $finder = new PregContentFinder($content, "[", "]"); // Bsp-Delimiter
        $this->assertSame($content, $finder->content);
        // Testen Sie auch, ob die Delimiter und der Modus korrekt initial gesetzt wurden
        $this->assertSame("[", $finder->getUserProvidedBeginDelimiter()); // Neuer Getter aus meinem Vorschlag
        $this->assertSame("]", $finder->getUserProvidedEndDelimiter());   // Neuer Getter
        $this->assertSame(SearchMode::LAZY_WHITESPACE->value, $finder->getSearchMode()); // Standardmodus
    }

    public function testSetBeginEndDelimitersWithStrings(): void // Umbenannt von testSetBeginEndRegEx...
    {
        // Konstruktor benötigt Delimiter, auch wenn wir sie gleich überschreiben
        $finder = new PregContentFinder("some content", "", ""); // Start mit null-Delimitern
        $finder->setBeginEndDelimiters("START{", "}END");
        $this->assertSame("START{", $finder->getCurrentEffectiveBeginRegex()); // oder getUserProvided... je nachdem was Sie testen wollen
        $this->assertSame("}END", $finder->getCurrentEffectiveEndRegex());
    }

    public function testSetBeginEndDelimitersWithArray(): void
    {
        $finder = new PregContentFinder("some content", "", "");
        $finder->setBeginEndDelimiters(["BEGIN", "CLOSE"]);
        $this->assertSame("BEGIN", $finder->getCurrentEffectiveBeginRegex());
        $this->assertSame("CLOSE", $finder->getCurrentEffectiveEndRegex());
    }

    public function testSetBeginEndDelimitersThrowsOnInvalidArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $finder = new PregContentFinder("some content", "", "");
        $finder->setBeginEndDelimiters(["BEGIN_ONLY"]); // Dies löst die Exception in setBeginEndDelimiters aus
    }

    public function testSetSearchModeWithEnum(): void
    {
        $finder = new PregContentFinder("content", "{", "}"); // Gültige Delimiter für Konstruktor
        $finder->setSearchMode(SearchMode::DONT_TOUCH_THIS);
        $this->assertSame(SearchMode::DONT_TOUCH_THIS->value, $finder->getSearchMode());
    }

    public function testSetSearchModeWithString(): void
    {
        $finder = new PregContentFinder("content", "{", "}");
        $finder->setSearchMode('dontTouchThis');
        $this->assertSame('dontTouchThis', $finder->getSearchMode());
    }

    public function testSetSearchModeThrowsOnInvalidString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $finder = new PregContentFinder("content", "{", "}");
        $finder->setSearchMode('invalid_mode'); // Dies löst die Exception in setSearchMode aus
    }

    public function testSetAndGetPosOfNextSearch(): void
    {
        $finder = new PregContentFinder("some content here", "[", "]");
        $this->assertSame(0, $finder->getPosOfNextSearch(), "Initial position should be 0");
        $finder->setPosOfNextSearch(10);
        $this->assertSame(10, $finder->getPosOfNextSearch());
    }

    public function testSetPosOfNextSearchThrowsOnNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $finder = new PregContentFinder("content", "[", "]");
        $finder->setPosOfNextSearch(-1); // Dies löst die Exception in setPosOfNextSearch aus
    }

    public function testSetPosOfNextSearchThrowsIfBeyondContentLength(): void
    {
        $content = "short";
        $finder = new PregContentFinder($content, "[", "]");
        $this->expectException(\InvalidArgumentException::class);
        $finder->setPosOfNextSearch(strlen($content) + 1); // Dies löst die Exception in setPosOfNextSearch aus
    }
}
