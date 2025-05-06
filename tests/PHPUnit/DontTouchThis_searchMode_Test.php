<?php
namespace SL5\PregContentFinder\Tests;
use SL5\PregContentFinder\PregContentFinder;
class DontTouchThis_searchMode_Test extends \PHPUnit\Framework\TestCase {

    public function test_Grabbing_HTML_Tag() {
        return false;
//        $source1 = file_get_contents(__FILE__);
        $expected = 'hiHo';
        $source1 = '<P>hiHo</P>';
        $cf = new PregContentFinder($source1);
        $rB = '<([A-Z][A-Z0-9]*)\b[^>]*>';
        $rE = '<\/{1}>';
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $cf->setBeginEnd_RegEx($rB, $rE);
//          '\s*\}\s*function\s+\s+function');
        $actual = $cf->getContent();
        $this->assertEquals($expected, $actual);
        $break = 'b';
    }

    public function test_123_g() {
        $source1 = '123#g';
        $cf = new PregContentFinder($source1);
        $actual_getContent = @$cf->getContent(
          $begin = '\d+',
          $end = '\w+',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $cf2 = new PregContentFinder($source1);
        $cf2->setSearchMode('dontTouchThis');
        $cf2->setBeginEnd_RegEx('\d+', '\w+');
        $actual_getContent_user_func_recursive = $cf2->getContent_user_func_recursive(
          function ($cut) {
              $cut['middle'] = '2.' . $cut['middle'];
              return $cut;
          });

        $expected = '#';
        $this->assertEquals($actual_getContent, $expected);
        $this->assertEquals($actual_getContent_user_func_recursive, '2.' . $expected);
    }
    public function test_123_z() {
        $source1 = '123#z';
        $expected = '#';
        $cf = new PregContentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\d+', '\w+');
        $sourceCF = $cf->getContent();
        $this->assertEquals($sourceCF, $expected);
    }
    public  function test_123_abc_v3() {
        $source1 = '{
        hiHo
        }';
        $expected = 'hiHo';
        $cf = new PregContentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('^\s*{\s*$\s*', '\s*^\s*}\s*$');
        $sourceCF = $cf->getContent();
        $this->assertEquals($sourceCF, $expected);
    }
    public  function test_123_abc_v4() {
        $source1 = '
class DontTouchThis_searchMode_Test extends \PHPUnit\Framework\TestCase {
15-06-19_15-32';
        $expected = '15-06-19_15-32';
        $cf = new PregContentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*', '^\s*\}\s*');
        $sourceCF = $cf->getContent();
        $levenshtein = levenshtein($expected, $sourceCF);
//        $this->assertEquals(0,$levenshtein);
        $this->assertEquals($expected . ' $levenshtein=' . $levenshtein, $sourceCF . ' $levenshtein=' . $levenshtein);
    }
    public function test_123_abc_v5() {
        $source1 = '
class DontTouchThis_searchMode_Test extends \PHPUnit\Framework\TestCase {
15-06-19_15-32';
        $expected = '15-06-19_15-32';
        $cf = new PregContentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*', '^\s*\}\s*$');
        $sourceCF = $cf->getContent();
        $levenshtein = levenshtein($expected, $sourceCF);
//        $this->assertEquals(0,$levenshtein);
        $this->assertEquals($expected . ' $levenshtein=' . $levenshtein, $sourceCF . ' $levenshtein=' . $levenshtein);
    }
}
?>