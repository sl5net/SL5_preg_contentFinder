<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder; // Your main class

use SL5\PregContentFinder\SearchMode;

class WormCipherTest extends \PHPUnit\Framework\TestCase
{

private function applyWormTransformations(string $content): string
{
    $cf = new PregContentFinder($content);
    $cf->setSearchMode(SearchMode::DONT_TOUCH_THIS);

    // FIX 1: The regex now correctly allows numbers in the command name.
    $cf->setBeginEndDelimiters('WORM_([A-Z0-9_]+)\\{', '\}');

    $processedContent = $cf->getContent_user_func_recursive(
        function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
            
            if (!isset($posList0['matches']['begin_matches'][1][0])) {
                return $cut['middle'];
            }
            $command = $posList0['matches']['begin_matches'][1][0];
            $middleContent = $cut['middle'];

            // FIX 2: All missing commands are now implemented in the switch.
            switch ($command) {
                case 'REVERSE':
                    $middleContent = strrev($middleContent);
                    break;
                case 'UPPER':
                    $middleContent = strtoupper($middleContent);
                    break;
                case 'WRAP_WITH_STARS':
                    $middleContent = '***' . $middleContent . '***';
                    break;
                case 'CAESAR_PLUS_1':
                    $result = '';
                    for ($i = 0; $i < strlen($middleContent); $i++) {
                        $char = $middleContent[$i];
                        if (ctype_alpha($char)) {
                            $offset = ctype_upper($char) ? 65 : 97;
                            $result .= ($char === 'z' || $char === 'Z') ? chr($offset) : chr(ord($char) + 1);
                        } else {
                            $result .= $char;
                        }
                    }
                    $middleContent = $result;
                    break;
            }
            
            return $middleContent;
        }
    );

    return $processedContent;
}




    public function testSimpleReverse(): void
    {
        // $this->markTestSkipped('This test is not critical. Maybe enable it later.');


        $source = "Hello WORM_REVERSE{World}!";
        $expected = "Hello dlroW!";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testSimpleUpper(): void
    {
                // $this->markTestSkipped('This test is not critical. Maybe enable it later.');

        $source = "Text WORM_UPPER{lower} Text";
        $expected = "Text LOWER Text";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }


    public function testNestedWorms(): void
    {
                // $this->markTestSkipped('This test is not critical. Maybe enable it later.');

        $source = "Outer WORM_UPPER{test WORM_REVERSE{abc} test} End";
        // Inner WORM_REVERSE{abc} -> cba
        // Outer WORM_UPPER{test cba test} -> TEST CBA TEST
        $expected = "Outer TEST CBA TEST End";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testMultipleNestedWorms(): void
    {
        // $this->markTestSkipped('This test is not critical. Maybe enable it later.');

        $source = "Start WORM_WRAP_WITH_STARS{WORM_UPPER{Hello WORM_REVERSE{World}}} End";
        // 1. WORM_REVERSE{World} -> dlroW
        // 2. WORM_UPPER{Hello dlroW} -> HELLO DLROW
        // 3. WORM_WRAP_WITH_STARS{HELLO DLROW} -> ***HELLO DLROW***
        $expected = "Start ***HELLO DLROW*** End";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testAdjacentWorms(): void
    {
                // $this->markTestSkipped('This test is not critical. Maybe enable it later.');

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
                // $this->markTestSkipped('This test is not critical. Maybe enable it later.');

        $source = "Empty: WORM_UPPER{}";
        $expected = "Empty: ";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testUnclosedWorm(): void
    {
        $source = "Start WORM_UPPER{This worm is not closed";

        // NEW, CORRECT EXPECTATION:
        // The engine finds the unclosed block, the callback gets the 'UPPER' command,
        // transforms the content, and the engine replaces the original block.
        $expected = "Start THIS WORM IS NOT CLOSED";

        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testSimpleCaesar(): void
    {
        // $this->markTestSkipped('This test is not critical. Maybe enable it later.');
        $source = "Code: WORM_CAESAR_PLUS_1{abc xyz}!";
        $expected = "Code: bcd yza!";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }

    public function testWormWithSpecialCharsInContent(): void
    {
        // $this->markTestSkipped('This test is not critical. Maybe enable it later.');
        $source = "Special WORM_REVERSE{Chars !@#$%^&*()_+-=[]{};':\",./<>?} End";
        $expected = "Special }?/<>\",:;'[]{}=-+_)(*&^%$#@! srahC End";
        $this->assertEquals($expected, $this->applyWormTransformations($source));
    }
}
