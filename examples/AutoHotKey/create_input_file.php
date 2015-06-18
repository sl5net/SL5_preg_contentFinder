<?php
//include_once 'input_compressed.ahk'
$file_content_original = file_get_contents('SciTEUpdate.ahk');
$file_content_compressed = preg_replace("/\s+/", " ", $file_content_original);
file_put_contents('input_compressed.ahk',$file_content_compressed);