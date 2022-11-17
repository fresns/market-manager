<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MarketRequireCommand extends Command
{
    protected $signature = 'market:require {unikey} {package_type=plugin}
        {--install_type= : Plugin installation type}';

    protected $description = 'require fresns extensions';

    public function getPluginFromMarket()
    {
        return Http::market()->get('/api/open-source/v2/download', [
            'unikey' => $this->argument('unikey'),
        ]);
    }

    public function isComposerPackage(string $unikey)
    {
        if ($this->isLocalPath($unikey)) {
            return false;
        }

        $parts = explode('/', $unikey);

        return count($parts) == 2;
    }

    public function isLocalPath(string $unikey)
    {
        return file_exists($unikey)
            || str_contains($unikey, '.')
            || str_starts_with($unikey, '/');
    }

    public function getPackageInfo(string $package)
    {
        $process = \Fresns\MarketManager\Support\Process::run("composer info -a -f json $package", false);

        return json_decode($process->getOutput(), true) ?? [];
    }

    public function getDownloadUrlFromPackagist()
    {
        $info = $this->getPackageInfo($this->argument('unikey'));

        $url = $info['dist']['url'] ?? null;
        if (empty($url)) {
            return null;
        }

        return [
            'zipBall' => $url,
            'packageType' => $this->argument('package_type'),
            'extension' => $info['dist']['type'] ?? 'zip',
        ];
    }

    public function getDownloadUrlFromMarket()
    {
        // request market api
        $pluginResponse = $this->getPluginFromMarket();

        if ($pluginResponse->failed()) {
            $this->error('Error: request failed (host or api)');

            return;
        }

        if ($pluginResponse->json('code') !== 0) {
            $this->error($pluginResponse->json('message'));

            return;
        }

        return $pluginResponse;
    }

    public function handle()
    {
        $unikey = $this->argument('unikey');

        $type = $this->option('install_type');

        if (! $type) {
            $type = match (true) {
                str_contains($unikey, '://') => 'url',
                $this->isComposerPackage($unikey) => 'composer',
                $this->isLocalPath($unikey) => 'local',
                default => 'market',
            };
        }

        switch ($type) {
            case 'url':
                // get install file (zip)
                $zipBall = $unikey;
                if (str_contains($zipBall, 'github')) {
                    $tempString = mb_strstr($zipBall, '/zipball', true);
                    $tempString = mb_strstr($tempString, 'repos/');
                    $packageName = str_replace('repos/', '', $tempString);
                    $unikey = Str::studly(basename($packageName));
                } else {
                    $unikey = basename($zipBall);
                }

                $packageType = $this->argument('package_type');
                $extension = 'zip';
                break;
            case 'composer':
                $unikey = Str::studly(basename($unikey));

                $packageInfo = $this->getDownloadUrlFromPackagist();
                if (! $packageInfo) {
                    $this->error('Failed to get extension package download address from packagist');

                    return;
                }

                // get install file (zip)
                $zipBall = $packageInfo['zipBall'];
                $packageType = $packageInfo['packageType'];
                $extension = $packageInfo['extension'];
                break;

            case 'local':
                $mimeType = File::mimeType($unikey);
                $isAvailableLocalPath = str_contains($mimeType, 'zip') || str_contains($mimeType, 'directory');
                if (! $isAvailableLocalPath) {
                    $this->error('Not the correct local path. mimeType: $mimeType');

                    return;
                }

                $packageType = $this->argument('package_type');
                break;

            case 'market':
                $pluginResponse = $this->getDownloadUrlFromMarket();
                if (! $pluginResponse) {
                    $this->error('Failed to get extension package download address from app marketplace');

                    return;
                }

                // get install file (zip)
                $zipBall = $pluginResponse->json('data.zipBall');
                $packageType = $pluginResponse->json('data.packageType');
                $extension = pathinfo($pluginResponse->json('data.zipBall'), PATHINFO_EXTENSION);
                break;
        }

        if ($type == 'local') {
            $filepath = $unikey;
        } else {
            $filename = sprintf('%s-%s.%s', $unikey, date('YmdHis'), $extension);

            // get file
            $zipBallResponse = Http::get($zipBall);
            if ($zipBallResponse->failed()) {
                $this->error('Error: file download failed');

                return;
            }

            File::ensureDirectoryExists($path = config('markets.paths.markets', storage_path('extensions')));

            // zipBall save path
            $filepath = "$path/$filename";

            // save file
            File::put($filepath, $zipBallResponse->body());
        }

        // get install command
        $command = match ($packageType) {
            default => 'plugin:install',
            'theme' => 'theme:install',
        };

        // install command
        $this->call($command, [
            'path' => $filepath,
            '--seed' => true,
        ]);

        // Update the upgrade_code field of the plugin table
        if (! empty($pluginResponse)) {
            Plugin::upgrade([
                'unikey' => $pluginResponse?->json('data.unikey') ?? $unikey,
                'upgrade_code' => $pluginResponse?->json('data.upgradeCode'),
            ]);
        }

        return 0;
    }
}
