<?php

declare(strict_types=1);

const SKIP_DIRECTORIES = ['.git', 'vendor', 'node_modules'];
const SKIP_FILES = ['plugin-init.php', 'README.md'];
const BINARY_EXTENSIONS = [
    'png',
    'jpg',
    'jpeg',
    'gif',
    'webp',
    'ico',
    'pdf',
    'zip',
    'gz',
    'tar',
    'woff',
    'woff2',
    'ttf',
    'eot',
    'otf',
    'mp4',
    'mp3',
    'mov',
    'avi',
];

$arguments = parseArguments(array_slice($_SERVER['argv'], 1));

if (isset($arguments['help'])) {
    usage(0);
}

$vendorInput = $arguments['vendor'] ?? $arguments['positional'][0] ?? null;
$pluginInput = $arguments['name'] ?? $arguments['positional'][1] ?? null;
$dryRun = isset($arguments['dry-run']);
$noInteraction = isset($arguments['no-interaction']);

if (! is_string($vendorInput) || trim($vendorInput) === '' || ! is_string($pluginInput) || trim($pluginInput) === '') {
    usage(1);
}

$vendorSlug = slugify($vendorInput);
$vendorNamespace = studly($vendorInput);
$pluginSlug = slugify($pluginInput);
$pluginClass = studly($pluginInput);

if ($vendorSlug === '' || $vendorNamespace === '' || $pluginSlug === '' || $pluginClass === '') {
    fwrite(STDERR, "Unable to derive valid names. Use alphanumeric input.\n");
    exit(1);
}

$namespace = $vendorNamespace.'\\'.$pluginClass;
$namespaceEscaped = str_replace('\\', '\\\\', $namespace);

$replacements = [
    'TemplateVendor\\\\PluginName' => $namespaceEscaped,
    'TemplateVendor\\PluginName' => $namespace,
    'template-vendor/plugin-name' => $vendorSlug.'/'.$pluginSlug,
    'template-vendor-plugin-name' => $vendorSlug.'-'.$pluginSlug,
    'TemplateVendor' => $vendorNamespace,
    'template-vendor' => $vendorSlug,
    'PluginName' => $pluginClass,
    'plugin-name' => $pluginSlug,
];

$rootPath = realpath(__DIR__.'/..');

if ($rootPath === false) {
    fwrite(STDERR, "Unable to resolve project root path.\n");
    exit(1);
}

[$updatedFiles, $totalReplacements] = applyTemplateReplacements($rootPath, $replacements, $dryRun);

if ($dryRun) {
    fwrite(STDOUT, "Dry run complete. {$totalReplacements} replacement(s) in ".count($updatedFiles)." file(s).\n");
} else {
    fwrite(STDOUT, "Plugin initialized. {$totalReplacements} replacement(s) in ".count($updatedFiles)." file(s).\n");
}

if ($updatedFiles !== []) {
    foreach ($updatedFiles as $file) {
        fwrite(STDOUT, "- {$file}\n");
    }
}

if (! $dryRun) {
    $shouldCleanup = confirmAction(
        'Delete bin/plugin-init.php and remove init scripts from composer.json?',
        true,
        $noInteraction
    );

    if ($shouldCleanup) {
        cleanupInitializer($rootPath);
        fwrite(STDOUT, "Initializer command removed from this repo.\n");
    } else {
        fwrite(STDOUT, "Cleanup skipped.\n");
    }
}

exit(0);

function parseArguments(array $argv): array
{
    $parsed = ['positional' => []];

    for ($index = 0; $index < count($argv); $index++) {
        $arg = $argv[$index];

        if ($arg === '--help' || $arg === '-h') {
            $parsed['help'] = true;

            continue;
        }

        if ($arg === '--dry-run') {
            $parsed['dry-run'] = true;

            continue;
        }

        if ($arg === '--no-interaction' || $arg === '-n') {
            $parsed['no-interaction'] = true;

            continue;
        }

        if (str_starts_with($arg, '--vendor=')) {
            $parsed['vendor'] = substr($arg, 9);

            continue;
        }

        if ($arg === '--vendor') {
            $next = $argv[$index + 1] ?? null;
            if (is_string($next) && $next !== '') {
                $parsed['vendor'] = $next;
                $index++;
            }

            continue;
        }

        if (str_starts_with($arg, '--name=')) {
            $parsed['name'] = substr($arg, 7);

            continue;
        }

        if ($arg === '--name') {
            $next = $argv[$index + 1] ?? null;
            if (is_string($next) && $next !== '') {
                $parsed['name'] = $next;
                $index++;
            }

            continue;
        }

        $parsed['positional'][] = $arg;
    }

    return $parsed;
}

function usage(int $exitCode): void
{
    $output = <<<'TXT'
Usage:
  composer template:init -- --vendor=your-vendor --name="Your Plugin"
  php bin/plugin-init.php --vendor=your-vendor --name="Your Plugin"

Options:
  --vendor   Package vendor, for example "acme"
  --name     Plugin name, for example "Cookie Banner"
  --dry-run  Show files that would be changed
  --no-interaction  Never prompt (use defaults)
  --help     Show this help message

TXT;

    fwrite($exitCode === 0 ? STDOUT : STDERR, $output);
    exit($exitCode);
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

    return trim($value, '-');
}

function studly(string $value): string
{
    $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? '';
    $value = ucwords(strtolower(trim($value)));

    return str_replace(' ', '', $value);
}

function applyTemplateReplacements(string $rootPath, array $replacements, bool $dryRun): array
{
    $updatedFiles = [];
    $totalReplacements = 0;

    $directory = new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS);
    $filtered = new RecursiveCallbackFilterIterator(
        $directory,
        static function (SplFileInfo $entry): bool {
            if ($entry->isDir()) {
                return ! in_array($entry->getFilename(), SKIP_DIRECTORIES, true);
            }

            if (in_array($entry->getFilename(), SKIP_FILES, true)) {
                return false;
            }

            $extension = strtolower(pathinfo($entry->getFilename(), PATHINFO_EXTENSION));
            if ($extension !== '' && in_array($extension, BINARY_EXTENSIONS, true)) {
                return false;
            }

            return true;
        }
    );

    $iterator = new RecursiveIteratorIterator($filtered);

    foreach ($iterator as $entry) {
        if (! $entry instanceof SplFileInfo || ! $entry->isFile()) {
            continue;
        }

        $path = $entry->getPathname();
        $contents = file_get_contents($path);

        if (! is_string($contents) || str_contains($contents, "\0")) {
            continue;
        }

        $updated = str_replace(array_keys($replacements), array_values($replacements), $contents, $count);

        if ($count < 1 || ! is_string($updated)) {
            continue;
        }

        $totalReplacements += $count;
        $relative = ltrim(str_replace($rootPath, '', $path), DIRECTORY_SEPARATOR);
        $updatedFiles[] = $relative;

        if (! $dryRun) {
            file_put_contents($path, $updated);
        }
    }

    sort($updatedFiles);

    return [$updatedFiles, $totalReplacements];
}

function confirmAction(string $question, bool $defaultNo, bool $noInteraction): bool
{
    if ($noInteraction || ! isInteractiveStdin()) {
        return ! $defaultNo;
    }

    $suffix = $defaultNo ? ' [y/N]: ' : ' [Y/n]: ';
    fwrite(STDOUT, $question.$suffix);
    $response = trim((string) fgets(STDIN));

    if ($response === '') {
        return ! $defaultNo;
    }

    return in_array(strtolower($response), ['y', 'yes'], true);
}

function isInteractiveStdin(): bool
{
    if (function_exists('stream_isatty')) {
        return stream_isatty(STDIN);
    }

    if (function_exists('posix_isatty')) {
        return posix_isatty(STDIN);
    }

    return false;
}

function cleanupInitializer(string $rootPath): void
{
    $scriptPath = $rootPath.'/bin/plugin-init.php';
    if (is_file($scriptPath)) {
        @unlink($scriptPath);
    }

    $composerPath = $rootPath.'/composer.json';
    if (! is_file($composerPath)) {
        return;
    }

    $composerContents = file_get_contents($composerPath);
    if (! is_string($composerContents)) {
        return;
    }

    $composer = json_decode($composerContents, true);
    if (! is_array($composer) || ! isset($composer['scripts']) || ! is_array($composer['scripts'])) {
        return;
    }

    unset($composer['scripts']['template:init'], $composer['scripts']['plugin:init']);

    if ($composer['scripts'] === []) {
        unset($composer['scripts']);
    }

    $encoded = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (! is_string($encoded)) {
        return;
    }

    file_put_contents($composerPath, $encoded."\n");
}
