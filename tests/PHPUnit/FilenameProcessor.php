<?php
declare(strict_types=1);
namespace SL5\PregContentFinder\Tests; // Im selben Namespace wie die Tests
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
class FilenameProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        if (isset($record['extra']['file'])) {
            $record['extra']['filename_only'] = basename((string) $record['extra']['file']);
        }
        return $record;
    }
}
