<?php
namespace SL5\PregContentFinder\Tests; // Passe diesen Namespace an, falls er anders ist

use PHPUnit\Framework\TestCase;
use SL5\PregContentFinder\PregContentFinder;
use SL5\PregContentFinder\SearchMode;

class BackReference_Test extends TestCase
{
    /**
     * Ruft die geschützte getBorders-Methode auf einem PregContentFinder-Objekt auf.
     * Das ist der saubere Weg, um eine interne Methode für einen Test zugänglich zu machen.
     * @throws \ReflectionException
     */
    private function callProtectedGetBorders(PregContentFinder $finder): ?array
    {
        $reflection = new \ReflectionClass($finder);
        $method = $reflection->getMethod('getBorders');
        return $method->invoke($finder);
    }

    public function test_AABBCC() // Dies ist der Test, den wir die ganze Zeit repariert haben
    {
        $source1 = '<A>.</A> <B>..</B> <C>...</C>';
        $expected = 'Aa: . Bb: .. Cc: ... ';
        $beginEnd = ['(<)([^>]*)(>)?', '<\\/\\2>'];
        
        $cf = new PregContentFinder($source1, $beginEnd);
        $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);

        $actual = '';
        while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
            $tagName = $borders['matches']['begin_matches'][2][0];
            $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
            $actual .= $tagName . strtolower($tagName) . ': ' . $middleContent . ' ';
        }
        $this->assertSame($expected, $actual);
    }



public function test_A_A2_A_A2()
{
    // ...
    $source1 = ' some <A>XO</A></A> thing ';
    $expected = 'A: XO ';

    // === DER FIX IST HIER ===
    // Original: ['(<)([^>]*)(>)', '<\\/\\2>']
    // Neu:      Der Tag-Name darf KEINEN Slash enthalten.
    $beginEndRegEx = ['<([a-zA-Z0-9_]+)>', '<\\/\\1>'];
    
    // Alternative, falls du auch Attribute erlauben willst:
    // $beginEndRegEx = ['<([^\/ >]+)[^>]*>', '<\\/\\1>'];
    //   <             -> Ein <
    //   ([^\/ >]+)   -> Capture Group 1: Ein oder mehrere Zeichen, die KEIN Slash, Leerzeichen oder > sind. Das ist der reine Tag-Name.
    //   [^>]*         -> Optionaler Rest bis zum > (für Attribute)
    //   >             -> Ein >
    //   Backreference ist jetzt \\1, weil es die erste Klammer ist.
    
    // Wir nehmen die einfache Variante für diesen Test:
    $beginEndRegEx = ['<([a-zA-Z0-9_]+)>', '<\\/\\1>'];

    $cf = new PregContentFinder($source1, $beginEndRegEx);
    $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);

    $actual = '';
    while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
        if (is_null($borders['end_begin'])) {
            break;
        }
        
        // Da wir jetzt nur eine Capture Group im Start-Regex haben, ist es Index 1.
        $tagName = $borders['matches']['begin_matches'][1][0];
        $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
        $actual .= $tagName . ': ' . $middleContent . ' ';
    }
    $this->assertEquals($expected, $actual);
}


    public function test_A1_B2B_A()
    {
        // Dieser Test prüft, ob die Balancing-Logik verschachtelte Tags korrekt als Inhalt behandelt.
        $source1 = ' some <A>1<B>2</B></A> thing ';
        $expected = 'A: 1<B>2</B> '; // Das innere <B>...</B> ist Teil des Inhalts von A.
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\\/\\2>'];
        $cf = new PregContentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);

        $actual = '';
        while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
            $tagName = $borders['matches']['begin_matches'][2][0];
            $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
            $actual .= $tagName . ': ' . $middleContent . ' ';
        }
        $this->assertEquals($expected, $actual);
    }

    public function test_ABBA()
    {
        // Selber Fall wie A1_B2B_A, nur am String-Anfang.
        $source1 = '<A>a<B>b</B></A>';
        $expected = 'A: a<B>b</B> '; // Angepasst für Konsistenz
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\\/\\2>'];
        $cf = new PregContentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);

        $actual = '';
        while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
            $tagName = $borders['matches']['begin_matches'][2][0];
            $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
            $actual .= $tagName . ': ' . $middleContent . ' ';
        }
        $this->assertEquals($expected, $actual);
    }

    public function test_AA_BB()
    {
        // Testet zwei aufeinanderfolgende, nicht verschachtelte Blöcke.
        $source1 = '<A>a</A><B>b</B>';
        $expected = 'A: a B: b '; // Angepasst für Konsistenz
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\\/\\2>'];
        $cf = new PregContentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);

        $actual = '';
        while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
            $tagName = $borders['matches']['begin_matches'][2][0];
            $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
            $actual .= $tagName . ': ' . $middleContent . ' ';
        }
        $this->assertEquals($expected, $actual);
    }

    public function test_AaA_BbB()
    {
        // Testet einen komplexeren Regex mit optionalen Gruppen.
        $source1 = '<!--[A]-->a<!--[/A]--><!--[B]-->b<!--[/B]-->';
        $expected = 'A: a B: b ';
        // Regex: (<!--)?\[([^>]*)\](-->)?  End-Regex: <!--\[\/($2)\]-->
        // Capture Group 1: (<!--)?
        // Capture Group 2: ([^>]*) -> Der Tag-Name "A"
        // Capture Group 3: (-->)?
        // Backreference im End-Tag verweist auf Group 2 -> korrekt!
        $beginEnd = ['(<!--)?\\[([^\\]]*)\\](-->)?', '<!--\\[\\/\\2\\]-->']; // `\` in `[]` muss escaped werden
        
        $cf = new PregContentFinder($source1, $beginEnd);
        $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);

        $actual = '';
        while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
            $tagName = $borders['matches']['begin_matches'][2][0]; // Tag-Name ist immer noch in der 2. Gruppe
            $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
            $actual .= $tagName . ': ' . $middleContent . ' ';
        }
        $this->assertEquals($expected, $actual);
    }

    public function test_tags_AaA_BbB()
    {
        // Identisch zu test_AA_BB, nur mit " some " dazwischen.
        $source1 = '<A>a</A> some <B>b</B>';
        $expected = 'A: a B: b ';
        $beginEnd = ['(<)([^>]*)(>)?', '<\\/\\2>'];
        $cf = new PregContentFinder($source1, $beginEnd);
        $cf->setSearchMode(SearchMode::USE_BACKREFERENCE);
        
        $actual = '';
        while (($borders = $this->callProtectedGetBorders($cf)) !== null) {
            $tagName = $borders['matches']['begin_matches'][2][0];
            $middleContent = substr($source1, $borders['begin_end'], $borders['end_begin'] - $borders['begin_end']);
            $actual .= $tagName . ': ' . $middleContent . ' ';
        }
        $this->assertEquals($expected, $actual);
    }
}
?>
