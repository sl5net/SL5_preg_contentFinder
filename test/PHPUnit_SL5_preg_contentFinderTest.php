<?php
//@include_once("../SL5_preg_contentFinder.php");
//
require("../SL5_preg_contentFinder.php");

class SL5_preg_contentFinderTest extends PHPUnit_Framework_TestCase {


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
     * getContent_Next returns false if there is not a next content
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
     * CACHE_current: false if there is no matching cache. no found content.
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
     * getContent ... gives false if there isn't a content. if it found a content it gives true
     */
    public function test_getContent() {
        $cf = new SL5_preg_contentFinder("00123456");
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals(false, $cf->getContent_Next());
        $this->assertEquals(3, $cf->getContent());
    }
    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching content)
     */
    public function test_content_getBorders_before() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new SL5_preg_contentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", substr($content, 0, $cf->getBorders()['begin_begin']));
    }

    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching content)
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
     * gets content using borders with substring
     */
    public function test_getContentBefore() {
        $cf = new SL5_preg_contentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", $cf->getContent_Before());
    }
    /**
     *  gets content using borders with substring
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
        $this->assertEquals(null , $cf->getID() );
        $this->assertEquals(0 , $cf->getID() );
        $this->assertEquals('-' . 0 . '-', '-' . $cf->getID() . '-');
    }
    /**
     * todo: needs discussed
     */
    public function test_getContent_ByID_2() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($cf->getContent(), $cf->getContent_ByID(0));;
    }
    /**
     * todo: needs discussed
     */
    public function test_getContent_ByID_3() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals("2_{1_2", $cf->getContent_ByID(0));
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
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals("_a", $cf->getContent_Next());
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

    public function test_2() {
        $sourceCF = "(2)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $silentMode = true;
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        }
        $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        if (!$silentMode) {
            great($result);
        }
        $this->assertEquals($sourceCF, $result);
//        if ($sourceCF != $result) {
//            die(__LINE__ . " : #$sourceCF# != #$result#");
//        }
    }
    public function test_123() {
        $sourceCF = "(1((2)1)8)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $silentMode = 1;
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        }
        $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        if (!$silentMode) {
            great($result);
        }
        $this->assertEquals($sourceCF, $result);
    }

    public function test_bodyHaHiHo() {
        $content1 = $sourceCF = '<body>
ha <!--{01}-->1<!--{/01}-->
hi {02}2<!--{/02}-->
ho  <!--{03}-->3<!--{/03}-->
</body>';
        $silentMode = 0;
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        }
        $maxLoopCount = 0;
        $pos_of_next_search = 0;
        $begin = '(<!--)?{([^}>]*)}(-->)?';
        $end = '<!--{\/($2)}-->';
        $cf = new SL5_preg_contentFinder($sourceCF);
        $cf->setBeginEnd_RegEx($begin, $end);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        while ($maxLoopCount++ < 5) {

            $cf->setPosOfNextSearch($pos_of_next_search);
//                echo __LINE__ . ": \$maxLoopCount=$maxLoopCount<br>";
            $findPos = $cf->getBorders();
            $sourceCF = @$cf->getContent();
//                echo '' . __LINE__ . ': $content=' . $content . '<br>';
            $expectedContent = $maxLoopCount;
            if ($maxLoopCount > 3) {
                $expectedContent = '';
            }
            $this->assertEquals($sourceCF, $expectedContent);
//            if ($sourceCF != $expectedContent) {
//                die(__LINE__ . 'ERROR :   $content != $expectedContent :' . " '$sourceCF'!= '$expectedContent ");
//            }
            if (is_null($findPos['begin_begin'])) {
                break;
            }
            if (!$silentMode) {
                great(__LINE__ . ': ' . $content1 . ' ==> "' . $sourceCF . '"');
            }

            $pos_of_next_search = $findPos['end_end'];
        }
    }
    public function test_123_abc_dings() {
        # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
        # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
        $content1 = '<!--123_abc-->dings1<!--dings2<!--';
        $cf = new SL5_preg_contentFinder($content1);
        $sourceCF = @$cf->getContent(
          $begin = '<!--[^>]*-->',
          $end = '<!--',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $silentMode = 0;
        if (!$silentMode) {
            info(__LINE__ . ': ' . "$content1 => $sourceCF");
        }
        $expectedContent = 'dings1';
        $this->assertEquals($sourceCF, $expectedContent);
    }

    public function test_1() {
        $sourceCF = "(2)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $result = '(' . $cf->getContent($b = '(', $e = ')') . ')';
        great($result);
        if ($sourceCF != $result) {
            die(__LINE__ . " : #$sourceCF# != #$result#");
        }
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
        $silentMode = 0;
        if (!$silentMode) {
            info(__LINE__ . ': ' . "$content1 => $sourceCF");
        }
        $expectedContent = '#';
        $this->assertEquals($sourceCF, $expectedContent);
//       if ($sourceCF != $expectedContent) {
//           bad(" $sourceCF != $expectedContent");
//           die(__LINE__);
//       }
    }


    public function test_A_i_B_i_C() {
        $sourceCF = 'A (i) B (i) C';
        $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
        $silentMode = 0;
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        }
        $cut = SL5_preg_contentFinderTest1::recursionExample4_search_also_in_rest_of_the_string(
          $sourceCF);

        $result = $cut[1] . $cut[0] . $cut[2];
        if (!$silentMode) {
            great(__LINE__ . ": \n$result (result)");
        }

//        $this->assertNotSame(false , $expectedContent);
        $b = false === strpos($result, 'A [1] B [1] C');
        $strpos = strpos($result, '(i)');
        $this->assertTrue($b || $strpos);
    }
    //

    public function test_2_1() {
        $sourceCF = "((2)1)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $silentMode = 0;
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        } //
        $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($sourceCF, $result);
    }


    public function test_11218() {
        $sourceCF = "(1(1(2)1)8)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        $silentMode = 0;
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        }
        $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
    }


    public function test_A_source_B_C() {
        # recursion example 4
        $sourceCF = SL5_preg_contentFinderTest1::getExampleContent(1);
        $sourceCF = ' A ' . $sourceCF . ' B ' . $sourceCF . ' C ';
        $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
        $cut = SL5_preg_contentFinderTest1::recursionExample4_search_also_in_rest_of_the_string($sourceCF);
        $result = $cut[1] . $cut[0] . $cut[2];
        $proof = 'A (11(22(3)(2)22)11)(1) B (11(22(3)(2)22)11)(1) C';
        $this->assertFalse(strpos($result, $proof) === false);
//        $this->assertEquals($proof, $result);
    }


    public function test___() {     # recursion example 3
        $sourceCF = SL5_preg_contentFinderTest1::getExampleContent(1);
        $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
        $cut = SL5_preg_contentFinderTest1::recursionExample3_search_NOT_in_rest_of_the_string($sourceCF);
        $result = $cut[1] . $cut[0] . $cut[2];
//       if(!$silentMode)great("$content\n?=\n$result");
        $this->assertTrue(false === strpos($result, 'A (1) B (1) C') || strpos($result, '(i)'));
    }

    public function test_recursive_example() {
        # recursion example

        $sourceCF = SL5_preg_contentFinderTest1::getExampleContent(1);

        $silentMode = false;
        if (!$silentMode) {
            echo(__LINE__ . ': <u>recursion_example</u>:');
        }
        $cut = SL5_preg_contentFinderTest1::recursion_example($sourceCF);
        $this->assertFalse(false !== $cut);

    }

    public function test_nothing_special() {
        $sourceCF = 'nothing special';
        $cf = new SL5_preg_contentFinder($sourceCF);
        $noContent = @$cf->getContent($begin = 'bla', $end = 'noooo');
        $this->assertFalse($noContent);
    }
}
