<?php
/*
  This SL5_ContentFinder class is part of the doSqlWeb project,
  a PHP Template Engine.
  Copyright (C) 2013 Sebastian Lauffer, http://SL5.net
 
  SL5_ContentFinder stands under the terms of the GNU General Public
 License as published by the Free Software Foundation, either version 3
 of the License, or (at your option) any later version.
 
  SL5_ContentFinder is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 
  For GNU General Public License see <http://www.gnu.org/licenses/>.
 */
$bugIt = false;
// Add appgati.
if ($bugIt) {
    require_once 'appgati.class.php';
// Initialize
    $app = new AppGati();
// A step should be a continous string.
    $app->Step('1');
}

//ContentFinder::selfTest_collection();

// Add another step.
if ($bugIt) {
    $app->Step('2');
}

// Generate report between steps 1 and 2.
if ($bugIt) {
    $report1 = $app->Report('1', '2');
}

if ($bugIt) {
// Print reports.
    echo '<hr>';
    print_r($report1['Clock time in seconds']);
    echo '<hr>';
    print_r($report1);
}

if (basename($_SERVER["PHP_SELF"]) == basename(__FILE__)) {
    SL5_preg_contentFinder::selfTest_collection();
}
class SL5_preg_contentFinder
{
    private static $selfTest_defaults = array(); # please fill that first time
    private static $selfTest_called_from_init_defaults = false;
    private static $selfTest_collection_finished = false;
    private $content = "";
    private $regEx_begin = null;
    private $regEx_end = null;

    private $stopIf_EndBorder_NotExistInContent = false;
    private $stopIf_BothBorders_NotExistInContent = true;

    private $doOverwriteSetup_OF_pos_of_next_search = true;

    private $pos_of_next_search = null;
    private $searchMode = "lazyWhiteSpace";

    private $searchModes = array('lazyWhiteSpace', 'dontTouchThis', 'use_BackReference_IfExists_()$1${1}');
    private $CACHE_beginEndPos_2_findPosKey = array();
    private $history = array();

    private static $CACHE_id_arrayKeys = array('begin', 'end', 'pos_of_next_search', 'posArray');

    private static $posArray_arrayKeys = array('begin_begin', 'begin_end', 'end_begin', 'end_end', 'matches');

    private $findPos_list_current_ID = null;

    public
    static $lastObject = null;

    private $findPos_list;

    function __construct($content)
    {
        self::$lastObject = $this;
        $this->content = $content;
    }

    public function setPosOfNextSearch($pos_of_next_search)
    {
        if (!is_numeric($pos_of_next_search)) {
            bad(__FUNCTION__ . __LINE__ . ' : !is_numeric($pos_of_next_search)' . $pos_of_next_search);

            return false;
        }
        $this->pos_of_next_search = $pos_of_next_search;

        return true;
    }

    public static function recursion_example4($silentMode)
    {
        if (true) {
            if (!$silentMode) {
                echo '<font style="font-family: monospace">';
            }

            $source = str_repeat(implode('', range(0, 9)), 2) . '0';
            $numbers = str_repeat(implode('', range(0, 9)), 2) . '0';
            $sourceArray = str_split($source);
            $source = '';
            $delimiters = array('(', 'anywayNumber', ')');
            foreach ($sourceArray as $pos => $v) {
                $source .= (($pos + 1) % 3 == 2) ? $v : $delimiters[$pos % 3];
            }
            if (!$silentMode) {
                echo __LINE__ . ':$content=<br>' . $source . '<br>';
            }
            if (!$silentMode) {
                echo $numbers . '<br><br>';
            }
            $cf = new SL5_preg_contentFinder($source);

            for ($i = 0; $i < 2; $i++) {
                # rebuild with search tool. find every number
                # do this many times should be no problem
                $rebuild = '';
                for ($pos = 0; $pos < count($sourceArray); $pos += 3) {
                    $p = $cf->get_borders_left(__LINE__, $b = '(', $e = ')', $pos);
                    if (is_null($p['begin_begin'])) {
                        die(__FUNCTION__ . __LINE__);
                    }

                    $rebuild_1 = '(' . $cf->getContent($b, $e, $pos) . ')';
                    $rebuild_2 = '(' . $cf->getContent() . ')';
                    if ($rebuild_1 != $rebuild_2) {
                        die(__FUNCTION__ . __LINE__ . ": '$rebuild_1' != '$rebuild_2' (rebuild_1 != rebuild_2");
                    }
                    $rebuild .= $rebuild_1;
                }
                if (!$silentMode) {
                    echo __LINE__ . ':$rebuild= <br>' . $rebuild . '<br>';
                }
                if (!$silentMode) {
                    echo $numbers . '<br>';
                }
                if ($source != $rebuild) {
                    die(__LINE__ . ":ERROR <br>$source != <br>$rebuild");
                }
                if (!$silentMode) {
                    echo '<hr>';
                }
                if (!$silentMode) {
                    echo '--:' . $numbers . '<br>';
                }
                for ($pos = 0; $pos < count($sourceArray); $pos += 3) {
                    if ($pos == 3) {
                        132465789;
                    }
                    $p = $cf->get_borders_left(__LINE__, '(', ')', $pos);
                    if (is_null($p['begin_begin'])) {
                        die(__FUNCTION__ . __LINE__);
                    }
                    if (!$silentMode) {
                        echo ($pos > 9) ? "$pos:" : "0$pos:";
                    }
                    if ($pos - 2 >= 0) {
                        if (!$silentMode) {
                            echo str_repeat('_', $pos - 2);
                        }
                    }
                    if (!$silentMode) {
                        echo $cf->getContentPrev();
                    }
                    if ($pos > 0) {
                        if (!$silentMode) {
                            echo ')';
                        }
                    }
                    $cf->get_borders_left(__LINE__, '(', ')', $pos);
                    if (!$silentMode) {
                        echo '(' . @$cf->getContent();
                    }
                    if (!$silentMode) {
                        echo ')(';
                    }
                    $cf->get_borders_left(__LINE__, '(', ')', $pos);
                    if (!$silentMode) {
                        echo '' . $cf->getContentNext();
                    }

                    if (!$silentMode) {
                        echo '<br>';
                    }
                }
            }


        }

        if (1) {
            ######## borders beetween #########
            $cf->get_borders_left(__LINE__, '(1)', '(7)', 0);
            $c = @$cf->getContent();
            if (!$silentMode) {
                echo __LINE__ . ': ' . $c . '<br>';
                echo __LINE__ . ':$rebuild= <br>' . $rebuild . '<br>';
                echo __LINE__ . ': BetweenID 0,2<br>=' . $cf->getContentBetweenIDs(0, 2) . '<br>';
                echo __LINE__ . ': BetweenID 1,3<br>=' . $cf->getContentBetweenIDs(1, 3) . '<br>';
                echo __LINE__ . ': BetweenID 2,4<br>=' . $cf->getContentBetweenIDs(2, 4) . '<br>';
                echo __LINE__ . ': BetweenID 0,4<br>=' . $cf->getContentBetweenIDs(0, 4) . '<br>';
                echo __LINE__ . ': BetweenNext2Current<br>=' . $cf->getContentBetweenNext2Current() . '<br>';
                echo __LINE__ . ': BetweenPrev2Current<br>=' . $cf->getContentBetweenPrev2Current() . '<br>';
                echo '<br>';
            }
            if ($rebuild != $source) {
                die(__FUNCTION__ . __LINE__ . ': $rebuild != $source');
            }
            ######## borders beetween #########
        }


        return $rebuild;
    }

    private function getPosOfNextSearch()
    {
        $pos_of_next_search = $this->pos_of_next_search;
        if (!is_numeric($pos_of_next_search)) {
            $pos_of_next_search = 0;
        }

        return $pos_of_next_search;
    }

    public function setBeginEnd_RegEx($RegEx_begin, $RegEx_end)
    {
        $this->setRegEx_begin($RegEx_begin);
        $this->setRegEx_end($RegEx_end);

        return true;
    }

    public function setRegEx_begin($RegEx_begin)
    {
        if (is_null($RegEx_begin)) {
            die(__FUNCTION__ . __LINE__ . ": is_null($RegEx_begin)");
        }
        $this->setRegEx($this->regEx_begin, $RegEx_begin);

        return true;
    }

    private static function implement_BackReference_IfExists(&$matchesReturn, &$RegEx_begin, &$RegEx_end)
    {
        foreach ($matchesReturn['begin_begin'] as $nr => $valuePos) {
            $vQuote = preg_quote($valuePos[0], '/');
            $RegEx_end_new = str_replace(
                array('$' . ($nr + 1), '${' . ($nr + 1) . '}'),
                $vQuote,
                $RegEx_end
            );
            if ($RegEx_end_new != $RegEx_end) {
                preg_match_all("/\([^)]*\)/", $RegEx_begin, $bb, PREG_OFFSET_CAPTURE);
                list($bb['found'], $bb['pos']) = $bb[0][$nr];
                $bb['len'] = strlen($bb['found']);
                $RegEx_begin =
                    substr($RegEx_begin, 0, $bb['pos'])
                    . '(' . $vQuote . ')' . substr($RegEx_begin, $bb['pos'] + $bb['len']);
                $RegEx_end = $RegEx_end_new;
            }
        }
        $pattern = '/(' . $RegEx_begin . '|' . $RegEx_end . ')(.*)/sm';

        return true;
    }

    private function getRegEx_begin()
    {
        return $this->regEx_begin;
    }

    private function getRegEx_end()
    {
        return $this->regEx_end;
    }

    public function setRegEx_end($RegEx_end)
    {
        $this->setRegEx($this->regEx_end, $RegEx_end);

        return true;
    }

    public function setSearchMode($searchMode)
    {
        $searchModes = $this->searchModes;
        if (!in_array($searchMode, $searchModes)) {
            bad(
                __FUNCTION__ . __LINE__ . ' this $searchMode is not possible. pleas use on of them: ' . implode(
                    ', ',
                    $searchModes
                )
            );

            return false;
        }
        $this->searchMode = $searchMode;

        return true;
    }

    public function getSearchMode()
    {
        return $this->searchMode;
    }

    private static function bordersBeetweenExample($cf, $silentMode, $rebuild, $source)
    {
    }

    private static function content_before_behind_example($silentMode)
    {
        if (1) {
            $_source = str_repeat(implode('', range(0, 3)), 2) . '0';
            $numbers = str_repeat(implode('', range(0, 3)), 2) . '0';
            $_sourceArray = str_split($_source);
            $_source = '';
            $delimiters = array('(', 'anywayNumber', ')');
            foreach ($_sourceArray as $b_pos => $valArray) {
                $_source .= (($b_pos + 1) % 3 == 2) ? $valArray : $delimiters[$b_pos % 3];
            }
            # find every second third
            $rebuild = '';
            $c = new SL5_preg_contentFinder($_source);
            if (!$silentMode) {
                info(__LINE__ . ": \$content = <br>" . htmlspecialchars($_source));
            }

            for ($b_pos = 0; $b_pos < count($_sourceArray); $b_pos += 3) {

                $p = $c->get_borders_left(__LINE__, $b1 = '(', $b2 = ')', $b_pos);
                var_export($p);

                if (!$silentMode) {
                    great('$cf->prev()=' . $c->getContentPrev());
                }
                if (!$silentMode) {
                    info('$cf->next()=' . $c->getContentNext());
                }
                info('pos_of_next_search=' . $c->pos_of_next_search);
                $rebuild .= '(' . $c->getContent() . ')';
            }
            if (!$silentMode) {
                echo __LINE__ . ':$rebuild= <br>' . $rebuild . '<br>';
            }
            if ($_source != $rebuild) {
                die(__LINE__ . ": ERROR:<br>$_source != <br>$rebuild (rebuild)");
            }
            echo '<br>';

            self::before_behind_example($silentMode);
        }
    }

    private static function simple123example($silentMode = false)
    {
        if (true) {
            $content = '123';
            $cf = new SL5_preg_contentFinder($content);

            $c = @$cf->getContent($b1 = 'q', $b2 = 'x');
            if (!$silentMode) {
                info(__LINE__ . ': $c="' . $c . '"');
            }
            if ($c != '') {
                die("$c!=''");
            }

            $c = @$cf->getContent($b1 = '1', $b2 = 'x');
            if (!$silentMode) {
                info(__LINE__ . ': $c="' . $c . '"');
            }
            if ($c != '23') {
                die(" '$c' != '23'");
            }

            $c = @$cf->getContent($b1 = 'q', $b2 = '3');
            if (!$silentMode) {
                info(__LINE__ . ': $c="' . $c . '"');
            }
            if ($c != '12') {
                die("'$c' != '12' ");
            }

            return array($cf, $b1, $b2, $c);
        }
    }

    /**
     * @param $silentMode
     * @return array
     */
    private static function selfTest_Tags_Parsing_Example($silentMode = false)
    {
        $content1 = $source = '<body>
ha <!--[01.o0]-->1<!--[/01.o0]-->
hi [02.o0]2<!--[/02.o0]-->
ho  <!--[03.o0]-->3<!--[/03.o0]-->
</body>';
        if (!$silentMode) {
            info(__LINE__ . ': ' . $source);
        }
        $pos_of_next_search = 0;
        $begin = '(<!--)?\[([^\]>]*\.o0)\](-->)?';
        $end = '<!--\[\/($2)\]-->';
        $cf = new SL5_preg_contentFinder($source);
        $cf->setBeginEnd_RegEx($begin, $end);
        $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
        $loopCount = 0;
        while ($loopCount++ < 5) {
            $cf->setPosOfNextSearch($pos_of_next_search);
            $findPos = $cf->get_borders_left(__LINE__);
            $sourceCF = @$cf->getContent();
            $expectedContent = $loopCount;
            if ($loopCount > 3) {
                $expectedContent = '';
            }
            if ($sourceCF != $expectedContent) {
                info('$sourceCF=' . $sourceCF);
                $str = __LINE__ . ': ' . "loop=$loopCount: '$sourceCF' != '$expectedContent' <br>(source != expected) ";
                bad($str);
                die(__LINE__ . $str);
            }
            if (is_null($findPos['begin_begin'])) {
                break;
            }
            if (!$silentMode) {
                great(__LINE__ . ': ' . $content1 . ' ==> "' . $sourceCF . '"');
            }
            $pos_of_next_search = $findPos['end_end'];
        }

        return array(
            $source,
            $content1,
            $loopCount,
            $pos_of_next_search,
            $begin,
            $end,
            $cf,
            $findPos,
            $sourceCF,
            $expectedContent
        );
    }

    private static function before_behind_example($silentMode)
    {
        if (true) {
            switch (3) {
                case 1:
                    $b1 = 'b';
                    $E = ')';
                    $e = '}';
                    break;
                case 2:
                    $b1 = 'b';
                    $E = '>';
                    $e = 'e';
                    break;
                case 3:
                    $b1 = '[';
                    $E = '>';
                    $e = ']';
                    break;
            }
            $b2 = "$E$e";

            $source_before = '1';
            $content = "2$b1$e" . '2';
            $behind_source = '3';
            $_source = $source_before . "$b1" . $content . "$E$e" . $behind_source;
            if (!$silentMode) {
                great(__LINE__ . ': $source = ' . str_replace($b1, "<i>$b1</i>", $_source), false);
            }
            if (!$silentMode) {
                great(__LINE__ . ': $b1 = ' . $b1 . ' $end = ' . $b2);
            }

            $cf = new SL5_preg_contentFinder($_source);
//            $cf->setBeginEnd_RegEx($begin, $end);
            $content1 = @$cf->getContent($b1, $b2);
            if ($content != $content1) {
                die("$content!=$content1");
            }
            $findPos = $cf->get_borders_left(__LINE__);
            if ($findPos['end_begin'] == $findPos['end_end']) {
                die('<br>die in line: ' . __LINE__ . ': ' . var_export(
                        $findPos,
                        true
                    ));
            }


            $before_content = substr($_source, 0, $findPos['begin_begin']);
            $behind_content = substr($_source, $findPos['end_end']);
            if ($source_before != $before_content) {

                die("before: $source_before != $before_content");
                if (!$silentMode) {
                    info(__LINE__ . ': $content_before = "' . $before_content . '"');
                }
                if (!$silentMode) {
                    info(__LINE__ . ': content _ _ _ _ = "' . $content1 . '"');
                }
                bad_little(__LINE__ . ': was muss content sein???? inklusive rest??? ');
            }
            if (!$silentMode) {
                info(__LINE__ . ': $content_behind = "' . $behind_content . '"');
            }
            if ($findPos['end_begin'] >= $findPos['end_end']
            ) {
                bad_little("<br>end_begin >= end_end<br> {$findPos['end_begin']} >= {$findPos['end_end']}");
                info(__LINE__ . ': ' . $_source . ' ==> ' . $content1);
                if ($behind_source != $behind_content) {
                    bad_little(
                        __LINE__ . ':behind: <br>' . $behind_source . ' ==> ' . $behind_content . '   $source_behind != $content_behind '
                    );
                    die('<br>die in line: ' . __LINE__);
                } else {
                    info(__LINE__ . ':behind: <br>' . $behind_source . ' ==> ' . $behind_content);
                }
                info(__LINE__ . ':before: <br>' . $source_before . ' ==> ' . $before_content, 'yellow', false);
                die('<br>die in line: ' . __LINE__);
            }

        }
    }


    public function getLastObject()
    {
        $l = self::$lastObject;

        return self::$lastObject;
    }

    public
    function get_borders_left(
        $fromLine,
        $RegEx_begin = null,
        $RegEx_end = null,
        $pos_of_next_search = null,
        &$txt = null,
        $searchMode = null,
        $bugIt = false
    ) {
        if (is_null($txt)) {
            $txt = $this->content;
        }
        if (is_null($searchMode)) {
            $searchMode = $this->getSearchMode();
        }
        $this->update_RegEx_BeginEndPos($RegEx_begin, $RegEx_end, $pos_of_next_search);

        $pos_of_next_search_backup = $pos_of_next_search;

        # please use selfTest of this class for understanding this function completely. it returns to positions.
        # it gives back the beginning of the borders (left). left beginning of each
        # it searchs from the beginning of the $txt
        # benchark tipps: http://floern.com/webscripting/geschwindigkeit-von-php-scripts-optimieren
        if ($searchMode == 'lazyWhiteSpace') {
            $RegEx_begin_backup = $RegEx_begin;
            $RegEx_end_backup = $RegEx_end;
            $RegEx_begin = SL5_preg_contentFinder::preg_quote_by_SL5($RegEx_begin);
            $RegEx_end = SL5_preg_contentFinder::preg_quote_by_SL5($RegEx_end);
        } elseif (strrpos($searchMode, 'use_BackReference') !== false || strrpos(
                $searchMode,
                'dontTouchThis'
            ) !== false
        ) {
            # begin and end should are regular expressions! i could not proof this ... hmm
            $RegEx_begin_backup = $RegEx_begin;
            $RegEx_end_backup = $RegEx_end;
        } else {
            die(__LINE__ . ': actually ... are the only implemented search modes. not "' . $searchMode . '" ' . "\$begin=$RegEx_begin, \$end=$RegEx_end");
        }

        $RegEx_begin_CACHE = $RegEx_begin;
        $RegEx_end_CACHE = $RegEx_end;
        $findPosID = & $this->CACHE_beginEndPos_2_findPosKey[$RegEx_begin_CACHE][$RegEx_end_CACHE][$pos_of_next_search];
        if (isset($findPosID)) {
            $return = & $this->findPos_list[$findPosID];

            return $return;
        }

        $emergency_Stop = 0;

        $findPos['begin_begin'] = null; // the begin is easy case. find the right end little more difficult.
        $findPos['end_begin'] = null;
        $matchesReturn = null; # optionally you could store parts inside of borders

        $strLen_txt = strlen($txt);
//        $strLen_begin = strlen($begin_backup); # may little long. long enough ...
//        $strLen_end = strlen($end_backup); # may little long. long enough ...
//        $strLen_begin_backup = strlen($begin_backup);

        $pattern = '/(' . $RegEx_begin . '|' . $RegEx_end . ')(.*)/sm';
        if ($searchMode == 'dontTouchThis') {
            if ($bugIt) {
                echo(__LINE__ . ": $RegEx_begin | $RegEx_end     \$pattern=" . $pattern);
            }
        }
        $count_begin = 0;
        $count_end = 0;

        while (($count_begin == 0 || $count_begin > $count_end)
            && $emergency_Stop < 1000
        ) {
            $emergency_Stop++;
            if ($count_begin == 0) {
                # first search the startBorder

                /*
                 *  preg_match returns the number of times
        * <i>pattern</i> matches. That will be either 0 times
        * (no match) or 1 time because <b>preg_match</b> will stop
        * searching after the first match.
                 */
                $preg_match_result = preg_match(
                    '/'
                    . $RegEx_begin . '/sm',
                    $txt,
                    $matches_begin,
                    PREG_OFFSET_CAPTURE,
                    $pos_of_next_search
                );

                if (!$preg_match_result) {
                    # no first element found/exist
                    if (preg_match('/' . $RegEx_end . '/', $txt, $matches, PREG_OFFSET_CAPTURE, $pos_of_next_search)) {
                        $findPos['end_begin'] = $matches[0][1];
                    }
//                    die(__LINE__ . ':$findPos[end] = ' . $findPos['end_begin'] . " \$txt=$txt");
                    break;
                }
                $findPos['begin_begin'] = $matches_begin[0][1];
                $count_begin++;

                $pos_of_last_found = $matches_begin[0][1];
                $pos_of_next_search = $pos_of_last_found
                    + strlen($matches_begin[0][0]) + 0;

                $findPos['begin_end'] = $pos_of_next_search;

                $matchesReturn['begin_begin'] = array_splice($matches_begin, 1);

                if ($searchMode == 'use_BackReference_IfExists_()$1${1}') {
                    self::implement_BackReference_IfExists($matchesReturn, $RegEx_begin, $RegEx_end, $pattern);
                }
                if (false) {
                    echo '<pre>';
                    var_export($matches_begin);
                    echo('13-09-20_07-10');
                    echo '</pre>';
                }
            }
            if (1 || $bugIt) {
                $temp = substr($txt, $pos_of_next_search);
            }
            if ('1[2[]2>]3' == $txt) {
                info(__LINE__ . ': $count_begin=' . $count_begin);
            }
            if (preg_match($pattern, $txt, $matches, PREG_OFFSET_CAPTURE, $pos_of_next_search)) {
                $pos_of_last_found = $matches[1][1];
                $pos_of_next_search = $pos_of_last_found
                    + strlen($matches[1][0]); # you could also use + 0 it also works correct in the tests.
                if (preg_match('/' . $RegEx_end . '/sm', $matches[1][0])) {
                    $findPos['end_begin'] = $pos_of_last_found;
                    $findPos['end_end'] = $pos_of_next_search;
                    $count_end++;
                } else {
                    #$findPos['begin_begin'] = $pos_of_last_found;
                    $count_begin++;
                }
            } else {
                $pos_of_next_search = $pos_of_next_search_backup + $strLen_txt;
                break;
            }
        }
        if ($matches && count($matches) > 0) {
            $matchesReturn['end_begin'] = array_splice($matches, 2, count($matches) - 3);
        }


//        echo('<br>' . __LINE__ . ':' . $findPos['end_begin'] . ", $count_begin = $count_end ");

        if (!isset($matches[1][0])) {
            $matches[1] = & $matches[0];
        }

        if (is_numeric($findPos['end_begin'])) {
            $findPos['end_end'] = $findPos['end_begin'] + strlen($matches[1][0]);
            if ($findPos['end_begin'] >= $findPos['end_end']) {
                $findPos['end_end'] = $findPos['end_begin'] + strlen($RegEx_end_backup);
                if ($findPos['end_begin'] >= $findPos['end_end']) {
                    die(__LINE__ . ': ups');
                }
            }
        }
        if (!isset($findPos['end_end']) || is_null($findPos['end_end'])) {
            $findPos['end_end'] = $strLen_txt;
        }
        if ($RegEx_begin_backup == '[w') {
            'breakPoint';
        }

        $key_findPos_list = $this->update_key_findPos_list($findPos, $matchesReturn);

        $this->setCACHE_beginEndPos(
            $RegEx_begin_CACHE,
            $RegEx_end_CACHE,
            $pos_of_next_search_backup,
            $key_findPos_list
        );

        if ($bugIt || true) {
            $temp = substr($txt, $pos_of_next_search_backup);
            $content = (@$findPos['end_begin'])
                ? substr(
                    $txt,
                    @$findPos['begin_end'],
                    @$findPos['end_begin'] - @$findPos['begin_end']
                )
                :
                substr(
                    $txt,
                    (isset($findPos['begin_end']) && is_numeric(
                            $findPos['begin_end']
                        )) ? $findPos['begin_end'] : $pos_of_next_search_backup
                );

        }
//    $findPos['matches'] = $matchesReturn;
        $this->findPos_list[$findPosID]['matches'] = $matchesReturn;
        $return = & $this->findPos_list[$findPosID];

        return $return;
    }

    public function CACHE_current($key = null)
    {
        $t = & $this;
        if ($key == 'pos_of_next_search') {
            return $t->pos_of_next_search;
        }
        if ($key == 'begin') {
            return $t->regEx_begin;
        }
        if ($key == 'end') {
            return $t->regEx_end;
        }

        return false;
    }

    public function getContentByID($id)
    {
        $t = & $this;
        if (is_nan($id) || $id < 0) {
            debug_print_backtrace();
            die(__FUNCTION__ . __LINE__ . ": \$id=is_nan($id)");
        }
        if (!isset($t->findPos_list[$id])) {
            return false;
        }

        $C = $t->findPos_list[$id];

        $backup = $t->doOverwriteSetup_OF_pos_of_next_search;
        $t->doOverwriteSetup_OF_pos_of_next_search = false;
        $content = $t->getContent(
            $C['begin_begin'],
            $C['end_begin'],
            (is_null(@$C['pos_of_next_search'])) ? 0 : $C['pos_of_next_search']
        );
        $t->doOverwriteSetup_OF_pos_of_next_search = $backup;

        return $content;
    }

    public function getContentPrev()
    {
        $id = $this->findPos_list_current_ID;
        if (is_nan($id)) {
            $return = false;

            return $return;
        }
        $return = (--$id < 0) ? '' : $this->getContentByID($id);

        return $return;
    }

    public function getContentNext()
    {
        $id = $this->findPos_list_current_ID;
        if (is_nan($id)) {
            $return = false;

            return $return;
        }
        $return = $this->getContentByID($id + 1);

        return $return;
    }

    public function getID()
    {
        return $this->findPos_list_current_ID;
    }

    private
    static function selfTest_init_defaults()
    {
        $temp = self::$selfTest_defaults;
        if (count(self::$selfTest_defaults) > 0) {
            return true;
        } # we was already here. nothing to do.

        # pseudo constructor
        # please call this in nearly every methods inside. for init the default values.
        self::$selfTest_called_from_init_defaults = true;
        self::selfTest();
        self::$selfTest_called_from_init_defaults = false;

        return true;
    }

    public function setID($id)
    {
        if (!isset($this->findPos_list_current_ID)) {
            $this->findPos_list_current_ID = $id;
        }
    }

    public function getContentBetweenNext2Current()
    {
        $current_ID = $this->findPos_list_current_ID;
        $next_ID = $current_ID + 1;

        return $this->getContentBetweenIDs($current_ID, $next_ID);
    }

    public function getContentBetweenPrev2Current()
    {
        $current_ID = $this->findPos_list_current_ID;
        if (is_nan($current_ID)) {
            $return = false;

            return $return;
        }
        $prev_ID = $this->findPos_list_current_ID - 1;

        $doInclusive = false;


        if ($prev_ID < 0) {
            $p = $this->findPos_list[$current_ID];
            if (!$doInclusive) {
                return substr($this->content, 0, $p['begin_begin']);
            }

            return substr($this->content, 0, $p['end_end']);
        }

        $findPos_list = & $this->findPos_list;
        if (!isset($findPos_list[$prev_ID])) {
            $prev_end_end = $findPos_list[$prev_ID]['end_end'];

            $current_begin = $findPos_list[$current_ID]['begin_begin'];;
//        $CACHE = & $this->CACHE;
            $c = substr($this->content, $prev_end_end, $current_begin - $prev_end_end);

            return $c;
        }

        return $this->getContentBetweenIDs($prev_ID, $current_ID);
    }

    public function getContentBetweenIDs($id1, $id2)
    {
        if (is_nan($id1) || is_nan($id2)) {
            die("(is_nan($id1) || is_nan($id2)");
        }

        $t = & $this;

        $id_1_CH = & $t->findPos_list[$id1];
        $id_2_CH = & $t->findPos_list[$id2];
        if (!@isset($id_1_CH) || !@isset($id_2_CH)) {
            debug_print_backtrace();
            die(__FUNCTION__ . __LINE__ . ": !isset(...) $id1, $id2");
        }


        $p1begin = $id_1_CH['begin_begin'];
        $p2begin = $id_2_CH['begin_begin'];
        if ($p1begin < $p2begin) {
            $p1end_end = $id_1_CH['end_end'];
            $c = substr($t->content, $p1end_end, $p2begin - $p1end_end);
        } else {
            $p2end_end = $id_2_CH['end_end'];
            $c = substr($t->content, $p2end_end, $p1begin - $p2end_end);
        }

        return $c;
    }

    private function setCACHE_beginEndPos($begin, $end, $pos_of_next_search, $key_findPos_list)
    {
        $t = & $this;
        if (true) {
            #;<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            # plausibilitiy checks
            if (!is_string($begin) || !is_string($end)) {
                echo(__LINE__ . ': ' . "!is_string($begin) || !is_string($end)");
                debug_print_backtrace();
                die(__FUNCTION__ . '>' . __LINE__);
            }
            if (!is_numeric($pos_of_next_search)) {
                echo(__LINE__ . ': ' . "!is_numeric($pos_of_next_search)");
                debug_print_backtrace();
                die(__FUNCTION__ . '>' . __LINE__);
            }
            if (!is_numeric($key_findPos_list)) {
                echo(__LINE__ . ': ' . "!is_numeric($key_findPos_list)");
                debug_print_backtrace();
                die(__FUNCTION__ . '>' . __LINE__);
            }
            #;>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        }
        if (is_null($pos_of_next_search)) {
            $pos_of_next_search = 0;
        }
        $t->CACHE_beginEndPos_2_findPosKey[$begin][$end][$pos_of_next_search] = $key_findPos_list;
        $t->setID($key_findPos_list);

        return true;
    }

    public static function getExampleContent($nr = null)
    {
        $bugIt = false;


        $content = str_repeat(implode('', range(0, 9)), 2) . '0';
        $numbers = str_repeat(implode('', range(0, 9)), 2) . '0';
        $contentArray = str_split($content);
        $content = '';
        $delimiters = array('(', ')', ')');
        foreach ($contentArray as $pos => $v) {
            if ($nr == 1) {
                $temp = (($pos + 1) % 4 == 2) ? $v : $delimiters[$pos % 3];
                $temp = (($pos + 1) % 3 == 2) ? $v : $delimiters[$pos % 3];
                if (in_array($v, array(5, 2))) {
                    $temp = ($pos > 9) ? ($pos - 10) : $pos;
                }
                $content .= $temp;
            } else {
                $content .= (($pos + 1) % 3 == 2) ? $v : $delimiters[$pos % 3];
            }
        }
        if ($bugIt) {
            echo __LINE__ . ':$content=<br>' . $content . '<br>';
        }
        if ($bugIt) {
            echo $numbers . '<br><br>';
        }

        return $content . (($nr == 1) ? '' : "\n" . $numbers);
    }

    private static function recursion_example($content)
    {

        $silentMode = true;
        if (!$silentMode) {
            echo '<pre>';
        }
        if (!$silentMode) {
            echo '<font style="font-family: monospace">';
        }
        $cf = new SL5_preg_contentFinder($content);
        if ($cut = @$cf->getContent($b = '(', $e = ')')) {
//            great(__LINE__ . ":\n \$cut= \n$content ==> " . $cut);
//            @$cf->getContent($b,$e,0,$cut);
            return self::recursion_example($cut);
        }

//        info(__LINE__ . ":\n \$cut= \n" . var_export($cut, true));
        return $cut;
    }

    private static function recursion_example2($content, $before = null, $behind = null)
    {
        $silentMode = true;
        if (is_null($before)) {
            if (!$silentMode) {
                echo('<u>' . __FUNCTION__ . '</u>:');
            }
        }

        echo '<pre>';
        echo '<font style="font-family: monospace">';
        $cf = new SL5_preg_contentFinder($content);
        $delimiters = array('(', ')');
        $delimiters[1];
        if ($cut = @$cf->getContent($delimiters[0], $delimiters[1])) {
            $p = $cf->get_borders_left(__LINE__, $delimiters[0], $delimiters[1]);
            if (is_null($p['begin_begin'])) {
                die(__FUNCTION__ . __LINE__);
            }

            $before .= substr($content, 0, $p['begin_begin']) . $delimiters[0];
            $behind = $delimiters[1] . substr($content, $p['end_end']) . $behind;

//            great(__LINE__ . ":\n" . '$before.$cut.$behind=' . "\n$content ==> " . "$before#$cut#$behind");
//            @$cf->getContent($b,$e,0,$cut);


            return self::recursion_example2($cut, $before, $behind);
        }

//        info(__LINE__ . ":\n \$cut= \n" . var_export($cut, true));
        return array(($cut) ? $cut : $content, $before, $behind);
    }

    private static function recursionExample3_search_NOT_in_rest_of_the_string($content, $before = null, $behind = null)
    {
        $silentMode = true;
        if (is_null($before)) {
            if (!$silentMode) {
                echo('<u>' . __FUNCTION__ . '</u>:');
            }
        }

        echo '<pre>';
        echo '<font style="font-family: monospace">';
        $cf = new SL5_preg_contentFinder($content);
        $delimiters = array('(', ')');
        $delimiters[1];
        if ($cut = @$cf->getContent($delimiters[0], $delimiters[1])) {
            $p = $cf->get_borders_left(__LINE__, $delimiters[0], $delimiters[1]);
            if (is_null($p['begin_begin'])) {
                die(__FUNCTION__ . __LINE__);
            }


            $before .= substr($content, 0, $p['begin_begin']) . $delimiters[0];
            $behind = $delimiters[1] . substr($content, $p['end_end']) . $behind;


//            great(__LINE__ . ": \n" . '$before.$cut.$behind=' . "\n$content ==> " . "$before#$cut#$behind");
//            @$cf->getContent($b,$e,0,$cut);

            if (true) {
                # change cut a little
                $dataExample = 1;
                if (preg_match("/\d/", $cut, $e)) {
                    $dataExample = $e[0] + 1;
                }
                $cut = preg_replace("/\w/", ($dataExample > 9) ? $dataExample - 10 : $dataExample, $cut);
            }

            return self::recursionExample3_search_NOT_in_rest_of_the_string($cut, $before, $behind);
        }

//        info(__LINE__ . ":\n \$cut= \n" . var_export($cut, true));

        return array(($cut) ? $cut : $content, $before, $behind);
    }

    private static function recursionExample6_search_also_in_rest_of_the_string(
        $content,
        $delimiters = array('(', ')'),
        $newDelimiter = null,
        $before = null,
        $behind = null
    ) {
        $isFirsRecursion = is_null($before);
        $cf = new SL5_preg_contentFinder($content);
        if (is_null($newDelimiter)) {
            $newDelimiter =& $delimiters;
        }
        if ($cut = @$cf->getContent($delimiters[0], $delimiters[1])) {
            $function = 'self::' . __FUNCTION__;

            $p = $cf->get_borders_left(__LINE__, $delimiters[0], $delimiters[1]);
            if (is_null($p['begin_begin'])) {
                die(__FUNCTION__ . __LINE__);
            }

            $before .= substr($content, 0, $p['begin_begin']) . $newDelimiter[0];
            $behindTemp = substr($content, $p['end_end']);

            if (!$isFirsRecursion) {
                $behind = $newDelimiter[1] . $behindTemp;
            }
            if ($isFirsRecursion) {
                $return = call_user_func(
                    $function,
                    $behindTemp
                    ,
                    $delimiters,
                    $newDelimiter
                );
                list($c, $bf, $bh) = $return;
                $behind = (is_null($c)) ? $newDelimiter[1]
                    . $behindTemp : $newDelimiter[1] . $bf . $c . $bh;
            }

            # change cut a little
            $dataExample = 1;
            if (preg_match("/\d/", $cut, $e)) {
                $dataExample = $e[0] + 1;
            }
            $cut = preg_replace("/\w/", ($dataExample > 9) ? $dataExample - 10 : $dataExample, $cut);

            great(
                __LINE__ . ": \n" . "\n$content (content) ==> \n" . "$before<u><b>$cut</b></u>$behind" . ' (before.cut.behind)',
                false
            );
            $return = call_user_func(
                $function,
                $cut,
                $delimiters,
                $newDelimiter,
                $before,
                $behind
            );

            return $return;
        }

//        info(__LINE__ . ":\n \$cut=" . var_export($cut, true) . ' $content=' . var_export($content, true) . "\n \$before=$before, \$cut=" . (($cut) ? $cut : $content) . " ,  \$behind=$behind");
        $return = array(($cut) ? $cut : $content, $before, $behind);

        return $return;
    }

    private static function recursionExample5_search_also_in_rest_of_the_string(
        $content,
        $newDelimiter = null,
        $before = null,
        $behind = null
    ) {
        $silentMode = true;
        $isFirsRecursion = is_null($before);
        $cf = new SL5_preg_contentFinder($content);
        $delimiters = array('(', ')');
        if (is_null($newDelimiter)) {
            $newDelimiter =& $delimiters;
        }
        if ($cut = @$cf->getContent($delimiters[0], $delimiters[1])) {
            $p = $cf->get_borders_left(__LINE__, $delimiters[0], $delimiters[1]);
            if (is_null($p['begin_begin'])) {
                die(__FUNCTION__ . __LINE__);
            }
            $before .= substr($content, 0, $p['begin_begin']) . $newDelimiter[0];
            $behindTemp = substr($content, $p['end_end']);

            if (!$isFirsRecursion) {
                $behind = $newDelimiter[1] . $behindTemp;
            }
            if ($isFirsRecursion) {
                list($c, $bf, $bh) =
                    self::recursionExample5_search_also_in_rest_of_the_string(
                        $behindTemp
                        ,
                        $newDelimiter
                    );
                $behind = (is_null($c)) ? $newDelimiter[1]
                    . $behindTemp : $newDelimiter[1] . $bf . $c . $bh;
            }

            # change cut a little
            $dataExample = 1;
            if (preg_match("/\d/", $cut, $e)) {
                $dataExample = $e[0] + 1;
            }
            $cut = preg_replace("/\w/", ($dataExample > 9) ? $dataExample - 10 : $dataExample, $cut);


            if (!$silentMode) {
                great(
                    __LINE__ . ": \n" . "\n$content (content) ==> \n" . "$before<u><b>$cut</b></u>$behind" . ' (before.cut.behind)',
                    false
                );
            }
            $return = self::recursionExample5_search_also_in_rest_of_the_string(
                $cut,
                $newDelimiter,
                $before,
                $behind
            );

            return $return;
        }

//        info(__LINE__ . ":\n \$cut=" . var_export($cut, true) . ' $content=' . var_export($content, true) . "\n \$before=$before, \$cut=" . (($cut) ? $cut : $content) . " ,  \$behind=$behind");
        $return = array(($cut) ? $cut : $content, $before, $behind);

        return $return;
    }


    private static function recursionExample4_search_also_in_rest_of_the_string(
        $content,
        $before = null,
        $behind = null
    ) {
        $silentMode = true;
        $isFirsRecursion = is_null($before);

        if ($isFirsRecursion) {
            if (!$silentMode) {
                echo('<u>' . __FUNCTION__ . '</u>:');
            }
        }

        echo '<pre>';
        echo '<font style="font-family: monospace">';
        $cf = new SL5_preg_contentFinder($content);
        $delimiters = array('(', ')');
        $delimiters[1];
        if ($cut = @$cf->getContent($delimiters[0], $delimiters[1])) {
            $p = $cf->get_borders_left(__LINE__, $delimiters[0], $delimiters[1]);
            if (is_null($p['begin_begin'])) {
                die(__FUNCTION__ . __LINE__);
            }


            $before .= substr($content, 0, $p['begin_begin']) . $delimiters[0];
            $behindTemp = substr($content, $p['end_end']) . $behind;

            if (!$isFirsRecursion) {
                $behind = $delimiters[1] . $behindTemp;
            }
            if ($isFirsRecursion) {
                'breakpoint';
                list($c, $bf, $bh) =
                    self::recursionExample4_search_also_in_rest_of_the_string($behindTemp);
//                info(__LINE__ . ": $c");
                $behind = (is_null($c)) ? $delimiters[1] . $behindTemp : $delimiters[1] . $bf . $c . $bh;
//                info(__LINE__ . ": $behind");
            }

            # change cut a little
            $dataExample = 1;
            if (preg_match("/\d/", $cut, $e)) {
                $dataExample = $e[0] + 1;
            }
            $cut = preg_replace("/\w/", ($dataExample > 9) ? $dataExample - 10 : $dataExample, $cut);


            if (!$silentMode) {
                great(
                    __LINE__ . ": \n" . "\n$content (content) ==> \n" . "$before<u><b>$cut</b></u>$behind" . ' (before.cut.behind)',
                    false
                );
            }
            $return = self::recursionExample4_search_also_in_rest_of_the_string(
                $cut,
                $before,
                $behind
            );

            return $return;
        }

//        info(__LINE__ . ":\n \$cut=" . var_export($cut, true) . ' $content=' . var_export($content, true) . "\n \$before=$before, \$cut=" . (($cut) ? $cut : $content) . " ,  \$behind=$behind");
        $return = array(($cut) ? $cut : $content, $before, $behind);

        return $return;
    }


    public static function selfTest_collection()
    {
        if (self::$selfTest_collection_finished) {
            return true;
        }
        self::$selfTest_collection_finished = true;

        $silentMode = false; # only shows errors 13-10-23_13-39
//    $silentMode = true; # only shows errors 13-10-23_13-39

        list($source, $content1, $maxLoopCount, $pos_of_next_search, $begin, $end, $cf, $findPos, $sourceCF, $expectedContent) = self::selfTest_Tags_Parsing_Example(
            $silentMode
        );


        if (true) {
            $sourceCF = "(2)";
            $cf = new SL5_preg_contentFinder($sourceCF);
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
            if (!$silentMode) {
                great($result);
            }
            if ($sourceCF != $result) {
                die(__LINE__ . " : #$sourceCF# != #$result#");
            }
        }

        if (true) {
            $sourceCF = "(1((2)1)8)";
            $cf = new SL5_preg_contentFinder($sourceCF);
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
            if (!$silentMode) {
                great($result);
            }
            if ($sourceCF != $result) {
                die(__LINE__ . " : #$sourceCF# != #$result#");
            }
        }


        if (true) {
            $content1 = $sourceCF = '<body>
ha <!--{01}-->1<!--{/01}-->
hi {02}2<!--{/02}-->
ho  <!--{03}-->3<!--{/03}-->
</body>';
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $maxLoopCount = 0;
            $pos_of_next_search = 0;
            $begin = '(<!--)?{([^}>]*)}(-->)?';
            $end = '<!--{\/($2)}-->';
            $cf = new SL5_preg_contentFinder($sourceCF);
            $cf->setBeginEnd_RegEx($begin, $end);
            $cf->setSearchMode('use_BackReference_IfExists_()$1${1}');
            while ($maxLoopCount++ < 5) {

                $cf->setPosOfNextSearch($pos_of_next_search);
//                echo __LINE__ . ": \$maxLoopCount=$maxLoopCount<br>";
                $findPos = $cf->get_borders_left(__LINE__);
                $sourceCF = @$cf->getContent();
//                echo '' . __LINE__ . ': $content=' . $content . '<br>';
                $expectedContent = $maxLoopCount;
                if ($maxLoopCount > 3) {
                    $expectedContent = '';
                }
                if ($sourceCF != $expectedContent) {
                    die(__LINE__ . 'ERROR :   $content != $expectedContent :' . " '$sourceCF'!= '$expectedContent ");
                }
                if (is_null($findPos['begin_begin'])) {
                    break;
                }
                if (!$silentMode) {
                    great(__LINE__ . ': ' . $content1 . ' ==> "' . $sourceCF . '"');
                }

                $pos_of_next_search = $findPos['end_end'];
            }
        }
        list($cf, $b, $e, $sourceCF) = self::simple123example($silentMode);
        if (true) {
            # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
            # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
            $content1 = '<!--123_abc-->dings1<!--dings2<!--';
            $cf = new SL5_preg_contentFinder($content1);
            $sourceCF = @$cf->getContent(
                $begin = '<!--[^>]*-->',
                $end = '<!--',
                $p = null,
                $t = null,
                $searchMode = 'dontTouchThis'
            );
            if (!$silentMode) {
                info(__LINE__ . ': ' . "$content1 => $sourceCF");
            }
            $expectedContent = 'dings1';
            if ($sourceCF != $expectedContent) {
                bad(" $sourceCF != $expectedContent");
                die(__LINE__);
            }
        }
        if (true) {
            # problem: Finally, even though the idea of nongreedy matching comes from Perl, the -U modifier is incompatible with Perl and is unique to PHP's Perl-compatible regular expressions.
            # http://docstore.mik.ua/orelly/webprog/pcook/ch13_05.htm
            $content1 = '123#abc';
            $cf = new SL5_preg_contentFinder($content1);
            $sourceCF = @$cf->getContent(
                $begin = '\d+',
                $end = '\w+',
                $p = null,
                $t = null,
                $searchMode = 'dontTouchThis'
            );
            if (!$silentMode) {
                info(__LINE__ . ': ' . "$content1 => $sourceCF");
            }
            $expectedContent = '#';
            if ($sourceCF != $expectedContent) {
                bad(" $sourceCF != $expectedContent");
                die(__LINE__);
            }
        }
        if (true) {
            $sourceCF = 'A (i) B (i) C';
            $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $cut = self::recursionExample5_search_also_in_rest_of_the_string(
                $sourceCF,
                array('[', ']')
            );
            $result = $cut[1] . $cut[0] . $cut[2];
            if (!$silentMode) {
                great(__LINE__ . ": \n$result (result)");
            }
            if (false === strpos($result, 'A [1] B [1] C') || strpos($result, '(i)')
            ) {
                die(__LINE__ . ': ' . " ERROR (i) found: \n$result (result)");
            }
        }
        if (true) {
            # recursionExample4_search_also_in_rest_of_the_string
//            $content = ' A ' . $content . ' B ' . $content . ' C ';
            $sourceCF = 'A (i) B (i) C';
            $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $cut = self::recursionExample4_search_also_in_rest_of_the_string($sourceCF);
            $result = $cut[1] . $cut[0] . $cut[2];
            if (!$silentMode) {
                great(__LINE__ . ": \n$result (result)");
            }
            if (false === strpos($result, 'A (1) B (1) C') || strpos($result, '(i)')
            ) {
                die(__LINE__ . ': ' . " ERROR (i) found: (proof) => \n$result (result)");
            }
        }


        $sourceCF = "((2)1)";
        $cf = new SL5_preg_contentFinder($sourceCF);
        if (!$silentMode) {
            info(__LINE__ . ': ' . $sourceCF);
        }
        $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
        if (!$silentMode) {
            great($result);
        }
        if ($sourceCF != $result) {
            die(__LINE__ . " : #$sourceCF# != #$result#");
        }

        if (true) {
            $sourceCF = "(1(1(2)1)8)";
            $cf = new SL5_preg_contentFinder($sourceCF);
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $result = '(' . @$cf->getContent($b = '(', $e = ')') . ')';
//       if(!$silentMode)great($result);
            if ($sourceCF != $result) {
                die(__LINE__ . " : #$sourceCF# != #$result#");
            }
        }

        if (true) {
            # recursion example 4
            $sourceCF = self::getExampleContent(1);
            $sourceCF = ' A ' . $sourceCF . ' B ' . $sourceCF . ' C ';
            $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
            if (!$silentMode) {
                info(__LINE__ . ': ' . $sourceCF);
            }
            $cut = self::recursionExample4_search_also_in_rest_of_the_string($sourceCF);
            $result = $cut[1] . $cut[0] . $cut[2];
            $proof = 'A (11(22(3)(2)22)11)(1) B (11(22(3)(2)22)11)(1) C';
            if (!$silentMode) {
                great(__LINE__ . ": \n$proof  (proof)\n?=\n$result");
            }
            if (strpos($result, $proof) === false) {
                die(__LINE__ . ': ' . " ERROR: \n$proof (proof) => \n$result (result)");
            }
        }
//        die('' . __LINE__);

        # recursion example 3
        $sourceCF = self::getExampleContent(1);
        $sourceCF = preg_replace('/\d/', 'i', $sourceCF);
        if (!$silentMode) {
            info(__LINE__ . ':' . $sourceCF);
        }
        $cut = self::recursionExample3_search_NOT_in_rest_of_the_string($sourceCF);
        $result = $cut[1] . $cut[0] . $cut[2];
//       if(!$silentMode)great("$content\n?=\n$result");
        if (strpos($result, '(11(22(3)(2)22)11)(i)') === false) {
            die(__LINE__ . ': ' . " ERROR: \n$sourceCF => \n$result");
        }

        # recursion example 2
        $sourceCF = self::getExampleContent(1);
        $cut = self::recursion_example2($sourceCF);
        $result = $cut[1] . $cut[0] . $cut[2];
        if (!$silentMode) {
            great("$sourceCF\n?=\n$result");
        }
        if ($sourceCF !== $result) {
            die(__LINE__ . ': ' . " ERROR: \n$sourceCF => \n$result");
        }

        # recursion example

        $sourceCF = self::getExampleContent(1);

        $silentMode = false;
        if (!$silentMode) {
            echo(__LINE__ . ': <u>recursion_example</u>:');
        }
        $cut = self::recursion_example($sourceCF);
        if (false !== $cut) {
            die(__LINE__ . ': ' . " != $cut");
        }

        #;<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        $sourceCF = 'nothing special';
        $cf = new SL5_preg_contentFinder($sourceCF);
        $noContent = @$cf->getContent($begin = 'bla', $end = 'noooo');
        if ($noContent !== false) {
            die(__LINE__ . ': $noContent!==false');
        }

        #;<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
# some tests 13-09-17_12-05
# selfTest($begin = '[', $end = ']', $txt = '[123456789]', $expectedString = '123456789'
        $t = new SL5_preg_contentFinder('');
        $t->selfTest('[1', ']', 0, 'asdf[12]fdsa', $expectedBehind = 'fdsa', '2');

        $t->selfTest(
            '{d w>',
            '{/d paul>',
            0,
            "{d w>something{/d paul>"
            ,
            $expectedBehind = "",
            'something',
            true
        ); # new lines should be ignored
        $t->selfTest('[', ']c', 0, 'ab[1]cd', $expectedBehind = 'd', '1');
        $t->selfTest('{', ']', 3, '{1234]' . __LINE__, $expectedBehind = __LINE__, '34');
        $t->selfTest('[', ']n', 0, '[12]n', $expectedBehind = '', '12');
        $t->selfTest('[', ']n', 0, '[12]nbb', $expectedBehind = 'bb', '12');
        # rest is not well formated yet 13-09-25_14-44
        $t->selfTest(
            '{d l>',
            '{/d ju>',
            0,
            "{d   l>something{/d ju>",
            $expectedBehind = "",
            'something'
        ); # new lines should be ignored
        $t->selfTest(
            '{d paul>',
            '{/d paul>',
            0,
            "{d \n paul>something{/d paul>",
            $expectedBehind = "",
            'something'
        ); # new lines should be ignored
        $t->selfTest(
            '[',
            ']',
            0,
            'ulm]]]uu',
            $expectedBehind = ']]uu',
            'ulm'
        ); # this is very special. because its without beginning delimiter. may you want it not work?
        $t->selfTest(
            '[',
            ']',
            0,
            ']]]',
            $expectedBehind = ']]',
            ''
        ); # this is very special. because its without beginning delimiter. may you want it not work?
        $t->selfTest();
        $t->selfTest('[A', ']', 0, "[A\n2]", $expectedBehind = "", '2');
        $line = __LINE__;
        $t->selfTest(
            '[w',
            ']',
            0,
            '[w' . $line,
            $expectedBehind = '',
            $line
        ); # this is very special. becouse its without ending delimiter. may you want it not work?
        $t->selfTest('[', ']', 0, '[]]' . __LINE__, $expectedBehind = ']' . __LINE__, '');
        $t->selfTest('[', ']', 0, '[[[]]]' . __LINE__, $expectedBehind = __LINE__, '[[]]');
        $t->selfTest('[', ']', 0, '[1]2]3]' . __LINE__, $expectedBehind = '2]3]' . __LINE__, '1');
        $t->selfTest('[', ']', 0, '[]2]3]' . __LINE__, $expectedBehind = '2]3]' . __LINE__, '');
        $t->selfTest('[', ']', 0, '123[]2]3]', $expectedBehind = '2]3]', '');
        $t->selfTest('[', ']', 0, '[1[2]3]4]]]][[', $expectedBehind = '4]]]][[', '1[2]3');
        $t->selfTest('[', ']', 0, '[123]4]]]][[', $expectedBehind = '4]]]][[', '123');
        $t->selfTest('[', ']', 0, '[12[3]4]', $expectedBehind = '', '12[3]4');
        $t->selfTest('[1', ']', 0, '[12]______', $expectedBehind = '______', '2');
        $t->selfTest('[1', ']', 0, '[123]______', $expectedBehind = '______', '23');
        $t->selfTest('[1', ']a', 0, '[12]a', $expectedBehind = '', '2');
        $t->selfTest('[1', ']a', 0, '[12]abcd', $expectedBehind = 'bcd', '2');
        $t->selfTest('[1', ']ab', 0, '[123]abcd', $expectedBehind = 'cd', '23');
        $t->selfTest('[', '3]', 0, '__[123]_', $expectedBehind = '_', '12');
        $t->selfTest('[1', '56]', 0, '__[123456]_' . __LINE__, $expectedBehind = '_' . __LINE__, '234');
        $t->selfTest('<d2>', '</d2>', 0, '<d1><d2><d3></d3></d2></d1>', $expectedBehind = '</d1>', '<d3></d3>', true);
        $t->selfTest('[1', '9]', 0, '[123456789]', $expectedBehind = '', '2345678', true);


        $rebuild = self::recursion_example4($silentMode);


        self::bordersBeetweenExample($cf, $silentMode, $rebuild, $source);


        self::content_before_behind_example($silentMode);

        if (!$silentMode) {
            great(__LINE__ . ' Everything OK. No errors :-)');
        }

        return true;
    }

    public function getContent(
        &$RegEx_begin = null,
        &$RegEx_end = null,
        $pos_of_next_search = null,
        &$txt = null,
        $searchMode = null,
        $bugIt = false
    ) {
        if (is_null($txt)) {
            $txt = $this->content;
        }
        $this->update_RegEx_BeginEndPos($RegEx_begin, $RegEx_end, $pos_of_next_search);
        count_null(array($RegEx_begin, $RegEx_end, $pos_of_next_search));
        if (!$searchMode) {
            $searchMode = $this->getSearchMode();
        }
        $p = $this->get_borders_left(
            __LINE__,
            $RegEx_begin,
            $RegEx_end,
            $pos_of_next_search,
            $txt,
            $searchMode
        );
        $count_null = count_null(array($p['begin_begin'], $p['end_begin']), false);
        if ($count_null > 0) {
            if ($count_null == 2 && $this->stopIf_BothBorders_NotExistInContent === true) {
                return false;
            }
            if ($count_null == 2) {
                return substr($txt, $pos_of_next_search);
            }

            if (is_null($p['end_begin'])) {
                if ($this->stopIf_EndBorder_NotExistInContent === true) {
                    return false;
                } else {
                    $p['end_begin'] = strlen($txt);
                }
            }

            if (is_null($p['begin_begin'])) {
                return substr(
                    $txt,
                    $pos_of_next_search,
                    $p['end_begin'] - $pos_of_next_search
                );
            }

        }
        $content = substr($txt, $p['begin_end'], $p['end_begin'] - $p['begin_end']);

        return $content;
    }

    public
    static function selfTest(
        $begin = '[',
        $end = ']',
        $pos_of_next_search = 0,
        $txt = '_[123]_',
        $expectedBehind = null,
        $expectedContent = '123',
        $searchMode = null,
        $bugIt = false
    ) {
        if (false) {
            $bugIt = (basename(__FILE__) == basename(
                    $_SERVER['PHP_SELF']
                ));
        } # // TODO attention: bugIt parameter is ignored here 13-09-20_08-59
        echo '</pre>';
//        var_export(get_func_argNames_of_Method('ContentFinder', 'selfTest')); # it works :)
//        var_export(get_func_argValues_of_Method('ContentFinder', 'selfTest')); # gives back null :(
//        die('13-09-17_11-19');
        $silentMode = true;
        $argNames = self::get_func_argNames_of_Method(__CLASS__, __FUNCTION__);
        if (!$silentMode) {
            foreach ($argNames as $k) {
                echo " $k=" . ${$k} . '  ';
            }
        }

//        var_export($argNames);
        $cf = new SL5_preg_contentFinder($txt);

        if (is_null($searchMode)) {
            $searchMode = $cf->getSearchMode();
        }

        #;<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        # the following code use enables you to use null in argument list for using default value.
        # it needs a defined empty class  array called $selfTest_defaults
        # $selfTest_defaults will be filled in the moment of first empty call.
        # useful, because you could use defaults at only one pace and you could use null for using default values.
        # what you need first time is an empty call of the function. you need to fill the default values.
        $func = func_get_args();
        $behind = self::$selfTest_defaults;
        $t = new SL5_preg_contentFinder($txt);
        if (count(self::$selfTest_defaults) == 0) {
            $temp2 = self::$selfTest_called_from_init_defaults;
            if ($temp2 === true) {
                self::$selfTest_defaults =
                    array($begin, $end, $pos_of_next_search, $txt, $expectedBehind, $expectedContent, $bugIt);

                return true; # this call from constructor was only for init default values.
            } else {

                self::selfTest_init_defaults();
//                $t::selfTest_init_defaults();
            }
        }
        # set args with value null to default value.
        foreach ($func as $k => $arg) {
            if (is_null($arg)) {
                $argNames = $t->get_func_argNames_of_Method(__CLASS__, __FUNCTION__);
                ${$argNames[$k]} = SL5_preg_contentFinder::$selfTest_defaults[$k];
            }
        }
        #;>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
        if ($bugIt) {
            list($findPos['begin_begin'], $findPos['end_begin']) = $t->get_borders_left(
                __LINE__,
                $begin,
                $end,
                $pos_of_next_search,
                $txt,
                $searchMode
            );
        }
        $content = $t->getContent($begin, $end, $pos_of_next_search, $txt, $searchMode, $bugIt);

        if ($bugIt) {
            echo '<font style="font-family: monospace">';
        }

        if ($content != $expectedContent) {
            echo '</pre>';
            bad("$content != $expectedContent");
            if (true) {
                info(__LINE__ . ': ' . $txt);
                echo '<hr>line <u>' . __LINE__ . '</u>: </pre>' . "   b= '<b>" . htmlspecialchars(
                        $begin
                    ) . "</b>' , e= '<b>" . htmlspecialchars($end) . "</b>' , from pos= $pos_of_next_search </pre>";
                echo "<b>" . htmlspecialchars($txt) . '</b> => ' . "<b>" . htmlspecialchars(
                        $content
                    ) . '</b> (' . $findPos['begin_begin'] . '-' . $findPos['end_begin'] . ")";
                echo '<br>' . implode('', range(0, 9)) . implode('', range(0, 9)) . implode('', range(0, 9));
            }
            if ($content != $expectedContent) {

                $findPos = $t->get_borders_left(
                    __LINE__,
                    $begin,
                    $end,
                    0,
                    $txt,
                    $searchMode
                );

                echo '<br>' . __LINE__ . ':' . 'list(' . $findPos['begin_begin'] . ', ' . $findPos['end_begin'] . ') ';
                die("\n" . '<br><b>ERROR \'' . htmlspecialchars($content) . '\' != \'' . htmlspecialchars(
                        $expectedContent
                    ) . "'</b> (expected)");

            }
            if (!is_null($expectedBehind)) {
                $end_end = $t->CACHE_current('end_end');
                $behind = substr($txt, $end_end);
                if (true && $expectedBehind != $behind) {
                    die('<br>' . __LINE__ . ": 13-09-25_16-28");
                }
            }

            # try to find content before
//            $content_before;


        }

        return true;
    } # EndOf selfTest

    private static function get_func_argNames_of_Method($className, $funcName)
    {
        # // TODO this function not really net to be a part of a this class, but this class use it.
        $f = new ReflectionMethod($className, $funcName);
        $result = array();
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;
        }

        return $result;
    }

    public function echo_content_little_excerpt(
        $Content
        ,
        $size1 = 60,
        $size2 = 50
    ) {
        great(
            __LINE__ . ': echo_content_little_excerpt: <b>'
            . htmlspecialchars(substr($Content, 0, $size1))
            . " ...\n   "
            . htmlspecialchars(substr($Content, -$size2)) . '</b>',
            false
        );
    }

    public function nl2br_Echo($fromLine, $file, $s)
    {
        # // TODO this function not really net to be a part of a this class, but this class use it.
        echo '' . $fromLine . ': ' . nl2br($s) . '<br>';
    }

    /**
     * @param $string
     * @return mixed|string
     */
    public static function preg_quote_by_SL5(&$string)
    {
        # btw must have lib: http://regexlib.com/Search.aspx?k=email
        $r = preg_quote($string);
        # preg_quote Quote regular expression characters
    # @link http://php.net/manual/en/function.preg-quote.php
        $r = str_replace('/', '\/', $r);
        $r = preg_replace('/\s+/sm', '\s+', $r);

        return $r;
    }

    private function update_RegEx_BeginEndPos(&$RegEx_begin, &$RegEx_end, &$pos_of_next_search)
    {
        $doOverwriteSetup = false;
        $t = & $this;
        $doOverwriteSetup_OF_pos_of_next_search = $t->doOverwriteSetup_OF_pos_of_next_search;
        if (is_null($RegEx_begin)) {
            if (is_null($t->getRegEx_begin())) {
                die(__LINE__ . ':is_null(BeginRegEx');
            }
            $RegEx_begin = $t->getRegEx_begin();
        } elseif ($doOverwriteSetup || is_null($t->getRegEx_begin())) {
            $t->setRegEx_begin($RegEx_begin);
        }
        if (is_null($RegEx_end)) {
            if (is_null($t->getRegEx_end())) {
                die(__LINE__ . ':is_null(EndRegEx');
            }
            $RegEx_end = $t->getRegEx_end();
        } elseif ($doOverwriteSetup || is_null($t->getRegEx_end())) {
            $t->setRegEx_end($RegEx_end);
        }

        if (is_null($pos_of_next_search)) {
            if (is_null(
                $t->pos_of_next_search
            )
            ) {
                $t->pos_of_next_search = 0;
            } // that's default value. if you want start search from the beginning. 13-10-25_12-38
            $pos_of_next_search = $t->getPosOfNextSearch();
        } elseif ($doOverwriteSetup_OF_pos_of_next_search || is_null($t->getRegEx_begin())) {
            $t->setPosOfNextSearch(
                $pos_of_next_search
            );
        }

        return true;
    }

    private function update_key_findPos_list(&$findPos, &$matchesReturn)
    {
        $count = count($this->findPos_list);
        $key_findPos_list = (is_numeric($count)) ? $count : 0;
        $this->findPos_list[$key_findPos_list] = $findPos;
        if ($this->findPos_list_current_ID === $key_findPos_list) {
            die(__FUNCTION__ . __LINE__ . ': $this->findPos_list_current_ID == $key_findPos_list = ' . $key_findPos_list);
        }

        $this->findPos_list[$key_findPos_list]['matches'] = $matchesReturn;
        $this->setID($key_findPos_list);

        return $key_findPos_list;
    }

    private function setRegEx(&$RegEx_old, &$RegEx_new)
    {
        if (!is_null($RegEx_new) && !is_string($RegEx_new)) {
            die(__FUNCTION__ . __LINE__ . ': !is_string(' . htmlspecialchars($RegEx_new) . ')');
        }
        $RegEx_old = $RegEx_new;

        return true;
    }

}

function get_func_argValues_of_Method($className, $funcName)
{
    # // TODO unused function
    $f = new ReflectionMethod($className, $funcName);
    $result = array();
    foreach ($f->getParameters() as $param) {
        $result[] = $param->value;
    }

    return $result;
    function wwwSearchResults()
    {
        echo '

google search: "php parser yiidecoda +milesj.me"

$code->setBrackets(\'{\', \'}\');
Decoda by milesj http://milesj.me/code/php/decoda
http://bakery.cakephp.org/articles/view/4cb57d06-6a34-4cab-86e7-4eadd13e7814/lang:deu

http://www.yiiframework.com/extension/yiidecoda/#hh1
\'brackets\' => array({, }),

https://gist.github.com/johnkary/5596493

http://php.net/manual/de/function.json-decode.php

        ';
    }
}

function get_func_argNames($funcName)
{
# // TODO unused function
    $f = new ReflectionFunction($funcName);
    $result = array();
    foreach ($f->getParameters() as $param) {
        $result[] = $param->name;
    }

    return $result;
# print_r(get_func_argNames('get_func_argNames'));
}

function bad($message)
{
    echo "<div style='background-color: #ff0000'>:-( $message</div><p>";
    echo '</pre>';
    debug_print_backtrace(); # http://www.php.net/manual/de/function.debug-print-backtrace.php
    #PHP_com
}

function bad_little($message)
{
    echo "<div style='background-color: #ff5555'>:-( $message</div><p>";
    #debug_print_backtrace(); # http://www.php.net/manual/de/function.debug-print-backtrace.php
    #PHP_com
}


function great($message, $htmlSpecialChars = true)
{
    echo "\n";
    if ($htmlSpecialChars) {
        $messageNEW = htmlspecialchars($message);
        if ($messageNEW != '') {
            $message = $messageNEW;
        }
    }
    echo "<div style='background-color: greenyellow'>" . $message . "</div><p>";
    echo "\n";

    return true;
}

function info($message, $color = 'yellow', $htmlSpecialChars = true)
{
    if ($htmlSpecialChars) {
        $messageNEW = htmlspecialchars($message);
        if ($messageNEW != '') {
            $message = $messageNEW;
        }
    }
    echo "<div style='background-color: $color'>"
        . $message . "</div><p>";
}

function count_null($arr, $dieIfIsNull = true)
{
    $countNull = 0;
    if (!is_bool($dieIfIsNull)) {
        die(__FUNCTION__ . __LINE__ . ': !is_bool($dieIfIsNull)');
    }
    if (!is_array($arr)) {
        return is_null($arr);
    }
    foreach ($arr as $n => $v) {
        if (is_null($v)) {
            if ($dieIfIsNull !== true) {
                $countNull++;
            } else {
                echo(__FUNCTION__ . '>' . __LINE__ . ": $n => is_null($v)");
                debug_print_backtrace();
                die(__FUNCTION__ . __LINE__);
            }
        }
    }

    return $countNull;
}
