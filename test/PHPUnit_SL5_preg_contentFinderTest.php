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