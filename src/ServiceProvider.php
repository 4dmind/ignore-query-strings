<?php

namespace Fdmind\IgnoreQueryStrings;

use Statamic\Providers\AddonServiceProvider;
use Fdmind\IgnoreQueryStrings\Middleware\FilterOutQueryStrings;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->registerPublishables()
            ->autoPublishConfig();

        $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddleware(
            FilterOutQueryStrings::class
        );
    }

    protected function registerPublishables(): self
    {
        $this->publishes([
            __DIR__.'/../config/ignore-query-strings.php' => config_path('ignore-query-strings.php'),
        ], 'fdm-ignore-query-strings');

        return $this;
    }

    protected function autoPublishConfig(): self
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'fdm-ignore-query-strings',
            ]);
        });

        return $this;
    }
}
