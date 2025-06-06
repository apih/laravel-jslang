# Laravel JS Lang

[![Latest Version on Packagist](https://img.shields.io/packagist/v/apih/laravel-jslang.svg?style=flat-square)](https://packagist.org/packages/apih/laravel-jslang)
[![Total Downloads](https://img.shields.io/packagist/dt/apih/laravel-jslang.svg?style=flat-square)](https://packagist.org/packages/apih/laravel-jslang)
[![License](https://img.shields.io/packagist/l/apih/laravel-jslang?style=flat-square)](https://packagist.org/packages/apih/laravel-jslang)

This package provides the ability to use all language localization messages from your Laravel-based app in browser's JavaScript. It also comes with a simple JavaScript Translator library that has some methods to interact with the messages.

## Requirements

- PHP: `^8.0`
- Laravel: `^8.0|^9.0|^10.0|^11.0|^12.0`

## Installation

You can install the package via Composer:

```bash
composer require apih/laravel-jslang
```

The `Apih\JsLang\JsLangServiceProvider` class is auto-discovered and registered by default.

If you want to register it yourself, add the service provider in `config/app.php`:

```php
'providers' => [
    /*
     * Package Service Providers...
     */
    Apih\JsLang\JsLangServiceProvider::class,
],
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Apih\JsLang\JsLangServiceProvider" --tag="jslang-config"
```

You can publish the JavaScript Translator library with:
```bash
php artisan vendor:publish --provider="Apih\JsLang\JsLangServiceProvider" --tag="jslang-script"
```

## Usage

### Testing

To test whether the installation is successful, you can run the following code:

```php
use Apih\JsLang\JsLang;

$jsLang = app(JsLang::class);
echo $jsLang->getUrl('en', 'all');
echo $jsLang->getContents('en', 'all');
```

It should output the URL and the JavaScript code for language localization messages.

### Message Types

There are three message types:
- `short` - localization messages defined in `PHP` files
- `long` - localization messages defined in `JSON` files
- `all` - combination of both `short` and `long` types

### JavaScript Files

By opening the URL generated by `getUrl()`, you will get the respective contents based on provided locale and type. By default, the contents are generated on runtime, unless you generate all the files beforehand, using the provided Artisan command.

### Artisan Commands

You can generate all JavaScript files with:

```bash
php artisan jslang:generate
```

By default, `crc32` hashing algorithm is used to generate the contents' hash included in the filename. You can change the algorithm by using `--hash-algo` option.  The following example uses `sha256` hashing algorithm and only takes first 12 characters from the generated hash:

```bash
php artisan jslang:generate --hash-algo=sha256,12
```

Generated files are placed in `public/lang` directory, based on the default config. It is recommended to put the directory in `.gitignore` file.

You can clear all generated files with:

```bash
php artisan jslang:clear
```

### Bootstrapping for Front-end

In order to use in front-end, several steps have to be taken. First, edit `resources/js/app.js` to add the JavaScript translator:

```js
window.Lang = require('./lang');
```

Then, use `npm run dev` or `npm run prod` to compile `resources/js/app.js`.

After that, you need to configure it in your page:


```blade
<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ app(\Apih\JsLang\JsLang::class)->getUrl('en', 'all') }}"></script>
<script>
    Lang.setLocale('en');
    Lang.setMessages(window.langData);
</script>
```

To test it, you can open the page in the browser and run the following code in the console to test it:

```js
Lang.get('validation.required', { attribute: 'email' });
// Output: The email field is required.

Lang.choice('[0] No user|[1] 1 user|[2,*] :count users', 0);
// Output: No user

Lang.choice('[0] No user|[1] 1 user|[2,*] :count users', 1);
// Output: 1 user

Lang.choice('[0] No user|[1] 1 user|[2,*] :count users', 2);
// Output: 2 users
```

## Security Vulnerabilities

If you discover any security related issues, please email <hafizuddin_83@yahoo.com> instead of using the issue tracker. Please prefix the subject with `Laravel JS Lang:`.

## Credits

- [Mohd Hafizuddin M Marzuki](https://github.com/apih)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
