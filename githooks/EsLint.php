<?php

declare(strict_types=1);

namespace GitHooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Hook\Action;
use Illuminate\Support\Facades\Request;
use RuntimeException;
use SebastianFeldmann\Git\Repository;

final class EsLint implements Action
{
    public function execute(Config $config, IO $io, Repository $repository, Config\Action $action): void
    {
        $stagedFiles = $repository->getIndexOperator()->getStagedFiles();

        $filesToLint = [];

        foreach ($stagedFiles as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            if (in_array($ext, ['vue', 'ts', 'js', 'tsx', 'jsx'], true)) {
                $filesToLint[] = $file;
            }
        }

        if ($filesToLint === []) {
            return;
        }

        $files = implode(' ', array_map(escapeshellarg(...), $filesToLint));
        $output = [];
        $exitCode = 0;

        exec($this->resolveEslintCommand() . ' --fix ' . $files, $output, $exitCode);

        foreach ($filesToLint as $file) {
            shell_exec('git add ' . escapeshellarg($file));
        }

        if ($exitCode !== 0) {
            throw new RuntimeException("ESLint found errors:\n" . implode("\n", $output));
        }
    }

    /**
     * Build a `node /path/to/eslint.js` command that does not rely on `npx`
     * or on `node` being on the inherited PATH (which is empty when committing
     * from GUIs that bypass the user's shell init — e.g. VS Code under WSL).
     */
    private function resolveEslintCommand(): string
    {
        $eslintScript = 'node_modules' . DIRECTORY_SEPARATOR . 'eslint' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'eslint.js';

        throw_unless(is_file($eslintScript), RuntimeException::class, 'ESLint not installed. Run `npm install` first.');

        return escapeshellarg($this->findNodeBinary()) . ' ' . escapeshellarg($eslintScript);
    }

    private function findNodeBinary(): string
    {
        $fromPath = mb_trim((string) shell_exec('command -v node 2>/dev/null'));

        if ($fromPath !== '' && is_executable($fromPath)) {
            return $fromPath;
        }

        $candidates = [];
        $home = getenv('HOME') ?: (Request::server('HOME') ?? '');

        if ($home !== '') {
            $nvmDir = getenv('NVM_DIR') ?: $home . '/.nvm';
            $nvmNodes = glob($nvmDir . '/versions/node/*/bin/node') ?: [];

            usort($nvmNodes, static function (string $a, string $b): int {
                preg_match('#/v?([\d.]+)/bin/node$#', $a, $ma);
                preg_match('#/v?([\d.]+)/bin/node$#', $b, $mb);

                return version_compare($mb[1] ?? '0', $ma[1] ?? '0');
            });

            $candidates = array_merge($candidates, $nvmNodes);
            $candidates[] = $home . '/.volta/bin/node';
        }

        $candidates = array_merge($candidates, [
            '/opt/homebrew/bin/node',
            '/usr/local/bin/node',
            '/usr/bin/node',
        ]);

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'Could not find node binary. Searched PATH, $NVM_DIR (or ~/.nvm), ~/.volta, /opt/homebrew, /usr/local/bin, /usr/bin.'
        );
    }
}
