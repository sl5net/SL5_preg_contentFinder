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
include_once "_callbackShortExample.php";


class Callback_Test extends PHPUnit_Framework_TestCase {
//     $collectString = '';

    function test_tag_to_UPPER_lower() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ': jO_2_maN nO_9_uP';
        $expected = $LINE__ . ': JO_2_man NO_9_up';
        $old_beginEnd = ['[a-zA-Z]+_', '_[a-zA-Z]+'];

        $cf = new SL5_preg_contentFinder($source1, $old_beginEnd);
        $cf->setSearchMode('dontTouchThis');

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) {
              if($cut['middle'] === false) return $cut;

              $new_open_default = strtoupper(substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']));
              $new_close_default = strtolower(substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin']));

              # put everything ! into middle and begin not into behind!
              $cut['middle'] = $new_open_default . $cut['middle'] . $new_close_default . $cut['behind'];

              return $cut;
          });

        $this->assertEquals($expected, $actual);

    }

    function test_simple_a_A_â__o_O_ô() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':a{1}a b{2}b';
        $expected = $LINE__ . ':a{1}A b{2}B';

        $old_open = '\w{';
        $old_close = '}\w';

        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = "";
        $newline = "\r\n";
        $newline = "";
        $indentSize = 2;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);
        $cf->setSearchMode('dontTouchThis');


        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $foundPos_list, $source1) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;

              $n = $newline;
              $indentStr0 = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $indentStr1 = $getIndentStr($deepCount, $charSpace, $indentSize);

//              $foundPos_list, $source1

              $posList0 = $foundPos_list;
              $new_open_default = substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']);
              $new_close_default = strtoupper(substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin']));


              $cut['middle'] = $n . $indentStr0 . $new_open_default . $n . $indentStr1 . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr1 . "$1", $cut['middle']) . $n
                . $indentStr0 . $new_close_default . $cut['behind'];

              return $cut;
          });

        $this->assertEquals($expected, $actual);

    }


    function test_parse_expected_actual() {
//        $file_content_original = file_get_contents(__FILE__);
        $file_content_original = file_get_contents('PHPUnitAllTest_AutoCollected.php');
        $mode = 1;
        if($mode == 2) {
            $file_content_original
              =
              'function a() {a} function b() {b}';
        }
        $cf = new SL5_preg_contentFinder($file_content_original);
        $cf->setSearchMode('dontTouchThis');
        if($mode == 2) {
            $beginEnd = ['function \w+\(\)\s*\{', '\}'];
        }
        else {
            $beginEnd = [
              '\n\s+'
              . 'function\s+'
              . '\w+\s*'
              . '\(\)\s*\{'
              . '\s*\n'
              ,
              '\}\s*function'];
        }

//        $beginEnd = ['\{'
//                     ,
//        '\}'];


        $cf->setBeginEnd_RegEx($beginEnd);
//        $collectString = &$this->collectString;
        $fileName = 'examples_extracted_from_UnitTest.txt';
        @unlink($fileName);

        $msg = 'following examples are automatically extracted from the UnitTest source. For implementation please look into the UnitTest( https://github.com/sl5net/SL5_preg_contentFinder/tree/master/tests/PHPUnit ).';
        file_put_contents($fileName, "\n\n" . $msg . "\n\n", FILE_APPEND | LOCK_EX);


        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($fileName) {
              if(strpos($cut['middle'], '$expected') !== false) {
                  if($cut['middle'] === false) return $cut;
                  $m = &$cut['middle'];

                  $functionName = preg_replace(
                    '/\s*FUNCTION\s+TEST_([\w_\w]+)\(\s*\).*/is',
                    "$1", substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']));


                  $reg_expected = ['\$expected\s*=[^\'"]*("|\')', '("|\')'];
                  $reg_source = ['\$source1\s*=[^\'"]*("|\')', '("|\')'];

//                  $source1 = $LINE__ . ':a{b{c{o}c}b}a';


                  $p = new SL5_preg_contentFinder($m, $reg_expected);
                  $p->setSearchMode('dontTouchThis');
                  $expectedStr = $p->getContent();

                  $p->setBeginEnd_RegEx($reg_source);
                  $sourceStr = $p->getContent();

                  if($functionName && trim($sourceStr) && strpos($sourceStr, '$cfEmpty_IfEmptyResult') === false) {
                      $msg = "Example source conversion '$functionName':

$sourceStr

==>

$expectedStr

______________________
";
                      file_put_contents($fileName, "\n\n" . $msg . "\n\n", FILE_APPEND | LOCK_EX);
                  }

              }
              else {
//                  $cut['before'] = "\n";
              }
              $cut['middle'] .= $cut['behind'];

              return $cut;
          });
        $break = 'break';
        $break = 'break';
//        $collectString;
    }


    function test_a_b_B_callback() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':a{b{B}}';
        $expected = $LINE__ . ':a<b<B>>';
        $old = ['{', '}'];

        $new_open_default = '<';
        $new_close_default = '>';
        $charSpace = "";
        $newline = "\r\n";
        $newline = "";
        $indentSize = 2;
        $source1 = $source1;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $new_open_default;
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $actual);

    }


    function test_recursive_02() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $source1 = $LINE__ . ':a{b{c{o}c}b}a';

        $expected = $LINE__ . ':a[_´b[_´´c[_´´´o_´´`]_´´c_´`]_´b_`]_a';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = ".";
        $newline = "_";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
              $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $new_open_default;
//              return $cut  ;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
              $charSpace = '´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) {
//                  return $cut['behind'];
//                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
              $charSpace = '`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

//              $cut['behind'] .= $indentStr . $new_close_default . $n;
              $cut['middle'] .= $indentStr . $new_close_default . $n . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        $this->assertEquals($expected, $actual);

    }


    function test_simple3() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':
a{b{B}}';
        $old_open = '{';
        $old_close = '}';
        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;
        $source1 = $source1;
        $expected = $LINE__ . ':
a
1|[
1:..b
2|..[
2:....B
2:..]
1:]';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
              $n .= $deepCount . '|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $new_open_default;

              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
              $n .= $deepCount . ':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
              $n .= $deepCount . ';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $cut['behind'];

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $actual);

    }
    function test_simple() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':
a{A}b{B}';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;

        $source1 = $source1;

//        $expected = $LINE__.':1[_.2[_..3[_...o_...]_3_..]_2_.]_1';
        $expected = $LINE__ . ':
a
1|[
1:..A
1:]
1;b
1|[
1:..B
1:]
1;';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
              $n .= $deepCount . '|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $n . $indentStr . $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
              $n .= $deepCount . ':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
              $n .= $deepCount . ';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

//              $cut['behind'] .= $indentStr . $new_close_default . $n;
//              $cut['behind'] = $indentStr . $new_close_default . $n . $cut['behind'];
              $cut['middle'] .= $indentStr . $new_close_default . $n . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $actual);

    }


    function test_15_() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':
if(a1){$A1;}if(a2){$A2;}';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;

        $source1 = $source1;

//        $expected = $LINE__.':1[_.2[_..3[_...o_...]_3_..]_2_.]_1';
        $expected = $LINE__ . ':
if(a1)
1|[
1:..$A1;
1:]
1;if(a2)
1|[
1:..$A2;
1:]
1;';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
              $n .= $deepCount . '|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $n . $indentStr . $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
              $n .= $deepCount . ':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
              $n .= $deepCount . ';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

//              $cut['behind'] .= $indentStr . $new_close_default . $n;
              $cut['middle'] .= $indentStr . $new_close_default . $n . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $actual);

    }


    function test_shortest_new_close_recursive() {
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $source1 = $LINE__ . ':{}';
        $expected = $LINE__ . ':{#';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = $old_open;
        $new_close_default = '#';
//        $new_close_default = $old_close; // this line is reason for endless loop
        $charSpace = "";
        $newline = "";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        $this->assertEquals($expected, $actual);

    }


    function test_reformat_compressed_AutoHotKey() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $source1 = $LINE__ . ':{{o}}';
        $expected = $LINE__ . ':{n {n on >n >';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = $old_open;
        $new_close_default = '>';
//        $new_close_default = $old_close; // this line is reason for endless loop
        $charSpace = " ";
        $newline = "n";
//        $newline = "aölsdkfjösaldkjfsöalfdkj"; // see closure functions
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $cut['behind'];

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
//      {{o}}
//    $actual = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $actual);

    }


    function test_shortest_lette_in_middle_recursive() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{l}';
        $expected = $LINE__ . ':{l}';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '{';
        $new_close_default = '}';
//        $new_close_default = $old_close; // this line is reason for endless loop
        $charSpace = "";
        $newline = "";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // , function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        $this->assertEquals($expected, $actual);

    }


    function test_recursive_01() {
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{k}';
        $expected = $LINE__ . ':[k]';
        $old_open = '{';
        $old_close = '}';
        $charSpace = ' ';
        $charSpace = '';
        $newline = "\n";
        $newline = "";
        $indentSize = 1;
        $new_open_default = '[';
        $new_close_default = ']';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
        $this->assertEquals($expected, $actual);
    }

    function test_shortest_new_open_recursive() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{}';
        $expected = $LINE__ . ':#}';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '#';
        $new_close_default = '}';
//        $new_close_default = $old_close; // this line is reason for endless loop
        $charSpace = "";
        $newline = "";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $new_close_default . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        $this->assertEquals($expected, $actual);

    }


    function test_recursion_simplyReproduction() {
        # this recursion is deprecated and not implemented into the core class. so dont waste time ;)
//        return false;
        $expected = 'A {11{22{3}{2}22}11}{1} B';
        $cf = new SL5_preg_contentFinder($expected);
        list($c, $bf, $bh) = recursion_simplyReproduction($expected);
        $actual = $bf . $c . $bh;
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($expected, $actual);
    }


    /**
     * using class SL5_preg_contentFinder
     * and ->getContent_user_func_recursive.
     * in a case i don't like this style using closures to much. so you only need one function (advantage) from the outside. but looks more ugly from the inside. not best way for debugging later (inside). you need to compare, decide for your business.
     */

    function test_callback_with_closures() {
        $source1 = '_if(X1){$X1;if(X2){$X2;}}';
        $expected = '_if(X1)[
..$X1;if(X2)[
....$X2;
..]
]';
        $old_open = '{';
        $old_close = '}';
        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $new_open_default;
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n]+)/', $n . $indentStr . "$1", $cut['middle']);

//          $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='-';
//          $indentStr = $getIndentStr(0, $charSpace, $indentSize);

              $cut['middle'] .= $n . $new_close_default;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

        $this->assertEquals($expected,
          $actual);
    }


    function test_reformatCode_recursion_add() {
        $source1 = "if(InStr(tc,needle)){win:=needle}else{win:=needle2}";
        $expected =
          "if(InStr(tc,needle)){
   win:=needle;
}else{
   win:=needle2;
}";
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        list($c, $bf, $bh) = self::recursion_add($source1, "{\r\n   ", ";\r\n}");
        $actual = $bf . $c . $bh;
        $this->assertEquals($expected, $actual);
        $this->assertEquals(strlen($expected), strlen($actual));
    }


    static function recursion_add(
      $content,
      $addBefore = null,
      $addBehind = null,
      $before = null,
      $behind = null
    ) {
        $isFirstRecursion = is_null($before); # null is used as trigger for first round.
        $cf = new SL5_preg_contentFinder($content);
        if($cut['middle'] = @$cf->getContent($b = '{', $e = '}')) {
            $before .= $cf->getContent_Before() . $addBefore;
            $behindTemp = $cf->getContent_Behind() . $behind;

            if($isFirstRecursion) {
                list($c, $bf, $bh) =
                  self::recursion_add($behindTemp,
                    $addBefore,
                    $addBehind); // this version of recursion also includes the rest of contentDemo.
                $behind = (is_null($c)) ? $addBehind . $behindTemp : $addBehind . $bf . $c . $bh;
            }
            else {
                $behind = $addBehind . $behindTemp;
            }

            $return = self::recursion_add(
              $cut['middle'],
              $addBefore,
              $addBehind,
              $before,
              $behind
            );

            return $return;
        }
        $return = array($content, $before, $behind); // core element.
        return $return;
    }


}

?>