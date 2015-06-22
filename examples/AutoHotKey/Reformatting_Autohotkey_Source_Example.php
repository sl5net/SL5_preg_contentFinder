little example with funny result :)
output_reformatted.ahk is generated from very compressed file input_compressed.ahk
<?php
require("../../SL5_preg_contentFinder.php");
//{{o}}
//reformat_compressed_AutoHotKey($f_input_compressed = 'oneLineDummy.ahk', $f_output_reformatted = 'output_reformatted_1.ahk');
reformat_compressed_AutoHotKey($f_input_compressed = 'input_compressed_1.ahk', $f_output_reformatted = 'output_reformatted_1.ahk');
reformat_compressed_AutoHotKey($f_input_compressed = 'input_compressed_2.ahk', $f_output_reformatted = 'output_reformatted_2.ahk');
function reformat_compressed_AutoHotKey($f_input_compressed, $f_output_reformatted) {
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $file_content_compressed = file_get_contents($f_input_compressed);

    $old_open = '{';
    $old_close = '}';

    $new_open_default = $old_open;
    $new_close_default = '##';
    $new_close_default = $old_close; // this line is reason for endless loop
    $charSpace = ".";
    $newline = "\r\n";
    $indentSize = 2;

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

//          return $indentStr . $new_close_default . $n ;
          return  $new_close_default . $n ;
          # todo: $behind dont need newline at the beginning
      });

    file_put_contents($f_output_reformatted, $file_content_reformatted);
}

?>