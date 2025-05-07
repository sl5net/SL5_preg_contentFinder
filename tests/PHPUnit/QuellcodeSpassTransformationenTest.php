<?php
namespace SL5\PregContentFinder\Tests; // Ihr Test-Namespace

use SL5\PregContentFinder\PregContentFinder; // Ihre Hauptklasse

/*
Wichtiger Hinweis zur Verwendung von PregContentFinder hier:

Diese Art der Anwendung ist ein wenig ein "Missbrauch" der Klasse, da ihre Stärken (Rekursion, komplexe Delimiter, Backreferences) hier nicht wirklich genutzt werden. Wir verwenden sie im Grunde nur als Vehikel, um einen Callback auf einen gesamten String anzuwenden. Für diese spezifischen Transformationen gäbe es direktere Wege in PHP ohne PregContentFinder.

Aber als humorvolle Antwort und Demonstration, dass der Callback alles Mögliche mit dem Inhalt machen kann, ist es perfekt!

    applyTransformation(string $content, callable $transformerCallback): Eine Hilfsmethode, die PregContentFinder so konfiguriert, dass es den gesamten $content als einen Block an den $transformerCallback übergibt.

        $cf->setBeginEnd_RegEx('/^/', '/$/s');: Diese Delimiter sorgen dafür, dass der gesamte String als Inhalt zwischen (dem nicht existierenden) Anfang und (dem nicht existierenden) Ende gematcht wird. Das s (DOTALL) ist wichtig, falls der String Newlines enthält, die von . gematcht werden sollen, obwohl es hier für ^ und $ weniger relevant ist.

        Der Callback erhält den gesamten String in $cut['middle'].

    Transformer Callbacks: Für jede der drei angeforderten Transformationen wird eine eigene anonyme Funktion definiert, die die Logik implementiert:

        explode("\n", ...): Teilt den String in Zeilen.

        array_reverse(...): Kehrt die Reihenfolge der Zeilen um.

        array_map('strrev', ...): Wendet strrev (Zeichenumkehr) auf jede Zeile an.

        implode("\n", ...): Fügt die Zeilen wieder zu einem String zusammen.

    Testmethoden: Jede Testmethode definiert einen Beispiel-Input ($source) und den erwarteten Output ($expected) und ruft dann applyTransformation mit dem entsprechenden Transformer-Callback auf. Ich habe auch Beispiele mit typischem Code-Layout hinzugefügt.
*/

class QuellcodeSpassTransformationenTest extends \PHPUnit\Framework\TestCase
{
    private function transformiereInhalt(string $inhalt, callable $transformerCallback): string
    {
        // Für diese Transformationen behandeln wir den gesamten Inhalt als einen Block
        $cf = new PregContentFinder($inhalt);
        $cf->setBeginEnd_RegEx('/^/', '/$/s'); // Passt den gesamten String

        $transformierterInhalt = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) use ($transformerCallback) {
                $cut['middle'] = $transformerCallback($cut['middle']);
                return $cut;
            }
        );
        return $transformierterInhalt;
    }

    // a) Lesbar von unten nach oben (Zeilenreihenfolge umkehren)
    public function testFormatierungLesbarVonUntenNachOben(): void
    {
        $source = "Zeile 1\nZeile 2\nZeile 3";
        $expected = "Zeile 3\nZeile 2\nZeile 1";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            return implode("\n", array_reverse($lines));
        };

        $this->assertEquals($expected, $this->transformiereInhalt($source, $transformer));
    }

    // b) Lesbar von rechts nach links (Zeichen pro Zeile umkehren)
    public function testFormatierungLesbarVonRechtsNachLinks(): void
    {
        $source = "Hallo\nWelt";
        $expected = "ollaH\ntleW";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            $reversedLines = array_map('strrev', $lines);
            return implode("\n", $reversedLines);
        };

        $this->assertEquals($expected, $this->transformiereInhalt($source, $transformer));
    }

    // c) Beides zusammen (von unten nach oben, dann rechts nach links pro Zeile)
    public function testFormatierungUntenObenUndRechtsLinks(): void
    {
        $source = "Zeile 1\nZeile 2\nZeile 3";
        $expected = "3 elieZ\n2 elieZ\n1 elieZ";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            $reversedLinesOrder = array_reverse($lines);
            $completelyReversedLines = array_map('strrev', $reversedLinesOrder);
            return implode("\n", $completelyReversedLines);
        };
            
        $this->assertEquals($expected, $this->transformiereInhalt($source, $transformer));
    }
}