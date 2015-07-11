<html>
<body>
converts your Autohotkey code into pretty format.

Just copy the source code to the first pane and click "Prettyfy!".
<?php

$uglySource = 'this is ugly example source

isFileOpendInSciteUnsaved(filename){
    SetTitleMatchMode,2
doSaveFirst := false ; initialisation
   IfWinNotExist,%filename% - SciTE4AutoHotkey{
doSaveFirst := true
IfWinNotExist,%filename% * SciTE4AutoHotkey
MsgBox,oops   NotExist %filename% * SciTE4AutoHotkey
 if(false){
      Too(Last_A_This)
   s := Com("{D7-2B-4E-B8-B54}")
   if !os
ExitApp
   else if(really){
MsgBox, yes really :)
     } else
   ExitApp


   ; comment :) { { hi } {


   }
}
return doSaveFirst
}
';
if(!$_POST['code1']) $_POST['code1'] = $uglySource;
include_once('../../examples/AutoHotKey/Reformatting_Autohotkey_Source.php');
$actual = reformat_AutoHotKey($_POST['code1'], $arguments = '');
$actual = preg_replace('/[<]/', '&lt;', $actual);
?>

<form method="post">
    <input type="submit" value="Prettyfy!">

    <div id="code1">
        <strong>Source code:</strong><br/>
        <textarea name="code1" rows="10" cols="80" onClick="javascript:this.form.code1.focus();this.form.code1.select();"><?php echo $uglySource; ?></textarea>
    </div>
    <div id="code2">
        <strong>Pretty indented code:</strong><br/>
              <textarea id="code2" name="code2" rows="10" cols="80" readonly="readonly"
                        onClick="javascript:this.form.code2.focus();this.form.code2.select();"><?php echo $actual; ?></textarea>
    </div>
</form>
</body>
</html>