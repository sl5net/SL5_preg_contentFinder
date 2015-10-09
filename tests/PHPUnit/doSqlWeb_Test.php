
<?php
//@include_once("../SL5_preg_contentFinder.php");
//
//require("../SL5_preg_contentFinder.php");
$f = 'SL5_preg_contentFinder.php';
while(!file_exists($f)) {
    $f = '../' . $f;
    echo "$f exist.";
}
include_once "../create_1file_withAll_PHPUnit_tests.php"; # ok little overhead. sometimes ;) 15-06-19_12-35
include_once $f;
include_once "_callbackShortExample.php";


include '../../lib/finediff.php';


class DoSqlWeb_Test extends PHPUnit_Framework_TestCase {
    function test_99_simple() {
        /*
         * Example from:
         * http://dosqlweb.de/dope.php?f=/dope/online-manual2/MindTree/frameset.htm?url=99+a+8+999+default-tree-titles-viewonly.tmpl+2+dope_mindtree_dope_stoffsammlung_light+0
         * http://sourceforge.net/projects/dosqlweb/files/dosqlweb/1.0/DOPE-PHP_Version_070415.zip/download
         * $html = preg_replace("/\[".$selection_alias."\s+(\d+)\]/sx" , "[$selection_alias \\1]" , $html);
 create-cache-file.inc.php  -  200.747 Bytes  -  Fr, 13.04.07 um 22:23  -           */
        # todo doSqlWeb test not complete written now.
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":" .
          '[SELECT 5+5 as calculation#alias]mitte[alias calculation]';
        $expected = $LINE__ . ":"
          . '';
        $old = ['[', ']'];
        $newQuotes = ['[', ']'];
        $html = $source1;
        $preg_kapsel = "/^(\[[^<#][^#]+?#[^\]]+?\])$/s";
        $preg_kapsel = "/^(\[[^<#][^#]+?#[^\]]+?\])$/s";
//        $old = "/^(\[[^<#][^#]+?#[^\]]+?\])$/s";

//        $html = preg_replace("/\[".$selection_alias."\s+(\d+)\]/sx" , "[$selection_alias \\1]" , $html);
        $reg_ausdruck = "/\[(\w+)(\s+[^\#]+)#\s*([^\/\]\#]+?)\s*(#\d+)?(#[^#\]]+)?\]/"; // see function function interpret_one_sql_kapsel(
        $old[0]= $reg_ausdruck;
        $old[1] = "/\[\w+[^]]*\]/";
        $cf = new SL5_preg_contentFinder($source1, $old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes) {
              $cut['before'] .= $newQuotes[0];
              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }
}
?>