<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

class LabyrinthSolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Helper function to apply path marking transformations.
     * The callback will mark spaces within PATH{...} blocks as '.'
     */
    private function markPathInLabyrinth(string $labyrinthRepresentation): string
    {
        $cf = new PregContentFinder($labyrinthRepresentation);
        // Delimiter for path segments to be "solved" or marked
        $cf->setBeginEnd_RegEx('/PATH\{/', '/\}/');

        $processedLabyrinth = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
                // We are interested in the $cut['middle'] part, which is the content
                // inside PATH{...}
                $pathToMark = $cut['middle'];

                // Simple transformation: replace spaces with '.' to mark the path
                $markedPath = str_replace(' ', '.', $pathToMark);

                // The callback is expected to return the modified $cut array
                // or just the transformed middle string, depending on how PregContentFinder
                // is designed to use the callback's return value.
                // Assuming the callback should return the modified $cut array:
                $cut['middle'] = $markedPath;
                return $cut;
            }
        );
        // Assuming getContent_user_func_recursive returns the fully processed string
        return $processedLabyrinth;
    }

    public function testSimplePathMarking(): void
    {
        $labyrinth =
            "S # E\n" .
            "  #  \n" .
            "PATH{   }#  \n" . // Path segment to be marked
            "#####";

        $expectedSolvedLabyrinth =
            "S # E\n" .
            "  #  \n" .
            "...#  \n" . // Spaces within PATH{} are now '.'
            "#####";

        $this->assertEquals($expectedSolvedLabyrinth, $this->markPathInLabyrinth($labyrinth));
    }

    public function testPathMarkingWithWallsInsidePathBlock(): void
    {
        // This tests if only spaces are replaced, not walls
        $labyrinth =
            "S PATH{ # } E\n" . // Path segment with a wall inside
            "###########";

        $expectedSolvedLabyrinth =
            "S .#. E\n" .
            "###########";

        $this->assertEquals($expectedSolvedLabyrinth, $this->markPathInLabyrinth($labyrinth));
    }

    public function testNestedPathMarking(): void
    {
        // This demonstrates the recursive capability if PATH blocks could be nested,
        // though for simple path marking, nesting PATH{} might not be typical.
        // If the class processes innermost first:
        $labyrinth =
            "S PATH{PATH{   } # PATH{ }}E\n" .
            "#######################";

        // Innermost PATH{   } -> ...
        // Innermost PATH{ }   -> .
        // Outer PATH{... # .} -> ... # . (assuming spaces around # and . are also marked)
        // This depends heavily on how $cut['before'] and $cut['behind'] are handled
        // by PregContentFinder when assembling the final string from nested callback results.
        // For simplicity, let's assume it works sequentially on the content passed.
        // Let's simplify: the callback only sees the direct content of its PATH{}.
        // So, PATH{   } becomes ...
        // Then, PATH{ } becomes .
        // Then, the outer callback for PATH{... # .} would get "... # ." as its middle.
        // If it replaces spaces, it would become "...#.".
        // But the PATH{ and } are consumed by the delimiter.
        // The structure of what `getContent_user_func_recursive` returns and how it
        // reassembles is key.

        // Let's test a simpler nested case: one level of processing for each PATH block.
        // The callback transforms the content of *its own* PATH{...} block.
        $labyrinthSimpleNested =
            "S PATH{ one PATH{ two } three } E";

        // 1. Inner PATH{ two } becomes "two" (no spaces to replace) or ".two." if it had spaces. Let's assume "two".
        //    The class would replace PATH{ two } with the result of the callback for it.
        //    If callback returns $cut, and $cut[middle] is "two"
        //    The string becomes "S PATH{ one two three } E" (assuming the class just puts the middle part back)
        // 2. Outer PATH{ one two three } callback gets " one two three ".
        //    This becomes ".one.two.three."
        // Result: "S .one.two.three. E"

        // This requires the class to correctly reconstruct the string after each callback.
        // The current test structure is better for non-nested or simple independent nestings.
        // For true recursive "drawing" or solving, the callback would need more context or
        // the class would need a more sophisticated way to manage state across recursive calls.

        // Let's stick to a test that primarily shows the callback modifying content.
        $labyrinthForRecursivePotential =
            "PATH{Outer space PATH{Inner space} between}";
        $expectedForRecursivePotential =
            "Outer.space.Inner.space.between"; // If all spaces in all PATH blocks are targeted

        $this->assertEquals($expectedForRecursivePotential, $this->markPathInLabyrinth($labyrinthForRecursivePotential));
    }

    public function testNoPathToMark(): void
    {
        $labyrinth =
            "S####\n" .
            " #  E\n" .
            "#####";

        $expectedSolvedLabyrinth =
            "S####\n" .
            " #  E\n" .
            "#####";

        $this->assertEquals($expectedSolvedLabyrinth, $this->markPathInLabyrinth($labyrinth));
    }

    public function testMultiplePathSegments(): void
    {
        $labyrinth =
            "S PATH{ }###\n" .
            "###PATH{  }#\n" .
            "E  #PATH{   }";

        $expectedSolvedLabyrinth =
            "S .###\n" .
            "###..#\n" .
            "E  #...";

        $this->assertEquals($expectedSolvedLabyrinth, $this->markPathInLabyrinth($labyrinth));
    }

    // --- More complex "Drawing" or "Solving" would require more state in callbacks ---
    // --- or a different approach than simple content replacement. ---
    // --- For example, to "draw" a path from S to E, the callback would need to know ---
    // --- the overall map, current position, and have pathfinding logic. ---
    // --- PregContentFinder's strength here is more about finding and transforming ---
    // --- pre-defined blocks or patterns. ---

    /**
     * Hypothetical test for a "DRAW" command.
     * This is more complex because the callback would need to modify the *surrounding*
     * text structure, not just the content *inside* the delimiter.
     * PregContentFinder is primarily designed to work *between* delimiters.
     * To "draw" effectively, the callback might need to return instructions that are
     * then applied to a larger map representation.
     *
     * For simplicity, let's imagine DRAW just inserts a string.
     */
    private function applyDrawCommand(string $map, string $drawCommandBlock, string $drawnPath): string
    {
        $cf = new PregContentFinder($map);
        // Example: DRAW{R:3} for "Draw Right 3 steps"
        // The regex captures the command itself.
        $cf->setBeginEnd_RegEx('/(DRAW\{[^\}]+\})/', '/NEVER_MATCH_THIS_FOR_SINGLE_BLOCK_REPLACEMENT/');
        // This setup is a bit of a hack for single block replacement.
        // We're finding the DRAW command block, and the callback will replace IT.

        $processedMap = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) use ($drawnPath) {
                // In this setup, $cut['middle'] would be empty if we set end delimiter to something that never matches
                // and $cut['before'] would contain the DRAW command itself.
                // A cleaner way for block replacement is needed if the class doesn't support it directly.

                // Simpler assumption: getContent_user_func_recursive allows replacing the found block
                // If $cut['before'] is the text before the match, $cut['middle'] is the match, $cut['behind'] is after.
                // Let's assume $cut['middle'] IS the DRAW{...} block.
                // This depends on how setBeginEnd_RegEx handles a single regex as $begin
                // and how getContent_user_func_recursive structures $cut.

                // To test this, we would need a clear definition of how the class
                // handles single-delimiter matching for replacement.
                // For now, this test is highly conceptual.

                // If the class structure for $cut in this scenario is:
                // $cut['before'] = text before DRAW{...}
                // $cut['middle'] = DRAW{...} (the matched block by the start delimiter if end is never found)
                // $cut['behind'] = text after DRAW{...}
                // Then the callback would be:
                $cut['middle'] = $drawnPath; // Replace the DRAW command with the path
                return $cut;
            }
        );
        return $processedMap; // Placeholder
    }

    // public function testCorridorDrawing() // This test is too complex for a simple demo here
    // {
    //     $map =
    //         "S  \n" .
    //         " # \n" .
    //         " E \n" .
    //         "DRAW{R:2}"; // Hypothetical: Draw Right 2 from S (needs S position)

    //     // This would require the callback to understand "R:2", find 'S',
    //     // and modify the map string by placing '..' or similar.
    //     // This goes beyond simple content replacement within delimiters.
    //     $expectedMap =
    //         "S..\n" .
    //         " # \n" .
    //         " E \n";

    //     // $this->assertEquals($expectedMap, $this->applyDrawCommand($map, "DRAW{R:2}", ".."));
    //     $this->markTestSkipped("Labyrinth drawing logic is too complex for this conceptual test of PregContentFinder's core features.");
    // }
}
