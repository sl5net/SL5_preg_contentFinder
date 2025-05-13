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
    protected ?string $logFilePath = '';

    protected ?string $logFileFullPath = '';
    protected ?string $shortFileName = '';

    protected function setUp(): void
    {
            parent::setUp();

            // OLD (causes runtime error in PHPUnit 10+ AND Intelephense P1013 error)
            // $channelName = (new \ReflectionClass($this))->getShortName() . '::' . $this->getName(false);

            // NEW (correct for PHPUnit 10+)
            $currentTestMethodName = $this->name ?? 'UnknownTestMethod'; // $this->name should exist
            $channelName = (new \ReflectionClass($this))->getShortName() . '::' . $currentTestMethodName;

            $this->logger = new Logger($channelName);
            $this->logger->pushProcessor(new IntrospectionProcessor(Level::Info, ['YourBaseTestClass', 'PHPUnit\\Framework\\TestCase']));
            $this->logger->pushProcessor(new FilenameProcessor()); // Dein Prozessor


            // --- Korrigierte Pfadberechnung ---
            $testFilePath = (new \ReflectionClass($this))->getFileName(); // Voller Pfad der Testdatei (z.B. /app/tests/PHPUnit/F1/F1T1_Test.php)
            $appBaseDir = '/app/'; // Basisverzeichnis im Container

            // Den Teil des Pfades extrahieren, der nach /app/ kommt (z.B. tests/PHPUnit/F1/F1T1_Test.php)
            // Sicherstellen, dass der Pfad mit /app/ beginnt
            if (strpos($testFilePath, $appBaseDir) === 0) {
                $relativePathFromApp = substr($testFilePath, strlen($appBaseDir)); // z.B. tests/PHPUnit/F1/F1T1_Test.php
            } else {
                // Fehlerbehandlung, falls die Testdatei nicht unter /app liegt (sollte in Docker nicht passieren)
                error_log("Test file not located under $appBaseDir: " . $testFilePath);
                $this->logger = new NullLogger();
                return; // Wichtig: Hier abbrechen, wenn der Pfad nicht passt
            }


            // Das Basis-Log-Verzeichnis definieren
            $logBaseDir = '/app/logs/';

            // Den Verzeichnis-Teil des relativen Pfades extrahieren (z.B. tests/PHPUnit/F1/)
            $logDirStructure = dirname($relativePathFromApp) . '/'; // z.B. tests/PHPUnit/F1/

            // Den vollständigen Ziel-Log-Ordnerpfad zusammensetzen
            $logTargetDir = $logBaseDir . $logDirStructure; // z.B. /app/logs/tests/PHPUnit/F1/

            // Sicherstellen, dass dieser Ordner existiert (inklusive Unterordner)
            if (!is_dir($logTargetDir)) {
                // Redundante Prüfung, aber sicher: Falls an dieser Stelle ein FILE existiert, löschen
                if (file_exists($logTargetDir)) {
                    @unlink($logTargetDir);
                }
                // Ordner erstellen, rekursiv
                if (!@mkdir($logTargetDir, 0777, true) && !is_dir($logTargetDir)) {
                    error_log("YourBaseTestClass: Failed to create log directory: " . $logTargetDir);
                    $this->logger = new NullLogger(); // Fallback
                    return; // Wichtig: Hier abbrechen, wenn der Ordner nicht erstellt werden kann
                }
            }

            // Den Dateinamen für die Logdatei bestimmen (z.B. F1T1_Test.log)
            $shortFileName = (new \ReflectionClass($this))->getShortName(); // F1T1_Test
            $logFileName = $shortFileName . '.log';

            // Den vollständigen Log-Datei-Pfad zusammensetzen
            $this->logFilePath = $logTargetDir . $logFileName; // z.B. /app/logs/tests/PHPUnit/F1/F1T1_Test.log

            echo "Calculated log file path: " . $this->logFilePath . "\n"; // Debugging Echo


            // --- Configure Formatter and Handler (wie vorher, aber mit dem neuen Pfad) ---
            // $outputFormat = "%extra.filename_only%:%extra.line% [%extra.function%()] %level_name%: %message% %context%\n"; // Beispiel Format
            // Wenn du den ClassName am Anfang des Formats willst (wie in deinem Beispiel)
            $outputFormat = $shortFileName . ":%extra.line%[%extra.function%()]%level_name%: %message%\n";


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
