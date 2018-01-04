<?php
declare(strict_types=1);

chdir(dirname(__DIR__));

require_once  './vendor/autoload.php';
use Selami\Console\ApplicationFactory;

$config = include './config/global.php';
$container = include './config/container.php';

$cli = ApplicationFactory::makeApplication($container);
$cli->run();
