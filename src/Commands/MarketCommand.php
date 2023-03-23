<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class MarketCommand extends Command
{
    protected $signature = 'market';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all available commands';

    /**
     * @var string
     */
    public static $logo = <<<LOGO
    __  ___           __        __     __  ___                                 
   /  |/  /___ ______/ /_____  / /_   /  |/  /___ _____  ____ _____ ____  _____
  / /|_/ / __ `/ ___/ //_/ _ \/ __/  / /|_/ / __ `/ __ \/ __ `/ __ `/ _ \/ ___/
 / /  / / /_/ / /  / ,< /  __/ /_   / /  / / /_/ / / / / /_/ / /_/ /  __/ /    
/_/  /_/\__,_/_/  /_/|_|\___/\__/  /_/  /_/\__,_/_/ /_/\__,_/\__, /\___/_/     
                                                            /____/             
LOGO;

    public function handle(): void
    {
        $this->info(static::$logo);

        $this->comment('');
        $this->comment('Available commands:');

        $this->comment('');
        $this->comment('market');
        $this->listAdminCommands();
    }

    protected function listAdminCommands(): void
    {
        $commands = collect(Artisan::all())->mapWithKeys(function ($command, $key) {
            if (Str::startsWith($key, 'market')) {
                return [$key => $command];
            }

            return [];
        })->toArray();

        $width = $this->getColumnWidth($commands);

        /** @var Command $command */
        foreach ($commands as $command) {
            $this->info(sprintf(" %-{$width}s %s", $command->getName(), $command->getDescription()));
        }
    }

    private function getColumnWidth(array $commands): int
    {
        $widths = [];

        foreach ($commands as $command) {
            $widths[] = static::strlen($command->getName());
            foreach ($command->getAliases() as $alias) {
                $widths[] = static::strlen($alias);
            }
        }

        return $widths ? max($widths) + 2 : 0;
    }

    /**
     * Returns the length of a string, using mb_strwidth if it is available.
     *
     * @param  string  $string  The string to check its length
     * @return int The length of the string
     */
    public static function strlen($string): int
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }
}
