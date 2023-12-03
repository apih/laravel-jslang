<?php

namespace Apih\JsLang;

use Illuminate\Filesystem\Filesystem;

class JsLang
{
    protected Filesystem $filesystem;
    protected array $paths;

    /**
     * Create a new instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        $paths = [
            base_path('vendor/laravel/framework/src/Illuminate/Translation/lang'),
            app()->langPath(),
        ];

        if (version_compare(app()->version(), '10', '<')) {
            array_shift($paths);
        }

        $this->filesystem = $filesystem;
        $this->paths = $paths;
    }

    /**
     * Get the contents of the localization messages by locale and type.
     *
     * @param  string  $locale
     * @param  string  $type
     * @param  bool  $minify
     * @return string
     */
    public function getContents(string $locale, string $type, bool $minify = false)
    {
        $namespace = trim(config('jslang.namespace'), '.') . ".{$locale}.{$type}";

        if ($type === 'short') {
            $contents = [];

            foreach ($this->paths as $path) {
                $directory = "{$path}/{$locale}";

                if ($this->filesystem->missing($directory)) {
                    continue;
                }

                $files = $this->filesystem->files($directory);

                foreach ($files as $file) {
                    $key = basename($file, '.php');

                    if (!array_key_exists($key, $contents)) {
                        $contents[$key] = [];
                    }

                    $contents[$key] = array_merge($contents[$key], include $file);
                }
            }
        } elseif ($type === 'long') {
            $contents = [];

            if ($locale !== 'en') {
                foreach ($this->paths as $path) {
                    $filepath = "{$path}/{$locale}.json";

                    if ($this->filesystem->exists($filepath)) {
                        $contents = array_merge($contents, json_decode($this->filesystem->get($filepath), true));
                    }

                    foreach ($contents as $key => $value) {
                        if ($key === $value) {
                            unset($contents[$key]);
                        }
                    }
                }
            }
        } elseif ($type === 'all') {
            return $this->getContents($locale, 'short', $minify) . PHP_EOL . $this->getContents($locale, 'long', $minify);
        }

        $keys = explode('.', $namespace);
        $partialNamespace = '';
        $prefix = '';

        foreach ($keys as $key) {
            $partialNamespace = trim("{$partialNamespace}.{$key}.", '.');

            if ($partialNamespace === $namespace) {
                $prefix .= "window.{$partialNamespace} = ";
            } else {
                $prefix .= "window.{$partialNamespace} = window.{$partialNamespace} || {}; ";
            }
        }

        $prefix = $minify ? str_replace(' ', '', $prefix) : $prefix;
        $contents = $prefix . json_encode($contents, ($minify ? 0 : JSON_PRETTY_PRINT) | JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';';

        return $contents;
    }

    /**
     * Get the URL for the JavaScript language localization file by locale and type.
     *
     * @param  string  $locale
     * @param  string  $type
     * @param  bool  $absolute
     * @return string
     */
    public function getUrl(string $locale, string $type, bool $absolute = true)
    {
        $file = $this->publicLangPath('hashes.json');
        $any = '';

        if ($this->filesystem->exists($file)) {
            $hashes = json_decode($this->filesystem->get($file), true);
            $any = '.' . $hashes["{$locale}.{$type}"];
        }

        return route(config('jslang.route_name'), ['locale' => $locale, 'type' => $type, 'any' => $any], $absolute);
    }

    /**
     * Get the path to the public language directory.
     *
     * @param  string  $path
     * @return string
     */
    public function publicLangPath($path = '')
    {
        return public_path(config('jslang.public_lang_dir') . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }
}
