# Laravel json field cast

[![Packagist License](https://img.shields.io/packagist/l/yaroslawww/laravel-restricted-access?color=%234dc71f)](https://github.com/yaroslawww/laravel-restricted-access/blob/main/LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/yaroslawww/laravel-restricted-access)](https://packagist.org/packages/yaroslawww/laravel-restricted-access)
[![Total Downloads](https://img.shields.io/packagist/dt/yaroslawww/laravel-restricted-access)](https://packagist.org/packages/yaroslawww/laravel-restricted-access)
[![Build Status](https://scrutinizer-ci.com/g/yaroslawww/laravel-restricted-access/badges/build.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/laravel-restricted-access/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/yaroslawww/laravel-restricted-access/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/laravel-restricted-access/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yaroslawww/laravel-restricted-access/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/yaroslawww/laravel-restricted-access/?branch=main)

Restrict access for pages, with pin code email and name.

## Installation

Install the package via composer:

```bash
composer require yaroslawww/laravel-restricted-access
```

Optionally you can publish the config file with:

```bash
php artisan vendor:publish --provider="LinkRestrictedAccess\ServiceProvider" --tag="config"
```

Run migrations:

```shell
php artisan migrate
```

## Usage

Use trait `HasRestrictedLink` for model

```php
class File extends Model 
{
    use \LinkRestrictedAccess\Models\HasRestrictedLink;
}
```

Then you can create link

```php
/** @var RestrictedLink $shareLink */
$shareLink = $file->restrictedLinks()->make([
    'name'         => $this->input('name'),
    'pin'          => $this->input('pin'),
    'check_pin'    => $this->boolean('check_pin'),
    'check_name'   => $this->boolean('check_name'),
    'check_email'  => $this->boolean('check_email'),
]);

$user = $this->user();
if ($user) {
    $shareLink->meta->toMorph('creator', $user);
}

$shareLink->save();
```

Example check verification.

```php
if($shareUuid = $request->string('share')) {
    $sharedLink = RestrictedLink::query()->where('uuid', $shareUuid)->firstOrFail();

    if (!$sharedLink->needVerification()) {
        if (!$sharedLink->verifiedOpenActionFromCookie($request)) {
            return // display verification view
        }
    }

    return $sharedLink->linkable;
}
```

To verify access you can use routes `restricted-access-link.check-pin` and `restricted-access-link.open-document`

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
