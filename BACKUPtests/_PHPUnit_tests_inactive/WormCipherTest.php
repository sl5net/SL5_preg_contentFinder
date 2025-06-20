<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder; // Your main class

class WormCipherTest extends \PHPUnit\Framework\TestCase
{
    private function applyWormTransformations(string $content): string
    {
        $cf = new PregContentFinder($content);
        // Start delimiter: WORM_COMMAND{
        // COMMAND is captured in group 1
        // The \{ needs to be escaped
        $cf->setBeginEnd_RegEx('/WORM_([A-Z_]+)\{/', '/\}/');
        // We could use 'use_BackReference' if the end delimiter were more complex,
        // but for a simple '}' it's not strictly necessary for the basic functionality here.
        // What's more important is that the recursion correctly finds the innermost { } pairs first.

        $processedContent = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
                // $posList0['matches']['begin_matches'][1][0] should contain the COMMAND
                // based on the assumption that the regex for the start delimiter
                // has the command in its first (or rather second, as [0] is the full match)
                // capturing group.
                // In /WORM_([A-Z_]+)\{/, ([A-Z_]+) is the first capturing group ($matches[1]).

                // Ensure we get the matches from the correct preg_match.
                // The $posList0 structure must match what getBorders provides.
                // Assuming $posList0['matches']['begin_matches'] contains the matches from the begin delimiter.
                if (!isset($posList0['matches']['begin_matches'][1][0])) {
                    // Could not extract command, possibly an error in the delimiter or match structure.
                    // In case of error or if no command is recognized, leave content unchanged (or throw an error).
                    // For the test, we return the unchanged middle part to isolate issues.
                    // In a real application, more robust error handling would be needed here.
                    return $cut['before'] . '{' . $cut['middle'] . '}' . $cut['behind'];
                }
                $command = $posList0['matches']['begin_matches'][1][0];
                $middleContent = $cut['middle'];

                $transformedMiddle = "UNKNOWN_COMMAND<{$command}>[{$middleContent}]"; // Default for unknown commands

                switch ($command) {
                    case 'REVERSE':
                        $transformedMiddle = strrev($middleContent);
                        break;
                    case 'UPPER':
                        $transformedMiddle = strtoupper($middleContent);
                        break;
                    case 'CAESAR_PLUS_1':
                        $transformedMiddle = '';
                        for ($i = 0; $i < strlen($middleContent); $i++) {
                            $char = $middleContent[$i];
                            if (ctype_alpha($char)) {
                                $offset = (ctype_upper($char)) ? ord('A') : ord('a');
                                $transformedMiddle .= chr((ord($char) - $offset + 1) % 26 + $offset);
                            } else {
                                $transformedMiddle .= $char; // Leave non-alphabetic characters unchanged
                            }
                        }
                        break;
                    case 'WRAP_WITH_STARS':
                        $transformedMiddle = "***" . $middleContent . "***";
                        break;
                }

                // Assumption: The callback should return the modified $cut array.
                // The original version of `getContent_user_func_recursivePRIV` assembled the string.
                // We need to align the callback's expectation or the class.
                // For this test, we simplify: The callback transforms $cut['middle'].
                $cut['middle'] = $transformedMiddle;
                // We don't re-add the original delimiters here, as the class should do that,
                // or the delimiters are part of $cut['before'] / $cut['behind'] of the outer level.
                // It depends on what $cut['before'] and $cut['behind'] precisely contain in the callback.

                // Safest assumption for the test: The callback returns the transformed *content*.
                // The class is responsible for building the new overall string.
                // The original callback structure modified and returned the $cut array. We do that here too:
                return $cut;
            }
        );
        // If getContent_user_func_recursive returns the entire modified string:
        return $processedContent;
    }

    public function testSimpleReverse(): void
    {
        $source = "Hello WORM_REVERSE{World}!";
        $expected = "Hello dlroW!";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testSimpleUpper(): void
    {
        $source = "Text WORM_UPPER{lower} Text";
        $expected = "Text LOWER Text";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testSimpleCaesar(): void
    {
        $source = "Code: WORM_CAESAR_PLUS_1{abc xyz}!";
        $expected = "Code: bcd yza!";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testNestedWorms(): void
    {
        $source = "Outer WORM_UPPER{test WORM_REVERSE{abc} test} End";
        // Inner WORM_REVERSE{abc} -> cba
        // Outer WORM_UPPER{test cba test} -> TEST CBA TEST
        $expected = "Outer TEST CBA TEST End";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testMultipleNestedWorms(): void
    {
        $source = "Start WORM_WRAP_WITH_STARS{WORM_UPPER{Hello WORM_REVERSE{World}}} End";
        // 1. WORM_REVERSE{World} -> dlroW
        // 2. WORM_UPPER{Hello dlroW} -> HELLO DLROW
        // 3. WORM_WRAP_WITH_STARS{HELLO DLROW} -> ***HELLO DLROW***
        $expected = "Start ***HELLO DLROW*** End";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testAdjacentWorms(): void
    {
        $source = "WORM_UPPER{one} WORM_REVERSE{two}";
        $expected = "ONE owt"; // Note: space is preserved
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testNoWorms(): void
    {
        $source = "This text has no worms.";
        $expected = "This text has no worms.";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testWormWithNoContent(): void
    {
        $source = "Empty: WORM_UPPER{}";
        $expected = "Empty: ";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testUnclosedWorm(): void
    {
        // The behavior here heavily depends on how PregContentFinder handles unclosed delimiters.
        // Does it take everything алкоголь the end of the string? Does it ignore the block?
        // Assumption for this test: The unclosed block is ignored or treated as text until the end.
        $source = "Start WORM_UPPER{This worm is not closed";
        // Expected behavior needs to be defined here based on your class's logic.
        // Option 1: The unclosed part is treated as normal text (if no '}' comes)
        $expected = "Start WORM_UPPER{This worm is not closed";
        // Option 2: The unclosed block is not transformed (conservative approach)
        // $expected = "Start WORM_UPPER{This worm is not closed";

        // For a robust test, we'd need to know the exact behavior of your class with unclosed delimiters.
        // We assume for this test that the unclosed block is not processed and remains as is.
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testWormWithSpecialCharsInContent(): void
    {
        $source = "Special WORM_REVERSE{Chars !@#$%^&*()_+-=[]{};':\",./<>?} End";
        $expected = "Special }?/<>\",:;'[]{}=-+_)(*&^%$#@! srahC End";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }
}
