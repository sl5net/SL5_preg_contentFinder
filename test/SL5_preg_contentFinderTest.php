<?php

require("../SL5_preg_contentFinder.php");

class SL5_preg_contentFinderTest extends PHPUnit_Framework_TestCase {
    public function test_0() {
        $this->assertEquals(0, 0);
    }
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
            $findPos = $cf->get_borders_left(__LINE__);
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
        $cut = SL5_preg_contentFinder::recursionExample4_search_also_in_rest_of_the_string(
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
        $sourceCF = SL5_preg_contentFinder::getExampleContent(1);
        $sourceCF = ' A ' . $sourceCF . ' B ' . $sourceCF . ' C ';
        $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
        $cut = SL5_preg_contentFinder::recursionExample4_search_also_in_rest_of_the_string($sourceCF);
        $result = $cut[1] . $cut[0] . $cut[2];
        $proof = 'A (11(22(3)(2)22)11)(1) B (11(22(3)(2)22)11)(1) C';
        $this->assertFalse(strpos($result, $proof) === false);
//        $this->assertEquals($proof, $result);
    }


    public function test___() {     # recursion example 3
        $sourceCF = SL5_preg_contentFinder::getExampleContent(1);
        $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
        $cut = SL5_preg_contentFinder::recursionExample3_search_NOT_in_rest_of_the_string($sourceCF);
        $result = $cut[1] . $cut[0] . $cut[2];
//       if(!$silentMode)great("$content\n?=\n$result");
        $this->assertTrue(false === strpos($result, 'A (1) B (1) C') || strpos($result, '(i)'));
    }

    public function test_recursive_example() {
        # recursion example

        $sourceCF = SL5_preg_contentFinder::getExampleContent(1);

        $silentMode = false;
        if (!$silentMode) {
            echo(__LINE__ . ': <u>recursion_example</u>:');
        }
        $cut = SL5_preg_contentFinder::recursion_example($sourceCF);
        $this->assertFalse(false !== $cut) ;

    }

    public function test_nothing_special(){
        $sourceCF = 'nothing special';
        $cf = new SL5_preg_contentFinder($sourceCF);
        $noContent = @$cf->getContent($begin = 'bla', $end = 'noooo');
        $this->assertFalse($noContent);
    }
}
