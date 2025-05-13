<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

class PregContentFinderPerformanceComparisonTest extends \PHPUnit\Framework\TestCase
{
    // --- Helper from PregContentFinderPerformanceTest ---
    private function generateNestedString(string $char, int $depth): string
    {
        if ($depth <= 0) {
            return "core{$char}content";
        }
        return "pre{$char}" . $char . "{" . $this->generateNestedString($char, $depth - 1) . "}" . $char . "post{$char}";
    }

    private function generateLargeStringWithManyBlocks(string $prefix, string $open, string $close, int $numBlocks, int $contentLength): string
    {
        $content = $prefix;
        for ($i = 0; $i < $numBlocks; $i++) {
            $content .= $open . str_repeat('a', $contentLength) . $i . $close . "some_separator_text_";
        }
        return $content;
    }

    // --- "Vanilla PHP" Implementations for Comparison ---

    /**
     * Vanilla PHP equivalent for deeply nested structure processing.
     * This will be much simpler as it doesn't have the generic callback mechanism.
     * It will just find and prepend depth to the innermost content.
     * This is a simplified simulation; true balanced parsing is complex.
     */
    private function processNestedVanilla(string $text, string $openDelim, string $closeDelim, int &$iterations, int $currentDepth = 0): string
    {
        $iterations++; // Count each call as an iteration
        $openPos = strpos($text, $openDelim);
        if ($openPos === false) {
            return "D{$currentDepth}:" . $text; // Base case: no more open delimiters
        }

        // This is a very simplified non-balanced approach for vanilla comparison.
        // True balanced parsing without a proper parser or complex regex is hard.
        // We will just find the first opening and try to find its corresponding closing.
        // This will NOT correctly handle multiple non-nested blocks at the same level if $openDelim/$closeDelim are simple.

        $nestLevel = 0;
        $closePos = -1;
        $startContentPos = $openPos + strlen($openDelim);

        for ($i = $startContentPos; $i < strlen($text); $i++) {
            if (substr($text, $i, strlen($openDelim)) === $openDelim) {
                $nestLevel++;
            } elseif (substr($text, $i, strlen($closeDelim)) === $closeDelim) {
                if ($nestLevel === 0) {
                    $closePos = $i;
                    break;
                }
                $nestLevel--;
            }
        }

        if ($closePos === -1) {
            return "D{$currentDepth}:" . $text; // No matching close delimiter found for the first open
        }

        $before = substr($text, 0, $openPos);
        $middle = substr($text, $startContentPos, $closePos - $startContentPos);
        $after = substr($text, $closePos + strlen($closeDelim));

        // Recursively process the middle part
        $processedMiddle = $this->processNestedVanilla($middle, $openDelim, $closeDelim, $iterations, $currentDepth + 1);

        return $before . $openDelim . $processedMiddle . $closeDelim . $this->processNestedVanilla($after, $openDelim, $closeDelim, $iterations, $currentDepth);
    }


    /**
     * Vanilla PHP equivalent for extracting content from many non-nested blocks.
     */
    private function extractManyBlocksVanilla(string $text, string $openDelim, string $closeDelim, int &$iterations): array
    {
        $results = [];
        $offset = 0;
        $openDelimLen = strlen($openDelim);
        $closeDelimLen = strlen($closeDelim);

        while (($openPos = strpos($text, $openDelim, $offset)) !== false) {
            $iterations++;
            $contentStartPos = $openPos + $openDelimLen;
            $closePos = strpos($text, $closeDelim, $contentStartPos);

            if ($closePos === false) {
                break; // No closing delimiter found for the current open one
            }

            $results[] = substr($text, $contentStartPos, $closePos - $contentStartPos);
            $offset = $closePos + $closeDelimLen;
        }
        return $results;
    }

    // --- Performance Tests with Comparison ---

    /**
     * group performance
     */
    public function testDeeplyNestedStructureComparison(): void
    {
        $nestingDepth = 15;
        $testString = $this->generateNestedString('N', $nestingDepth);
        $openDelim = '{';
        $closeDelim = '}';

        // --- PregContentFinder ---
        $finder = new PregContentFinder($testString);
        $finder->setBeginEnd_RegEx($openDelim, $closeDelim);
        $pcfIterations = 0;
        $startTimePCF = microtime(true);
        $startMemoryPCF = memory_get_usage();
        $resultPCF = $finder->getContent_user_func_recursive(
            function ($cut, $deepCount) use (&$pcfIterations) {
                $pcfIterations++;
                $cut['middle'] = "D{$deepCount}:" . $cut['middle'];
                return $cut;
            }
        );
        $endTimePCF = microtime(true);
        $endMemoryPCF = memory_get_usage();
        $durationPCF = $endTimePCF - $startTimePCF;
        $memoryPCF = $endMemoryPCF - $startMemoryPCF;

        echo "\n--- Deeply Nested Structure (PregContentFinder) ---\n";
        echo "Nesting Depth: {$nestingDepth}, String Length: " . strlen($testString) . " bytes\n";
        echo "Callback Iterations: {$pcfIterations}\n";
        echo "Duration: " . number_format($durationPCF, 6) . " seconds\n";
        echo "Memory Used (approx.): " . number_format($memoryPCF / 1024, 2) . " KB\n";
        $this->assertIsString($resultPCF);
        $this->assertGreaterThan(0, $pcfIterations);


        // --- Vanilla PHP ---
        $vanillaIterations = 0;
        $startTimeVanilla = microtime(true);
        $startMemoryVanilla = memory_get_usage();
        // Note: The vanilla implementation is simplified and might not produce the exact same string result
        // if the callback logic of PCF does more than just transforming the innermost.
        // The goal is to compare a similar recursive task.
        // For a fair comparison, the vanilla version should aim to achieve a similar *transformation*.
        // The current `processNestedVanilla` does a recursive call for `after` part too,
        // which is different from PCF's typical single-pass recursive replacement.
        // A truly equivalent vanilla version for PCF's recursive callback is complex.
        // Let's simplify the vanilla task to just finding the *first, outermost* balanced block
        // and then recursively processing its middle, to better mimic one pass of PCF.

        // Simpler vanilla for comparison - finds first balanced and recurses:
        $iterationsVanillaSimple = 0;
        $resultVanilla = $this->processFirstBalancedBlockVanilla($testString, $openDelim, $closeDelim, $iterationsVanillaSimple);

        $endTimeVanilla = microtime(true);
        $endMemoryVanilla = memory_get_usage();
        $durationVanilla = $endTimeVanilla - $startTimeVanilla;
        $memoryVanilla = $endMemoryVanilla - $startMemoryVanilla;

        echo "\n--- Deeply Nested Structure (Simplified Vanilla PHP for comparison) ---\n";
        echo "Nesting Depth: {$nestingDepth}, String Length: " . strlen($testString) . " bytes\n";
        echo "Function Calls: {$iterationsVanillaSimple}\n"; // Different meaning than PCF iterations
        echo "Duration: " . number_format($durationVanilla, 6) . " seconds\n";
        echo "Memory Used (approx.): " . number_format($memoryVanilla / 1024, 2) . " KB\n";
        $this->assertIsString($resultVanilla);

        // We can't easily assert $resultPCF == $resultVanilla due to different processing logic
        // but we can observe the times.
    }

    // Simplified vanilla to find and process the first outermost balanced block recursively (middle part)
    private function processFirstBalancedBlockVanilla(string $text, string $openDelim, string $closeDelim, int &$calls, int $depth = 0): string {
        $calls++;
        $openPos = strpos($text, $openDelim);
        if ($openPos === false) {
            return "D{$depth}:" . $text;
        }

        $nestLevel = 0;
        $closePos = -1;
        $startContentPos = $openPos + strlen($openDelim);

        for ($i = $startContentPos; $i < strlen($text); $i++) {
            if (substr($text, $i, strlen($openDelim)) === $openDelim) {
                $nestLevel++;
            } elseif (substr($text, $i, strlen($closeDelim)) === $closeDelim) {
                if ($nestLevel === 0) {
                    $closePos = $i;
                    break;
                }
                $nestLevel--;
            }
        }

        if ($closePos === -1) {
            return "D{$depth}:" . $text; // No matching close
        }

        $before = substr($text, 0, $openPos);
        $middle = substr($text, $startContentPos, $closePos - $startContentPos);
        $after = substr($text, $closePos + strlen($closeDelim));

        $processedMiddle = $this->processFirstBalancedBlockVanilla($middle, $openDelim, $closeDelim, $calls, $depth + 1);

        return $before . $openDelim . $processedMiddle . $closeDelim . $after; // Reconstruct
    }


    /**
     * group performance
     */
    public function testLargeStringManyBlocksComparison(): void
    {
        $numBlocks = 5000;
        $blockContentLength = 50;
        $openDelim = "BLOCK{";
        $closeDelim = "}END_BLOCK";
        $testString = $this->generateLargeStringWithManyBlocks("StartText_", $openDelim, $closeDelim, $numBlocks, $blockContentLength);

        // --- PregContentFinder ---
        $finder = new PregContentFinder($testString);
        $finder->setBeginEnd_RegEx($openDelim, $closeDelim);
        $pcfIterations = 0;
        $extractedPCF = [];
        $startTimePCF = microtime(true);
        $startMemoryPCF = memory_get_usage();
        $currentPosPCF = 0;
        $finder->setPosOfNextSearch($currentPosPCF);
        while (($content = $finder->getContent()) !== false && $content !== "") { // Assuming empty string can be valid content
            $pcfIterations++;
            $extractedPCF[] = $content;
            $borders = $finder->getBorders();
            if ($borders === null || $borders['end_end'] === null) break;
            $currentPosPCF = $borders['end_end'];
            if ($currentPosPCF >= strlen($testString)) break;
            $finder->setPosOfNextSearch($currentPosPCF);
             if ($pcfIterations > $numBlocks + 5) break;
        }
        $endTimePCF = microtime(true);
        $endMemoryPCF = memory_get_usage();
        $durationPCF = $endTimePCF - $startTimePCF;
        $memoryPCF = $endMemoryPCF - $startMemoryPCF;

        echo "\n--- Large String Many Blocks (PregContentFinder) ---\n";
        echo "Number of Blocks: {$numBlocks}, String Length: " . strlen($testString) . " bytes\n";
        echo "Found Blocks (Iterations): {$pcfIterations}\n";
        echo "Duration: " . number_format($durationPCF, 6) . " seconds\n";
        echo "Memory Used (approx.): " . number_format($memoryPCF / 1024, 2) . " KB\n";
        $this->assertEquals($numBlocks, $pcfIterations);

        // --- Vanilla PHP ---
        $vanillaIterations = 0;
        $startTimeVanilla = microtime(true);
        $startMemoryVanilla = memory_get_usage();
        $extractedVanilla = $this->extractManyBlocksVanilla($testString, $openDelim, $closeDelim, $vanillaIterations);
        $endTimeVanilla = microtime(true);
        $endMemoryVanilla = memory_get_usage();
        $durationVanilla = $endTimeVanilla - $startTimeVanilla;
        $memoryVanilla = $endMemoryVanilla - $startMemoryVanilla;

        echo "\n--- Large String Many Blocks (Vanilla PHP) ---\n";
        echo "Number of Blocks: {$numBlocks}, String Length: " . strlen($testString) . " bytes\n";
        echo "Found Blocks (Iterations): {$vanillaIterations}\n"; // Should be same as pcfIterations
        echo "Duration: " . number_format($durationVanilla, 6) . " seconds\n";
        echo "Memory Used (approx.): " . number_format($memoryVanilla / 1024, 2) . " KB\n";
        $this->assertEquals($numBlocks, $vanillaIterations);
        $this->assertEquals($extractedPCF, $extractedVanilla, "Extracted content should be identical.");
    }
}
