# RecAnalyst

[![Packagist](https://img.shields.io/packagist/v/recanalyst/recanalyst.svg)](https://packagist.org/packages/recanalyst/recanalyst)
[![License](https://img.shields.io/packagist/l/recanalyst/recanalyst.svg)](https://packagist.org/packages/recanalyst/recanalyst)
[![Build Status](https://travis-ci.org/goto-bus-stop/recanalyst.svg?branch=master)](https://travis-ci.org/goto-bus-stop/recanalyst)
[![Gitter chat](https://badges.gitter.im/goto-bus-stop/recanalyst.svg)](https://gitter.im/goto-bus-stop/recanalyst)

RecAnalyst is a PHP package for analyzing Age of Empires II recorded games. It
supports recorded game files from Age of Kings, The Conquerors, UserPatch,
Forgotten Empires, and HD Edition (optionally with expansions).

[License][] - [Credits][] - [Requirements][] - [Installation][] -
[Configuration][] - [Usage Examples][] - [API Documentation][] -
[Limitations][]

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

See also [references.md][].

## Requirements

RecAnalyst works with PHP 5.6+ and PHP 7. The Imagick or GD extensions need to
be installed to generate map images.

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

> If you're using RecAnalyst with Laravel, scroll down to learn about
> [Laravel integration][].

RecAnalyst contains a basic Translator class for standalone use. By default,
RecAnalyst uses the English language files from Age of Empires II: HD Edition.

RecAnalyst contains icons for civilizations, units and researches in the
`resources/images` folder. If you're using RecAnalyst standalone, and want to
use the icons, you can copy that folder into your own project. You can then
refer to the different categories of icons in the following ways:

| Category      | URL |
|---------------|-----|
| Civilizations | `'/path/to/resources/images/civs/'.$colorId.'/'.$civId.'.png'` |
| Researches    | `'/path/to/resources/images/researches/'.$researchId.'.png'`   |

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

| Category      | URL |
|---------------|-----|
| Civilizations | `public_path('vendor/recanalyst/civs/'.$colorId.'/'.$civId.'.png')` |
| Researches    | `public_path('vendor/recanalyst/researches/'.$researchId.'.png')`   |

## API Documentation

To get started, the [Usage Examples][] might be helpful.

Full API documentation is available at
https://goto-bus-stop.github.io/recanalyst/doc/.

## Limitations

These are some things to take into account when writing your own applications
with RecAnalyst:

 - Achievements data is only available in multiplayer UserPatch 1.4 (`.mgz`)
   games. It isn't saved in single player recordings nor in any other game
   version.
 - RecAnalyst cannot be used to find the state of the recorded game at any point
   except the very start. This is because AoC stores a list of actions, so to
   reconstruct the game state at a given point, the game has to be simulated
   exactly. See [#1][limitation/gameplay].
 - Rarely, Age of Empires fails to save Resign actions just before the end of
   the game. In those cases, RecAnalyst cannot determine the `resignTime`
   property for players. See [#35][limitation/resignTime].

[references.md]: ./references.md
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
[API Documentation]: https://goto-bus-stop.github.io/recanalyst/doc/
[Limitations]: #limitations

[limitation/gameplay]: https://github.com/goto-bus-stop/recanalyst/issues/1
[limitation/resignTime]: https://github.com/goto-bus-stop/recanalyst/issues/35
