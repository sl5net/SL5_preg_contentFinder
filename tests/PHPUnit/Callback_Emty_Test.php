<?php
//@include_once("../SL5_preg_contentFinder.php");
//
//require("../SL5_preg_contentFinder.php");
$f = 'SL5_preg_contentFinder.php';
while(!file_exists($f)) {
    $f = '../' . $f;
    echo "$f exist.";
}
include_once "../create_1file_withAll_PHPUnit_tests.php"; # ok little overhead. sometimes ;) 15-06-19_12-35
include_once $f;
//include_once "_callbackSh!!ortExample.php";
//include '../../lib/finediff.php';
class Callback_Test extends PHPUnit_Framework_TestCase {
    function test_empty_0_DANGER() {
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(true, emptyLenNot0('0'));
            $this->assertEquals(true, empty('0'));
            # Gibt FALSE zurück, wenn var existiert und einen nicht-leeren, von 0 verschiedenen Wert hat. !!!!!
        }
    }
    function test_emptyREAL_0_DANGER() {
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(true, empty('0'));
            # Gibt FALSE zurück, wenn var existiert und einen nicht-leeren, von 0 verschiedenen Wert hat. !!!!!
        }
    }
    function test_empty_1() {
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(false, empty('1'));
        }
    }
    function test_behind_with_a_0() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{9}0';
        $expected = $LINE__ . ':90';
        $old = ['{', '}'];
        $newQuotes = ['', ''];
        $cf = new SL5_preg_contentFinder($source1);
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
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

    function test_behind_BBB() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{1}BBB';
        $expected = $LINE__ . ':1BBB';
        $old = ['{', '}'];
        $newQuotes = ['', ''];
        $cf = new SL5_preg_contentFinder($source1);
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
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

}

function emptyLenNot0($input) {
    # empty Gibt FALSE zurück, wenn var existiert und einen nicht-leeren, von 0 verschiedenen Wert hat. 
    $strTemp = $input;

    if(isset($strTemp) && $strTemp !== '') //Also tried this "if(strlen($strTemp) > 0)"
    {
        return true;
    }

    return false;
}

?>