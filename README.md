# ImpulsePHP Translation

A modular translation system for the ImpulsePHP framework with multilingual support, domain based files and automatic fallback to English.

## Requirements

- PHP 8.2 or higher

## Installation

Use [Composer](https://getcomposer.org/) to install the package:

```bash
composer require impulsephp/translation
```

After installation the service provider `Impulse\Translation\TranslatorProvider` can be registered within your application container if your framework does not handle it automatically.

## Usage

Translations are organized in domain files placed under a `translations` directory. Each locale has its own subdirectory. For example:

```
translations/
    en/
        messages.php
    fr/
        messages.php
```

Each file must return an associative array:

```php
<?php
return [
    'hello' => 'Hello',
    'welcome' => 'Welcome {name}',
];
```

Create a `Translator` instance by providing the locale:

```php
use Impulse\Translation\Translator;

$translator = new Translator('fr');
```

### Basic translation

```php
echo $translator->trans('messages.hello'); // Bonjour
```

### Parameter replacement and fallback

```php
echo $translator->trans('messages.welcome', ['name' => 'John']);
// Welcome John (falls back to English because the key is missing in French)
```

### Changing locale

```php
$translator->setLocale('en');
```

### Namespaces

You can add additional translation paths using namespaces:

```php
$translator->addNamespace('package', '/path/to/package/translations');

$translator->trans('package::messages.key');
```

## Running Tests

Install development dependencies and run PHPUnit:

```bash
composer install
composer test
```

## License

This project is released under the MIT License.
