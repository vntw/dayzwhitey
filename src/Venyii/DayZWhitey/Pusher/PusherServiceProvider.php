<?php

namespace Venyii\DayZWhitey\Pusher;

use Silex\Application;
use Silex\ServiceProviderInterface;

class PusherServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['pusher.api_key']=null;
        $app['pusher.api_secret']=null;
        $app['pusher.api_id']=null;

        $app['pusher'] = $app->share(function ($app) {
            return new \Pusher($app['pusher.api_key'], $app['pusher.api_secret'], $app['pusher.api_id'], false, 'https://api.pusherapp.com', 443);
        });
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }

}
