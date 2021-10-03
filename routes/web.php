<?php

use Apih\JsLang\JsLang;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get(config('jslang.public_lang_dir') . '/{locale}/{type}{any?}.js', function (string $locale, string $type) {
    $isLocal = app()->isLocal();
    $jsLang = app(JsLang::class);

    if ($isLocal) {
        $content = $jsLang->getContents($locale, $type);
    } else {
        $cacheDuration = config('jslang.cache_duration');
        $cacheKey = "jslang-{$locale}-{$type}";
        $content = cache($cacheKey);

        if (empty($content)) {
            $content = $jsLang->getContents($locale, $type, true);
            cache([$cacheKey => $content], $cacheDuration);
        }
    }

    return response($content)->header('Content-Type', 'application/javascript');
})
->where([
    'locale' => implode('|', config('jslang.locales')),
    'type' => 'short|long|all',
    'any' => '.*',
])
->name(config('jslang.route_name'));
