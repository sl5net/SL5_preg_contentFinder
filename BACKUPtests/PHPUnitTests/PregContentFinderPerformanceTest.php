<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

class PregContentFinderPerformanceTest extends \PHPUnit\Framework\TestCase
{
/**
 * remove $this->markTestSkipped(...)-Zeilen when you want test it.
 * Im Docker-Container oder über Ihr runUnitTest.sh, das vendor/bin/phpunit verwendet
 * vendor/bin/phpunit tests/PHPUnit/PregContentFinderPerformanceTest.php
 * oder
 * vendor/bin/phpunit --group performance
 * oder
 * 
 * 
 * Ein reiner PHPUnit-Test ist traditionell nicht das primäre Werkzeug für präzise Performance-Messungen
 * oder Benchmarking, 
 * da PHPUnit selbst einen gewissen Overhead hat. 
 * Für ernsthaftes Benchmarking gibt es spezialisierte Tools wie 
 * Blackfire.io, 
 * Xdebug mit Profiling-Ausgaben oder Bibliotheken wie phpbench/phpbench.
 */

    // Helper to generate a deeply nested string
    private function generateNestedString(string $char, int $depth): string
    {
        if ($depth <= 0) {
            return "core{$char}content";
        }
        return "pre{$char}" . $char . "{" . $this->generateNestedString($char, $depth - 1) . "}" . $char . "post{$char}";
    }

    /**
     * Generates a large string with many potential (non-nested) matches.
     */
    private function generateLargeStringWithManyBlocks(string $prefix, string $open, string $close, int $numBlocks, int $contentLength): string
    {
        $content = $prefix;
        for ($i = 0; $i < $numBlocks; $i++) {
            $content .= $open . str_repeat('a', $contentLength) . $i . $close . "some_separator_text_";
        }
        return $content;
    }

    /**
     * group performance
     * This test is more of a benchmark and its timing can vary greatly.
     * It's intended to stress the recursive capabilities.
     */
    public function testDeeplyNestedStructurePerformance(): void
    {
        // $this->markTestSkipped('Performance tests are environment-dependent and should be run manually or with dedicated benchmarking tools.');
        // Re-enable by removing the line above if you want to run it.

        $nestingDepth = 15; // Adjust for more or less stress (e.g., 10, 15, 20)
                           // Be careful: high nesting can lead to very long execution or recursion limits
        $testString = $this->generateNestedString('N', $nestingDepth);
        // Example for depth 3: preN N{preN N{preN N{coreNcontent}N postN}N postN}N postN
        
        $finder = new PregContentFinder($testString);
        $finder->setBeginEnd_RegEx('{', '}');

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $iterations = 0;
        $result = $finder->getContent_user_func_recursive(
            function ($cut, $deepCount) use (&$iterations) {
                $iterations++;
                // Simple transformation: just prepend depth to the middle content
                $cut['middle'] = "D{$deepCount}:" . $cut['middle'];
                return $cut;
            }
        );

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        echo "\n--- Deeply Nested Structure Performance ---\n";
        echo "Nesting Depth: {$nestingDepth}\n";
        echo "String Length: " . strlen($testString) . " bytes\n";
        echo "Callback Iterations: {$iterations}\n";
        echo "Duration: " . number_format($duration, 4) . " seconds\n";
        echo "Memory Used by Operation (approx.): " . number_format($memoryUsed / 1024, 2) . " KB\n";
        // echo "Result Preview (last 100 chars): " . substr($result, -100) . "\n"; // Can be very long

        // Assertions here are tricky. We mainly output data.
        // We can assert that it completed and the result is a string.
        $this->assertIsString($result);
        $this->assertGreaterThan(0, $iterations, "Callback should have been called.");

        // Example of a very loose time assertion (highly dependent on environment)
        // $this->assertLessThan(5.0, $duration, "Deeply nested processing took too long (> 5 seconds).");
        // It's better to observe the output than to have a strict time limit in a unit test.
    }

    /**
     * group performance
     * Tests performance with a large string and many non-nested blocks.
     */
    public function testLargeStringManyBlocksPerformance(): void
    {
        // $this->markTestSkipped('Performance tests are environment-dependent and should be run manually or with dedicated benchmarking tools.');
        // Re-enable by removing the line above if you want to run it.

        $numBlocks = 5000; // Number of {block} occurrences
        $blockContentLength = 50; // Length of content inside each block
        $testString = $this->generateLargeStringWithManyBlocks(
            "StartText_", 
            "BLOCK{", 
            "}END_BLOCK", 
            $numBlocks, 
            $blockContentLength
        );

        $finder = new PregContentFinder($testString);
        $finder->setBeginEnd_RegEx('BLOCK{', '}END_BLOCK');

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $iterations = 0;
        $extractedContents = [];

        // Loop to get all top-level matches, similar to how one might use getContent in a loop
        $currentPos = 0;
        $finder->setPosOfNextSearch($currentPos);
        while (($content = $finder->getContent()) !== false && $content !== "") {
            $iterations++;
            $extractedContents[] = $content; // Store or process content
            
            // Manually advance position based on the found block
            // This requires knowing the end position of the last match.
            // The class's internal state for pos_of_next_search should ideally handle this
            // if getBorders() updates it correctly.
            // Let's assume getBorders (called by getContent) updates the internal next search position.
            // If not, this loop needs more complex position management.

            // To ensure we advance, we need to get the position after the last match.
            // This is where accessing $finder->foundPos_list or a similar mechanism would be useful,
            // or if getContent() or getBorders() reliably updates an internal offset for the next call.
            // For simplicity, if getContent() itself updates the internal search position correctly for the *next* call:
            // No explicit pos update needed here IF getContent correctly sets internal pos_of_next_search for the subsequent call.
            // However, the original class's `doOverwriteSetup_OF_pos_of_next_search` flag
            // and how `update_RegEx_BeginEndPos` works is crucial here.

            // More robust way if getContent() doesn't auto-advance for subsequent calls in a loop like this
            // without explicit setPosOfNextSearch:
            $borders = $finder->getBorders(); // Call again to get current borders if not cached or to re-evaluate
                                            // This is inefficient if called repeatedly without state change.
                                            // The original class has caching, which might help.
            if ($borders === null || $borders['end_end'] === null) break; // No more matches
            $currentPos = $borders['end_end'];
            if ($currentPos >= strlen($testString)) break;
            $finder->setPosOfNextSearch($currentPos);

            if ($iterations > $numBlocks + 5) { // Safety break to prevent infinite loops if pos not advancing
                echo "Safety break in large string test!\n";
                break;
            }
        }


        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $duration = $endTime - $startTime;
        $memoryUsed = $endMemory - $startMemory;

        echo "\n--- Large String Many Blocks Performance ---\n";
        echo "Number of Blocks: {$numBlocks}\n";
        echo "String Length: " . strlen($testString) . " bytes\n";
        echo "Found Blocks (Iterations): {$iterations}\n";
        echo "Duration: " . number_format($duration, 4) . " seconds\n";
        echo "Memory Used by Operation (approx.): " . number_format($memoryUsed / 1024, 2) . " KB\n";

        $this->assertEquals($numBlocks, $iterations, "Should find all specified blocks.");
        $this->assertCount($numBlocks, $extractedContents);
        // $this->assertLessThan(10.0, $duration, "Large string processing took too long (> 10 seconds).");
    }
}
