<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class GenerateNativeBrandAssetsCommand extends Command
{
    protected $signature = 'agora:generate-native-brand-assets {--force : Regenerate PNGs even when they already exist}';

    protected $description = 'Generate NativePHP launcher icon and splash assets for Android';

    public function handle(): int
    {
        if (! $this->option('force') && $this->assetsExist()) {
            $this->line('NativePHP brand assets already exist. Use --force to regenerate.');
        } elseif (! $this->generateAssets()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('NativePHP brand assets are ready. Rebuild the Android app to apply them:');
        $this->line('  .\\scripts\\native-run-android.cmd');

        return self::SUCCESS;
    }

    private function assetsExist(): bool
    {
        return File::exists(base_path('nativephp/android/app/src/main/res/drawable-mdpi/splash.png'))
            && File::exists(base_path('nativephp/android/app/src/main/res/mipmap-mdpi/ic_launcher.png'));
    }

    private function generateAssets(): bool
    {
        $npm = $this->resolveNpmExecutable();

        if ($npm === null) {
            $this->error('npm was not found. Install Node.js, run npm install, then retry.');

            return false;
        }

        $process = new Process([$npm, 'run', 'build:native-brand'], base_path());
        $process->setTimeout(300);
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->newLine();
            $this->error('Native brand asset generation failed.');

            return false;
        }

        return true;
    }

    private function resolveNpmExecutable(): ?string
    {
        $candidates = ['npm', 'npm.cmd'];

        if (PHP_OS_FAMILY === 'Windows') {
            $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
            $candidates[] = $programFiles.'\\nodejs\\npm.cmd';
        }

        foreach ($candidates as $candidate) {
            $process = new Process([$candidate, '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                return $candidate;
            }
        }

        return null;
    }
}
