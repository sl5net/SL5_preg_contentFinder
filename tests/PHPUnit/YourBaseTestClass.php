<?php
declare(strict_types=1);
namespace SL5\PregContentFinder\Tests\PHPUnit; 


use SL5\PregContentFinder\Tests\PHPUnit\FilenameProcessor;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class YourBaseTestClass extends \PHPUnit\Framework\TestCase
{
    protected ?LoggerInterface $logger = null;
    protected string $logFilePath = '';

    protected string $logFullPath = '';
    protected string $shortFileName = '';
    protected string $logDir = '';
    protected string $logBaseDir = '';
    protected function setUp(): void
    {
            parent::setUp();
            $currentPHPMethodName = $this->name ?? 'UnknownTestMethod'; // $this->name should exist
            $channelName = (new \ReflectionClass($this))->getShortName() . '::' . $currentPHPMethodName;
            $this->logger = new Logger($channelName);
            $this->logger->pushProcessor(new IntrospectionProcessor(Level::Info, ['YourBaseTestClass', 'PHPUnit\\Framework\\TestCase']));
            $this->logger->pushProcessor(new FilenameProcessor()); // Dein Prozessor

            $appBaseDir = '/app/'; // Basisverzeichnis im Container
            $PHPfileFullPath = (new \ReflectionClass($this))->getFileName(); // Voller Pfad der Testdatei (z.B. /app/tests/PHPUnit/F1/F1T1_Test.php)
            $this->logBaseDir = $appBaseDir . 'logs/';

            $PHPfilePathMinusBaseDir = '';
            if (strpos($PHPfileFullPath, $appBaseDir) === 0) {
                $PHPfilePathMinusBaseDir = ltrim($PHPfileFullPath, $appBaseDir); // z.B. tests/PHPUnit/F2/F1T1_Test.php
            } else {
                error_log("YourBaseTestClass: Test file '{$PHPfileFullPath}' not located under appBaseDir '{$appBaseDir}'. Logging disabled.");
                $this->logger = new NullLogger();
                return;
            }
            $PHPdirMinusBaseDir = dirname($PHPfilePathMinusBaseDir); // z.B. tests/PHPUnit/F2

            $this->logDir = $this->logBaseDir . $PHPdirMinusBaseDir . '/'; // z.B. /app/logs/tests/PHPUnit/F1/

            if (!is_dir($this->logDir)) {
                if (file_exists($this->logDir)) {
                    @unlink($this->logDir);
                }
                if (!@mkdir($this->logDir, 0777, true)
                 && !is_dir($this->logDir)) {
                    error_log("YourBaseTestClass: Failed to create log directory: " . $this->logDir);
                    // $this->logger = new NullLogger(); // Fallback
                    return; // Wichtig: Hier abbrechen, wenn der Ordner nicht erstellt werden kann
                }
            }

            // Den Dateinamen für die Logdatei bestimmen (z.B. F1T1_Test.log)
            $PHPfileWithoutExt = (new \ReflectionClass($this))->getShortName(); // F1T1_Test
            $logFileName = $PHPfileWithoutExt . '.log';

            $this->logFilePath = $this->logDir . $logFileName; // z.B. /app/logs/tests/PHPUnit/F1/F1T1_Test.log

            // --- Configure Formatter and Handler (wie vorher, aber mit dem neuen Pfad) ---
            // $outputFormat = "%extra.filename_only%:%extra.line% [%extra.function%()] %level_name%: %message% %context%\n"; // Beispiel Format
            // Wenn du den ClassName am Anfang des Formats willst (wie in deinem Beispiel)
            $outputFormat = $PHPfileWithoutExt . ":%extra.line%[%extra.function%()]%level_name%: %message%\n";


            $formatter = new LineFormatter($outputFormat, null, true, true);

            // Create the handler pointing to the correct file
            // Use the calculated $this->logFilePath!
            $handler = new StreamHandler($this->logFilePath, Level::Debug);
            $handler->setFormatter($formatter);
 
            // --- Attach Handler (Clear previous if any) ---
            if ($this->logger instanceof Logger) {
                // Remove any handlers potentially added by parent::setUp or previous runs
                // Pop from the end until no handlers are left
                while ($this->logger->getHandlers()) {
                    $this->logger->popHandler();
                }
                $this->logger->pushHandler($handler);
            }

            // --- Wichtig für den Test: Überprüfe den Logger ---
            if ($this->logger instanceof NullLogger) {
                // Wenn wir hier ankommen, ist beim Logging-Setup was schiefgelaufen (z.B. Ordner erstellen)
                // Du könntest hier den Test fehlschlagen lassen oder eine Warnung ausgeben
                $this->fail("Failed to initialize logger correctly. Check logs for directory creation errors.");
            }


        } // <<< Das ist die einzige schließende Klammer für setUp()




    protected function tearDown(): void
    {
        if ($this->logger && !($this->logger instanceof NullLogger)) {
            $this->logger->info("from tearDown:65 Test Method FINISH ---");
            foreach ($this->logger->getHandlers() as $handler) {
                if (method_exists($handler, 'close')) {
                    $handler->close();
                }
            }
        }
        $this->logger = null;
        parent::tearDown();
    }
}
