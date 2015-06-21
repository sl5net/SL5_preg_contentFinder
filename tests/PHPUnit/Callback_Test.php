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


    function test_recursive_02() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $file_content_compressed = $LINE__ . ':a{b{c{o}c}b}a';

        $expected = $LINE__ . ':a[_´b[_´´c[_´´´o_´´`]_´´c_´`]_´b_`]_a';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '[';
        $new_close_default = ']';
        $charSpace = ".";
        $newline = "_";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($file_content_compressed);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $file_content_reformatted = $cf->getContent_user_func_recursive(
          function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
              $charSpace = "'";
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
              return $before  . $new_open_default  ;
          },
          function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
              $charSpace ='´';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
              $cut .= $n;

              return $cut;
          },
          function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut === false || $behind === false){
//                  return $behind;
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
              $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              return $indentStr . $new_close_default . $n ;
              # todo: $behind dont need newline at the beginning
          });

//      {{o}}

        $this->assertEquals($expected, $file_content_reformatted);

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
        $file_content_compressed = $source1;
        $expected = $LINE__ . ':
a
1|[
1:..b
2|..[
2:....B
2:..]
1:]';

        $cf = new SL5_preg_contentFinder($file_content_compressed);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $file_content_reformatted = $cf->getContent_user_func_recursive(
          function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
              $n = $newline;
              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
              return $before  . $n . $indentStr . $new_open_default  ;
          },
          function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut === false) return $cut;
              $n = $newline;
              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
              $cut .= $n;

              return $cut;
          },
          function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut === false || $behind === false){
//                  return $behind;
                  return false;
              }
              $n = $newline;
              $n .= $deepCount.';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);

              return $indentStr . $new_close_default  ;
              # todo: $behind dont need newline at the beginning
          });

//      {{o}}
//        $file_content_reformatted = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $file_content_reformatted);


    }
    function test_simple2() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':a{b{B}}';
        $old_open = '{';
        $old_close = '}';

        $new_open_default = '<';
        $new_close_default = '>';
        $charSpace = "";
        $newline = "\r\n";
        $newline = "";
        $indentSize = 2;

        $file_content_compressed = $source1;

//        $expected = $LINE__.':1[_.2[_..3[_...o_...]_3_..]_2_.]_1';
        $expected = $LINE__ . ':a<b<B>>';
//        $expected = $LINE__ . ':a<b<B>>';


        $cf = new SL5_preg_contentFinder($file_content_compressed);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $file_content_reformatted = $cf->getContent_user_func_recursive(
          function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
              return $before  . $n . $indentStr . $new_open_default  ;
          },
          function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
              $cut .= $n;

              return $cut;
          },
          function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut === false || $behind === false){
//                  return $behind;
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);

              return $indentStr . $new_close_default  ;
              # todo: $behind dont need newline at the beginning
          });

//      {{o}}
//        $file_content_reformatted = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $file_content_reformatted);


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

        $file_content_compressed = $source1;

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

        $cf = new SL5_preg_contentFinder($file_content_compressed);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null
        $file_content_reformatted = $cf->getContent_user_func_recursive(
          function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
              $n = $newline;
              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
              return $before  . $n . $indentStr . $new_open_default  ;
          },
          function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut === false) return $cut;
              $n = $newline;
              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
              $cut .= $n;

              return $cut;
          },
          function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut === false || $behind === false){
//                  return $behind;
                  return false;
              }
              $n = $newline;
              $n .= $deepCount.';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);

              return $indentStr . $new_close_default . $n ;
              # todo: $behind dont need newline at the beginning
          });

//      {{o}}
//        $file_content_reformatted = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $file_content_reformatted);


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

        $file_content_compressed = $source1;

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

        $cf = new SL5_preg_contentFinder($file_content_compressed);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $file_content_reformatted = $cf->getContent_user_func_recursive(
          function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
              $n = $newline;
              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
              return $before  . $n . $indentStr . $new_open_default  ;
          },
          function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut === false) return $cut;
              $n = $newline;
              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
              $cut .= $n;

              return $cut;
          },
          function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut === false || $behind === false){
//                  return $behind;
                  return false;
              }
              $n = $newline;
              $n .= $deepCount.';';
//              $charSpace ='´';
              $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);

              return $indentStr . $new_close_default . $n ;
              # todo: $behind dont need newline at the beginning
          });

//      {{o}}
//        $file_content_reformatted = $cBefore . $content . $cBehind;

        $this->assertEquals($expected, $file_content_reformatted);


    }




function test_shortest_new_close_recursive() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $LINE__ = __LINE__;

    $file_content_compressed = $LINE__ . ':{}';
    $expected = $LINE__ . ':{#';
    $old_open = '{';
    $old_close = '}';

    $new_open_default = $old_open;
    $new_close_default = '#';
//        $new_close_default = $old_close; // this line is reason for endless loop
    $charSpace = "";
    $newline = "";
    $indentSize = 1;

    $cf = new SL5_preg_contentFinder($file_content_compressed);
    $cf->setBeginEnd_RegEx($old_open, $old_close);

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    };

    $file_content_reformatted = $cf->getContent_user_func_recursive(
      function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
          $n = $newline;
//          $n .= $deepCount.'|';
          $charSpace = "'";
          $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
          return $before  . $new_open_default  ;
      },
      function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
          if($cut === false) return $cut;
          $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
          $indentStr = $getIndentStr(1, $charSpace, $indentSize);
          $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
          $cut .= $n;

          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
          if($cut === false || $behind === false){
//                  return $behind;
              return false;
          }
          $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
          $indentStr = $getIndentStr(1, $charSpace, $indentSize);

          return $indentStr . $new_close_default . $n ;
          # todo: $behind dont need newline at the beginning
      });

//      {{o}}

    $this->assertEquals($expected, $file_content_reformatted);

}


function test_reformat_compressed_AutoHotKey() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $LINE__ = __LINE__;

    $file_content_compressed = $LINE__ . ':{{o}}';
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

    $cf = new SL5_preg_contentFinder($file_content_compressed);
    $cf->setBeginEnd_RegEx($old_open, $old_close);

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    };

    $file_content_reformatted = $cf->getContent_user_func_recursive(
      function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
          $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
          $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
          return $before  . $new_open_default  ;
      },
      function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
          if($cut === false) return $cut;
          $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
          $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
          $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
          $cut .= $n;

          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
          if($cut === false || $behind === false){
//                  return $behind;
              return false;
          }
          $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
          $indentStr = $getIndentStr(1, $charSpace, $indentSize);

          return $indentStr . $new_close_default  ;
          # todo: $behind dont need newline at the beginning
      });
//      {{o}}
//    $file_content_reformatted = $cBefore . $content . $cBehind;

    $this->assertEquals($expected, $file_content_reformatted);

}



function test_shortest_lette_in_middle_recursive() {
    return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $LINE__ = __LINE__;

    $file_content_compressed = $LINE__ . ':{l}';
    $expected = $LINE__ . ':{l}';
    $old_open = '{';
    $old_close = '}';

    $new_open_default = '{';
    $new_close_default = '}';
//        $new_close_default = $old_close; // this line is reason for endless loop
    $charSpace = "";
    $newline = "";
    $indentSize = 1;

    $cf = new SL5_preg_contentFinder($file_content_compressed);
    $cf->setBeginEnd_RegEx($old_open, $old_close);

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    };

    list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
      function ($before, $cut, $behind, $deepCount) use ($new_open_default) {
          if($deepCount > 50) {
              die(__LINE__ . ':to much for this example. $deepCount=' . $deepCount);
          }

          return $before . $new_open_default;
      },
      function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
          if($deepCount > 55) {
              die(__LINE__ . ':to much for this example. $deepCount=' . $deepCount);
          }
          if($cut === false) return $cut;
          $n = $newline;
          $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
          $cut = $n . $indentStr . preg_replace('/' . $n . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
          $cut .= $n;

          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
          if($deepCount > 50) {
              die(__LINE__ . ':to much for this example. $deepCount=' . $deepCount);
          }
          if($cut === false || $behind === false) return $behind;
          $n = $newline;
          $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

          return $indentStr . $new_close_default . $n . ltrim($behind);
          # todo: $behind dont need newline at the beginning
      });

//      {{o}}
    $file_content_reformatted = $cBefore . $content . $cBehind;

    $this->assertEquals($expected, $file_content_reformatted);

}


function test_recursive_01() {
    return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $LINE__ = __LINE__;
    $file_content_compressed = $LINE__ . ':{k}';
    $expected = $LINE__ . ':[k]';
    $old_open = '{';
    $old_close = '}';

    $new_open_default = '[';
    $new_close_default = ']';

    $cf = new SL5_preg_contentFinder($file_content_compressed);
    $cf->setBeginEnd_RegEx($old_open, $old_close);

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    };

    list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
      function ($before) use ($new_open_default) {
          return $before . $new_open_default;
      },
      function ($before, $cut) {
          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default) {
          if($cut === false) return $behind;

          return $new_close_default;
      });
    $file_content_reformatted = $cBefore . $content . $cBehind;
    $this->assertEquals($expected, $file_content_reformatted);
}

function test_shortest_new_open_recursive() {
    return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $LINE__ = __LINE__;

    $file_content_compressed = $LINE__ . ':{}';
    $expected = $LINE__ . ':#}';
    $old_open = '{';
    $old_close = '}';

    $new_open_default = '#';
    $new_close_default = '}';
//        $new_close_default = $old_close; // this line is reason for endless loop
    $charSpace = " ";
    $newline = "n";
    $indentSize = 1;

    $cf = new SL5_preg_contentFinder($file_content_compressed);
    $cf->setBeginEnd_RegEx($old_open, $old_close);

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    };

    list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
      function ($before, $cut, $behind, $deepCount) use ($new_open_default) {
          if($deepCount > 50) {
              die(__LINE__ . ':to much for this example. $deepCount=' . $deepCount);
          }

          return $before . $new_open_default;
      },
      function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
          if($deepCount > 55) {
              die(__LINE__ . ':to much for this example. $deepCount=' . $deepCount);
          }
          if($cut === false) return $cut;
          $n = $newline;
          $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
          $cut = $n . $indentStr . preg_replace('/' . $n . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
          $cut .= $n;

          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
          if($deepCount > 50) {
              die(__LINE__ . ':to much for this example. $deepCount=' . $deepCount);
          }
          if($cut === false) return $behind;
          $n = $newline;
          $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

          return $indentStr . $new_close_default . $n . ltrim($behind);
          # todo: $behind dont need newline at the beginning
      });

//      {{o}}
    $file_content_reformatted = $cBefore . $content . $cBehind;

    $this->assertEquals($expected, $file_content_reformatted);

}

//    function test_shortest_new_close_recursive() {
////        return true;
////    include_once 'input_compressed.ahk'
////    $file_content_original = file_get_contents('SciTEUpdate.ahk');
//        $LINE__=__LINE__;
//
//        $file_content_compressed = $LINE__.':{}';
//        $expected = $LINE__.':{#';
//        $old_open = '{';
//        $old_close = '}';
//
//        $new_open_default = $old_open;
//        $new_close_default = '#';
////        $new_close_default = $old_close; // this line is reason for endless loop
//        $charSpace = " ";
//        $newline = "n";
//        $indentSize = 1;
//
//        $cf = new SL5_preg_contentFinder($file_content_compressed);
//        $cf->setBeginEnd_RegEx($old_open, $old_close);
//
//        $getIndentStr = function ($indent, $char, $indentSize) {
//            $multiplier = $indentSize * $indent;
//            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));
//
//            return $indentStr;
//        };
//
//        list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
//          function ($before, $cut, $behind, $deepCount) use ($new_open_default) {
//              if($deepCount>50)
//              {
//                  die(__LINE__. ':to much for this example. $deepCount=' . $deepCount);
//              }
//              return $before . $new_open_default; },
//          function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
//              if($deepCount>55)
//              {
//                  die(__LINE__. ':to much for this example. $deepCount=' . $deepCount);
//              }
//              if($cut === false) return $cut;
//              $n = $newline;
//              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
//              $cut = $n . $indentStr . preg_replace('/' . $n . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
//              $cut .= $n;
//
//              return $cut;
//          },
//          function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
//              if($deepCount>50)
//              {
//                  die(__LINE__. ':to much for this example. $deepCount=' . $deepCount);
//              }
//              if($cut === false || $behind === false) return $behind;
//              $n = $newline;
//              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
//
//              return $indentStr . $new_close_default . $n . ltrim($behind);
//              # todo: $behind dont need newline at the beginning
//          });
//
////      {{o}}
//        $file_content_reformatted = $cBefore . $content . $cBehind;
//
//        $this->assertEquals($expected, $file_content_reformatted);
//
//    }



public
function test_recursion_simplyReproduction() {
    # this recursion is deprecated and not implemented into the core class. so dont waste time ;)
//        return false;
    $source = 'A {11{22{3}{2}22}11}{1} B';
    $cf = new SL5_preg_contentFinder($source);
    list($c, $bf, $bh) = recursion_simplyReproduction($source);
    $cut = $bf . $c . $bh;
    $cf->setBeginEnd_RegEx('{', '}');
    $this->assertEquals($source, $cut);
}









/**
 * using class SL5_preg_contentFinder
 * and ->getContent_user_func_recursive.
 * in a case i don't like this style using closures to much. so you only need one function (advantage) from the outside. but looks more ugly from the inside. not best way for debugging later (inside). you need to compare, decide for your business.
 */
public
function test_callback_with_closures() {
    $source1 = 'if(X1){$X1;if(X2){$X2;}}';
    $expected = 'if(X1)[
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

    $source2 = $cf->getContent_user_func_recursive(
      function ($before, $cut, $behind, $deepCount) use ($new_open_default,$charSpace, $newline, $indentSize,$getIndentStr) {
          $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
          $indentStr = $getIndentStr($deepCount-1, $charSpace, $indentSize);
          return $before  . $new_open_default  ;
      },
      function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
          if($cut === false) return $cut;
          $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='´';
          $indentStr = $getIndentStr(1, $charSpace, $indentSize);
          $cut = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n]+)/', $n . $indentStr . "$1", $cut);
//          $cut .= $n;

          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
          if($cut === false || $behind === false){
//                  return $behind;
              return false;
          }
          $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='-';
//          $indentStr = $getIndentStr(0, $charSpace, $indentSize);

          return $n . $new_close_default  ;
          # todo: $behind dont need newline at the beginning
      });

    $this->assertEquals($expected,
      $source2);
}


public
function test_reformatCode_recursion_add() {
    $source1 = "if(InStr(tc,needle)){win:=needle}else{win:=needle2}";
    $source2 =
      "if(InStr(tc,needle)){
   win:=needle;
}else{
   win:=needle2;
}";
    $cf = new SL5_preg_contentFinder($source1);
    $cf->setBeginEnd_RegEx('{', '}');
    list($c, $bf, $bh) = self::recursion_add($source1, "{\r\n   ", ";\r\n}");
    $cut = $bf . $c . $bh;
    $this->assertEquals($source2, $cut);
    $this->assertEquals(strlen($source2), strlen($cut));
}

public
static function recursion_add(
  $content,
  $addBefore = null,
  $addBehind = null,
  $before = null,
  $behind = null
) {
    $isFirstRecursion = is_null($before); # null is used as trigger for first round.
    $cf = new SL5_preg_contentFinder($content);
    if($cut = @$cf->getContent($b = '{', $e = '}')) {
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
          $cut,
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