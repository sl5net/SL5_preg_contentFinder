<?php

//@include_once("../SL5_preg_contentFinder.php");
//
//require("../SL5_preg_contentFinder.php");
$f = 'SL5_preg_contentFinder.php';
while(!file_exists($f)) {
    $f = '../' . $f;
    echo "$f exist.";
}
include_once "../create_1file_withAll_PHPUnit_tests.php"; # ok little overhead. sometimes ;) 15-06-19_12-35
include_once $f;
//include_once "_callbackSh!!ortExample.php";
//include '../../lib/finediff.php';
class Callback_Test extends PHPUnit_Framework_TestCase {
    function test_doSqlWeb_def() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":" 
          . ' [select * from table1#mySelect1]'
          . ' [select * from table2#mySelect2]'
          . ' ';
        $old = ['\[select\s+[^]]+#\w+', '\]'];
        $newQuotes = ['[', ']'];
        $expected = $LINE__ . ":" . ' [mySelect1:select * from table1#mySelect1] [mySelect2:select * from table2#mySelect2] ';
 
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes) {
//              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }

              $begin = substr($source1, $posList0['begin_begin'] + 1, $posList0['begin_end'] - $posList0['begin_begin'] - 1);
              
            $end = substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin']);
              
              $definition = substr($source1, $posList0['begin_begin'] + 1 , $posList0['end_end'] - $posList0['begin_begin'] - 2);
              


              if(preg_match("/(.*)#(\w+).*?/", $definition, $matches)) {
                  $sql = $matches[1];
                  $aliasName = $matches[2];
                  unset($matches);
              }
              $cut['middle'] = $aliasName . ':' . $begin . $cut['middle'] ;
              $cut['before'] .= $newQuotes[0];
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];
              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }
    function test_doSqlWeb_def_prototyp_1() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":"
          . ' <s bu#1> <s hu#2> '
          . ' <s ui#3> <s uf#4> ';
        $old = ['<\w+\s+', '>'];
        $newQuotes = ['[', ']'];
        $expected = str_replace(array('<', '>'), $newQuotes, $source1);
        $expected = $LINE__ . ":" . ' [1:s bu#1] [2:s hu#2]  [3:s ui#3] [4:s uf#4] ';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes) {
//              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }

              $begin = substr($source1, $posList0['begin_begin'] + 1, $posList0['begin_end'] - $posList0['begin_begin'] - 1);
              
            $end = substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin']);
              
              $definition = substr($source1, $posList0['begin_begin'] + 1 , $posList0['end_end'] - $posList0['begin_begin'] - 2);
              


              if(preg_match("/(.*)#(\w+).*?/", $definition, $matches)) {
                  $sql = $matches[1];
                  $aliasName = $matches[2];
                  unset($matches);
              }
              $cut['middle'] = $aliasName . ':' . $begin . $cut['middle'] ;
              $cut['before'] .= $newQuotes[0];
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];
              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }
    function test_tag_attribute() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":"
          . ' <a bu> <b hu> '
          . ' <c ui> <d uf> ';
        $old = ['<', '>'];
        $newQuotes = ['[', ']'];
        $expected = str_replace($old, $newQuotes, $source1);
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes) {
//              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['before'] .= $newQuotes[0];
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

    function test_AHK_prettify_return_problem_SL5smal_style() {
        # if you have problems with this test it may helps reading this: https://youtrack.jetbrains.com/issue/WI-29216 , https://youtrack.jetbrains.com/issue/WI-11032
        include_once('../../examples/AutoHotKey/Reformatting_Autohotkey_Source.php');
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":" .
          'this is ugly example source

 if(doIt)
{
if(doIt)
{
      if ( next )
         Check
               else
      Check = 5
      if ( next )
{
         Send,5b{Right 5}
}
}
}

MyLabel:
Send,{AltUp}
return

^s::
Send,save
return

isFileOpendInSciteUnsaved(filename){
    SetTitleMatchMode,2
doSaveFirst := false ; initialisation
   IfWinNotExist,%filename% - SciTE4AutoHotkey{
doSaveFirst := true
IfWinNotExist,%filename% * SciTE4AutoHotkey
MsgBox,oops   NotExist %filename% * SciTE4AutoHotkey
 if(false){
      Too(Last_A_This)
      if (next )
         Check = 1
      else if (nils == "tsup")
      Check = 42
      else
      Check = 3

   s := Com("{D7-2B-4E-B8-B54}")
   if !os
ExitApp
   else if(really){
MsgBox, yes really :)
     }
     else
   ExitApp


   ; comment :) { { hi } {


   }
}
return doSaveFirst
}

';
        $expected = $LINE__ . ":" .
          'this is ugly example source

if(doIt)
{
   if(doIt)
   {
      if ( next )
         Check
      else
         Check = 5
      if ( next )
      {
         Send,5b{Right 5}
      }}}

MyLabel:
   Send,{AltUp}
return

^s::
   Send,save
return

isFileOpendInSciteUnsaved(filename){
   SetTitleMatchMode,2
   doSaveFirst := false ; initialisation
   IfWinNotExist,%filename% - SciTE4AutoHotkey{
      doSaveFirst := true
      IfWinNotExist,%filename% * SciTE4AutoHotkey
         MsgBox,oops   NotExist %filename% * SciTE4AutoHotkey
      if(false){
         Too(Last_A_This)
         if (next )
            Check = 1
         else if (nils == "tsup")
            Check = 42
         else
            Check = 3
         
         s := Com("{D7-2B-4E-B8-B54}")
         if !os
         ExitApp
         else if(really){
            MsgBox, yes really :)
         }
         else
            ExitApp
         
         
         ; comment :) { { hi } {
      }}
   return doSaveFirst
}';
        $charSpace = " ";
        $newline = "\r\n";
        $indentSize = 3;
        $arguments = array('charSpace' => $charSpace,
                           'newline' => $newline,
                           'indentSize' => $indentSize,
                           'indentStyle' => 'SL5net_small');
        $actual = reformat_AutoHotKey($source1, $arguments);
        if(trim($expected) != trim($actual)) {
            $a = 1;
        }
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(trim($expected), trim($actual));
        }
    }

    function test_prettify_indentStyle_SL5net() {
        include_once('../../examples/AutoHotKey/Reformatting_Autohotkey_Source.php');
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":" .
          '
if(1) {
1a
if(2)
{
2a
if(3)
{
3a
}
else{
Send,3b{Right 3}
Send,3c{Right 4}
}
}
return 5
}
';
        $expected = $LINE__ . ":" .
          '
if(1) {
   1a
   if(2)
   {
      2a
      if(3)
      {
         3a
      }else  {
         Send,3b{Right 3}
         Send,3c{Right 4}
      }}
   return 5
}';
        $charSpace = " ";
        $newline = "\r\n";
        $indentSize = 3;
        $arguments = array('charSpace' => $charSpace,
                           'newline' => $newline,
                           'indentSize' => $indentSize,
                           'indentStyle' => 'SL5net_small');
        $actual = reformat_AutoHotKey($source1, $arguments);
        if(trim($expected) != trim($actual)) {
            $a = 1;
        }
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(trim($expected), trim($actual));
        }
    }

    function test_prettify_autohotkey_Label() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":\n" . '; this is label indent test

MyLabel1:
Send,{AltUp}
return
MyLabel2:
Send,{AltUp}
Send,:(
return
Label_3:
Send,{AltUp}
Send,:)
return

MyLabel5:
Send,{Space}
Suspend,off
return

^::
Send,{Space}
Suspend,off
return


';
        $expected = $LINE__ . ":\n"
          . ';.this.is.label.indent.test

MyLabel1:
...Send,{AltUp}
return
MyLabel2:
...Send,{AltUp}
...Send,:(
return
Label_3:
...Send,{AltUp}
...Send,:)
return

MyLabel5:
...Send,{Space}
...Suspend,off
return

^::
...Send,{Space}
...Suspend,off
return';
        include_once('../../examples/AutoHotKey/Reformatting_Autohotkey_Source.php');
        $actual = reformat_AutoHotKey($source1, $arguments = '');
        # equalize newline style
        $expected = preg_replace('/\r/', "", $expected);
        $expected = str_replace(' ', '.', $expected);
        $actual = str_replace(' ', '.', $actual);
        $actual = preg_replace('/\r/', "", $actual);
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
//        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals(trim($expected), trim($actual));
    }

    function test_prettify_autohotkey_Tab_indent() {
        include_once('../../examples/AutoHotKey/Reformatting_Autohotkey_Source.php');
        $LINE__ = __LINE__;
        $newline = "\r\n";
        $source1 = $LINE__ . ":" . $newline . 'MyLabel1:
Send,{AltUp}
return
';
        $charSpace = "\t"; # \t its a tab
        $newline = "\n"; // if u use "\r\n" test fails :(
        /* if u use "\r\n" test fails :( result is greater then $source1
        first you need to fix source!
        */
//            $source1= preg_replace("/\n/","\r\n",$source1);
        $indentSize = 1;
        $expected = $LINE__ . ":\r\n" . 'MyLabel1:' . $newline . $charSpace . 'Send,{AltUp}' . $newline . 'return';
        if($newline == "\r\n") {
            $source1 = str_replace(array("\r\n", "/\n/"), "\r\n", $source1);
            $expected = str_replace(array("\r\n", "/\n/"), "\r\n", $expected);
        }

        $arguments = array('charSpace' => $charSpace, 'newline' => $newline, 'indentSize' => $indentSize);
        $actual = reformat_AutoHotKey($source1, $arguments);
        # equalize newline
        /*
         *  @return int > 0 if <i>str1</i> is less than
 * <i>str2</i>; <; 0 if <i>str1</i>
 * is greater than <i>str2</i>, and 0 if they are
 * equal.

         */
        $strcmp = strcmp($expected, $actual);
        if($strcmp) {
            if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
        }
//        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals(trim($expected), trim($actual));
    }
    function test_prettify_autohotkey5() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":\n" . 'this is ugly example source

isFileOpendInSciteUnsaved(filename){
    SetTitleMatchMode,2
doSaveFirst := false ; initialisation
   IfWinNotExist,%filename% - SciTE4AutoHotkey{
doSaveFirst := true
IfWinNotExist,%filename% * SciTE4AutoHotkey
MsgBox,oops   NotExist %filename% * SciTE4AutoHotkey
 if(false){
      Too(Last_A_This)
      if ( next )
         Check = 1
      else if (nils == "tsup")
      Check = 42
      else
      Check = 3

   s := Com("{D7-2B-4E-B8-B54}")

   ; comment :) { { hi } {
   }
}
return doSaveFirst
}
        ';
        $expected = $LINE__ . ":\n"
          . 'this.is.ugly.example.source

isFileOpendInSciteUnsaved(filename){
...SetTitleMatchMode,2
...doSaveFirst.:=.false.;.initialisation
...IfWinNotExist,%filename%.-.SciTE4AutoHotkey{
......doSaveFirst.:=.true
......IfWinNotExist,%filename%.*.SciTE4AutoHotkey
.........MsgBox,oops...NotExist.%filename%.*.SciTE4AutoHotkey
......if(false){
.........Too(Last_A_This)
.........if.(.next.)
............Check.=.1
.........else.if.(nils.==."tsup")
............Check.=.42
.........else
............Check.=.3
.........
.........s.:=.Com("{D7-2B-4E-B8-B54}")
.........
.........;.comment.:).{.{.hi.}.{
......}
...}
...return.doSaveFirst
}';
        include_once('../../examples/AutoHotKey/Reformatting_Autohotkey_Source.php');
        $actual = reformat_AutoHotKey($source1, $arguments = '');
        # equalize newline style
        $expected = preg_replace('/\r/', "", $expected);
        $expected = str_replace(' ', '.', $expected);
        $actual = str_replace(' ', '.', $actual);
        $actual = preg_replace('/\r/', "", $actual);
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
//        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals(trim($expected), trim($actual));
    }
    function test_wrongSource_No_endQuote_expected() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{^_^}{\_/}{No_';
        $expected = $LINE__ . ':[^_^][\_/][No_Oops';
        $old = ['{', '}'];
        $newQuotes = ['[', ']'];

        $charSpace = "";
        $newline = "";
        $indentSize = 2;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;

//              $end = substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin']);
              if(!$posList0['end_begin']) $newQuotes[1] = 'Oops';


              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }
    function test_wrongSource_NoX_callback() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{NoX';
        $expected = $LINE__ . ':[NoX]';
        $old = ['{', '}'];
        $newQuotes = ['[', ']'];

        $charSpace = "";
        $newline = "";
        $indentSize = 2;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

    function test_wrongSource_NIXNIX_callback() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{NIX{}';
        $expected = $LINE__ . ':[NIX{]';
        $old = ['{', '}'];
        $newQuotes = ['[', ']'];

        $charSpace = "";
        $newline = "";
        $indentSize = 2;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);


              if(!isset($posList0['begin_end'])) $posList0['begin_end'] = strlen($source1);
              $start = '' . substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']) . '';
              $end = '' . ltrim(substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin'])) . '';

              $cut['middle'] .= (is_null($posList0['end_begin']))
                ? $indentStr . $cut['behind']
                : $indentStr . $newQuotes[1] . $cut['behind'];


              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_wrongSource_NIX_empty_quote() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{NIX{}';
        $expected = $LINE__ . ':NIX{';
        $old = ['{', '}'];
        $newQuotes = ['', ''];

        $charSpace = "";
        $newline = "";
        $indentSize = 2;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);


              if(!isset($posList0['begin_end'])) $posList0['begin_end'] = strlen($source1);
              $start = '' . substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']) . '';
              $end = '' . ltrim(substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin'])) . '';

              $cut['middle'] .= (is_null($posList0['end_begin']))
                ? $indentStr . $cut['behind']
                : $indentStr . $newQuotes[1] . $cut['behind'];


              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }

    function test_only_text() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':NoX';
        $expected = $LINE__ . ':NoX';
        $old = ['{', '}'];
        $newQuotes = ['{', '}'];

        $charSpace = "";
        $newline = "";
        $indentSize = 2;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

//     $collectString = '';


    function test_wrongSource_NIXopen_plus_callback() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':NIX{a';
        $expected = $LINE__ . ':NIX{a';
        $old = ['{', '}'];
        $charSpace = "";
        $newline = "";
        $indentSize = 2;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };
        $newQuotes = ['{', '}'];
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount, $callsCount, $posList0, $source1) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);


              if(!isset($posList0['begin_end'])) $posList0['begin_end'] = strlen($source1);
              $start = '' . substr($source1, $posList0['begin_begin'], $posList0['begin_end'] - $posList0['begin_begin']) . '';
              $end = '' . ltrim(substr($source1, $posList0['end_begin'], $posList0['end_end'] - $posList0['end_begin'])) . '';


              $cut['middle'] .= (is_null($posList0['end_begin'])) ? $indentStr . $cut['behind'] : $indentStr . $newQuotes[1] . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }

    function test_a_b_B_callback() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':a{b{B}}';
        $expected = $LINE__ . ':a<b<B>>';
        $old = ['{', '}'];

        $newQuotes = ['<', '>'];
        $charSpace = "";
        $newline = "\r\n";
        $newline = "";
        $indentSize = 2;
        $source1 = $source1;
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//              $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];
              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//              $n .= $deepCount.':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//              $n .= $deepCount.';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_recursive_02() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $source1 = $LINE__ . ':a{b{c{o}c}b}a';

        $expected = $LINE__ . ':a[_�b[_��c[_���o_��`]_��c_�`]_�b_`]_a';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = ['[', ']'];
        $charSpace = ".";
        $newline = "_";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
              $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $newQuotes[0];
//              return $cut  ;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
              $charSpace = '�';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) {
//                  return $cut['behind'];
//                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
              $charSpace = '`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

//              $cut['behind'] .= $indentStr . $newQuotes[1] . $n;
              $cut['middle'] .= $indentStr . $newQuotes[1] . $n . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_simple3() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':
a{b{B}}';
        $old_open = '{';
        $old_close = '}';
        $newQuotes = ['[', ']'];
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;
        $source1 = $source1;
        $expected = $LINE__ . ':
a
1|[
1:..b
2|..[
2:....B
2:..]
1:]';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
              $n .= $deepCount . '|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);
              $cut['before'] .= $n . $indentStr . $newQuotes[0];

              // return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
              $n .= $deepCount . ':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
              $n .= $deepCount . ';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $cut['behind'];

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }

    function test_simple() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':
a{A}b{B}';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = ['[', ']'];
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;

        $source1 = $source1;

//        $expected = $LINE__.':1[_.2[_..3[_...o_...]_3_..]_2_.]_1';
        $expected = $LINE__ . ':
a
1|[
1:..A
1:]
1;b
1|[
1:..B
1:]
1;';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
              $n .= $deepCount . '|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $n . $indentStr . $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
              $n .= $deepCount . ':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
              $n .= $deepCount . ';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

//              $cut['behind'] .= $indentStr . $newQuotes[1] . $n;
//              $cut['behind'] = $indentStr . $newQuotes[1] . $n . $cut['behind'];
              $cut['middle'] .= $indentStr . $newQuotes[1] . $n . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_15_() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':
if(a1){$A1;}if(a2){$A2;}';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = ['[', ']'];
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;

        $source1 = $source1;

//        $expected = $LINE__.':1[_.2[_..3[_...o_...]_3_..]_2_.]_1';
        $expected = $LINE__ . ':
if(a1)
1|[
1:..$A1;
1:]
1;if(a2)
1|[
1:..$A2;
1:]
1;';

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        //        $openFunc = null,
//      $contentFunc = null,
//      $closeFunc = null

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
              $n .= $deepCount . '|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $n . $indentStr . $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
              $n .= $deepCount . ':';
//              $charSpace ='`';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
              $n .= $deepCount . ';';
//              $charSpace ='�';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

//              $cut['behind'] .= $indentStr . $newQuotes[1] . $n;
              $cut['middle'] .= $indentStr . $newQuotes[1] . $n . $cut['behind'];

              // return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}
//        $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_shortest_new_close_recursive() {
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $source1 = $LINE__ . ':{}';
        $expected = $LINE__ . ':{#';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = [$old_open, '#'];
//        $newQuotes[1] = $old_close; // this line is reason for endless loop
        $charSpace = "";
        $newline = "";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='�';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_reformat_compressed_AutoHotKey() {
//        return true;
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;

        $source1 = $LINE__ . ':{{o}}';
        $expected = $LINE__ . ':{n {n on >n >';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = [$old_open, '>'];
//        $newQuotes[1] = $old_close; // this line is reason for endless loop
        $charSpace = " ";
        $newline = "n";
//        $newline = "a�lsdkfj�saldkjfs�alfdkj"; // see closure functions
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='�';
              $indentStr = $getIndentStr($deepCount, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $cut['behind'];

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
//      {{o}}
//    $actual = $cBefore . $content . $cBehind;

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_shortest_lette_in_middle_recursive() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{l}';
        $expected = $LINE__ . ':{l}';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = ['{', '}'];
//        $newQuotes[1] = $old_close; // this line is reason for endless loop
        $charSpace = "";
        $newline = "";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='�';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // , function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


    function test_recursive_01() {
//    include_once 'input_compressed.ahk'
//    $file_content_original = file_get_contents('SciTEUpdate.ahk');
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{k}';
        $expected = $LINE__ . ':[k]';
        $old_open = '{';
        $old_close = '}';
        $charSpace = ' ';
        $charSpace = '';
        $newline = "\n";
        $newline = "";
        $indentSize = 1;
        $newQuotes = ['[', ']'];

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='�';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

    function test_shortest_new_open_recursive() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{}';
        $expected = $LINE__ . ':#}';
        $old_open = '{';
        $old_close = '}';

        $newQuotes = ['#', '}'];
//        $newQuotes[1] = $old_close; // this line is reason for endless loop
        $charSpace = "";
        $newline = "";
        $indentSize = 1;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='�';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n])/', $n . $indentStr . "$1", $cut['middle']);
              $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='`';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);

              $cut['middle'] .= $indentStr . $newQuotes[1] . $n;

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

//      {{o}}

        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);

    }


//    function test_recursion_simplyReproduction() {
//        # this recursion is deprecated and not implemented into the core class. so dont waste time ;)
//        return false;
//        $expected = 'A {11{22{3}{2}22}11}{1} B';
//        $cf = new SL5_preg_contentFinder($expected);
//        list($c, $bf, $bh) = recursion_simplyReproduction($expected);
//        $actual = $bf . $c . $bh;
//        $cf->setBeginEnd_RegEx('{', '}');
//        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
//    }


    /**
     * using class SL5_preg_contentFinder
     * and ->getContent_user_func_recursive.
     * in a case i don't like this style using closures to much. so you only need one function (advantage) from the outside. but looks more ugly from the inside. not best way for debugging later (inside). you need to compare, decide for your business.
     */

    function test_callback_with_closures() {
        $source1 = '_if(X1){$X1;if(X2){$X2;}}';
        $expected = '_if(X1)[
..$X1;if(X2)[
....$X2;
..]
]';
        $old_open = '{';
        $old_close = '}';
        $newQuotes = ['[', ']'];
        $charSpace = ".";
        $newline = "\r\n";
        $indentSize = 2;

        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($old_open, $old_close);

        $getIndentStr = function ($indent, $char, $indentSize) {
            $multiplier = $indentSize * $indent;
            $indentStr = str_repeat($char, (($multiplier < 0) ? 0 : $multiplier));

            return $indentStr;
        };

        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes, $charSpace, $newline, $indentSize, $getIndentStr) {
              $n = $newline;
//          $n .= $deepCount.'|';
//          $charSpace = "'";
              $indentStr = $getIndentStr($deepCount - 1, $charSpace, $indentSize);

              $cut['before'] .= $newQuotes[0];
// return $cut;
              // }, function ($cut, $deepCount) use ($charSpace, $newline, $indentSize, $getIndentStr) {
              if($cut['middle'] === false) return $cut;
              $n = $newline;
//          $n .= $deepCount.':';
//          $charSpace ='�';
              $indentStr = $getIndentStr(1, $charSpace, $indentSize);
              $cut['middle'] = $n . $indentStr . preg_replace('/' . preg_quote($n) . '[ ]*([^\s\n]+)/', $n . $indentStr . "$1", $cut['middle']);

//          $cut['middle'] .= $n;

              // return $cut;
              // }, function ($cut, $deepCount) use ($newQuotes[1], $newline, $charSpace, $indentSize, $getIndentStr) {
              if($cut['middle'] === false || $cut['behind'] === false) {
//                  return $cut['behind'];
                  return false;
              }
              $n = $newline;
//          $n .= $deepCount.';';
//          $charSpace ='-';
//          $indentStr = $getIndentStr(0, $charSpace, $indentSize);

              $cut['middle'] .= $n . $newQuotes[1];

// return $cut;
              return $cut; # todo: $cut['behind'] dont need newline at the beginning
          });

        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals($expected,
              $actual);
        }
    }


    function test_reformatCode_recursion_add() {
        $source1 = "if(InStr(tc,needle)){win:=needle}else{win:=needle2}";
        $expected =
          "if(InStr(tc,needle)){
   win:=needle;
}else{
   win:=needle2;
}";
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        list($c, $bf, $bh) = self::recursion_add($source1, "{\r\n   ", ";\r\n}");
        $actual = $bf . $c . $bh;
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals(strlen($expected), strlen($actual));
    }


    static function recursion_add(
      $content,
      $addBefore = null,
      $addBehind = null,
      $before = null,
      $behind = null
    ) {
        $isFirstRecursion = is_null($before); # null is used as trigger for first round.
        $cf = new SL5_preg_contentFinder($content);
        if($cut['middle'] = @$cf->getContent($b = '{', $e = '}')) {
            $before .= $cf->getContent_Before() . $addBefore;
            $behindTemp = $cf->getContent_Behind() . $behind;

            if($isFirstRecursion) {
                list($c, $bf, $bh) =
                  self::recursion_add($behindTemp,
                    $addBefore,
                    $addBehind); // this version of recursion also includes the rest of contentDemo.
                $behind = (is_null($c)) ? $addBehind . $behindTemp : $addBehind . $bf . $c . $bh;
            }
            else {
                $behind = $addBehind . $behindTemp;
            }

            $return = self::recursion_add(
              $cut['middle'],
              $addBefore,
              $addBehind,
              $before,
              $behind
            );

            return $return;
        }
        $return = array($content, $before, $behind); // core element.
        return $return;
    }
}

function emptyLenNot0($input) {
    # empty Gibt FALSE zurück, wenn var existiert und einen nicht-leeren, von 0 verschiedenen Wert hat. 
    $strTemp = $input;
    if(isset($strTemp) && $strTemp !== '') //Also tried this "if(strlen($strTemp) > 0)"
    {
        return true;
    }

    return false;
}

