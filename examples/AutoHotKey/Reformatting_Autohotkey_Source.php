<?php
use SL5\PregContentFinder\PregContentFinder;
/*
This script expects named arguments (some optional):
script.php --source1=./hello-world.go

// name1="value1" name2="value2"
// or
// name1=value1
$fileAddress $arguments['source1'] : '';
$arguments['indentStyle'] = 'free';
$arguments['renameSymbol'])) {
$charSpace $arguments['charSpace']))
$newline = $arguments['newline'])) ? $arguments['newline'] : "\r\n";
$indentSize = (isset($arguments['indentSize'])) ? $arguments['indentSize'] : 3;
if(strpos($arguments['indentStyle'],'SL5net_small+_NotRecommended') !== false )
if(strpos($arguments['indentStyle'], 'SL5net_small') !== false) {
$arguments['indentStyle'] = 'SL5net_small';
$arguments['indentStyle'] == 'SL5net_small_v0.2') ? true : false;
$arguments['indentStyle'] = 'free'
$arguments['renameSymbol'])

*/

$bugIt=true;
#$bugIt=false;
if($bugIt)echo __LINE__.':  :-) ' . "\n";

// $bugIt=false;

$pathinfo__FILE__ = pathinfo(__FILE__);
$pathinfo_Script_Name = (isset($_SERVER['SCRIPT_NAME'])) ? pathinfo($_SERVER['SCRIPT_NAME']) : '';
$isIncluded = ((isset($argv[0]) && !empty($argv[0]))
  && ($pathinfo_Script_Name && @$pathinfo__FILE__['basename'] == @$pathinfo_Script_Name['basename']));
if($isIncluded){
  echo __LINE__.':  :-) $isIncluded=' . $isIncluded . "\n";
  $SCRIPT_NAMEpath = $pathinfo_Script_Name['dirname'] . '/' . $pathinfo_Script_Name['basename'];
  if($argv[0] == $SCRIPT_NAMEpath)
    $isIncluded = false;
}

if($bugIt){
  echo __LINE__.':  :-) $isIncluded=' . $isIncluded . "\n";

  echo __LINE__.':  :-) $pathinfo_Script_Name=' . "\n";
  var_dump($pathinfo_Script_Name);


  $isCli = (php_sapi_name() == "cli");
  $temp = "
isCli = $isCli
$SCRIPT_NAMEpath = SCRIPT_NAMEpath
${argv[0]} = argv[0]
  ";
  # die($temp);
  echo $temp;
}


// die(var_export($pathinfo__FILE__, true));
$pathDir = $pathinfo__FILE__['dirname'];



if( !file_exists( $pathDir . "/../../PregContentFinder.php")) {
//     die($pathDir);
    die("\n".__LINE__ . $pathDir . ':( NOT EXIST: ../../PregContentFinder.php\n\n' . PHP_EOL );
}
if($bugIt)echo __LINE__.':  :-) ';
$bugIt=true;
if($bugIt)echo __LINE__.':  :-) ';


// ??????????? $fileAddressSaved = '../../../../../' . preg_replace('/\..*$/', '', $fileAddress) . '.ahk';

if($bugIt)echo __LINE__.':  :-) ';


# http://php.net/manual/de/features.commandline.php
//parse_str(implode('&', array_slice($argv, 1)), $_GET);
//if(!$isIncluded) echo 'little autohotkey example. $argv[0]=' . @$argv[0];
//echo '' . @$argv[0];
if($bugIt)echo ' :-) ';
if(!$isIncluded && !file_exists('SL5_phpGeneratedRunOnChanged.tmpl.ahk')) {
    if($bugIt)echo ' :-] ';
    echo(':( NOT EXIST: SL5_phpGeneratedRunOnChanged.tmpl.ahk');
}

if($bugIt)echo __LINE__.':  :-) ';

if(!$isIncluded && !isset($argv[1])) {
    $file = 'test.ahk';
    $i = 0;

    while(!file_exists($file) && $i++ < 6) {
        $file = '..\\' . $file;
    }
    $realpath = realpath($file);
    if(!$realpath) {
      echo "\r\n". PHP_EOL;
      echo "\r\n". PHP_EOL;
        die("\n".__LINE__ . ':( NOT EXISTS ' . nl2br("\n\$file=" . $realpath . " = $file" ) . "\n" . PHP_EOL);
    }
    else {
//        echo __LINE__ . ':' . nl2br("\n\$file=" . $realpath . " = $file\n");

        $argv[1] = '--source1="E:\fre\private\HtmlDevelop\AutoHotKey\SL5_AHK_Refactor_engine_gitHub\\' . $file . '" --renameSymbol="Mod" --renameSymbol_To="zzzzzzz"';
        $argv[1] = '--source1="E:\fre\private\HtmlDevelop\AutoHotKey\SL5_AHK_Refactor_engine_gitHub\\' . $file . '" renameSymbol="zzzzzzz" renameSymbol_To="rrrrrrrrr"';
        $argv[1] = '--source1="E:\fre\private\HtmlDevelop\AutoHotKey\SL5_AHK_Refactor_engine_gitHub\\' . $file . '" --A_ThisLabel="Alt & Down"';

        $argv[1] = 'E:\fre\private\HtmlDevelop\AutoHotKey\SL5_AHK_Refactor_engine\phpdesktop-msie-1.14-php-5.4.33\php\php-cgi.exe E:\fre\private\HtmlDevelop\AutoHotKey\SL5_AHK_Refactor_engine\phpdesktop-msie-1.14-php-5.4.33\www\PregContentFinder\examples\AutoHotKey\Reformatting_Autohotkey_Source.php --source1="E:\fre\private\HtmlDevelop\AutoHotKey\SL5_AHK_Refactor_engine\keys_SL5_AHK_Refactor_engine.ahk" --A_ThisLabel="Alt & Up"
';
        unset($argv[1]);
    }
}
if($bugIt)echo __LINE__.':  :-) ' . "\n";

if(isset($argv)) {
    if($bugIt)echo __LINE__.':  :-) ' . "\n";

    $arguments = arguments($argv); // PHPDoc    Note: The first argument $argv[0] is always the name that was used to run the script.

    if($bugIt){
      echo 'var_dump($argv): ' . "\n";
      var_dump($argv);
      echo 'var_dump($arguments): ' . "\n";
      var_dump($arguments);
  }


    // name1="value1" name2="value2"
    // or
    // name1=value1
    $fileAddress = (isset($arguments['source1'])) ? $arguments['source1'] : '';
//    $fileAddress = (isset($arguments['source1'])) ? $arguments['source1'] : '';
if( !file_exists($fileAddress)) {
  $fileAddress = $pathDir . '/' . $fileAddress;
}
if( !file_exists($fileAddress)) {
    die(__LINE__ . ': :( arguments source1 NOT EXIST: fileAddress="' . $fileAddress . '", $pathDir=\'' . $pathDir .  "'\n" . PHP_EOL);
}

}
    if($bugIt)echo __LINE__.':  :-) ';

if(!$isIncluded && isset($fileAddress) && file_exists($fileAddress)) {
    if($bugIt)echo "its isIncluded";
    if(!isset($fileAddress) || !$fileAddress || empty($fileAddress)) {

        $fileAddress = 'input_compressed_2.ahk';
        $file_content = file_get_contents($fileAddress);
        $fileAddress = 'output_reformatted_2.ahk';
        $arguments = null;
    }
    else {
        $file_content = file_get_contents($fileAddress);

        if($bugIt)var_dump($file_content);

    }
}
if($bugIt)echo ' :-) $isIncluded='.$isIncluded . " " . "\n";
if($bugIt)echo __LINE__.':  :-) ' . "\n";
if($bugIt)echo __LINE__.':  :-) $fileAddress=' . $fileAddress . "\n";

if(!$isIncluded && isset($fileAddress) && file_exists($fileAddress)) {
    $format = new DateTime();
    $timeStamp = $format->format('s'); // Y-m-d_H-s
    if($bugIt)echo __LINE__.':  :-) ' . $file_content;
    file_put_contents($fileAddress . '.backup' . $timeStamp . '.ahk', $file_content);

    if($bugIt)echo __LINE__.':  :-) ' . $file_content;
    if($bugIt)echo __LINE__.':  :-) ';
    $actual_content = reformat_AutoHotKey($file_content, $arguments);
    file_put_contents($fileAddress, $actual_content); // Write data to a file
    if($bugIt)echo __LINE__.':  :-) reult written to fileAddress=\'' . $fileAddress . "'";

}

#######################################
######### endo of program , start of functions
########################################

function reformat_AutoHotKey($file_content, $arguments = null) {
    if(!isset($file_content)) die('15-06-25_15-07 $f_input');
    if(@empty($arguments['indentStyle'])) {
        /*
https://en.wikipedia.org/wiki/Indent_style
https://de.wikipedia.org/wiki/Einr%C3%BCckungsstil#1TBS_.2F_K.26R_.2F_Kernel_.2F_Linux_.2F_UNIX_.2F_.E2.80.9EWest_Coast.E2.80.9C_.2F_Stroustrup_.2F_Java_.2F_Sun
        Java-Style
int f(int x, int y, int z) {
     if (x < foo(y, z)) {
         qux = bar[4] + 5;
     } else {
         return ++x + bar();
     }
 }
        SL5net-Style
int f(int x, int y, int z) {
     if (x < foo(y, z)) {
         qux = bar[4] + 5;
     } else {
         return ++x + bar();
}    }
        Allman-Style
     if (x < foo(y, z))
     {
         qux = bar[4] + 5;
     }
     else
     {
         return ++x + bar();
     }
         */
        $arguments['indentStyle'] = 'free';
    }
    if(!@empty($arguments['renameSymbol'])) {
        $fArgs = '\([^)]*\)';
        $old_open = '(' . $fArgs . '\s*[^{;\n]*)\{[\s\n]';
    }
    else {
        # } else {
        $old_open = '([^\n{;]*)\{[^\w%$`]'; # problem this searchs in comments
        $old_open = '^([^{;]*?)\{[^\w%$`]'; #
//        $old_open = '\{';
        $old_open = '^([^{;\n]*)\{[\s\n]';# todo: problem. dont finds:  } else {
    }

    $old_close = '^[ ]*\}?([^{};\n\r]*?)\}?[\s\n\r]';
    $old_close = '^[ ]*\}?([^{};\n\r]*)\}';
    $old_close = '(\n\s*)\}';
//    $old_close = '^([^{};\n\r]*)\}[ ]*$';
//    $old_close = '\}';
//Send,{CtrlUp} {Blind}


    $new_open_default = '[ ';
    $new_close_default = ']';
    $new_open_default = '{ ';
    $new_close_default = '}';
    $charSpace = (isset($arguments['charSpace'])) ? $arguments['charSpace'] : " ";
    $newline = (isset($arguments['newline'])) ? $arguments['newline'] : "\r\n";
    $indentSize = (isset($arguments['indentSize'])) ? $arguments['indentSize'] : 3;


    $file_content = trim(preg_replace('/^\h+/ism', '', $file_content));
    # horizontal whitespace character class \h. http://stackoverflow.com/questions/3469080/match-whitespace-but-not-newlines-perl
    # Match whitespace but not newlines

    $getIndentStr = function ($indent, $char, $indentSize) {
        $multiplier = $indentSize * $indent;
        $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

        return $indentStr;
    };

    $indentStr = $getIndentStr(1, $charSpace, $indentSize);

//    $indentStyle SL5net_small_v0.1
    $conf['noNewlineAtEnd'] = false;
    $conf['noNewlineAt_Start_beta']=false;
    if(strpos($arguments['indentStyle'],'SL5net_small+_NotRecommended') !== false )
    {
        $conf['noNewlineAtEnd'] = true;
        $conf['noNewlineAt_Start_beta']=true;
    }else {
        if(strpos($arguments['indentStyle'], 'SL5net_small') !== false) {
            $arguments['indentStyle'] = 'SL5net_small';
        }

        $conf['noNewlineAtEnd'] = ($arguments['indentStyle'] == 'SL5net_small') ? true : false;
        $conf['noNewlineAt_Start_beta'] = ($arguments['indentStyle'] == 'SL5net_small_v0.2') ? true : false;
        $conf['noNewlineAtStart'] = false;
    }
    if($conf['noNewlineAt_Start_beta'] || $conf['noNewlineAtStart']) {
        # todo: thats not performing. thats a global replace.
        $preg = '(\bif\s*\([^\n\r)]+\))\R';
            $file_content = preg_replace('/'.$preg.'/', "$1", $file_content); # \R matches \r and \n
    }



    if($conf['noNewlineAtStart']) {
        $file_content = preg_replace('/^\s*\}\s*else(\s+if\s*\([^\n\r]+\)\s*)?\s*\{+/smi', "} " . '' . "else $1 {", $file_content);
    } // dirty BugFix .. need temporary newline that script later works correct
    else {
        $file_content = preg_replace('/^\s*\}\s*else(\s+if\s*\([^\n\r]+\)\s*)?\s*\{+/smi', "} " . $newline . '' . "else $1 {", $file_content);
    } // dirty BugFix .. need temporary newline that script later works correct


//    $file_content = preg_replace('/(\s*\bif\s*\([^\n\r)]+\)\s*)[\n\r]+([^{\s])/smi', "$1\n" . $newline . $indentStr . "$2", $file_content); // dirty BugFix
    $file_content = preg_replace_callback('/(\s*\bif\s*\([^\n\r)]+\)\s*)[\n\r]+([^{\s])/smi',
      function ($m) use ($newline, $indentStr, $arguments) {
          if(!isset($arguments['indentStyle'])) {
              die(':( $arguments[\'indentStyle\']');
          } # $arguments['indentStyle'] = 'free'

          /* if ( next )
              Check
          */
//          if(preg_match('/\n$/',$m[1]) ) {
//              return '*' . $m[1] . '#' . $indentStr . $m[2];
//          }
//          if(strpos($m[2], "\n") !== false) {
//              return ':' . $m[1] . '#' . $indentStr . $m[2];
//          }
          if(strpos($m[0], "\n") !== false) {
              return '' . $m[1] . '' . $indentStr . $m[2];
//              return '\\' . $m[1] . '/' . $indentStr . $m[2];
          }

          return '-' . $m[1] . "_\n" . $newline . '7' . $indentStr . $m[2];
//          return strtolower($treffer[0]);
      }
      ,
      $file_content); // dirty BugFix


    $file_content = preg_replace(
      '/(\s*\belse\s*)[\n\r]+([^{\s])/smi', "$1\n" . $indentStr . "$2", $file_content); // dirty BugFix


//    $pattern = '([\r\n](If|#if)[a-z]+[ ]*[ ]*[^\n\r{]+)[ ]*[\r\n]+[ ]*(\w)';
    # IfWinNotExist,%filename% * SciTE4AutoHotkey
//    return $file_content;
    $pattern = '([\r\n](if|#if)([a-z]+[ ]*,|\()[ ]*[^\n\r{]+)[ ]*[\r\n]+[ ]*(\w)';
    if($conf['noNewlineAtStart']) {
        $file_content = '' . preg_replace_callback('/' . $pattern . '/is',
            function ($m) use ($newline, $indentStr) {
                $i = 456;

                # "$1" . $newline . '8' . $indentStr . "$4"
                return $m[1] . $newline . '' . $indentStr . $m[4];
            }
            , $file_content);
    }
    else {
        $file_content = '' . preg_replace('/' . $pattern . '/is', "$1" . $newline . '' . $indentStr . "$4", $file_content);
    }

    $cf = new PregContentFinder($file_content);
    $cf->setBeginEnd_RegEx($old_open, $old_close);
    $cf->setSearchMode('dontTouchThis');


    /*
     *             $cut = call_user_func($func['open'], $cut, $deepCount + 1, $callsCount, $C->foundPos_list[0], $C->content);

     */

//    return $file_content;


    $actual = $cf->getContent_user_func_recursive(

      function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($conf, $arguments, $new_open_default, $new_close_default, $charSpace, $newline, $indentSize, $getIndentStr) {
          if($cut['middle'] === false) return $cut;
//          if($cut['middle'] === false || $cut['behind'] === false) {
//              return false;
//          }
//}   else if(RegExMatch(c, ":" )) {

//          $charSpace = '.';
          $indentStr = $getIndentStr(1, $charSpace, $indentSize);

          if(!isset($posList0['begin_end'])) $posList0['begin_end'] = strlen($source1);


          $start = '' . substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']) . '';
          $end = '' . ltrim(substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin'])) . '';

          if(@$arguments['A_ThisLabel'] == "Alt & Up" || @$arguments['A_ThisLabel'] == "Alt & Down"
            || !@empty($arguments['renameSymbol']) && !empty($arguments['renameSymbol_To'])
          ) {
              $markerXXXXstring = "xxxxxxxx" . "xxxxxxxx";
//              $markerXXXXstring = "xxxxxxxxxxxxxxxx";
              $strposMarker = strpos($cut['middle'], $markerXXXXstring);
              if($strposMarker !== false) {

                  if(@$arguments['A_ThisLabel'] == "Alt & Up" || @$arguments['A_ThisLabel'] == "Alt & Down") {
                      if(@$arguments['A_ThisLabel'] == "Alt & Down") {

                          preg_match_all('/\n/', substr($cut['middle'], $strposMarker + strlen($markerXXXXstring)), $matches);
                          $command = 'Down';
                      }
                      else {
                          preg_match_all('/\n/', substr($cut['middle'], 0, $strposMarker), $matches);
                          $command = 'Up';
                      }
                      $linesAboveMarker = count($matches[0]) + 1;

//                      $fileAddress = realpath('../../SL5_phpGeneratedRunOnChanged.ahk');
//                      $fileAddress = realpath('p.txt');
                      $fileAddress = 'SL5_phpGeneratedRunOnChanged.tmpl.ahk';
                      $pathinfo = pathinfo($fileAddress);
                      if(!file_exists($fileAddress)) {
                          die("!file_exists($fileAddress) 15-07-06_14-26");
                      }
                      $contents = file_get_contents($fileAddress);
                      if(!$contents) {
                          die('!$contents 15-07-06_14-18 \n $contents=' . $contents . '$fileAddress=' . $fileAddress);
                      }
                      $ahkContent =
                        '
Suspend,on
; Send,^z
; Sleep,50
Send,{' . $command . ' ' . $linesAboveMarker . '}
Suspend,off
';
                      $contents = preg_replace('/<body>.*<\/body>/ism', "<body>\n" . $ahkContent . "\n;</body>", $contents);
//                      $fileAddressSaved = realpath('../../../../../' . $fileAddress . '.ahk');
                      $fileAddressSaved = '../../../../../' . preg_replace('/\..*$/', '', $fileAddress) . '.ahk';
                      echo nl2br("\n$fileAddressSaved=" . $fileAddressSaved);
//                       die($fileAddressSaved);
                      file_put_contents($fileAddressSaved, $contents);

                  }

                  # cut out markerString
                  $cut['middle'] = preg_replace('/;\s*' . $markerXXXXstring . '/', '', $cut['middle']);

                  if(!@empty($arguments['renameSymbol'])) {
                      $start = preg_replace('/\b(' . $arguments['renameSymbol'] . ')\b/', $arguments['renameSymbol_To'], $start);
                      $cut['middle'] = preg_replace('/\b(' . $arguments['renameSymbol'] . ')\b/', $arguments['renameSymbol_To'], $cut['middle']);
                  }
              }

          }

//          return $cut;

//          $cut['middle'] = preg_replace("/\r/", "\n" . $indentStr , $cut['middle']); // <Remember this is without \r means buggy for
          if($conf['noNewlineAtEnd']) {
              $doNoNewlineAtEnd = preg_match("/\W[}\s]*\}\R*$/ms", $cut['middle'], $m);
              if($doNoNewlineAtEnd) {
//               $cut['middle'] = implode($indentStr, preg_split("/(\r\n|\n|\r)/m", $cut['middle']));
                  $cut['middle'] = rtrim($cut['middle']) . '';
                  $i = 456;
              }
//              $doNoNewlineAtEnd = preg_match("/}\s*}\s*$/m", $cut['middle'], $m);
          }
//          $cut['middle'] = implode($newline . '' . $indentStr, preg_split("/(\r\n|\n|\r)/m", $cut['middle']));
          $cut['middle'] = implode($newline . '' . $indentStr, preg_split("/(\R)/m", $cut['middle']));

//          return $cut;

          if($conf['noNewlineAtStart']) {
              $preg = '\bif\s*\([^\n\r)]+\)';
              if(preg_match("/$preg/ims", $start, $m)) {
                  $start = preg_replace("/\R/", '', $start); # \R matches \r and \n
                  $cut['middle'] = ltrim($cut['middle']);
              }
//                 $file_content = preg_replace_callback('/(\s*\bif\s*\([^\n\r)]+\)\s*)[\n\r]+([^{\s])/smi',
//                   function ($m) use ($newline, $indentStr, $arguments) {

              $i1 = 45;
              $cut['middle'] = '' . trim($start) . '' . '' .
                $indentStr . '' . trim($cut['middle']) . '';
              $i1 = 45;
          }
          else {
              $cut['middle'] = '' . rtrim($start) . '' . $newline . '' .
                $indentStr . '' . trim($cut['middle']) . '';
          }
//          return $cut;
//          $cut['middle'] = '' . rtrim($start) . $n . $indentStr
//            . trim(preg_replace('/\n/', "\n" . $indentStr, $cut['middle']));
//          $charSpace = '.';


//          $indentStr = $getIndentStr(0, $charSpace, $indentSize);
          if($conf['noNewlineAtEnd'] && $doNoNewlineAtEnd) {
//              if($doNoNewlineAtEnd) {
              $cut['middle'] .= $end . '' . $cut['behind'] . '';
//              }
          }
          else {
              $cut['middle'] .= $newline . '' . $end . '' . $cut['behind'] . '';
          }

//          die('<pre>'.implode('',$cut).$end.'</pre>');


          return $cut;
      });
//return $actual;

    if($conf['noNewlineAtEnd']) {
        $actual = preg_replace_callback('/(\})[\s\n\r]*(else(\s+if\s*\([^\n\r]+\)\s*)?\s*\{+)/smi',
          function ($m) {
//      "$1$2"
              $i = 156;

              return $m[1] . '' . $m[2] . '';
          }
          , $actual);
    } // dirty BugFix
# ([^\n\r]+\)
    else {
        $actual = preg_replace('/(\})[\s\n\r]*(else(\s+if\s*\([^\n\r]+\)\s*)?\s*\{+)/smi', "$1$2", $actual);
    } // dirty BugFix
# ([^\n\r]+\)

//    $pattern = '/^(\w+:)(\R(?:\N*\R)*?)(return)$/mis';
//    $pattern = '/^(\w+:)(\h*\n)(?:.*\n)*?(return)/m';
    $label = '^[a-z][\w\d_]*:';
    $hotkey = '^.+::\h*';
    $pattern = '/' . "($label|$hotkey)(\h*\R)((?:.*\R)*?)(return\b)" . '/im';
    preg_match_all($pattern, $actual, $matches, PREG_OFFSET_CAPTURE);
    $labelsAr = $matches[1];
//    $contentAr = preg_replace('/\n/ism', "\n" . $indentStr, $matches[3]);
    $contentAr = $matches[3];
    $returnAr = $matches[4];
    for($k = count($labelsAr); $k--; $k >= 0) {
        $new = $labelsAr[$k][0]
          . $newline . '' . $indentStr
          . rtrim(preg_replace('/\R/ism', $newline . ''
            . "" . $indentStr, $contentAr[$k][0]))
          . $newline . '' . trim($returnAr[$k][0]);
        $actual = substr($actual, 0, $labelsAr[$k][1])
          . $new
          . substr($actual, $returnAr[$k][1] + strlen($returnAr[$k][0]));
    }

    return $actual;
}

//test::
//sdfsdf
//return
// name1="value1" name2="value2"
// or
// name1=value1
function arguments($argv) {
    $_ARG = array();
    foreach($argv as $arg) {
        if(preg_match_all('/--([^=]+)="?([^"]*)"?/', $arg, $reg)) {
            foreach($reg[1] as $k => $v) {
                $var = $reg[2][$k];
                $_ARG[$v] = $var;
            }

        }
        elseif(preg_match_all('/-([^=]+)="?([^"]*)"?/', $arg, $reg)) {
            $_ARG[$reg[1]] = 'true';
        }

    }

    return $_ARG;
}
