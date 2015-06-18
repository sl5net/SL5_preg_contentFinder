<?php
//@include_once("../SL5_preg_contentFinder.php");
//
require("../SL5_preg_contentFinder.php");


class SL5_preg_contentFinderTest extends PHPUnit_Framework_TestCase {

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

        $cf_False_IfNoResult = new SL5_preg_contentFinder("{}");
        $cf_False_IfNoResult->setBeginEnd_RegEx('[', ']');
        $contentFalse = $cf_False_IfNoResult->getContent();
        $this->assertTrue($contentFalse !== "");
        $this->assertTrue($contentFalse === false);
        $this->assertTrue($contentFalse !== null);
    }

    public function test_callback_if1() {//        return true;
        $source1 = 'if(1)';
        $C = new callbackShortExample('{', '}', '[[[', ']]]', '.');
        $source2 = $C->test_add_callback($source1);
        $this->assertEquals($source1, $source2);
    }
    public function test_callback_nothing() {//        return true;
        $source1 = 'begin7(middle7)end7';
        $C2 = new callbackShortExample('{', '}', '[[[', ']]]', '.');
        $source2 = $C2->test_add_callback($source1);
        $this->assertEquals($source1, $source2);
    }
    public function test_callback1b() {
        $C = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C->test_add_callback('if(9){a}');
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
        $source2 = $C->test_add_callback($source1);
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
        $source2 = $C2->test_add_callback('begin{middle}');
        $this->assertEquals('begin{
..middle
}
',
          $source2);
    }
    public function test_callback_middleEnd() {
        $C2 = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C2->test_add_callback('{middle5}end5');
        $this->assertEquals('{
..middle5
}
end5',
          $source2);
    }

    public function test_callback_beginMiddleEnd() {
//        return false;
        $C2 = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $C2->test_add_callback('begin8{middle8}end8');
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
        $source2 = $c->test_add_callback($source1);
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
        $source2 = $c->test_add_callback($source1);
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
        $source2 = $c->test_add_callback($source1);
        $this->assertEquals('if(X1)[
..$X1;if(X2)[
....$X2;
..]
]
',
          $source2);
    }

    public function test_callback_ifA_ifB() {
//        return false;
        $source1 = 'if(a1){$A1;}if(a2){$A2;}';
        $c = new callbackShortExample('{', '}', '{', '}', '.');
        $source2 = $c->test_add_callback($source1);
        $this->assertEquals('if(a1){
..$A1;
}
if(a2){
..$A2;
}
',
          $source2);
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


    public function test_change_quote() {
        $source1 = 'A {11{22{3}{2}22}11}{1} B';
        $source2 = 'A (11(22(3){2}22)11)(1) B';
        # simple replacement is missunderstand of the tool. dont do it.
        $cf = new SL5_preg_contentFinder($source1);
        list($c, $bf, $bh) = self::recursion_add($source1, '(', ')');
        $cut = $bf . $c . $bh;
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($source2, $cut);
    }
    public function test_reformatCode1() {
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
class callbackShortExample {
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
    public function onOpen($before, $cut, $behind, $deepCount) {
//        if($deepCount < 0 || $cut === false) return $before;

        return $before . $this->openNew;
    }
    public function onClose($before, $cut, $behind, $deepCount) {

        if($cut === false) return $behind;
        $indentStr = $this->getIndentStr($deepCount - 1);

//        $indentStr = str_repeat(' ', ($deepCount>0)? $deepCount : 0);
        if(preg_match("/^[\r\n]/", $behind)) {
            # could happen if inner block gets newline at the end and nextline is close breaket.
            # workaround  ltrim($behind)
            $break = 'break';
//            die(__LINE__);
        }

        return $indentStr . $this->closeNew . "\r\n" . ltrim($behind); # todo: $behind dont need newline at the beginning
//        return $this->closeNew . $behind;

//        return $indentStr . $this->closeNew;
    }
    public function onContent($before, $cut, $behind, $deepCount) {
        if($cut === false) return $cut;
        $indentStr = $this->getIndentStr($deepCount, $this->charSpace);;
        $cut = "\r\n" . $indentStr . preg_replace("/\r\n[ ]*([^\s\n])/", "\r\n" . $indentStr . "$1", $cut);
        $cut .= "\r\n";

        return $cut;
    }
    public function test_add_callback($source1) {
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($this->openOld, $this->closeOld);
        list($cBefore, $content, $cBehind) = $cf->getContent_user_func_recursive(
          [$this, 'onOpen'],
          [$this, 'onContent'],
          [$this, 'onClose']);
        $source2 = $cBefore . $content . $cBehind;

        return $source2;
    }
    /**
     * @param int $indent
     * @param string $char
     * @return string
     */
    public function getIndentStr($indent, $char = null) {
        if(is_null($char)) $char = $this->charSpace;
        $multiplier = $this->indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    }

}
