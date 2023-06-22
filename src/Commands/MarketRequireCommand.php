<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Fresns\MarketManager\Models\Plugin;
use Fresns\MarketManager\Support\Zip;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MarketRequireCommand extends Command
{
    protected $signature = 'market:require {fskey}
        {--install_type= : Plugin installation type}';

    protected $description = 'require fresns extensions';

    protected $packageType = null;

    public function isComposerPackage(string $fskey)
    {
        if ($this->isLocalPath($fskey)) {
            return false;
        }

        if (str_contains($fskey, 'composer')) {
            return true;
        }

        $parts = explode('/', $fskey);

        return count($parts) == 2;
    }

    public function getPluginPath($fskey)
    {
        $extensionPath = config('markets.paths.base');

        return sprintf('%s/plugins/%s', rtrim($extensionPath), ltrim($fskey, '/'));
    }

    public function getThemePath($fskey)
    {
        $extensionPath = config('markets.paths.base');

        return sprintf('%s/themes/%s', rtrim($extensionPath), ltrim($fskey, '/'));
    }

    public function getPluginDirectory($fskey)
    {
        return match ($this->packageType) {
            default => $fskey,
            'plugin' => $this->getPluginPath($fskey),
            'theme' => $this->getThemePath($fskey),
        };
    }

    public function isLocalPath(string $fskey)
    {
        if (file_exists($this->getPluginPath($fskey))) {
            $this->packageType = 'plugin';

            return true;
        }

        if (file_exists($this->getThemePath($fskey))) {
            $this->packageType = 'theme';

            return true;
        }

        return file_exists($fskey)
            || str_contains($fskey, '.')
            || str_starts_with($fskey, '/');
    }

    public function getPackageInfo(string $package)
    {
        $process = \Fresns\MarketManager\Support\Process::run("composer info -a -f json $package", false);

        return json_decode($process->getOutput(), true) ?? [];
    }

    public function getDownloadUrlFromPackagist($fskey)
    {
        $info = $this->getPackageInfo($fskey);

        $url = $info['dist']['url'] ?? null;
        if (empty($url)) {
            return null;
        }

        return [
            'zipBall' => $url,
            'fileExtension' => $info['dist']['type'] ?? 'zip',
        ];
    }

    public function getDownloadUrlFromMarket()
    {
        // request market api
        $plugin = Plugin::withTrashed()->where('fskey', $this->argument('fskey'))->first();
        if ($plugin) {
            $pluginResponse = Http::market()->get('/api/open-source/v2/upgrade', [
                'fskey' => $plugin->fskey,
                'version' => $plugin->version,
                'upgradeCode' => $plugin->upgrade_code,
            ]);
        } else {
            $pluginResponse = Http::market()->get('/api/open-source/v2/download', [
                'fskey' => $this->argument('fskey'),
            ]);
        }

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
                if (str_contains($zipBall, 'api.github.com')) {
                    $tempString = mb_strstr($zipBall, '/zipball', true);
                    $tempString = mb_strstr($tempString, 'repos/');
                    $packageName = str_replace('repos/', '', $tempString);

                    $fskey = Str::studly(basename($packageName));
                } elseif (str_contains($zipBall, 'github.com')) {
                    $zipBallPathInfo = parse_url($zipBall);

                    $zipBallData = explode('/', $zipBallPathInfo['path']);
                    $zipBallData = array_values(array_filter($zipBallData));
                    $packageName = $zipBallData[1] ?? null;
                    if (! $packageName) {
                        $this->error("Error: github zip link parse failed, url is: $zipBall");

                        return Command::FAILURE;
                    }

                    $fskey = Str::studly(basename($packageName));
                } else {
                    $fskey = basename($zipBall);
                }

                $fileExtension = 'zip';
                break;

            case 'composer':
                $packageName = Str::studly(basename($fskey));

                if (str_contains($fskey, 'composer require')) {
                    $fskey = str_replace('composer require', '', $fskey);
                }

                $packageInfo = $this->getDownloadUrlFromPackagist($fskey);

                if (! $packageInfo) {
                    $this->error('Failed to get extension package download address from packagist, fskey is: $fskey');

                    return Command::FAILURE;
                }

                // get install file (zip)
                $fskey = $packageName;
                $zipBall = $packageInfo['zipBall'];
                $fileExtension = $packageInfo['fileExtension'];
                break;

            case 'local':
                if (! $this->isLocalPath($fskey)) {
                    $this->error("Not the correct plugin. pluginFsKey: $fskey");

                    return Command::FAILURE;
                }

                $pluginDirectory = $this->getPluginDirectory($fskey);
                if (! file_exists($pluginDirectory)) {
                    $this->error("Not the correct local path. pluginDirectory: $pluginDirectory");

                    return Command::FAILURE;
                }

                $mimeType = File::mimeType($pluginDirectory);
                $isAvailableLocalPath = str_contains($mimeType, 'zip') || str_contains($mimeType, 'directory');
                if (! $isAvailableLocalPath) {
                    $this->error('Not the correct local path. mimeType: $mimeType');

                    return Command::FAILURE;
                }
                $fskey = $pluginDirectory;
                break;

            case 'market':
                $pluginResponse = $this->getDownloadUrlFromMarket();
                if (! $pluginResponse) {
                    return Command::FAILURE;
                }

                // get install file (zip)
                $zipBall = $pluginResponse->json('data.zipBall');
                $fileExtension = pathinfo(parse_url($pluginResponse->json('data.zipBall'))['path'] ?? '', PATHINFO_EXTENSION);
                break;
        }

        if ($type == 'local') {
            $filepath = $fskey;
        } else {
            $extensionPath = storage_path('extensions');

            File::ensureDirectoryExists($path = config('markets.paths.markets', $extensionPath));

            if (! is_file($extensionPath.'/.gitignore')) {
                file_put_contents($extensionPath.'/.gitignore', '*'.PHP_EOL.'!.gitignore');
            }

            $filename = sprintf('%s-%s.%s', $fskey, date('YmdHis'), $fileExtension);

            // get file
            $zipBallResponse = Http::get($zipBall);
            if ($zipBallResponse->failed()) {
                $this->error("Error: file download failed, url is: $zipBall");

                return Command::FAILURE;
            }

            // zipBall save path
            $filepath = "$path/$filename";

            // save file
            File::put($filepath, $zipBallResponse->body());
        }

        try {
            // unzip packaeg and get install command
            $zip = new Zip();
            $tmpDirPath = $zip->unpack($filepath);
        } catch (\Throwable $e) {
            $this->error("Error: file unzip failed, reason: {$e->getMessage()}, filepath is: $filepath");

            return Command::FAILURE;
        }

        $pluginJsonPath = "{$tmpDirPath}/plugin.json";
        $themeJsonPath = "{$tmpDirPath}/theme.json";
        if (is_file($pluginJsonPath)) {
            $this->packageType = 'plugin';
        } elseif (is_file($themeJsonPath)) {
            $this->packageType = 'theme';
        } else {
            $this->packageType = null;
        }

        if (! $this->packageType) {
            $this->error("Error: unknown packageType, $filepath unzip to $tmpDirPath fail");

            return Command::FAILURE;
        }

        // get install command
        $command = match ($this->packageType) {
            default => 'plugin:install',
            'theme' => 'theme:install',
        };

        // install command
        $exitCode = $this->call($command, [
            'path' => $tmpDirPath,
            '--seed' => true,
        ]);

        if ($exitCode != 0) {
            return $exitCode;
        }

        // Update the upgrade_code field of the plugin table
        if ($pluginResponse) {
            Plugin::handleAppData($pluginResponse->json('data'));
        }

        return $exitCode;
    }
}
