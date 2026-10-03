<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register only when masbug/flysystem-google-drive-ext is installed (ADR-014).
        $adapterClass = 'Masbug\\Flysystem\\GoogleDriveAdapter';
        $clientClass = 'Google\\Client';
        $driveClass = 'Google\\Service\\Drive';

        if (! class_exists($adapterClass) || ! class_exists($clientClass) || ! class_exists($driveClass)) {
            return;
        }

        Storage::extend('google', function ($app, array $config) use ($adapterClass, $clientClass, $driveClass): FilesystemAdapter {
            $client = new $clientClass;

            if (
                ! is_callable([$client, 'setClientId'])
                || ! is_callable([$client, 'setClientSecret'])
                || ! is_callable([$client, 'refreshToken'])
            ) {
                throw new RuntimeException('Google Client is missing required OAuth methods.');
            }

            call_user_func([$client, 'setClientId'], $config['clientId'] ?? '');
            call_user_func([$client, 'setClientSecret'], $config['clientSecret'] ?? '');
            call_user_func([$client, 'refreshToken'], $config['refreshToken'] ?? '');

            $service = new $driveClass($client);
            $adapter = new $adapterClass($service, $config['folder'] ?? null);
            $driver = new Filesystem($adapter);

            return new FilesystemAdapter($driver, $adapter, $config);
        });
    }
}
