<?php
namespace SL5\PregContentFinder\Tests\PHPUnit\F1;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\Tests\PHPUnit\YourBaseTestClass;


class F1T1_Test extends YourBaseTestClass {
    function test_F1T1a_alwaysTrueTest(){

        $this->logger->info('hi from test_F1T1a_alwaysTrueTest');

        $this->assertTrue(true, "This assertion should always pass.");
    }
    function test_F1T1b_alwaysTrueTrue() {
        $this->assertEquals(9, 9);
    }

}

?>
