<?php
//@include_once("../SL5_preg_contentFinder.php");
//
require("../SL5_preg_contentFinder.php");


class SL5_preg_callbackTest extends PHPUnit_Framework_TestCase {


    public function test_callback_if1() {//        return true;
        $source1 = 'if(1)';
        $C = new callbackShortExample('{', '}', '[[[', ']]]', '.');
        $source2 = $C->start($source1);
        $this->assertEquals($source1, $source2);
    }
    public function test_callback_nothing() {//        return true;
        $source1 = 'begin7(middle7)end7';
        $C2 = new callbackShortExample('{', '}', '[[[', ']]]', '.');
        $source2 = $C2->start($source1);
        $this->assertEquals($source1, $source2);
    }
    public function test_callback1b() {
        $C = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C->start('if(9){a}');
        $this->assertEquals('if(9){
..a
}
',
          $source2);
    }
    public function test_callback_newlineInMiddle() {
        $C = new callbackShortExample('{', '}', '{', '}', '.');
        $source1 = 'if(3){newlinesMIddle

b}';
        $source2 = $C->start($source1);
        $expected = 'if(3){
..newlinesMIddle

..b
}
';
//        $source2 = preg_replace("/\n\n/", "__", $source2);
//        $this->assertEquals(0,levenshtein($source1,$source2));
//        $this->assertEquals(soundex($source1),soundex($source2));
//        $this->assertEquals(strlen($expected),strlen($source2));
        $this->assertEquals($expected, $source2);
    }
    public function test_callback_beginMiddle() {
        $C2 = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C2->start('begin{middle}');
        $this->assertEquals('begin{
..middle
}
',
          $source2);
    }
    public function test_callback_middleEnd() {
        $C2 = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C2->start('{middle5}end5');
        $this->assertEquals('{
..middle5
}
end5',
          $source2);
    }

    public function test_callback_beginMiddleEnd() {
//        return false;
        $C2 = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C2->start('begin8{middle8}end8');
        $this->assertEquals('begin8{
..middle8
}
end8',
          $source2);
    }

    public function test_callback2() {
//        return true;
        $source1 = 'if(1){$a}';
        $c = new callbackShortExample('{', '}', '[', ']', '.');
        $source2 = $c->start($source1);
        $this->assertEquals('if(1)[
..$a
]
',
          $source2);
    }
    public function test_callback_ifA() {
//        return false;
//        $source1 = 'if(1){if(2){$a;}if(3){$b;}}if(4){$c;}';
        $source1 = 'if(1){$a;}';
        $c = new callbackShortExample('{', '}', '[', ']', '.');
        $source2 = $c->start($source1);
        $this->assertEquals('if(1)[
..$a;
]
',
          $source2);
    }
    public function test_callback_ififAB() {
//        $source1 = 'if(1){if(2){$a;}if(3){$b;}}if(4){$c;}';
        $source1 = 'if(X1){$X1;if(X2){$X2;}}';
        $c = new callbackShortExample('{', '}', '[', ']', '.');
        $source2 = $c->start($source1);
        $this->assertEquals('if(X1)[
..$X1;if(X2)[
....$X2;
..]
]
',
          $source2);
    }

    /**
     * using class SL5_preg_contentFinder
     * and ->getContent_user_func_recursive.
     * in a case i don't like this style using closures to much. so you only need one function (advantage) from the outside. but looks more ugly from the inside. not best way for debugging later (inside). you need to compare, decide for your business.
     */
    public function test_callback_with_closures() {
        $source1 = 'if(X1){$X1;if(X2){$X2;}}';
        $expected = 'if(X1)[
..$X1;if(X2)[
....$X2;
..]
]
';
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

        $source2 = $cBefore . $content . $cBehind;
        $this->assertEquals($expected,
          $source2);
    }


    public function test_callback_ifA_ifB() {
//        return false;
        $source1 = 'if(a1){$A1;}if(a2){$A2;}';
        $c = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $c->start($source1);
        $this->assertEquals('if(a1){
..$A1;
}
if(a2){
..$A2;
}
',
          $source2);
    }


    public function test_reformatCode_recursion_add() {
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
    public static function recursion_add(
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


    public function test_recursion_simplyReproduction() {
        $source = 'A {11{22{3}{2}22}11}{1} B';
        $cf = new SL5_preg_contentFinder($source);
        list($c, $bf, $bh) = self::recursion_simplyReproduction($source);
        $cut = $bf . $c . $bh;
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($source, $cut);
    }
    public static function recursion_simplyReproduction(
      $content,
      $before = null,
      $behind = null
    ) {
        $isFirstRecursion = is_null($before); # null is used as trigger for first round.
        $cf = new SL5_preg_contentFinder($content);
        if($cut = @$cf->getContent($b = '{', $e = '}')) {
            $before .= $cf->getContent_Before() . '{';
            $behindTemp = $cf->getContent_Behind() . $behind;


            if($isFirstRecursion) {
                list($c, $bf, $bh) =
                  self::recursion_simplyReproduction($behindTemp); // this version of recursion also includes the rest of contentDemo.
                $behind = (is_null($c)) ? '}' . $behindTemp : '}' . $bf . $c . $bh;
            }
            else {
                $behind = '}' . $behindTemp;
            }

            $return = self::recursion_simplyReproduction(
              $cut,
              $before,
              $behind
            );

            return $return;
        }
        $return = array(($cut) ? $cut : $content, $before, $behind);

        return $return;
    }
}

class callbackShortExample {
    public $newline = "\r\n";
    private $openOld = '';
    private $closeOld = '';
    private $openNew = '';
    private $closeNew = '';
    private $indentSize = 2;
    private $charSpace = '.';

    public function __construct($old_open, $old_close, $new_open_default, $new_close_default, $charSpace) {
        $this->openOld = $old_open;
        $this->closeOld = $old_close;
        $this->openNew = $new_open_default;
        $this->closeNew = $new_close_default;
        $this->charSpace = $charSpace;
    }
    public function start($source1) {
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($this->openOld, $this->closeOld);
        list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
          function ($before) { return $before . $this->openNew; },
          [$this, 'onContent'],
          [$this, 'onClose']);
        $source2 = $cBefore . $content . $cBehind;

        return $source2;
    }
    public function onOpen($before, $cut, $behind, $deepCount) {
        return $before . $this->openNew;
    }
    public function onClose($before, $cut, $behind, $deepCount) {
        if($cut === false) return $behind;
        $n = $this->newline;
        $indentStr = $this->getIndentStr($deepCount - 1);

        return $indentStr . $this->closeNew . $n . ltrim($behind);
        # todo: $behind dont need newline at the beginning
    }
    public function onContent($before, $cut, $behind, $deepCount) {
        if($cut === false) return $cut;
        $n = $this->newline;
        $indentStr = $this->getIndentStr($deepCount, $this->charSpace);;
        $cut = $n . $indentStr . preg_replace('/' . $n . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut);
        $cut .= $n;

        return $cut;
    }
    /**
     * @param int $indent
     * @param string $char
     * @return string
     */
    private function getIndentStr($indent, $char = null) {
        if(is_null($char)) $char = $this->charSpace;
        $multiplier = $this->indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    }

}


function getIndentStr($indent, $char, $indentSize) {
    $multiplier = $indentSize * $indent;
    $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));
    return $indentStr;
}

