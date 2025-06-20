<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

/*
a) Lesbar von unten nach oben (Zeilen umkehren):

    Der Callback teilt den Inhalt in Zeilen auf.

    Kehrt die Reihenfolge der Zeilen um.

    Fügt die Zeilen wieder zusammen.

b) Lesbar von rechts nach links (Zeichen pro Zeile umkehren):

    Der Callback teilt den Inhalt in Zeilen auf.

    Für jede Zeile werden die Zeichen umgekehrt.

    Fügt die Zeilen wieder zusammen.

c) Beides zusammen (Zeilen umkehren, dann Zeichen pro Zeile umkehren):

    Der Callback kombiniert die Logik von a) und b).
*/

class SourceCodeFunHouseTransformationsTest extends \PHPUnit\Framework\TestCase
{
    private function applyTransformation(string $content, callable $transformerCallback): string
    {
        // For these transformations, we'll treat the entire content as one block
        $cf = new PregContentFinder($content);
        $cf->setBeginEnd_RegEx('/^/', '/$/s'); // Match the whole string, 's' for dotall if needed

        $transformedContent = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) use ($transformerCallback) {
                // $cut['middle'] contains the entire string in this setup
                $cut['middle'] = $transformerCallback($cut['middle']);
                return $cut;
            }
        );
        return $transformedContent;
    }

    // a) Readable from bottom to top (reverse line order)
    public function testFormatReadableBottomToTop(): void
    {
        $source = "Line 1\nLine 2\nLine 3";
        $expected = "Line 3\nLine 2\nLine 1";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            return implode("\n", array_reverse($lines));
        };

        $this->assertEquals($expected, $this->applyTransformation($source, $transformer));
    }

    public function testFormatReadableBottomToTopWithCode(): void
    {
        $source =
            "<?php\n" .
            "function hello() {\n" .
            "    echo \"World\";\n" .
            "}";
        $expected =
            "}\n" .
            "    echo \"World\";\n" .
            "function hello() {\n" .
            "<?php";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            return implode("\n", array_reverse($lines));
        };

        $this->assertEquals($expected, $this->applyTransformation($source, $transformer));
    }

    // b) Readable from right to left (reverse characters per line)
    public function testFormatReadableRightToLeft(): void
    {
        $source = "Hello\nWorld";
        $expected = "olleH\ndlroW";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            $reversedLines = array_map('strrev', $lines);
            return implode("\n", $reversedLines);
        };

        $this->assertEquals($expected, $this->applyTransformation($source, $transformer));
    }

    public function testFormatReadableRightToLeftWithCode(): void
    {
        $source =
            "function example() {\n" .
            "  return true;\n" .
            "}";
        $expected =
            "} ()elpmaxe noitcnuf\n" .
            ";eurt nruter  \n" .
            "}";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            $reversedLines = array_map('strrev', $lines);
            return implode("\n", $reversedLines);
        };

        $this->assertEquals($expected, $this->applyTransformation($source, $transformer));
    }

    // c) Both together (bottom to top, then right to left per line)
    public function testFormatReadableBottomToTopAndRightToLeft(): void
    {
        $source = "Line 1\nLine 2\nLine 3";
        // 1. Bottom to top:
        // Line 3
        // Line 2
        // Line 1
        // 2. Right to left per line:
        // 3 eniL
        // 2 eniL
        // 1 eniL
        $expected = "3 eniL\n2 eniL\n1 eniL";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            $reversedLinesOrder = array_reverse($lines);
            $completelyReversedLines = array_map('strrev', $reversedLinesOrder);
            return implode("\n", $completelyReversedLines);
        };

        $this->assertEquals($expected, $this->applyTransformation($source, $transformer));
    }

    public function testFormatReadableBottomToTopAndRightToLeftWithCode(): void
    {
        $source =
            "<?php\n" .
            "function main() {\n" .
            "    // Comment\n" .
            "    return 42;\n" .
            "}";
        // 1. Bottom to top:
        // }
        //     return 42;
        //     // Comment
        // function main() {
        // <?php
        // 2. Right to left per line:
        // }
        // ;24 nruter
        // tnemmoC //
        // { ()niam noitcnuf
        // php?<
        $expected =
            "}\n" .
            ";24 nruter    \n" .
            "tnemmoC //    \n" .
            "{ ()niam noitcnuf\n" .
            "php?<";

        $transformer = function (string $text): string {
            $lines = explode("\n", $text);
            $reversedLinesOrder = array_reverse($lines);
            $completelyReversedLines = array_map('strrev', $reversedLinesOrder);
            return implode("\n", $completelyReversedLines);
        };

        $this->assertEquals($expected, $this->applyTransformation($source, $transformer));
    }
}
