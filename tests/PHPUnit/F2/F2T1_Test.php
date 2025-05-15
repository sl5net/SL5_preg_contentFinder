<?php
namespace SL5\PregContentFinder\Tests\PHPUnit\F2;
use SL5\PregContentFinder\Tests\PHPUnit\FilenameProcessor;
use SL5\PregContentFinder\Tests\PHPUnit\YourBaseTestClass;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\SearchMode;

class F2T1_Test extends YourBaseTestClass {
    function test_F2T1a_alwaysTrueTest(){
        $this->assertTrue(true, "This assertion should always pass.");
    }
    function test_F2T1b_alwaysTrueTrue() {
        $this->assertEquals(9, 9);
    }

}



?>
