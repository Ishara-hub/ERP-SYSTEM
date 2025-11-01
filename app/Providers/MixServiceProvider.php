<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class MixServiceProvider extends ServiceProvider
{
    public function boot()
    {
        URL::macro('mix', function ($path, $manifestDirectory = '') {
            static $manifests = [];

            if (! str_starts_with($path, '/')) {
                $path = "/{$path}";
            }

            if ($manifestDirectory && ! str_starts_with($manifestDirectory, '/')) {
                $manifestDirectory = "/{$manifestDirectory}";
            }

            $rootPath = public_path($manifestDirectory);
            $manifestPath = $rootPath . '/mix-manifest.json';

            if (! isset($manifests[$manifestPath])) {
                if (! file_exists($manifestPath)) {
                    throw new \Exception('Mix manifest not found at: ' . $manifestPath);
                }

                $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
            }

            $manifest = $manifests[$manifestPath];

            if (! isset($manifest[$path])) {
                throw new \Exception("Unable to locate Mix file: {$path}.");
            }

            $assetUrl = env('ASSET_URL', '');
            
            return $assetUrl . $manifestDirectory . $manifest[$path];
        });
    }
}
