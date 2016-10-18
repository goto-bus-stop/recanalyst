<?php

namespace RecAnalyst\Laravel;

if (!class_exists(\Illuminate\Support\ServiceProvider::class)) {
    throw new \Exception('Laravel ServiceProvider class not found.');
}

/**
 * Service provider for use with Laravel.
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Configure translations and image resources.
     */
    public function boot()
    {
        $resources = realpath(__DIR__ . '/../../resources');
        $this->loadTranslationsFrom($resources . '/lang', 'recanalyst');

        $this->publishes([
            $resources . '/images' => public_path('vendor/recanalyst'),
        ], 'public');
    }
}
