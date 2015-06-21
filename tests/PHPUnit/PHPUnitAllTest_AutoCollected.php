 <h1>Dont edit this file. its overwritten next !</h1> \n  <?php
 $f = 'SL5_preg_contentFinder.php';
 while(!file_exists($f)) {
    $f = '../' . $f;
    echo "$f exist.";
}
include_once $f;
include_once '_callbackShortExample.php';
   class TestAll extends PHPUnit_Framework_TestCase {


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





    public function test_123_abc_v1() {
        # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
        # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
        $content1 = '123#abc';
        $cf = new SL5_preg_contentFinder($content1);
        $sourceCF = @$cf->getContent(
          $begin = '\d+',
          $end = '\w+',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $expectedContent = '#';
        $this->assertEquals($sourceCF, $expectedContent);
    }
    public function test_123_abc_v2() {
        $content1 = '123#abc';
        $expectedContent = '#';
        $cf = new SL5_preg_contentFinder($content1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\d+','\w+');
        $sourceCF=$cf->getContent();
        $this->assertEquals($sourceCF, $expectedContent);
    }
    public function test_123_abc_v3() {
        $content1 = '{
        hiHo
        }';
        $expectedContent = 'hiHo';
        $cf = new SL5_preg_contentFinder($content1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('^\s*{\s*$\s*','\s*^\s*}\s*$');
        $sourceCF=$cf->getContent();
        $this->assertEquals($sourceCF, $expectedContent);
    }
    public function test_123_abc_v4() {
        $content1 = '
class DontTouchThis_searchMode_Test extends PHPUnit_Framework_TestCase {
15-06-19_15-32
}
';
        $expectedContent = '
15-06-19_15-32
';
        $cf = new SL5_preg_contentFinder($content1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*$', '^\s*\}\s*$');
        $sourceCF=$cf->getContent();
        $this->assertEquals($expectedContent,$sourceCF);
    }


    /**
     * empty means it found an empty.
     * false means nothing was found.
     */
    public function test_false_versus_empty() {

        $cfEmpty_IfEmptyResult = new SL5_preg_contentFinder("{}");
        $cfEmpty_IfEmptyResult->setBeginEnd_RegEx('{', '}');
        $contentEmpty = $cfEmpty_IfEmptyResult->getContent();
        $this->assertTrue($contentEmpty === "");
        $this->assertTrue($contentEmpty !== false);
        $this->assertTrue($contentEmpty !== null);

        $cf_False_IfNoResult = new SL5_preg_contentFinder("mi{SOME}mo");
        $cf_False_IfNoResult->setBeginEnd_RegEx('[', ']');
        $contentFalse = $cf_False_IfNoResult->getContent();
        $this->assertTrue($contentFalse !== "");
        $this->assertTrue($contentFalse === false);
        $this->assertTrue($contentFalse !== null);
    }



    /**
     * echo and return from a big string a bit of the start and a bit from the end.
     */
    public function test_echo_content_little_excerpt() {
        $cf = new SL5_preg_contentFinder("dummy");
        $this->assertEquals("12...45", $cf->echo_content_little_excerpt("12345", 2, 2));
    }

    /**
     * nl2br_Echo returns nothong, returns null. it simly echo
     */
    public function test_nl2br_Echo() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals($cf->nl2br_Echo(__LINE__, "filename", "<br>"), null);
    }
    /**
     * getContent_Next returns false if there is not a next contentDemo
     */
    public function test_getContentNext() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals(false, $cf->getContent_Next());
    }
    /**
     * false if parameter is not  'pos_of_next_search' or 'begin' or 'end'
     */
    public function test_CACHE_current() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals(false, $cf->CACHE_current());
    }
    /**
     * CACHE_current: false if there is no matching cache. no found contentDemo.
     */
    public function test_CACHE_current_begin_end_false() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals(false, $cf->CACHE_current("begin"));
        $this->assertEquals(false, $cf->CACHE_current("end"));
    }
    /**
     * CACHE_current: simply the string of the current begin / end quote
     */
    public function test_CACHE_current_begin_end() {
        $cf = new SL5_preg_contentFinder(00123456);
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(2, $cf->CACHE_current("begin"));
        $this->assertEquals(4, $cf->CACHE_current("end"));
    }


    /**
     * getContent ... gives false if there isn't a contentDemo. if it found a contentDemo it gives true
     */
    public function test_getContent() {
        $cf = new SL5_preg_contentFinder("00123456");
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals(false, $cf->getContent_Next());
        $this->assertEquals(3, $cf->getContent());
    }

    public function test_getUniqueSignExtreme() {
        $cf = new SL5_preg_contentFinder(123456);
        $cf->isUniqueSignUsed = true; # needs to switched on first !! performance reasons
        $cf->setBeginEnd_RegEx('2', '4');
        $cf->getContent(); # needs to be searched first !! performance reasons
        $probablyUsedUnique = chr(007);
        $this->assertEquals($probablyUsedUnique, $cf->getUniqueSignExtreme());
    }

    public function test_protect_a_string() {
        $cf = new SL5_preg_contentFinder('"{{mo}}"');
        $cf->isUniqueSignUsed = true; # needs to switched on first !! performance reasons
        $cf->setBeginEnd_RegEx('{', '}');
        $content = $cf->getContent(); # needs to be searched first !! performance reasons
        $mo = '{mo}';
        $this->assertEquals('_'.$mo, '_'.$content);
        $uniqueSignExtreme = $cf->getUniqueSignExtreme();

        $o = $uniqueSignExtreme . 'o';
        $c = $uniqueSignExtreme . 'c';
        $content1 = str_replace(['{', '}'], [$o, $c], $content);
        $contentRedo = str_replace([$o, $c], ['{', '}'], $content1);
        $this->assertEquals('-'.$contentRedo, '-'.$content);

        $cf2 = new SL5_preg_contentFinder($content1);
        $cf2->setBeginEnd_RegEx('{', '}');
        $content2 = $cf2->getContent();
        $content_Before = $cf2->getContent_Before();
        $content_Behind = $cf2->getContent_Behind();
        $content3 = str_replace([$o, $c], ['{', '}'], $content2);
        $this->assertEquals('', $content_Before.$content3.$content_Behind); # means cut is not  found / created.
    }

    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching contentDemo)
     */
    public function test_content_getBorders_before() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new SL5_preg_contentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", substr($content, 0, $cf->getBorders()['begin_begin']));
    }

    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching contentDemo)
     *
     * todo: discuss getContent_Next ?? discuss getContent_Behind ?? (15-06-16_10-28)
     */
    public function test_content_getBorders_behind() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new SL5_preg_contentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("behind0,before1[in1]behind1", substr($content, $cf->getBorders()['end_end']));
//        $this->assertEquals(false, $cf->getContent_Next());
    }
    /**
     * gets contentDemo using borders with substring
     */
    public function test_getContentBefore_delimiterWords() {
        $cf = new SL5_preg_contentFinder("1_before0_behind0_2");
        $cf->setBeginEnd_RegEx('before0', 'behind0');
        $this->assertEquals("1_", $cf->getContent_Before());
        $this->assertEquals("_2", $cf->getContent_Behind());
    }
    /**
     * gets contentDemo using borders with substring
     */
    public function test_getContentBefore() {
        $cf = new SL5_preg_contentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", $cf->getContent_Before());
    }
    /**
     *  gets contentDemo using borders with substring
     */
    public function test_getContentBehind() {
        $cf = new SL5_preg_contentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("behind0,before1[in1]behind1", $cf->getContent_Behind());

    }



    /**
     * todo: needs discussed
     */
    public function test_getContent_ByID_1() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals(null, $cf->getID());
        $this->assertNotEquals('-' . 0 . '-', '-' . $cf->getID() . '-');
    }


    /**
     * setID please use integer not text. why?
     * todo: needs discussed
     */
    public function test_getContent_setID() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $cf->setID(1);
        $content1 = $cf->getContent();
        $this->assertEquals('1: ' . "2_{1_2", '1: ' . $content1);;
//        $this->assertEquals($content1, $cf->getContent_ByID(1));# todo: dont work like expected
        $cf->setBeginEnd_RegEx('1', '2');
        $cf->setID(2);
//        $this->assertEquals($content1, $cf->getContent_ByID(1));;
//        $this->assertEquals("1_2", $cf->getContent_ByID(2));;
//        $this->setExpectedException('InvalidArgumentException');
    }
    /**
     * todo: needs discussed
     */
    public function test_getContent_ByID_3() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
//        $this->assertEquals("2_{1_2", $cf->getContent_ByID(0)); # dont work like expected
    }
    /**
     * getContent takes the first. from left to right
     */
    public function test_getContent_2() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_2}_3}{_4}");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals("2_{1_2}_2", $cf->getContent());
    }
    /**
     * Prev and Next using getContent_ByID
     * todo: discuss
     */
    public function test_getContent_Prev_Next() {
        $cf = new SL5_preg_contentFinder("(1_3)_2_3_(_a)o");
        $cf->setBeginEnd_RegEx('(', ')');
        $this->assertEquals("1_3", $cf->getContent());
//        $this->assertEquals(false, $cf->getContent_Prev()); # todo: dont work like expected
//        $this->assertEquals("_a", $cf->getContent_Next()); # todo: dont work like expected
    }
    /**
     * Prev and Next using getContent_ByID
     * todo: discuss
     */
    public function test_getContent_Prev_Next_3() {
        $cf = new SL5_preg_contentFinder("{1_4}_2_3_{_b}o");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals("1_4", $cf->getContent());
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals(false, $cf->getContent_Next());
    }
    /**
     * Prev and Next using getContent_ByID
     */
//    public function test_getContent_4() {
//        $cf = new SL5_preg_contentFinder("o{2_{1_5}_2}_3}{_c}o");
//        $cf->setBeginEnd_RegEx('{', '}');
//        $this->assertEquals(false, $cf->getContent_Prev());
//        $this->assertEquals("{_c}", $cf->getContent_Next());
//    }

    public function test_128() {
        $sourceCF = "(1((2)1)8)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $result = '(' . $cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($sourceCF, $result);
    }

    public function test_123_abc() {
        # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
        # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
        $content1 = '123#abc';
        $cf = new SL5_preg_contentFinder($content1);
        $sourceCF = @$cf->getContent(
          $begin = '\d+',
          $end = '\w+',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $expectedContent = '#';
        $this->assertEquals($sourceCF, $expectedContent);
    }

    public function test_2_1() {
        $sourceCF = "((2)1)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($sourceCF, $result);
    }

 }