<?php

namespace Apih\JsLang;

use Illuminate\Filesystem\Filesystem;

class JsLang
{
    protected Filesystem $filesystem;

    /**
     * Create a new instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
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
        $langPath = resource_path('lang');

        if ($type === 'short') {
            $contents = [];
            $files = $this->filesystem->files("{$langPath}/{$locale}");

            foreach ($files as $file) {
                $contents[basename($file, '.php')] = include $file;
            }
        } elseif ($type === 'long') {
            if ($locale === 'en') {
                $contents = [];
            } else {
                $contents = [];
                $filepath = "{$langPath}/{$locale}.json";

                if ($this->filesystem->exists($filepath)) {
                    $contents = json_decode($this->filesystem->get($filepath), true);
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
        $file = public_path(config('jslang.public_lang_dir') . '/hashes.json');
        $any = '';

        if ($this->filesystem->exists($file)) {
            $hashes = json_decode($this->filesystem->get($file), true);
            $any = '.' . $hashes["{$locale}.{$type}"];
        }

        return route(config('jslang.route_name'), ['locale' => $locale, 'type' => $type, 'any' => $any], $absolute);
    }
}
