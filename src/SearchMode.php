<?php
declare(strict_types=1);
namespace SL5\PregContentFinder;
// ++++++++++++++++++++++++++++++++++++++++++++++++++
// + ENUM DEFINITION HIER EINFÜGEN                  +
// ++++++++++++++++++++++++++++++++++++++++++++++++++
enum SearchMode: string
{
    case LAZY_WHITESPACE = 'lazyWhiteSpace';
    case DONT_TOUCH_THIS = 'dontTouchThis';
    # case USE_BACKREFERENCE = 'use_BackReference'; // Ggf. den alten String-Wert beibehalten für Kompatibilität mit alten setSearchMode-Aufrufen
    case USE_BACKREFERENCE = 'use_BackReference_IfExists_()$1${1}'; // Alter String-Wert
    case SIMPLE_STRING_NO_NESTING = 'simpleString';
}

