<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

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
