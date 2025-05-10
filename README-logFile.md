Für den Anwender später:
mit Pos1 dann mehrmals Ctrol+rigt lässt sich 
Datname(Datname != DatnamePfad !!!!!!!!!1) und LineNummer leicht kopieren
in die Zwischenablage koppieren und dann in der verwenden um dort die Datei zu öffnen.


Die aktuellen hier sind also alle falsch !!!!!!!!!!!!!!!
Mach das jetzt mal richtig!!!!! wir machen jetzt shcon studnen daran herum!!!

:56 [setUp()] INFO: --- Test Method START: DontTouchThisSearchModeSimplifiedTest::testGetContentWithRegexDelimitersAndDontTouchThisMode --- 
:57 [setUp()] DEBUG: Logging to: /app/logs/DontTouchThisSearchModeSimplifiedTest.log 
:63 [tearDown()] INFO: --- Test Method FINISH --- 
:64 [tearDown()] INFO: --- Test Method FINISH: DontTouchThisSearchModeSimplifiedTest::testGetContentWithRegexDelimitersAndDontTouchThisMode --- 
:56 [setUp()] INFO: --- Test Method START: DontTouchThisSearchModeSimplifiedTest::testGetContentWithRegexDelimitersAndDontTouchThisModeNoEnd --- 
:57 [setUp()] DEBUG: Logging to: /app/logs/DontTouchThisSearchModeSimplifiedTest.log 
:63 [tearDown()] INFO: --- Test Method FINISH --- 
:64 [tearDown()] INFO: --- Test Method FINISH: DontTouchThisSearchModeSimplifiedTest::testGetContentWithRegexDelimitersAndDontTouchThisModeNoEnd --- 
:56 [setUp()] INFO: --- Test Method START: DontTouchThisSearchModeSimplifiedTest::testGetContentUserFuncRecursiveWithRegexDelimiters --- 
:57 [setUp()] DEBUG: Logging to: /app/logs/DontTouchThisSearchModeSimplifiedTest.log 
:63 [tearDown()] INFO: --- Test Method FINISH --- 
:64 [tearDown()] INFO: --- Test Method FINISH: DontTouchThisSearchModeSimplifiedTest::testGetContentUserFuncRecursiveWithRegexDelimiters --- 



Ziel: Soll die Log-Ausgabe soll exakt so aussehen (oder sehr ähnlich)? Nein!!!

PregContentFinder.php:123 [getBorders] DEBUG: Irgendeine Nachricht {"kontext":"wert"}
AnotherFile.php:45 [someFunction] INFO: Andere Nachricht {}

Falsch!!!!! ist weenn in der DateiDontTouchThisSearchModeSimplifiedTest.log
steht:

Richtig wäre z.B.

YourBaseTestClass.php:53 [setUp()][DEBUG] Logging to: /app/logs/DontTouchThisSearchModeSimplifiedTest.log 
DontTouchThisSearchModeSimplifiedTest.php:40 [testSomethingWithLogging()][INFO] Foo from test

Für den Anwender später:
mit Pos1 dann mehrmals Ctrol+rigt lässt sich 
Datname(Datname != DatnamePfad !!!!!!!!!1) und LineNummer leicht kopieren
in die Zwischenablage koppieren und dann in der verwenden um dort die Datei zu öffnen.

Wenn ich in der Zeile 50 der Datei  DontTouchThisSearchModeSimplifiedTest.php in der Funcion Func schreibe
$this->logger->info('HelloWorld');
dann muss
in /logger/DontTouchThisSearchModeSimplifiedTest.log
folgende stehen:

DontTouchThisSearchModeSimplifiedTest:50 [Func] Info: HelloWorld
