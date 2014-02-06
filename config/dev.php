<?php

use Silex\Provider\MonologServiceProvider;

// include the prod configuration
require __DIR__.'/prod.php';

$app['debug'] = true;

$app->register(new MonologServiceProvider(), array(
    'monolog.name' => 'dzwl',
    'monolog.logfile' => __DIR__.'/../var/logs/silex_dev.log',
));
