<?php

use Venyii\DayZWhitey\Db;
use Venyii\DayZWhitey\Config;
use Venyii\DayZWhitey\Entry\Manager;
use Venyii\DayZWhitey\DataTables\DataSource;
use Venyii\DayZWhitey\DataTables\Type;
use Venyii\DayZWhitey\Pusher\PusherServiceProvider;

$app['twig.path'] = __DIR__ . '/../templates';
$app['twig.options'] = array('cache' => __DIR__ . '/../var/cache/twig');

$app['cfg'] = $app->share(function () {
    $cfgFile = __DIR__ . '/../config.ini';

    if (!file_exists($cfgFile)) {
        throw new \Exception('Please set up the config.ini');
    }

    $cfg = new Config();
    $cfg->fromIni(new \SplFileInfo($cfgFile));

    return $cfg;
});

$app['db'] = $app->share(function () use ($app) {
    return new Db(
        sprintf('mysql:dbname=%s;host=%s', $app['cfg']['db']['dbname'], $app['cfg']['db']['host']), $app['cfg']['db']['user'], $app['cfg']['db']['passwd'], array(
        Db::ATTR_ERRMODE => Db::ERRMODE_EXCEPTION
    ));
});

$app['dataSource'] = $app->share(function () use ($app) {
    $dataSource = new DataSource($app);
    $dataSource->registerType(new Type\TypeWhitelist());
    $dataSource->registerType(new Type\TypeWhitelistLog());

    return $dataSource;
});

$app['entryManager'] = $app->share(function () use ($app) {
    return new Manager($app);
});

$app->register(new PusherServiceProvider(), array(
    'pusher.api_key' => $app['cfg']['pusher']['api_key'],
    'pusher.api_secret' => $app['cfg']['pusher']['api_secret'],
    'pusher.api_id' => $app['cfg']['pusher']['api_id']
));
