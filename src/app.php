<?php

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;

$app = new Silex\Application();

$app->register(new SessionServiceProvider());
$app->register(new TwigServiceProvider());

return $app;
