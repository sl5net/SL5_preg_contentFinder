<?php
// /app/tests/PHPUnit/F2/run_PCF.php
require_once __DIR__ . 'PregContentFinder.php'; // Correct path to /app/src/PregContentFinder.php

use SL5\PregContentFinder\PregContentFinder;

$p = new PregContentFinder('bob');
echo $p->helloWorld();
