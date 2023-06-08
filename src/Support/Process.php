<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace Fresns\MarketManager\Support;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{
    public static function run(string $cmd, mixed $output = null, ?string $cwd = null, array $env = []): SymfonyProcess
    {
        $cwd = $cwd ?? base_path();

        $process = SymfonyProcess::fromShellCommandline($cmd, $cwd);

        $process->setTimeout(900);

        try {
            if ($output !== false) {
                $output = app(OutputInterface::class);
            }
        } catch (\Throwable $e) {
            $output = $output ?? null;
        }

        if ($process->isTty()) {
            $process->setTty(true);
        }

        $envs = [
            'PATH' => rtrim(`echo \$PATH`),
        ] + $env;

        if ($output) {
            $output->write("\n");
            $process->run(
                function ($type, $line) use ($output) {
                    $output->write($line);
                },
                $envs,
            );
        } else {
            $process->run(null, $envs);
        }

        return $process;
    }
}
