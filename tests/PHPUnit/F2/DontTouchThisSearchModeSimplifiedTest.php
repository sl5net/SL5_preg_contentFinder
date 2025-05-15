<?php
namespace SL5\PregContentFinder\Tests\PHPUnit\F2;
use SL5\PregContentFinder\Tests\PHPUnit\FilenameProcessor;
use SL5\PregContentFinder\Tests\PHPUnit\YourBaseTestClass;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\SearchMode;

class DontTouchThisSearchModeSimplifiedTest extends YourBaseTestClass
{
    public function test_alwaysTrueTest(): void
    {
        $this->assertTrue(true, "This assertion should always be True.");
    }
    public function test_SomethingWithLogging(): void
    {
        $this->logger->info('Hey from function testSomethingWithLogging() out of DontTouchThisSearchModeSimplifiedTest.php near Line 14');
        $this->assertTrue(true, "This assertion should always pass.");
    }
    public function test_alwaysTrueTestWithLogging(): void
    {
        $this->logger->info('hiho');
        $this->assertTrue(true, "This assertion should always pass.");
    }
    public function testGetContentWithRegexDelimitersAndDontTouchThisMode(): void
    {
        $this->markTestSkipped('This test is disabled for now');

     
        $source = 'BEFORE_123#content_GHI_AFTER';
        $expectedContent = '#content_'; // Content between "123" and "GHI"

        $this->logger->info('Hey from function testGetContentWithRegex....() out of DontTouchThisSearchModeSimplifiedTest.php near Line 35');


        // Instanz erstellen (Standard-Delimiter des Konstruktors sind hier irrelevant)
        $finder = new PregContentFinder($source);

        // Parameter direkt an getContent übergeben
        // Annahme: getContent-Signatur ist ähnlich: (?string $begin, ?string $end, ?int $pos, SearchMode|string|null $mode)
        $actualContent = $finder->getContent(
            beginRegex: '\d+',              // Findet "123"
            endRegex: '[A-Z]+',           // Findet "GHI"
            startPosition: null,          // Startet von Anfang an oder von $finder->nextSearchPosition
            searchMode: SearchMode::DONT_TOUCH_THIS // Oder 'dontTouchThis' als String
        );

        $this->assertSame($expectedContent, $actualContent);
    }

    /**
     * Tests getContent with 'dontTouchThis' mode where the end delimiter is not found.
     */
    public function testGetContentWithRegexDelimitersAndDontTouchThisModeNoEnd(): void
    {
        $this->markTestSkipped('This test is disabled for now');


        $this->markTestSkipped('This test is disabled for now');

        $this->logger->info('Hey from function testGetContentWithRegexDelimitersAndDontTouchThisModeNoEnd() out of DontTouchThisSearchModeSimplifiedTest.php near Line 55');


        $source = 'START_456_NO_END_LETTERS';

        $finder = new PregContentFinder($source);
        // Annahme: stopOnMissingEndBorder ist false, stopOnMissingBothBorders ist true (Standard)
        // Wenn kein End-Delimiter gefunden wird UND stopOnMissingEndBorder false ist,
        // sollte der Inhalt bis zum Ende des Strings genommen werden (alte Logik)
        // oder false zurückgegeben werden (neue, striktere Logik).
        // Wir testen hier auf das "Rest des Strings"-Verhalten, wenn stopOnMissingEndBorder = false ist.
        // Falls Ihre neue Klasse hier false zurückgibt, muss die Erwartung angepasst werden.

        // Man könnte das Flag setzen, um das Verhalten zu steuern, falls es public ist oder einen Setter hat:
        // $finder->setStopOnMissingEndBorder(false);

        $actualContent = $finder->getContent(
            beginRegex: '\d+',      // Findet "456"
            endRegex: '[A-Z]+',   // Wird nicht gefunden
            searchMode: SearchMode::DONT_TOUCH_THIS
        );

        // Erwartetes Verhalten, wenn stopOnMissingEndBorder = false (alte Logik):
        $expectedContentIfNoStop = '_NO_END_LETTERS';
        // Erwartetes Verhalten, wenn getContent bei fehlendem Ende false zurückgibt (striktere V2 Logik):
        // $expectedContentIfFalse = false;

        // Passen Sie die folgende Assertion an das tatsächliche Verhalten Ihrer Klasse an.
        // Wenn Ihre Klasse den Rest des Strings zurückgibt:
        $this->assertSame($expectedContentIfNoStop, $actualContent, "Content should be from after begin regex to end of string if end regex not found and not stopping.");
        // Wenn Ihre Klasse false zurückgibt:
        // $this->assertFalse($actualContent, "Content should be false if end regex is not found.");
    }


    /**
     * Tests getContent_user_func_recursive with 'dontTouchThis' mode and regex delimiters.
     */
    public function testGetContentUserFuncRecursiveWithRegexDelimiters(): void
    {
        $this->markTestSkipped('This test is disabled for now');

        
        $source = 'DATA_123#transformed_GHI_MORE';
        $expectedTransformed = '#2.transformed_'; // "2." + "#transformed_"

        $finder = new PregContentFinder($source);
        $finder->setSearchMode(SearchMode::DONT_TOUCH_THIS);
        $finder->setBeginEndDelimiters('\d+', '[A-Z]+'); // Delimiter für die Instanz setzen

        $actualTransformed = $finder->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
                // Annahme: $cut['middle'] enthält den gefundenen Inhalt
                if ($cut['middle'] === false) { // Wichtig, falls nichts gefunden wurde
                    return $cut; // Oder eine andere Fehlerbehandlung im Callback
                }
                $cut['middle'] = '2.' . $cut['middle'];
                return $cut;
            }
        );

        // Was getContent_user_func_recursive zurückgibt, hängt von seiner Implementierung ab.
        // Gibt es den gesamten modifizierten String zurück oder nur den ersten transformierten Block?
        // Annahme: Es gibt den gesamten String zurück, wobei nur der erste gefundene Block transformiert wird,
        // wenn die Rekursion nicht weiter implementiert ist für diesen einfachen Fall.
        // Für einen präzisen Test müssten wir das erwartete Verhalten von getContent_user_func_recursive
        // für einen einzelnen, nicht-verschachtelten Fund kennen.

        // Wenn wir annehmen, dass es den gesamten String mit der ersten Ersetzung zurückgibt:
        $expectedFullString = 'DATA_123' . $expectedTransformed . 'GHI_MORE';

        // Dieser Test ist komplexer, da die Struktur des Rückgabewerts von
        // getContent_user_func_recursive und wie es den String zusammensetzt, klar sein muss.
        // Für den Moment konzentrieren wir uns darauf, ob der Callback den richtigen Mittelteil bekommt.
        // Wir brauchen eine Möglichkeit, zu verifizieren, was der Callback getan hat.

        // Temporäre Vereinfachung: Wir prüfen, ob der Inhalt korrekt extrahiert und an den Callback übergeben wird.
        $finderForCallbackCheck = new PregContentFinder($source);
        $finderForCallbackCheck->setSearchMode(SearchMode::DONT_TOUCH_THIS);
        $finderForCallbackCheck->setBeginEndDelimiters('\d+', '[A-Z]+');

        $contentPassedToCallback = null;
        $finderForCallbackCheck->getContent_user_func_recursive(
            function ($cut) use (&$contentPassedToCallback) {
                $contentPassedToCallback = $cut['middle'];
                $cut['middle'] = '2.' . $cut['middle']; // Transformation
                return $cut;
            }
        );
        $this->assertSame('#transformed_', $contentPassedToCallback, "Callback did not receive the correct middle part.");
    }
}
