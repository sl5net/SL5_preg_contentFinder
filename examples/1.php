<?php
include("../SL5_preg_contentFinder.php");
$content = "free{o{i}o}free";
//$p = $cf->get_borders_left(__LINE__, $b = '(', $e = ')', $pos);
//if (is_null($p['begin_begin'])) {
//    die(__FUNCTION__ . __LINE__);
//}

//$rebuild_1 = '(' . $cf->getContent($b, $e, $pos) . ')';
//$rebuild_1 = '(' . $cf->getContent($b, $e, $pos) . ')';
$cf = new SL5_preg_contentFinder($content,$b = '{', $e = '}');
echo $cf->getContent($b, $e);

echo $cf->getContentBetweenIDs(0, 1);