<?php
declare(strict_types=1);
namespace SL5\PregContentFinder\Tests;

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
    protected ?string $shortFileName = '';

    protected function setUp(): void
    {
        parent::setUp();

        $channelName = (new \ReflectionClass($this))->getShortName() . '::' . $this->getName(false);
        $this->logger = new Logger($channelName);

        // Prozessoren hinzufügen
        // IntrospectionProcessor MUSS VOR deinem FilenameProcessor kommen, wenn FilenameProcessor 'extra.file' erwartet
        $this->logger->pushProcessor(new IntrospectionProcessor(Level::Debug, ['YourBaseTestClass', 'PHPUnit\\Framework\\TestCase'])); // Level und Skip-Classes anpassen
        $this->logger->pushProcessor(new FilenameProcessor()); // Dein Prozessor

        // Format, das deine 'extra'-Felder nutzt
        // Achte auf die Leerzeichen, wenn du sie anders willst

        $shortFileName = (new \ReflectionClass($this))->getShortName() . '';

        // $outputFormat = "%extra.filename_only%:%extra.line% [%extra.function%()] %level_name%: %message% %context%\n";
        $outputFormat = $shortFileName . ":%extra.line%[%extra.function%()]%level_name%: %message%\n";


        // Wenn du das Leerzeichen vor INFO weg haben willst:
        // $outputFormat = "%extra.filename_only%:%extra.line% [%extra.function%]%level_name%: %message% %context%\n";
        $formatter = new LineFormatter($outputFormat, null, true, true); // allowInlineLineBreaks, ignoreEmptyContextAndExtra

        $this->shortFileName = $shortFileName;
        $logFileName = $shortFileName . '.log';

        $logDir = '/app/logs'; // Relativ zum src-Ordner -> projekt_root/logs
        $this->logFilePath = $logDir . '/' . $logFileName; // $this->logFilePath wird hier gesetzt!


        $handler = new StreamHandler($this->logFilePath, Level::Debug);
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler); // Handler hinzufügen!

    }

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
