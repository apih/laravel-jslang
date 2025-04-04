<?php

use Apih\JsLang\JsLang;
use Illuminate\Support\Facades\Route;

Route::get(config('jslang.public_lang_dir') . '/{locale}/{type}{any?}.js', static function (string $locale, string $type) {
    $isLocal = app()->isLocal();
    $jsLang = app(JsLang::class);

    $cacheDuration = $isLocal ? 3 : config('jslang.cache_duration');
    $cacheKey = "jslang-{$locale}-{$type}";
    $content = cache()->remember($cacheKey, $cacheDuration, static fn () => $jsLang->getContents($locale, $type, !$isLocal));

    return response($content)->withHeaders([
        'Cache-Control' => "public, max-age={$cacheDuration}, s-maxage={$cacheDuration}, immutable",
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Etag' => '"' . hash(version_compare(PHP_VERSION, '8.1', '>=') ? 'xxh128' : 'md5', $content) . '"',
    ]);
})
    ->where([
        'locale' => implode('|', config('jslang.locales')),
        'type' => 'short|long|all',
        'any' => '.*',
    ])
    ->name(config('jslang.route_name'));
