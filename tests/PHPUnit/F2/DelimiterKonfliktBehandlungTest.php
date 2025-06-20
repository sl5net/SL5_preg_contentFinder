<?php
namespace SL5\PregContentFinder\Tests;

use SL5\PregContentFinder\PregContentFinder;

use SL5\PregContentFinder\SearchMode;

class DelimiterKonfliktBehandlungTest extends \PHPUnit\Framework\TestCase
{
    public function testInhaltMitDelimitergleichenZeichenWirdKorrektBehandelt(): void
    {
        $source = "AUSSEN_START{Das ist {innerer Inhalt} mit Klammern}AUSSEN_ENDE";
        $erwarteterMittelteilFuerCallback = "Das ist {innerer Inhalt} mit Klammern";
        $erwarteteEndausgabe = "TRANSFORMIERT:Das ist {innerer Inhalt} mit Klammern";

        $finder = new PregContentFinder($source);
        $finder->setSearchMode(SearchMode::DONT_TOUCH_THIS);
        $finder->setBeginEnd_RegEx('AUSSEN_START\{', '\}AUSSEN_ENDE');
        // $finder->setBeginEnd_RegEx('/AUSSEN_START\{/', '/\}AUSSEN_ENDE/');

        $tatsaechlicherMittelteilAnCallback = null;

        $resultat = $finder->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) use (&$tatsaechlicherMittelteilAnCallback) {
                $tatsaechlicherMittelteilAnCallback = $cut['middle'];
                $cut['middle'] = "TRANSFORMIERT:" . $cut['middle'];
                return $cut;
            }
        );

        $this->assertEquals($erwarteterMittelteilFuerCallback, $tatsaechlicherMittelteilAnCallback, "Callback hat nicht den korrekten Mittelteil empfangen.");
        $this->assertEquals($erwarteteEndausgabe, $resultat, "Endausgabe nicht wie erwartet.");
    }

    public function testVerschachtelterInhaltMitGleichenDelimiternWirdDurchInterneMaskierungBehandelt(): void
    {
        $source = "daten_davor{ebene1_inhalt {ebene2_inhalt} ebene1_ende}daten_danach";
        // The engine will replace the inner `{...}` with `D1:...`, then the outer `{...}` with `D0:...`
        $erwartetesEndresultat = "daten_davorD0:ebene1_inhalt D1:ebene2_inhalt ebene1_endedaten_danach";

        $finder = new PregContentFinder($source);
        $finder->setBeginEnd_RegEx('{', '}'); 

        $resultat = $finder->getContent_user_func_recursive(
            function ($cut, $deepCount) {
                // Simple, consistent callback logic
                $cut['middle'] = "D{$deepCount}:" . $cut['middle'];
                return $cut;
            }
        );
        
        $this->assertEquals($erwartetesEndresultat, $resultat);
    }
    public function testSubstringDelimiterWirdKorrektBehandelt(): void
    {
        $source = "AUSSEN_START{{inhalt_mit_maskierter_klammer}}AUSSEN_ENDE";
        $erwarteterMittelteilFuerCallback = "{inhalt_mit_maskierter_klammer}";
        $erwartetesEnde = "TRANSFORMIERT:{inhalt_mit_maskierter_klammer}";

        $finder = new PregContentFinder($source);
        $finder->setSearchMode(SearchMode::DONT_TOUCH_THIS);

        // $finder->setBeginEnd_RegEx('/AUSSEN_START\{/', '/\}AUSSEN_ENDE/');
        $finder->setBeginEnd_RegEx('AUSSEN_START\{', '\}AUSSEN_ENDE');
        $tatsaechlicheMitte = null;
        $resultat = $finder->getContent_user_func_recursive(function($cut) use (&$tatsaechlicheMitte) {
            $tatsaechlicheMitte = $cut['middle'];
            $cut['middle'] = "TRANSFORMIERT:" . $cut['middle'];
            return $cut;
        });

        $this->assertEquals($erwarteterMittelteilFuerCallback, $tatsaechlicheMitte);
        $this->assertEquals($erwartetesEnde, $resultat);
    }
}