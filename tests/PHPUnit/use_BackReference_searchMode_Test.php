<?php
//@include_once("../SL5_preg_contentFinder.php");
//
$f = 'SL5_preg_contentFinder.php';
while(!file_exists($f)) {
    $f = '../' . $f;
    echo "$f exist.";
}
include_once "../create_1file_withAll_PHPUnit_tests.php"; # ok little overhead. sometimes ;) 15-06-19_12-35
include_once $f;
class DontTouchThis_searchMode_Test extends PHPUnit_Framework_TestCase {


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
15-06-19_15-32';
        $expectedContent = '15-06-19_15-32';
        $cf = new SL5_preg_contentFinder($content1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*', '^\s*\}\s*');
        $sourceCF=$cf->getContent();
        $levenshtein = levenshtein($expectedContent, $sourceCF);
//        $this->assertEquals(0,$levenshtein);
        $this->assertEquals($expectedContent . ' $levenshtein='.$levenshtein,$sourceCF . ' $levenshtein='.$levenshtein);
    }
    public function test_123_abc_v5() {
        $content1 = '
class DontTouchThis_searchMode_Test extends PHPUnit_Framework_TestCase {
15-06-19_15-32';
        $expectedContent = '15-06-19_15-32';
        $cf = new SL5_preg_contentFinder($content1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*', '^\s*\}\s*$');
        $sourceCF=$cf->getContent();
        $levenshtein = levenshtein($expectedContent, $sourceCF);
//        $this->assertEquals(0,$levenshtein);
        $this->assertEquals($expectedContent . ' $levenshtein='.$levenshtein,$sourceCF . ' $levenshtein='.$levenshtein);
    }
}
?>