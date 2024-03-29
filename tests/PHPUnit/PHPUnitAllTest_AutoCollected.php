 <h1>Dont edit this file. its overwritten next !</h1> \n  <?php
 $f = 'SL5_preg_contentFinder.php';
 while(!file_exists($f)) {
    $f = '../' . $f;
    echo "$f exist.";
}
include_once $f;
include_once '_callbackShortExample.php';
   class TestAll extends PHPUnit_Framework_TestCase {
    function test_99_simple() {
        /*
         * Example from:
         * http://dosqlweb.de/dope.php?f=/dope/online-manual2/MindTree/frameset.htm?url=99+a+8+999+default-tree-titles-viewonly.tmpl+2+dope_mindtree_dope_stoffsammlung_light+0
         * http://sourceforge.net/projects/dosqlweb/files/dosqlweb/1.0/DOPE-PHP_Version_070415.zip/download
         * $html = preg_replace("/\[".$selection_alias."\s+(\d+)\]/sx" , "[$selection_alias \\1]" , $html);
 create-cache-file.inc.php  -  200.747 Bytes  -  Fr, 13.04.07 um 22:23  -           */
        # todo doSqlWeb test not complete written now.
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ":" .
          '[SELECT 5+5 as calculation#alias]mitte[alias calculation]';
        $expected = $LINE__ . ":"
          . '';
        $old = ['[', ']'];
        $newQuotes = ['[', ']'];
        $html = $source1;
        $preg_kapsel = "/^(\[[^<#][^#]+?#[^\]]+?\])$/s";
        $preg_kapsel = "/^(\[[^<#][^#]+?#[^\]]+?\])$/s";
//        $old = "/^(\[[^<#][^#]+?#[^\]]+?\])$/s";

//        $html = preg_replace("/\[".$selection_alias."\s+(\d+)\]/sx" , "[$selection_alias \\1]" , $html);
        $reg_ausdruck = "/\[(\w+)(\s+[^\#]+)#\s*([^\/\]\#]+?)\s*(#\d+)?(#[^#\]]+)?\]/"; // see function function interpret_one_sql_kapsel(
        $old[0]= $reg_ausdruck;
        $old[1] = "/\[\w+[^]]*\]/";
        $cf = new SL5_preg_contentFinder($source1, $old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes) {
              $cut['before'] .= $newQuotes[0];
              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

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


    /**
     * empty means it found an empty.
     * false means nothing was found.
     */
    function test_false_versus_empty() {

        $cfEmpty_IfEmptyResult = new SL5_preg_contentFinder("{}");
        $cfEmpty_IfEmptyResult->setBeginEnd_RegEx('{', '}');
        $contentEmpty = $cfEmpty_IfEmptyResult->getContent();
        $this->assertTrue($contentEmpty === "");
        $this->assertTrue($contentEmpty !== false);
        $this->assertTrue($contentEmpty !== null);

        $cf_False_IfNoResult = new SL5_preg_contentFinder("mi{SOME}mo");
        $cf_False_IfNoResult->setBeginEnd_RegEx('[', ']');
        $contentFalse = $cf_False_IfNoResult->getContent();
        $this->assertTrue($contentFalse !== "");
        $this->assertTrue($contentFalse === false);
        $this->assertTrue($contentFalse !== null);
    }


    /**
     * echo and return from a big string a bit of the start and a bit from the end.
     */
    function test_echo_content_little_excerpt() {
        $cf = new SL5_preg_contentFinder("dummy");
        $this->assertEquals("12...45", $cf->echo_content_little_excerpt("12345", 2, 2));
    }

    /**
     * nl2br_Echo returns nothong, returns null. it simly echo
     */
    function test_nl2br_Echo() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals($cf->nl2br_Echo(__LINE__, "filename", "<br>"), null);
    }
    /**
     * getContent_Next returns false if there is not a next contentDemo
     */
    function test_getContentNext() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals(false, $cf->getContent_Next());
    }
    /**
     * false if parameter is not  'pos_of_next_search' or 'begin' or 'end'
     */
    function test_CACHE_current() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals(false, $cf->CACHE_current());
    }
    /**
     * CACHE_current: false if there is no matching cache. no found contentDemo.
     */
    function test_CACHE_current_begin_end_false() {
        $cf = new SL5_preg_contentFinder(123456);
        $this->assertEquals(false, $cf->CACHE_current("begin"));
        $this->assertEquals(false, $cf->CACHE_current("end"));
    }
    /**
     * CACHE_current: simply the string of the current begin / end quote
     */
    function test_CACHE_current_begin_end() {
        $cf = new SL5_preg_contentFinder(00123456);
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(2, $cf->CACHE_current("begin"));
        $this->assertEquals(4, $cf->CACHE_current("end"));
    }


    /**
     * getContent ... gives false if there isn't a contentDemo. if it found a contentDemo it gives true
     */
    function test_getContent() {
        $cf = new SL5_preg_contentFinder("00123456");
        $cf->setBeginEnd_RegEx('2', '4');
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals(false, $cf->getContent_Next());
        $this->assertEquals(3, $cf->getContent());
    }
    function test_wrongSource_NIX_getContent() {
        $source1 = '{NIX';
        $expected = 'NIX';
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($expected, $cf->getContent());
    }

    function test_wrongSource_NIXNIX_getContent() {
        $source1 = "{NIX{}";
        $expected = 'NIX{';
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($expected, $cf->getContent());
    }

    function test_getUniqueSignExtreme() {
        $cf = new SL5_preg_contentFinder(123456);
        $cf->isUniqueSignUsed = true; # needs to switched on first !! performance reasons
        $cf->setBeginEnd_RegEx('2', '4');
        $cf->getContent(); # needs to be searched first !! performance reasons
        $probablyUsedUnique = chr(007);
        $this->assertEquals($probablyUsedUnique, $cf->getUniqueSignExtreme());
    }

    function test_protect_a_string() {
        $cf = new SL5_preg_contentFinder('"{{mo}}"');
        $cf->isUniqueSignUsed = true; # needs to switched on first !! performance reasons
        $cf->setBeginEnd_RegEx('{', '}');
        $content = $cf->getContent(); # needs to be searched first !! performance reasons
        $mo = '{mo}';
        $this->assertEquals('_' . $mo, '_' . $content);
        $uniqueSignExtreme = $cf->getUniqueSignExtreme();

        $o = $uniqueSignExtreme . 'o';
        $c = $uniqueSignExtreme . 'c';
        $content1 = str_replace(['{', '}'], [$o, $c], $content);
        $contentRedo = str_replace([$o, $c], ['{', '}'], $content1);
        $this->assertEquals('-' . $contentRedo, '-' . $content);

        $cf2 = new SL5_preg_contentFinder($content1);
        $cf2->setBeginEnd_RegEx('{', '}');
        $content2 = $cf2->getContent();
        $content_Before = $cf2->getContent_Before();
        $content_Behind = $cf2->getContent_Behind();
        $content3 = str_replace([$o, $c], ['{', '}'], $content2);
        $this->assertEquals('', $content_Before . $content3 . $content_Behind); # means cut is not  found / created.
    }

    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching contentDemo)
     */
    function test_content_getBorders_before() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new SL5_preg_contentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", substr($content, 0, $cf->getBorders()['begin_begin']));
    }

    /**
     * get_borders ... you could get contents by using substr.
     * its different to getContent_Prev (matching contentDemo)
     *
     * todo: discuss getContent_Next ?? discuss getContent_Behind ?? (15-06-16_10-28)
     */
    function test_content_getBorders_behind() {
        $content = "before0[in0]behind0,before1[in1]behind1";
        $cf = new SL5_preg_contentFinder($content);
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("behind0,before1[in1]behind1", substr($content, $cf->getBorders()['end_end']));
//        $this->assertEquals(false, $cf->getContent_Next());
    }
    /**
     * gets contentDemo using borders with substring
     */
    function test_getContentBefore_delimiterWords() {
        $cf = new SL5_preg_contentFinder("1_before0_behind0_2");
        $cf->setBeginEnd_RegEx('before0', 'behind0');
        $this->assertEquals("1_", $cf->getContent_Before());
        $this->assertEquals("_2", $cf->getContent_Behind());
    }
    /**
     * gets contentDemo using borders with substring
     */
    function test_getContentBefore() {
        $cf = new SL5_preg_contentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("before0", $cf->getContent_Before());
    }
    /**
     *  gets contentDemo using borders with substring
     */
    function test_getContentBehind() {
        $cf = new SL5_preg_contentFinder("before0[in0]behind0,before1[in1]behind1");
        $cf->setBeginEnd_RegEx('[', ']');
        $this->assertEquals("behind0,before1[in1]behind1", $cf->getContent_Behind());

    }


    /**
     * todo: needs discussed
     */
    function test_getContent_ByID_1() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals(null, $cf->getID());
        $this->assertNotEquals('-' . 0 . '-', '-' . $cf->getID() . '-');
    }


    /**
     * setID please use integer not text. why?
     * todo: needs discussed
     */
    function test_getContent_setID() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
        $cf->setID(1);
        $content1 = $cf->getContent();
        $this->assertEquals('1: ' . "2_{1_2", '1: ' . $content1);;
//        $this->assertEquals($content1, $cf->getContent_ByID(1));# todo: dont work like expected
        $cf->setBeginEnd_RegEx('1', '2');
        $cf->setID(2);
//        $this->assertEquals($content1, $cf->getContent_ByID(1));;
//        $this->assertEquals("1_2", $cf->getContent_ByID(2));;
//        $this->setExpectedException('InvalidArgumentException');
    }
    /**
     * todo: needs discussed
     */
    function test_getContent_ByID_3() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_");
        $cf->setBeginEnd_RegEx('{', '}');
//        $this->assertEquals("2_{1_2", $cf->getContent_ByID(0)); # dont work like expected
    }
    /**
     * getContent takes the first. from left to right
     */
    function test_getContent_2() {
        $cf = new SL5_preg_contentFinder("{2_{1_2}_2}_3}{_4}");
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals("2_{1_2}_2", $cf->getContent());
    }
    /**
     * Prev and Next using getContent_ByID
     * todo: discuss
     */
    function test_getContent_Prev_Next() {
        $cf = new SL5_preg_contentFinder("(1_3)_2_3_(_a)o");
        $cf->setBeginEnd_RegEx('(', ')');
        $this->assertEquals("1_3", $cf->getContent());
//        $this->assertEquals(false, $cf->getContent_Prev()); # todo: dont work like expected
//        $this->assertEquals("_a", $cf->getContent_Next()); # todo: dont work like expected
    }
    /**
     * Prev and Next using getContent_ByID
     * todo: discuss
     */
    function test_getContent_Prev_Next_3() {
        $source1 = "{1_4}_2_3_{_b}o";
        $expected = "1_4";
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx('{', '}');
        $this->assertEquals($expected, $cf->getContent());
        $this->assertEquals(false, $cf->getContent_Prev());
        $this->assertEquals(false, $cf->getContent_Next());
    }

    function test_128() {
        $source1 = "(1((2)1)8)";
        $expected = "(1((2)1)8)";
        $cf = new SL5_preg_contentFinder($source1);
        $actual = '(' . $cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($expected, $actual);
    }

    function test_123_abc() {
        # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
        # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
        $content1 = '123#abc';
        $cf = new SL5_preg_contentFinder($content1);
        $expected = @$cf->getContent(
          $begin = '\d+',
          $end = '\w+',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $expectedContent = '#';
        $this->assertEquals($expected, $expectedContent);
    }

    function test_2_1() {
        $expected = "((2)1)";
        $cf = new SL5_preg_contentFinder($expected);
        $actual = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        $this->assertEquals($expected, $actual);
    }



    function test_Grabbing_HTML_Tag() {
        return false;
//        $source1 = file_get_contents(__FILE__);
        $expected = 'hiHo';
        $source1 = '<P>hiHo</P>';
        $cf = new SL5_preg_contentFinder($source1);
        $rB = '<([A-Z][A-Z0-9]*)\b[^>]*>';
        $rE = '<\/{1}>';
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $cf->setBeginEnd_RegEx($rB, $rE);
//          '\s*\}\s*function\s+\s+function');
        $actual = $cf->getContent();
        $this->assertEquals($expected, $actual);
        $break = 'b';
    }

    function test_123_g() {
        $source1 = '123#g';
        $cf = new SL5_preg_contentFinder($source1);
        $actual_getContent = @$cf->getContent(
          $begin = '\d+',
          $end = '\w+',
          $p = null,
          $t = null,
          $searchMode = 'dontTouchThis'
        );
        $cf2 = new SL5_preg_contentFinder($source1);
        $cf2->setSearchMode('dontTouchThis');
        $cf2->setBeginEnd_RegEx('\d+', '\w+');
        $actual_getContent_user_func_recursive = $cf2->getContent_user_func_recursive(
          function ($cut) {
              $cut['middle'] = '2.' . $cut['middle'];
              return $cut;
          });

        $expected = '#';
        $this->assertEquals($actual_getContent, $expected);
        $this->assertEquals($actual_getContent_user_func_recursive, '2.' . $expected);
    }
    function test_123_z() {
        $source1 = '123#z';
        $expected = '#';
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\d+', '\w+');
        $sourceCF = $cf->getContent();
        $this->assertEquals($sourceCF, $expected);
    }
    function test_123_abc_v3() {
        $source1 = '{
        hiHo
        }';
        $expected = 'hiHo';
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('^\s*{\s*$\s*', '\s*^\s*}\s*$');
        $sourceCF = $cf->getContent();
        $this->assertEquals($sourceCF, $expected);
    }
    function test_123_abc_v4() {
        $source1 = '
class DontTouchThis_searchMode_Test extends PHPUnit_Framework_TestCase {
15-06-19_15-32';
        $expected = '15-06-19_15-32';
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*', '^\s*\}\s*');
        $sourceCF = $cf->getContent();
        $levenshtein = levenshtein($expected, $sourceCF);
//        $this->assertEquals(0,$levenshtein);
        $this->assertEquals($expected . ' $levenshtein=' . $levenshtein, $sourceCF . ' $levenshtein=' . $levenshtein);
    }
    function test_123_abc_v5() {
        $source1 = '
class DontTouchThis_searchMode_Test extends PHPUnit_Framework_TestCase {
15-06-19_15-32';
        $expected = '15-06-19_15-32';
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx('\w\s*\{\s*', '^\s*\}\s*$');
        $sourceCF = $cf->getContent();
        $levenshtein = levenshtein($expected, $sourceCF);
//        $this->assertEquals(0,$levenshtein);
        $this->assertEquals($expected . ' $levenshtein=' . $levenshtein, $sourceCF . ' $levenshtein=' . $levenshtein);
    }

    function test_empty_0_DANGER() {
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(true, emptyLenNot0('0'));
            $this->assertEquals(true, empty('0'));
            # Gibt FALSE zurück, wenn var existiert und einen nicht-leeren, von 0 verschiedenen Wert hat. !!!!!
        }
    }
    function test_emptyREAL_0_DANGER() {
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(true, empty('0'));
            # Gibt FALSE zurück, wenn var existiert und einen nicht-leeren, von 0 verschiedenen Wert hat. !!!!!
        }
    }
    function test_empty_1() {
        if(class_exists('PHPUnit_Framework_TestCase')) {
            $this->assertEquals(false, empty('1'));
        }
    }
    function test_behind_with_a_0() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{9}0';
        $expected = $LINE__ . ':90';
        $old = ['{', '}'];
        $newQuotes = ['', ''];
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes) {
              $cut['before'] .= $newQuotes[0];
              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
    }

    function test_behind_BBB() {
        $LINE__ = __LINE__;
        $source1 = $LINE__ . ':{1}BBB';
        $expected = $LINE__ . ':1BBB';
        $old = ['{', '}'];
        $newQuotes = ['', ''];
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setSearchMode('dontTouchThis');
        $cf->setBeginEnd_RegEx($old);
        $actual = $cf->getContent_user_func_recursive(
          function ($cut, $deepCount) use ($newQuotes) {
              $cut['before'] .= $newQuotes[0];
              if($cut['middle'] === false) return $cut;
              if($cut['middle'] === false || $cut['behind'] === false) {
                  return false;
              }
              $cut['middle'] .= $newQuotes[1] . $cut['behind'];

              return $cut;
          });
        if(class_exists('PHPUnit_Framework_TestCase')) $this->assertEquals($expected, $actual);
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

    /*
     * suggestion: look inside https://github.com/sl5net/SL5_preg_contentFinder/blob/master/tests/PHPUnit/Callback_Test.php before using this technicals.
     */
    function test_AABBCC() {
        $source1 = '<A>.</A><B>..</B><C>...</C>';
        $expected = 'Aa: . Bb: .. Cc: ... ';
        $beginEnd = ['(<)([^>]*)(>)?', '<\/($2)>'];
        $maxLoopCount = $pos_of_next_search = 0;
        $cf = new SL5_preg_contentFinder($source1, $beginEnd);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $actual = '';
        while($maxLoopCount++ < 30) {
            $cf->setPosOfNextSearch($pos_of_next_search);
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) {
                break;
            }
            $tagName = $borders['matches']['begin_begin'][1][0];
            $actual .= $tagName . strtolower($tagName);
            $actual .= ': ' . $cf->getContent() . ' ';
            $pos_of_next_search = $borders['end_end'];
        }
        $this->assertEquals($expected, $actual);
    }


    function test_AA_xo_A() {
        /*
         * todo this test works not as expected. the misspelled source is not usable enough.
         */
        return false;
        $source1 = ' some </A><A>xo1</A><A>xo2</A> thing ';
        $expected = 'A: xo1 A: xo2 ';
        $beginEndRegEx = ['(<)(A)(>)?', '<\/($2)>'];
        $cf = new SL5_preg_contentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $maxLoopCount = 1000;
        $actual = '';
        while($maxLoopCount-- > 0) {
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) break;
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= ': ' . $cf->getContent() . ' ';
            $pos_of_next_search = $borders['end_end'];
            $cf->setPosOfNextSearch($pos_of_next_search);
        }
        $this->assertEquals($expected, $actual);
    }

    function test_A_A2_A_A2() {
        /*
         * in this example you really need for correct termination additionally:
         * is_null($borders['end_begin'])
         */
        $source1 = ' some <A>XO</A></A> thing ';
        $expected = 'A: XO ';
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\/($2)>'];
        $cf = new SL5_preg_contentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $maxLoopCount = 1000;
        $actual = '';
        while($maxLoopCount-- > 0) {
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])
              || is_null($borders['end_begin'])
            ) {
                break;
            }
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= ': ' . $cf->getContent() . ' ';
            $pos_of_next_search = $borders['end_end'];
            $cf->setPosOfNextSearch($pos_of_next_search);
        }
        $this->assertEquals($expected, $actual);
    }

    function test_A1_B2B_A() {
        $source1 = ' some <A>1<B>2</B></A> thing ';
        $expected = 'A: 1<B>2</B> ';
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\/($2)>'];
        $cf = new SL5_preg_contentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $maxLoopCount = 1000;
        $actual = '';
        while($maxLoopCount-- > 0) {
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) break;
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= ': ' . $cf->getContent() . ' ';
            $pos_of_next_search = $borders['end_end'];
            $cf->setPosOfNextSearch($pos_of_next_search);
        }
        $this->assertEquals($expected, $actual);
    }

    function test_ABBA() {
        $source1 = '<A>a<B>b</B></A>';
        $expected = 'Aa<B>b</B>';
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\/($2)>'];
        $cf = new SL5_preg_contentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $actual = '';
        $maxLoopCount = 1000;
        while($maxLoopCount-- > 0) {
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) break;
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= $cf->getContent();
            $pos_of_next_search = $borders['end_end'];
            $cf->setPosOfNextSearch($pos_of_next_search);
        }
        $this->assertEquals($expected, $actual);
    }


    function test_AA_BB() {
        $source1 = '<A>a</A><B>b</B>';
        $expected = 'AaBb';
        $actual = '';
        $beginEndRegEx = ['(<)([^>]*)(>)', '<\/($2)>'];
        $cf = new SL5_preg_contentFinder($source1, $beginEndRegEx);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $maxLoopCount = 1000;
        while($maxLoopCount-- > 0) {
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) break;
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= $cf->getContent();
            $pos_of_next_search = $borders['end_end'];
            $cf->setPosOfNextSearch($pos_of_next_search);
        }
        $this->assertEquals($expected, $actual);
    }


    function test_AaA_BbB() {
        $source1 = '<!--[A]-->a<!--[/A]--><!--[B]-->b<!--[/B]-->';
        $expected = 'AaBb';
        $actual = '';
        $maxLoopCount = $pos_of_next_search = 0;
        $beginEnd = ['(<!--)?\[([^>]*)\](-->)?', '<!--\[\/($2)\]-->'];
        $cf = new SL5_preg_contentFinder($source1);
        $cf->setBeginEnd_RegEx($beginEnd);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        while($maxLoopCount++ < 30) {
            $cf->setPosOfNextSearch($pos_of_next_search);
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) {
                break;
            }
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= $cf->getContent();
            $pos_of_next_search = $borders['end_end'];
        }
        $this->assertEquals($expected, $actual);
    }

    function test_tags_AaA_BbB() {
        $source1 = '<A>a</A> some <B>b</B>';
        $expected = 'A: a B: b ';
        $beginEnd = ['(<)([^>]*)(>)?', '<\/($2)>'];
        $maxLoopCount = $pos_of_next_search = 0;
        $cf = new SL5_preg_contentFinder($source1, $beginEnd);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $actual = '';
        while($maxLoopCount++ < 30) {
            $cf->setPosOfNextSearch($pos_of_next_search);
            $borders = $cf->getBorders();
            if(is_null($borders['begin_begin'])) {
                break;
            }
            $actual .= $borders['matches']['begin_begin'][1][0];
            $actual .= ': ' . $cf->getContent() . ' ';
            $pos_of_next_search = $borders['end_end'];
        }
        $this->assertEquals($expected, $actual);
    }

 }