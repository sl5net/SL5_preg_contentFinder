little example with funny result :)
output_reformatted.ahk is generated from very compressed file input_compressed.ahk
<?php
require("../../SL5_preg_contentFinder.php");
test_callback_with_closures();
 function test_callback_with_closures() {
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
    $file_content_compressed = file_get_contents('input_compressed.ahk');

    $old_open = '{';
    $old_close = '}';
    $new_open_default = '[';
    $new_close_default = ']';
    $charSpace = " ";
    $newline = "\r\n";
    $indentSize = 3;

    $cf = new SL5_preg_contentFinder($file_content_compressed);
    $cf->setBeginEnd_RegEx($old_open, $old_close);

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));
        return $indentStr;
    };

    list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
      function ($before) use ($new_open_default) { return $before . $new_open_default; },
      function ($before, $cut, $behind, $deepCount) use ($charSpace, $newline, $indentSize,$getIndentStr) {
          if($cut === false) return $cut;
          $n = $newline;
          $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
          $cut = $n . $indentStr . preg_replace('/' . $n . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
          $cut .= $n;
          return $cut;
      },
      function ($before, $cut, $behind, $deepCount) use ($new_close_default, $newline, $charSpace, $indentSize, $getIndentStr) {
          if($cut === false) return $behind;
          $n = $newline;
          $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
          return $indentStr . $new_close_default . $n . ltrim($behind);
          # todo: $behind dont need newline at the beginning
      });

    $file_content_reformatted = $cBefore . $content . $cBehind;
    file_put_contents('output_reformatted.ahk',$file_content_reformatted);
}
?>