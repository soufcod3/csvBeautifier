<?php

use CsvBeautifier\CsvBeautifier;

require('vendor/autoload.php');
require_once 'src/CsvBeautifier.php';

$csvBeautifier = new CsvBeautifier();

return $csvBeautifier->createTable($argv[1], $argv[2]);