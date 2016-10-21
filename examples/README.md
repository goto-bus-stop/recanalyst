# RecAnalyst Examples

## [Simple](./simple.php)

A very small example that saves a minimap and displays players and their
civilizations on the command line.

## [Localized](./simple-localized.php)

A small example that displays some information about the game settings of a
recorded game, in a language of user choice.

```bash
php examples/simple-localized.php # Default language (French, for this script).
php examples/simple-localized.php br # Use Brazilian
```

## [Tabbed](./tabbed/)

A larger example that implements a tabbed recorded game overview similar to the
one on AoCZone. It uses the Twig template engine to render data. Using a
template engine is recommended, because they'll usually handle HTML escaping for
you.

## [Laravel](./laravel.php)

A Laravel controller that shows certain bits and pieces of recorded game data,
and how RecAnalyst integrates with Laravel file uploads.
