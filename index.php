<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';
//require 'underconstruction.php';

$container = require __DIR__ . '/app/bootstrap.php';

$container->getService('application')->run();
