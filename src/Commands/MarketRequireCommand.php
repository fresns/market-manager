<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
    protected $signature = 'market:require {fskey} {package_type=plugin}
        {--install_type= : Plugin installation type}';

    protected $description = 'require fresns extensions';

    public function getPluginFromMarket()
    {
        return Http::market()->get('/api/open-source/v2/download', [
            'fskey' => $this->argument('fskey'),
        ]);
    }

    public function isComposerPackage(string $fskey)
    {
        if ($this->isLocalPath($fskey)) {
            return false;
        }

        $parts = explode('/', $fskey);

        return count($parts) == 2;
    }

    public function isLocalPath(string $fskey)
    {
        return file_exists($fskey)
            || str_contains($fskey, '.')
            || str_starts_with($fskey, '/');
    }

    public function getPackageInfo(string $package)
    {
        $process = \Fresns\MarketManager\Support\Process::run("composer info -a -f json $package", false);

        return json_decode($process->getOutput(), true) ?? [];
    }

    public function getDownloadUrlFromPackagist()
    {
        $info = $this->getPackageInfo($this->argument('fskey'));

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
            $this->error('Error: request failed (host or api)'."\n\n".$pluginResponse->body());

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
        $fskey = $this->argument('fskey');

        $type = $this->option('install_type');

        if (! $type) {
            $type = match (true) {
                str_contains($fskey, '://') => 'url',
                $this->isComposerPackage($fskey) => 'composer',
                $this->isLocalPath($fskey) => 'local',
                default => 'market',
            };
        }

        switch ($type) {
            case 'url':
                // get install file (zip)
                $zipBall = $fskey;
                if (str_contains($zipBall, 'github')) {
                    $tempString = mb_strstr($zipBall, '/zipball', true);
                    $tempString = mb_strstr($tempString, 'repos/');
                    $packageName = str_replace('repos/', '', $tempString);
                    $fskey = Str::studly(basename($packageName));
                } else {
                    $fskey = basename($zipBall);
                }

                $packageType = $this->argument('package_type');
                $extension = 'zip';
                break;
            case 'composer':
                $fskey = Str::studly(basename($fskey));

                $packageInfo = $this->getDownloadUrlFromPackagist();
                if (! $packageInfo) {
                    $this->error('Failed to get extension package download address from packagist');

                    return Command::FAILURE;
                }

                // get install file (zip)
                $zipBall = $packageInfo['zipBall'];
                $packageType = $packageInfo['packageType'];
                $extension = $packageInfo['extension'];
                break;

            case 'local':
                $mimeType = File::mimeType($fskey);
                $isAvailableLocalPath = str_contains($mimeType, 'zip') || str_contains($mimeType, 'directory');
                if (! $isAvailableLocalPath) {
                    $this->error('Not the correct local path. mimeType: $mimeType');

                    return Command::FAILURE;
                }

                $packageType = $this->argument('package_type');
                break;

            case 'market':
                $pluginResponse = $this->getDownloadUrlFromMarket();
                if (! $pluginResponse) {
                    return Command::FAILURE;
                }

                // get install file (zip)
                $zipBall = $pluginResponse->json('data.zipBall');
                $packageType = $pluginResponse->json('data.packageType');
                $extension = pathinfo($pluginResponse->json('data.zipBall'), PATHINFO_EXTENSION);
                break;
        }

        if ($type == 'local') {
            $filepath = $fskey;
        } else {
            $filename = sprintf('%s-%s.%s', $fskey, date('YmdHis'), $extension);

            // get file
            $zipBallResponse = Http::get($zipBall);
            if ($zipBallResponse->failed()) {
                $this->error('Error: file download failed');

                return Command::FAILURE;
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
        $exitCode = $this->call($command, [
            'path' => $filepath,
            '--seed' => true,
        ]);

        if ($exitCode != 0) {
            return $exitCode;
        }

        // Update the upgrade_code field of the plugin table
        if (! empty($pluginResponse)) {
            Plugin::upgrade([
                'fskey' => $pluginResponse?->json('data.fskey') ?? $fskey,
                'upgrade_code' => $pluginResponse?->json('data.upgradeCode'),
            ]);
        }

        return $exitCode;
    }
}
