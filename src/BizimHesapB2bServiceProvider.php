<?php

namespace TCGunel\BizimHesapB2b;

use Illuminate\Support\ServiceProvider;

class BizimHesapB2bServiceProvider extends ServiceProvider
{
    /**
     * Publishes configuration file.
     *
     * @return  void
     */
    public function boot()
    {
    }

    /**
     * Make config publishment optional by merging the config from the package.
     *
     * @return  void
     */
    public function register()
    {
        $this->app->bind('BizimHesapB2b', function($app) {
            return new BizimHesapB2b();
        });
    }
}
