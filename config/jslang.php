<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    |
    | Set which locales will be made available as JavaScript files.
    |
    */

    'locales' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Route Name
    |--------------------------------------------------------------------------
    |
    | Set the route name that will be used for the route's registration.
    |
    */

    'route_name' => 'jslang',

    /*
    |--------------------------------------------------------------------------
    | Public Language Directory
    |--------------------------------------------------------------------------
    |
    | Set the directory for generated JavaScript files. The directory location
    | will be in the public directory. It is recommended to put the directory
    | path in .gitignore file.
    |
    */

    'public_lang_dir' => 'lang',

    /*
    |--------------------------------------------------------------------------
    | JavaScript Namespace
    |--------------------------------------------------------------------------
    |
    | Set the namespace for the generated JavaScript objects which contains
    | the localization messages.
    |
    */

    'namespace' => 'langData',

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | Set the duration for caching of the runtime generated contents.
    |
    */

    'cache_duration' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Hash Prefix
    |--------------------------------------------------------------------------
    |
    | Set the prefix for the hash in the filename of the generated JavaScript
    | files.
    |
    */

    'hash_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Singleton Type
    |--------------------------------------------------------------------------
    |
    | Set which type of singleton is to be used for binding JsLang service.
    |
    */

    'scoped_singleton' => false,
];
