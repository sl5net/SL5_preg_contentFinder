<?php
namespace SL5\PregContentFinder\Tests;
use SL5\PregContentFinder\PregContentFinder;

class Callback_Emty_Test extends \PHPUnit\Framework\TestCase {
    function test_alwaysTrueTest(){
        $this->assertTrue(true, "This assertion should always pass.");
    }
    /**
     * @test
     */
    function alwaysFalseTest() {
        $this->assertEquals(0, 9);
    }

    function test_emptyREAL_0_DANGER() {
        $this->assertEquals(true, empty('0'));
    }
    function test_empty_1() {
            $this->assertEquals(false, empty('1'));
    }
    function test_behind_with_a_0() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{9}0';
        $expected = $LINE__ . ':90';
        $old = ['{', '}'];
        $newQuotes = ['', ''];
        $cf = new PregContentFinder($source1);
            $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes) {
              $cut['before'] .= $newQuotes[0];
              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        $this->assertEquals($expected, $actual);
    }

    function test_behind_BBB() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{1}BBB';
        $expected = $LINE__ . ':1BBB';
        $old = ['{', '}'];
        $newQuotes = ['', ''];
        $cf = new PregContentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes) {
              $cut['before'] .= $newQuotes[0];
              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        $this->assertEquals($expected, $actual);
    }
}

?>
