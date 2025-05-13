<?php
namespace SL5\PregContentFinder\Tests\PHPUnit\F2;
use SL5\PregContentFinder\Tests\PHPUnit\FilenameProcessor;
use SL5\PregContentFinder\Tests\PHPUnit\YourBaseTestClass;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\SearchMode;


class F2T2_Test extends YourBaseTestClass {
    function test_F2T2a_alwaysTrueTest(){
        $this->assertTrue(true, "This assertion should always pass.");
    }
    function test_F2T2b_alwaysTrueTrue() {
        $this->assertEquals(9, 9);
    }

    public function test_F2T2c_LogFile1Exists()
    {
        $logFile = '/app/logs/DontTouchThisSearchModeSimplifiedTest.log';
        $this->assertFileExists($logFile);
    }

    public function test_F2T2c_WriteLog_File1Exists()
    {
        // NICHT fest codieren, sondern den von setUp berechneten Pfad verwenden!
        $logFile = $this->logFilePath;
        $expectedEntry = 'Hey from test_F2T2c_WriteLog_File1Exists';


        // Sicherstellen, dass der Logger nicht der NullLogger ist (wegen Fehlerbehandlung in setUp)
        if ($this->logger !== null && !($this->logger instanceof NullLogger)) {
            $this->logger->info($expectedEntry);

            // Handler schließen, um sicherzustellen, dass der Puffer in die Datei geschrieben wird
            // Dies ist wichtig, bevor du file_get_contents aufrufst
            if ($this->logger instanceof Logger) { // Erneut prüfen, falls Fallback passierte
                foreach ($this->logger->getHandlers() as $handler) {
                    // Monolog's StreamHandler hat normalerweise close()
                    if (method_exists($handler, 'close')) {
                        $handler->close();
                    }
                }
            }

        } else {
            // Dies sollte jetzt in setUp gefangen werden, aber als zusätzliche Sicherheit
            $this->fail('Logger is not set or could not be initialized (likely directory creation failed).');
        }

        // Teste die Datei basierend auf dem korrekt berechneten Pfad
        $this->assertFileExists($logFile, "Log file determined by setUp ($logFile) does not exist.");
        $logContents = file_get_contents($logFile);
        $this->assertStringContainsString($expectedEntry, $logContents);
    }




/**
     * The log file is expected to contain the string 'INFO: from tearDown'.
     * The log file is expected to have a size greater than 100 bytes.
     * 
     * This test is sensitive to the order of the tests. The test above,
     * test_F2T2c_LogFile1Exists, must be run before this test.
     * 
     * @return void
     */
    public function test_F2T2d_LogFileEntry()
    {
        $expectedEntry = 'INFO: from tearDown';

        $logFile = '/app/logs/DontTouchThisSearchModeSimplifiedTest.log';
        $this->assertFileExists($logFile);
        $logContents = file_get_contents($logFile);
        $logSize = filesize($logFile);
        $logsizeInKB = $logSize / 1024; 
        if($logsizeInKB > 30) {
            unlink($logFile);
        }else{
            $logSize = filesize($logFile);
            $logContents = file_get_contents($logFile);
            $this->assertGreaterThan(100, $logSize, 'Log file is greater than 100 bytes');
            $this->assertStringContainsString($expectedEntry, $logContents);
        }
    }


    public function test_F2T2e_LogFile2Exists()
    {
        $logFile = '/app/logs/PregContentFinder.log';
        $this->assertFileExists($logFile);
    }
   


    public function test_F2T2f_PCF_FileEntry()
    {
        $filePath = '/app/logs/PregContentFinder.log';
        $this->assertFileExists($filePath);
        $this->assertFileIsReadable($filePath);
        $expectedEntry = 'INFO: from tearDown';
        $fileSize = filesize($filePath);
        $fileSizeInKB = $fileSize / 1024;
        if($fileSizeInKB > 30) {
            unlink($filePath);
        }{
            $logContents = file_get_contents($filePath);
            $this->assertStringContainsString($expectedEntry, $logContents);
        }
    }

}

?>
