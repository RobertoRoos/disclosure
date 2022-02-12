<?php

namespace Command;

require_once __DIR__ . "/../vendor/autoload.php";


use App\Console\TokenUpdater;

/**
 * This script will remove all expired tokens and create new ones up to a given datetime.
 */

$console = new TokenUpdater($argc, $argv);

$code = $console->run();

exit($code);
