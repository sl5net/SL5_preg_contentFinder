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

    function test_logFolders() {
        $this->assertDirectoryExists($this->logBaseDir);
        $this->assertDirectoryExists($this->logBaseDir . 'tests/');
        $this->assertDirectoryExists($this->logBaseDir . 'tests/PHPUnit/');
        $this->assertDirectoryExists($this->logBaseDir . 'tests/PHPUnit/F1/');
    }

    function test_PCF_logFolders() {
        $this->assertDirectoryExists($this->logBaseDir);
        $this->assertDirectoryExists($this->logBaseDir . 'app/src/'); // deprecated folder place, should be src only!!!! but dont change it!!! not important actually!!! 2025-0517-1743 
    }
}

?>
