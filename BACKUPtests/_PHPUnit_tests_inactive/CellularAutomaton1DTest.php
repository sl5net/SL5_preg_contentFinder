<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

/**
simulating a simple cellular automaton in text – this is another "crazy" application that really stretches `PregContentFinder` but can illustrate its pattern matching and callback transformation capabilities in an interesting way.

--Concept for the Cellular Automaton (Simplified 1D Elementary Automaton):--

-   --Representation:-- A string of characters, where each character is a "cell." For simplicity, let's use `.` for a "dead" cell and `X` for a "live" cell.
-   --Rule:-- We'll implement a very simple rule: A cell becomes live (`X`) in the next generation if exactly one of its immediate neighbors was live in the current generation. Otherwise, it becomes dead (`.`). This is a variation, not a standard elementary CA rule, chosen for simplicity in a text-based regex approach.
-   --Finding "Neighborhoods":-- This is the tricky part with `PregContentFinder`. We're not looking for balanced delimiters in the same way. Instead, we might define a "cell block" that includes a cell and its neighbors, and the callback decides the -next state of the central cell-.
-   --Iteration:-- Cellular automata evolve over generations. A single pass of `PregContentFinder` would represent one generation.

--Challenge:-- `PregContentFinder` is designed to find content -between- delimiters. For a 1D cellular automaton, we're interested in a "sliding window" of, say, 3 cells (left neighbor, current cell, right neighbor). It's not a natural fit for start/end delimiters that consume text.

--Workaround/Approach for the Unit Test:--

1.  We'll make `PregContentFinder` search for -every single cell- as a "block."
2.  The "delimiter" will be a regex that matches any single cell character, but we'll use capturing groups to also "see" its left and right neighbors if they exist.
3.  The callback will then look at the captured neighbors and the current cell to determine the next state of the -current cell-.
4.  This approach means the callback effectively rebuilds the string cell by cell for the next generation.

This is a bit of a hack because `PregContentFinder` isn't designed as a map-cellular-automaton tool, but we can make it work for a demonstration.

--Explanation and Why This is a "Stretch" for `PregContentFinder`:--

1.  --`simulateOneGeneration(string $currentState)`:--
    -   This is a standard, direct PHP implementation of the defined 1D cellular automaton rule. It iterates through the string, checks neighbors, and builds the next state. This function serves as the "oracle" or the source of truth for what the expected output should be.

2.  --`simulateWithPregContentFinder(string $currentState)`:--
    -   --The Hack:-- To make `PregContentFinder` process the string, we set its delimiters to match the -entire string once- (`setBeginEnd_RegEx('/^/', '/$/')`).
    -   --Callback is Key:-- The entire simulation logic is then moved -inside- the callback function. The callback receives the whole current state string as `$cut['middle']`.
    -   --Re-implementing Iteration:-- Inside the callback, we essentially re-implement the same character-by-character iteration and rule application as in `simulateOneGeneration`.
    -   --Return Value:-- The callback modifies `$cut['middle']` to be the new state string and returns `$cut`. `PregContentFinder` (in this specific setup) will then return this new string.

3.  --Test Methods:--
    -   They define an initial state.
    -   They assert that both `simulateOneGeneration` (direct method) and `simulateWithPregContentFinder` (PCF method) produce the same, correct next generation. This ensures our PCF "simulation" matches a known-good implementation.

--Why this is "Crazy" or a "Stretch" for `PregContentFinder`:--

-   --Not Block-Based:-- Cellular automaton are typically about local rules applied across a grid or line of cells simultaneously (or appearing so). `PregContentFinder` is designed for finding and transforming discrete, delimited -blocks- of text. We're forcing it to treat the entire string as one block, and then the callback does the "real" CA work.
-   --No Use of Delimiter Power:-- The power of `PregContentFinder`'s regex delimiters and its ability to handle nested structures is largely unused here. We're just using it as a mechanism to call a function once with the whole string.
-   --Inefficiency:-- Using `PregContentFinder` for this is likely less efficient than a direct loop in PHP, as PCF has overhead for its regex matching and internal state, which we are mostly bypassing.

--What this Unit Test -Does- Demonstrate (Indirectly):--

-   --Callback Flexibility:-- It shows that the callback mechanism of `getContent_user_func_recursive` is powerful enough that you -can- put almost any string transformation logic inside it, even if it means the callback takes over most of
    the work.
-   --Conceptual Application:-- It's a way to think about `PregContentFinder` as a generic "find a region and let me do something with it" tool, even if the "region" is the whole document and the "something" is complex.

--In a real-world scenario, you would NOT use `PregContentFinder` this way to implement a cellular automaton.-- A direct loop is far more appropriate and efficient. However, as a "crazy application" to test the limits of its callback system, it's an interesting exercise.

The test primarily validates that if you give `PregContentFinder` a string and a callback that knows how to produce the next CA generation from that string, `PregContentFinder` will correctly apply that callback and return its result.
  
 */

class CellularAutomaton1DTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Simulates one generation of a simple 1D cellular automaton.
     * Rule: A cell becomes live ('X') if exactly one of its immediate neighbors is live.
     *       Otherwise, it becomes dead ('.').
     * Boundary conditions: Cells outside the string are considered dead ('.').
     */
    private function simulateOneGeneration(string $currentState): string
    {
        $nextState = '';
        $length = strlen($currentState);

        for ($i = 0; $i < $length; $i++) {
            $leftNeighbor = ($i > 0) ? $currentState[$i-1] : '.';
            $currentCell = $currentState[$i];
            $rightNeighbor = ($i < $length - 1) ? $currentState[$i+1] : '.';

            $liveNeighbors = 0;
            if ($leftNeighbor === 'X') {
                $liveNeighbors++;
            }
            if ($rightNeighbor === 'X') {
                $liveNeighbors++;
            }

            if ($liveNeighbors === 1) {
                $nextState .= 'X';
            } else {
                $nextState .= '.';
            }
        }
        return $nextState;
    }

    /**
     * This function demonstrates how one *might* attempt to use PregContentFinder
     * for such a task. It's not its primary design goal, so it's a bit forced.
     * The callback will determine the next state of each cell.
     *
     * PregContentFinder is better at block-based transformations. For CAs, iterating
     * character by character (like in simulateOneGeneration) is usually more direct.
     * This is more of a conceptual test of callback power.
     */
    private function simulateWithPregContentFinder(string $currentState): string
    {
        $cf = new PregContentFinder($currentState);

        // We define delimiters that essentially try to capture each cell and its context.
        // This is tricky. A simpler approach for PregContentFinder might be to find patterns
        // that *cause* a change, e.g., ".X." -> "X" in the middle.
        // But to simulate cell by cell evolution with PCF, we make each cell a "block".

        // Regex to match any single character (our cell), and try to capture its neighbors.
        // This regex is designed to be called repeatedly, advancing one character at a time.
        // PCF's getBorders might not naturally do this sliding window.
        // Let's assume we are processing the string from left to right,
        // and the callback can access the original string to see neighbors.
        // This makes the use of PCF's delimiters less direct.

        // A more direct PCF approach would be to have the callback itself iterate.
        // Let's simplify: the callback receives the *whole string* in $cut['middle']
        // if we set delimiters that match nothing or the whole string once.
        // Then the callback does the iteration.

        $cf->setBeginEnd_RegEx('/^/', '/$/'); // Match the whole string as one block

        $newStateString = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
                // In this setup, $cut['middle'] is the entire current state string.
                $currentStateString = $cut['middle'];
                $nextState = '';
                $length = strlen($currentStateString);

                for ($i = 0; $i < $length; $i++) {
                    $leftNeighbor = ($i > 0) ? $currentStateString[$i-1] : '.';
                    // $currentCell = $currentStateString[$i]; // Not needed for this rule based on neighbors
                    $rightNeighbor = ($i < $length - 1) ? $currentStateString[$i+1] : '.';

                    $liveNeighbors = 0;
                    if ($leftNeighbor === 'X') {
                        $liveNeighbors++;
                    }
                    if ($rightNeighbor === 'X') {
                        $liveNeighbors++;
                    }

                    if ($liveNeighbors === 1) {
                        $nextState .= 'X';
                    } else {
                        $nextState .= '.';
                    }
                }
                $cut['middle'] = $nextState;
                return $cut;
            }
        );
        return $newStateString;
    }


    public function testSimpleEvolutionRule30LikePattern(): void
    {
        // This test uses the direct simulation logic first to establish expected output,
        // then it would ideally test the PregContentFinder based simulation.
        // Rule: A cell is 'X' if (left XOR right) for its neighbors.
        // This is similar to Wolfram's Rule 30 if you map states appropriately.
        // Our rule: 'X' if exactly one neighbor is 'X'.

        $initialState = "...X...";
        // Gen 1:
        // Pos 0 (.): L=., R=.; N=0 -> .
        // Pos 1 (.): L=., R=X; N=1 -> X
        // Pos 2 (.): L=., R=X; N=1 -> X
        // Pos 3 (X): L=., R=.; N=0 -> .
        // Pos 4 (.): L=X, R=.; N=1 -> X
        // Pos 5 (.): L=X, R=.; N=1 -> X
        // Pos 6 (.): L=X, R=.; N=1 -> X
        $expectedGen1 = ".XX.XXX";
        $this->assertEquals($expectedGen1, $this->simulateOneGeneration($initialState));
        $this->assertEquals($expectedGen1, $this->simulateWithPregContentFinder($initialState));

        // Gen 2 from ".XX.XXX":
        // .XX.XXX
        // LCR N -> Next
        // . .X 1 -> X (Pos 0)
        // . X. 0 -> . (Pos 1)
        // X X. 1 -> X (Pos 2)
        // . .X 1 -> X (Pos 3)
        // . XX 2 -> . (Pos 4)
        // X X. 1 -> X (Pos 5)
        // X X. 1 -> X (Pos 6)
        $expectedGen2 = "X.XX.XX";
        $this->assertEquals($expectedGen2, $this->simulateOneGeneration($expectedGen1));
        $this->assertEquals($expectedGen2, $this->simulateWithPregContentFinder($expectedGen1));
    }

    public function testAllDeadRemainsDead(): void
    {
        $initialState = ".......";
        $expectedGen1 = ".......";
        $this->assertEquals($expectedGen1, $this->simulateOneGeneration($initialState));
        $this->assertEquals($expectedGen1, $this->simulateWithPregContentFinder($initialState));
    }

    public function testSolidBlockDiesOut(): void
    {
        $initialState = ".XXXXX.";
        // Gen 1 from ".XXXXX.":
        // .XXXXX.
        // LCR N -> Next
        // . .X 1 -> X (Pos 0)
        // . XX 2 -> . (Pos 1)
        // X XX 2 -> . (Pos 2)
        // X XX 2 -> . (Pos 3)
        // X X. 1 -> X (Pos 4)
        // X . . 1 -> X (Pos 5)
        // . . . 0 -> . (Pos 6)
        $expectedGen1 = "X...XX."; // Corrected based on rule
        $this->assertEquals($expectedGen1, $this->simulateOneGeneration($initialState));
        $this->assertEquals($expectedGen1, $this->simulateWithPregContentFinder($initialState));

        // Gen 2 from "X...XX.":
        // X...XX.
        // LCR N -> Next
        // . X. 0 -> . (Pos 0)
        // X .. 0 -> . (Pos 1)
        // . .. 0 -> . (Pos 2)
        // . .X 1 -> X (Pos 3)
        // . XX 2 -> . (Pos 4)
        // X X. 1 -> X (Pos 5)
        // X . . 1 -> X (Pos 6)
        $expectedGen2 = "...X.XX";
        $this->assertEquals($expectedGen2, $this->simulateOneGeneration($expectedGen1));
        $this->assertEquals($expectedGen2, $this->simulateWithPregContentFinder($expectedGen1));
    }

    public function testOscillator(): void
    {
        // This simple rule might not produce many oscillators easily.
        // Let's test a small pattern.
        $initialState = ".X.X.";
        // Gen 1 from ".X.X.":
        // .X.X.
        // LCR N -> Next
        // . .X 1 -> X (Pos 0)
        // . X. 0 -> . (Pos 1)
        // X .X 2 -> . (Pos 2)
        // . X. 0 -> . (Pos 3)
        // X . . 1 -> X (Pos 4)
        $expectedGen1 = "X...X";
        $this->assertEquals($expectedGen1, $this->simulateOneGeneration($initialState));
        $this->assertEquals($expectedGen1, $this->simulateWithPregContentFinder($initialState));

        // Gen 2 from "X...X":
        // X...X
        // LCR N -> Next
        // . X. 0 -> . (Pos 0)
        // X .. 0 -> . (Pos 1)
        // . .. 0 -> . (Pos 2)
        // . .X 1 -> X (Pos 3)
        // . X. 0 -> . (Pos 4)
        $expectedGen2 = "...X.";
        $this->assertEquals($expectedGen2, $this->simulateOneGeneration($expectedGen1));
        $this->assertEquals($expectedGen2, $this->simulateWithPregContentFinder($expectedGen1));
    }
}
