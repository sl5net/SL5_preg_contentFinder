<?php
namespace SL5\PregContentFinder\Tests;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\SearchMode;

class GetContent_Test extends \PHPUnit\Framework\TestCase {

    /**
     * empty means it found an empty.
     * false means nothing was found.
     */
    function test_false_versus_empty() {

        // $this->markTestSkipped('This test should tested later. it slows the PC rapidly down');

        
        $cfEmpty_IfEmptyResult = new PregContentFinder("{}");
        $cfEmpty_IfEmptyResult->setBeginEnd_RegEx('{', '}');
        $contentEmpty = $cfEmpty_IfEmptyResult->getContent();
        $this->assertTrue($contentEmpty === "");
        $this->assertTrue($contentEmpty !== false);
        $this->assertTrue($contentEmpty !== null);

        $cf_False_IfNoResult = new PregContentFinder("mi{SOME}mo");
        $cf_False_IfNoResult->setBeginEnd_RegEx('[', ']');
        $contentFalse = $cf_False_IfNoResult->getContent();
        $this->assertTrue($contentFalse !== "");
        $this->assertTrue($contentFalse === false);
        $this->assertTrue($contentFalse !== null);
    }


    /**
     * echo and return from a big string a bit of the start and a bit from the end.
     */
    function test_echo_content_little_excerpt() {
        $this->markTestSkipped('2025-0619-2303');

        $cf = new PregContentFinder("dummy");
        $this->assertEquals("12...45", $cf->echo_content_little_excerpt("12345", 2, 2));
    }

    /**
     * nl2br_Echo returns nothong, returns null. it simly echo
     */
    function test_nl2br_Echo() {
        $this->markTestSkipped('Die Methode nl2br_Echo() ist veraltet und wurde aus der Klasse entfernt.');
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder(123456);
        $this->assertEquals($cf->nl2br_Echo(__LINE__, "filename", "<br>"), null);
    }
    /**
     * getContent_Next returns false if there is not a next contentDemo
     */
    function test_getContentNext() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder(123456);
        $this->assertEquals(false, $cf->getContent_Next());
    }
    /**
     * CACHE_current: false if there is no matching cache. no found contentDemo.
     */
    function test_CACHE_current_begin_end_false() {
        $this->markTestSkipped(
            'The CACHE_current() method is obsolete and has been removed during refactoring.'
        );
        $cf = new PregContentFinder(123456);
        $this->assertEquals(false, $cf->CACHE_current("begin"));
        $this->assertEquals(false, $cf->CACHE_current("end"));
    }
    /**
     * @group legacy
     * @group obsolete     * CACHE_current: simply the string of the current begin / end quote
     */
    function test_CACHE_current_begin_end() {
        $this->markTestSkipped(
            'The CACHE_current() method is obsolete and has been removed during refactoring.'
        );
        $cf = new PregContentFinder(00123456);
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(2, $cf->CACHE_current("begin"));
        $this->assertEquals(4, $cf->CACHE_current("end"));
    }


    /**
     * getContent ... gives false if there isn't a contentDemo. if it found a contentDemo it gives true
     */
    function test_getContent() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder("00123456");
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals(3, $cf->getContent());
        $this->assertEquals(false, $cf->getContent_Next());
    }
    function test_wrongSource_NIX_getContent() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $source1 = '{NIX';
        $expected = 'NIX';
        $cf = new PregContentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($expected, $cf->getContent());
    }

    function test_wrongSource_NIXNIX_getContent() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $source1 = "{NIX{}";
        $expected = 'NIX{';
        $cf = new PregContentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($expected, $cf->getContent());
    }

    function test_getUniqueSignExtreme() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder(123456);
        $cf->isUniqueSignUsed = true; # needs to switched on first !! performance reasons
        $cf->setBeginEnd_RegEx('2', '4');
        $cf->getContent(); # needs to be searched first !! performance reasons
        $probablyUsedUnique = chr(001);
        $this->assertEquals($probablyUsedUnique, $cf->getUniqueSignExtreme());
    }

    function test_protect_a_string() {
        // $this->markTestSkipped('? 2025-0619-2303');
        $cf = new PregContentFinder('"{{mo}}"');
        $cf->isUniqueSignUsed = true; # needs to switched on first !! performance reasons
        $cf->setBeginEnd_RegEx('{', '}');
        $content = $cf->getContent(); # needs to be searched first !! performance reasons
        $mo = '{mo}';
        $this->assertEquals('_' . $mo, '_' . $content);
        $uniqueSignExtreme = $cf->getUniqueSignExtreme();

        $o = $uniqueSignExtreme . 'o';
        $c = $uniqueSignExtreme . 'c';
        $content1 = str_replace(['{', '}'], [$o, $c], $content);
        $contentRedo = str_replace([$o, $c], ['{', '}'], $content1);
        $this->assertEquals('-' . $contentRedo, '-' . $content);

        $cf2 = new PregContentFinder($content1);
        $cf2->setBeginEnd_RegEx('{', '}');
        $content2 = $cf2->getContent();
        $content_Before = $cf2->getContent_Before();
        $content_Behind = $cf2->getContent_Behind();
        $content3 = str_replace([$o, $c], ['{', '}'], $content2);
        $this->assertEquals('', $content_Before . $content3 . $content_Behind); # means cut is not  found / created.
    }

    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching contentDemo)
     */
    function test_content_getBorders_before() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new PregContentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');

        // KORREKTUR: Wir rufen nicht mehr die interne 'getBorders'-Methode auf,
        // sondern die dafür vorgesehene öffentliche Methode 'getContent_Before()'.
        // Diese sollte exakt das gleiche Ergebnis liefern.
        $this->assertEquals("before0", $cf->getContent_Before());
    }


    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching contentDemo)
     *
     * todo: discuss getContent_Next ?? discuss getContent_Behind ?? (15-06-16_10-28)
     */
    function test_content_getBorders_behind() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new PregContentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');

        // KORREKTUR: Wir rufen die öffentliche Methode 'getContent_Behind()' auf,
        // die das gewünschte Verhalten kapselt.
        $this->assertEquals("behind0,before1[in1]behind1", $cf->getContent_Behind());
    }
    
    /**
     * gets contentDemo using borders with substring
     */
    function test_getContentBefore_delimiterWords() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder("1_before0_behind0_2");
        $cf->setBeginEnd_RegEx('before0', 'behind0');
        $this->assertEquals("1_", $cf->getContent_Before());
        $this->assertEquals("_2", $cf->getContent_Behind());
    }
    /**
     * gets contentDemo using borders with substring
     */
    function test_getContentBefore() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", $cf->getContent_Before());
    }
    /**
     *  gets contentDemo using borders with substring
     */
    function test_getContentBehind() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("behind0,before1[in1]behind1", $cf->getContent_Behind());
    }


    /**
     * todo: needs discussed
     */
    function test_getContent_ByID_1() {
        $this->markTestSkipped('Functionality for getContent_ByID() is not implemented.');

        $cf = new PregContentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals(null, $cf->getID());
        $this->assertNotEquals('-' . 0 . '-', '-' . $cf->getID() . '-');
    }


    /**
     * @group legacy
     * @group future-feature
     * setID please use integer not text. why?
     * todo: needs discussed
     */
    function test_getContent_setID() {
        $this->markTestSkipped(
            'Tests an unfinished legacy feature (setID/getContent_ByID). ' . 
            'The idea is valid, but the implementation is missing. Postponed for later.'
        );
        $cf = new PregContentFinder("{2_{1_2}_");
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
     * @group legacy
     * @group future-feature
     * todo: needs discussed
     */
    function test_getContent_ByID_3() {
        $this->markTestSkipped('Functionality for getContent_ByID() is not implemented.');
        $cf = new PregContentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals("2_{1_2", $cf->getContent_ByID(0)); # dont work like expected
    }
    /**
     * getContent takes the first. from left to right
     */
    function test_getContent_2() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder("{2_{1_2}_2}_3}{_4}");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals("2_{1_2}_2", $cf->getContent());
    }
    /**
     * Prev and Next using getContent_ByID
     * todo: discuss
     */
    function test_getContent_Prev_Next() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $cf = new PregContentFinder("(1_3)_2_3_(_a)o");
        $cf->setBeginEnd_RegEx('(', ')');
        $this->assertEquals("1_3", $cf->getContent());
//        $this->assertEquals(false, $cf->getContent_Prev()); # todo: dont work like expected
//        $this->assertEquals("_a", $cf->getContent_Next()); # todo: dont work like expected
    }
    function test_getContent_Prev_Next_3() {
        // $this->markTestSkipped('Wird nach Implementierung von getContent_Next aktiviert.');
        $source1 = "{1_4}_2_3_{_b}o";
        $cf = new PregContentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');

        // Erster Aufruf
        $this->assertEquals("1_4", $cf->getContent());
        
        // Zweiter Aufruf (der eigentliche Test für _Next)
        $this->assertEquals("_b", $cf->getContent_Next());

        // Dritter Aufruf (sollte jetzt false sein)
        $this->assertEquals(false, $cf->getContent_Next());

        // Test für _Prev
        $this->assertEquals("1_4", $cf->getContent_Prev()); 
    }

    function test_128() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303'); 
        $source1 = "(1((2)1)8)";
        $expected = "(1((2)1)8)";
        $cf = new PregContentFinder($source1);
        $actual = '(' . $cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($expected, $actual);
    }

    function test_123_abc() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
        # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
        $content1 = '123#abc';
        $cf = new PregContentFinder($content1);
        $cf->setSearchMode('dontTouchThis');
        $expected = @$cf->getContent(
          $begin = '\d+',
          $end = '\w+',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $expectedContent = '#';
        $this->assertEquals($expected, $expectedContent);
    }

    function test_2_1() {
        // $this->markTestSkipped('problemtic? 2025-0619-2303');
        $expected = "((2)1)";
        $cf = new PregContentFinder($expected);
        $actual = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($expected, $actual);
    }

}
?>
