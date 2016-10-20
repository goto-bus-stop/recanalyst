# RecAnalyst

[![Packagist](https://img.shields.io/packagist/v/recanalyst/recanalyst.svg)](https://packagist.org/recanalyst/recanalyst)
[![License](https://img.shields.io/packagist/l/recanalyst/recanalyst.svg)](https://packagist.org/recanalyst/recanalyst)
[![Build Status](https://travis-ci.org/goto-bus-stop/recanalyst.svg?branch=master)](https://travis-ci.org/goto-bus-stop/recanalyst)
[![Gitter chat](https://badges.gitter.im/goto-bus-stop/recanalyst.svg)](https://gitter.im/goto-bus-stop/recanalyst)

> The `master` branch is under development. Check out the [v3.x branch][] for
> the current version.

RecAnalyst is a PHP package for analyzing Age of Kings, The Conquerors,
Forgotten Empires and Age of Empires 2 HD (The Forgotten) recorded games.

[License][] - [Credits][] - [Requirements][] - [Installation][] -
[Configuration][] - [Usage Examples][] - [API Documentation][]

```php
$rec = new \RecAnalyst\RecordedGame('recorded_game.mgx2');
$rec->mapImage()->save('minimap.png');
foreach ($rec->players() as $player) {
    printf("%s (%s)", $player->name, $player->civName());
}
```

## License

[GPL-3][]. See [COPYING][].

## Credits

Originally forked from Biegleux's work:  
v2.1.0 © 2007-2010 biegleux &lt;biegleux@gmail.com&gt;    
[Original project homepage][]  
[Original project documentation][]

See also [CREDITS][].

## Requirements

RecAnalyst needs PHP 5.6+ or PHP 7. For generating map images, either the
Imagick or GD extension needs to be installed as well.

## Installation

With [Composer][]:

```
composer require recanalyst/recanalyst
```

<!-- TODO
Without Composer:

 - Add a download link to something that includes RecAnalyst and dependencies
 - Add docs on using the included Composer-generated autoloader,
   probably `require '/path/to/recanalyst/autoload.php'`

-->

## Configuration

RecAnalyst ships with translations and image files for researches and
civilizations.

> If you're using RecAnalyst with Laravel, scroll down to learn about [Laravel
> integration][].

RecAnalyst contains a basic Translator class for standalone use. By default,
RecAnalyst uses the English language files from Age of Empires II: HD Edition.

RecAnalyst contains icons for civilizations, units and researches in the
`resources/images` folder. If you're using RecAnalyst standalone, and want to
use the icons, you can copy that folder into your own project. You can then
refer to the different categories of icons in the following ways:

| Category | URL |
|----------|-----|
| Civilizations | `'/path/to/resources/images/civs/'.$colorId.'/'.$civId.'.png'` |
| Researches | `'/path/to/resources/images/researches/'.$researchId.'.png'` |

### Laravel

Add the RecAnalyst service provider to your `config/app.php`:

```php
'providers' => [
    RecAnalyst\Laravel\ServiceProvider::class,
],
```

RecAnalyst will automatically pick up the appropriate translations for your
Laravel app configuration.

To copy the civilization and research icons to your `public` folder:

```bash
php artisan vendor:publish --tag=public
```

You can then refer to the different categories of icons in the following ways:

| Category | URL |
|----------|-----|
| Civilizations | `public_path('vendor/recanalyst/civs/'.$colorId.'/'.$civId.'.png')` |
| Researches | `public_path('vendor/recanalyst/researches/'.$researchId.'.png')` |

## API Documentation

Full API documentation is available at https://goto-bus-stop.github.io/recanalyst/.

[CREDITS]: ./CREDITS
[Composer]: https://getcomposer.org
[v3.x branch]: https://github.com/goto-bus-stop/recanalyst/tree/v3.x
[Original project homepage]: http://recanalyst.sourceforge.net/
[Original project documentation]: http://recanalyst.sourceforge.net/documentation/
[GPL-3]: https://www.tldrlegal.com/l/gpl-3.0
[COPYING]: ./COPYING

[License]: #license
[Credits]: #credits
[Requirements]: #requirements
[Installation]: #installation
[Configuration]: #configuration
[Laravel integration]: #laravel
[Usage Examples]: ./examples#readme
[API Documentation]: https://goto-bus-stop.github.io/recanalyst/
