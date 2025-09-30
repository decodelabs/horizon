# Horizon

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/horizon?style=flat)](https://packagist.org/packages/decodelabs/horizon)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/horizon.svg?style=flat)](https://packagist.org/packages/decodelabs/horizon)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/horizon.svg?style=flat)](https://packagist.org/packages/decodelabs/horizon)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/horizon/integrate.yml?branch=develop)](https://github.com/decodelabs/horizon/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/horizon?style=flat)](https://packagist.org/packages/decodelabs/horizon)

### Simple HTML view containers

Horizon provides a simple structure for building and rendering HTML views using the <code>DecodeLabs\Tagged</code> interfaces.

---

## Installation

This package requires PHP 8.4 or higher.

Install via Composer:

```bash
composer require decodelabs/horizon
```

## Usage

Programmatically build and render HTML views using the `Page` class:

```php
use DecodeLabs\Horizon\Page;
use DecodeLabs\Tagged as Html;

$page = new Page(function($page) {
    $page->title = 'Hello, World!';
    $page->setMeta('description', 'This is a test page');

    $page->addLink(
        key: 'styles',
        rel: 'stylesheet',
        href: '/styles.css'
    );

    $page->addLink(
        key: 'favicon',
        rel: 'icon',
        href: '/favicon.ico'
    );

    $page->addBodyScript(
        key: 'bundle',
        src: '/bundle-45346534.js'
    );

    $page->bodyTag->addClass('section-home');

    yield Html::{'h1'}('Hello, World!');
    yield Html::{'p'}('This is a test page');
});
```

### Decorators

Horizon provides a simple decorator system for adding additional functionality to your pages.
Decorators must be findable by `Archetype` - either in `DecodeLabs\Horizon\Decorator` or in a namespace registered in the `Archetype` namespace map.

```php
namespace DecodeLabs\Horizon\Decorator;

use DecodeLabs\Horizon\Decorator;
use DecodeLabs\Horizon\Page;

class MyDecorator implements Decorator
{
    public function decorate(
        Page $page, // Require parameter
        string $basePath // Decorator-specific parameter
    ): void {
        $page->title = 'My Decorated Page';

        $page->addBodyScript(
            key: 'analytics',
            src: $basePath.'/analytics.js'
        );
    }
}

$page = new Page(function($page) {
    yield Html::{'h1'}('Hello, World!');
    yield Html::{'p'}('This is a test page');
});

$page->decorate('MyDecorator', '/base/path');
```


### Harvest Transformer

Horizon includes a `Harvest` transformer that can be used to convert a `Page` instance into a PSR-7 HTTP Response during the `Harvest` request lifecycle.

Harvest Transformers should be managed by the HTTP Middleware that needs to transform Responses, for example `Greenleaf` will transparently call `transform()` for the return value of a `Greenleaf Action`.

The result is that you can return a `Page` instance from a `Greenleaf Action` and it will be automatically transformed into a PSR-7 Response.

```php
use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Greenleaf\Action\ByMethodTrait;
use DecodeLabs\Horizon\Harvest;

class MyAction implements Action
{
    use ByMethodTrait;

    public function get(): Page
    {
        return new Page(function() {
            yield 'My content';
        });
    }
}
```

## Licensing

Horizon is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
