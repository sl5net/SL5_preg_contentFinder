<?php
namespace SL5\PregContentFinder\Tests; // Ihr Test-Namespace

use SL5\PregContentFinder\PregContentFinder; // Ihre Hauptklasse

class ZellulaererAutomat1DTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Simuliert eine Generation eines einfachen 1D zellulären Automaten.
     * Regel: Eine Zelle wird lebendig ('X'), wenn genau einer ihrer direkten Nachbarn lebendig ist.
     *        Andernfalls wird sie tot ('.').
     * Randbedingungen: Zellen außerhalb des Strings werden als tot ('.') betrachtet.
     */
    private function simuliereEineGeneration(string $aktuellerZustand): string
    {
        $naechsterZustand = '';
        $laenge = strlen($aktuellerZustand);

        for ($i = 0; $i < $laenge; $i++) {
            $linkerNachbar = ($i > 0) ? $aktuellerZustand[$i-1] : '.';
            // $aktuelleZelle = $aktuellerZustand[$i]; // Für diese Nachbar-basierte Regel nicht direkt benötigt
            $rechterNachbar = ($i < $laenge - 1) ? $aktuellerZustand[$i+1] : '.';

            $lebendeNachbarn = 0;
            if ($linkerNachbar === 'X') {
                $lebendeNachbarn++;
            }
            if ($rechterNachbar === 'X') {
                $lebendeNachbarn++;
            }

            if ($lebendeNachbarn === 1) {
                $naechsterZustand .= 'X';
            } else {
                $naechsterZustand .= '.';
            }
        }
        return $naechsterZustand;
    }

    /**
     * Diese Funktion demonstriert, wie man PregContentFinder für eine solche Aufgabe
     * verwenden *könnte*. Es ist nicht das primäre Designziel, daher etwas "gezwungen".
     * Der Callback bestimmt den nächsten Zustand jeder Zelle.
     */
    private function simuliereMitPregContentFinder(string $aktuellerZustand): string
    {
        $cf = new PregContentFinder($aktuellerZustand);

        // Wir definieren Delimiter, die im Wesentlichen den gesamten String als einen Block erfassen.
        $cf->setBeginEnd_RegEx('/^/', '/$/'); // Passt den gesamten String als einen Block

        $neuerZustandString = $cf->getContent_user_func_recursive(
            function ($cut, $deepCount, $callsCount, $posList0, $originalSegmentContent) {
                // In diesem Setup ist $cut['middle'] der gesamte aktuelle Zustandsstring.
                $aktuellerZustandString = $cut['middle'];
                $naechsterZustand = '';
                $laenge = strlen($aktuellerZustandString);

                for ($i = 0; $i < $laenge; $i++) {
                    $linkerNachbar = ($i > 0) ? $aktuellerZustandString[$i-1] : '.';
                    $rechterNachbar = ($i < $laenge - 1) ? $aktuellerZustandString[$i+1] : '.';

                    $lebendeNachbarn = 0;
                    if ($linkerNachbar === 'X') {
                        $lebendeNachbarn++;
                    }
                    if ($rechterNachbar === 'X') {
                        $lebendeNachbarn++;
                    }

                    if ($lebendeNachbarn === 1) {
                        $naechsterZustand .= 'X';
                    } else {
                        $naechsterZustand .= '.';
                    }
                }
                $cut['middle'] = $naechsterZustand;
                return $cut; // Callback gibt modifiziertes $cut Array zurück
            }
        );
        // Annahme: getContent_user_func_recursive gibt den vollständig verarbeiteten String zurück
        return $neuerZustandString;
    }

    public function testEinfacheEntwicklungRegel30Aehnlich(): void
    {
        $initialerZustand = "...X...";
        $erwarteteGen1 = ".XX.XXX";
        $this->assertEquals($erwarteteGen1, $this->simuliereEineGeneration($initialerZustand));
        $this->assertEquals($erwarteteGen1, $this->simuliereMitPregContentFinder($initialerZustand));

        $erwarteteGen2 = "X.XX.XX";
        $this->assertEquals($erwarteteGen2, $this->simuliereEineGeneration($erwarteteGen1));
        $this->assertEquals($erwarteteGen2, $this->simuliereMitPregContentFinder($erwarteteGen1));
    }

    public function testAllesTotBleibtTot(): void
    {
        $initialerZustand = ".......";
        $erwarteteGen1 = ".......";
        $this->assertEquals($erwarteteGen1, $this->simuliereEineGeneration($initialerZustand));
        $this->assertEquals($erwarteteGen1, $this->simuliereMitPregContentFinder($initialerZustand));
    }

    public function testSoliderBlockStirbtAus(): void
    {
        $initialerZustand = ".XXXXX.";
        $erwarteteGen1 = "X...XX.";
        $this->assertEquals($erwarteteGen1, $this->simuliereEineGeneration($initialerZustand));
        $this->assertEquals($erwarteteGen1, $this->simuliereMitPregContentFinder($initialerZustand));

        $erwarteteGen2 = "...X.XX";
        $this->assertEquals($erwarteteGen2, $this->simuliereEineGeneration($erwarteteGen1));
        $this->assertEquals($erwarteteGen2, $this->simuliereMitPregContentFinder($erwarteteGen1));
    }

    public function testOszillator(): void
    {
        $initialerZustand = ".X.X.";
        $erwarteteGen1 = "X...X";
        $this->assertEquals($erwarteteGen1, $this->simuliereEineGeneration($initialerZustand));
        $this->assertEquals($erwarteteGen1, $this->simuliereMitPregContentFinder($initialerZustand));

        $erwarteteGen2 = "...X.";
        $this->assertEquals($erwarteteGen2, $this->simuliereEineGeneration($erwarteteGen1));
        $this->assertEquals($erwarteteGen2, $this->simuliereMitPregContentFinder($erwarteteGen1));
    }
}
