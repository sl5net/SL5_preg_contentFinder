<?php
namespace SL5\PregContentFinder\Tests\PHPUnit\F2;
use SL5\PregContentFinder\Tests\PHPUnit\FilenameProcessor;
use SL5\PregContentFinder\Tests\PHPUnit\YourBaseTestClass;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\SearchMode;

class DontTouchThisSearchModeSimplifiedTest extends YourBaseTestClass
{
    public function testGetContentWithRegexDelimitersAndDontTouchThisMode(): void
    {
        // $this->markTestSkipped('This test is disabled for now');
        $source = 'BEFORE_123#content_GHI_AFTER';
        $expectedContent = '#content_'; // Content between "123" and "GHI"

        // Instanz erstellen (Standard-Delimiter des Konstruktors sind hier irrelevant)
 
        $finder = new PregContentFinder($source);

        $this->logger->info('Hey from function testGetContentWithRegex....() out of DontTouchThisSearchModeSimplifiedTest.php near Line 35');
        $this->logger->info('F11: Please use autoKey-Script jumpFromLog with Hotkey F11 to jump from Log to the respondig source');
         

        // Parameter direkt an getContent übergeben
        // Annahme: getContent-Signatur ist ähnlich: (?string $begin, ?string $end, ?int $pos, SearchMode|string|null $mode)
        $actualContent = $finder->getContent(
            beginRegex: '\d+',              // Findet "123"
            endRegex: '[A-Z]+',           // Findet "GHI"
            startPosition: null,          // Startet von Anfang an oder von $finder->nextSearchPosition
            searchMode: SearchMode::DONT_TOUCH_THIS // Oder 'dontTouchThis' als String
        );
           
        $this->assertSame('\d+', $finder->userProvidedBeginDelimiter);
        // $this->assertSame('\d+', $finder->effectiveBeginDelimiter); 
                   
        $this->assertSame($expectedContent, $actualContent);
    }

}
