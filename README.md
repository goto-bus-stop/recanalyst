# RecAnalyst

[![Packagist](https://img.shields.io/packagist/v/recanalyst/recanalyst.svg)](https://packagist.org/recanalyst/recanalyst)
[![License](https://img.shields.io/packagist/l/recanalyst/recanalyst.svg)](https://packagist.org/recanalyst/recanalyst)
[![Build Status](https://travis-ci.org/goto-bus-stop/recanalyst.svg?branch=master)](https://travis-ci.org/goto-bus-stop/recanalyst)
[![Gitter chat](https://badges.gitter.im/goto-bus-stop/recanalyst.svg)](https://gitter.im/goto-bus-stop/recanalyst)

> The `master` branch is under development. Check out the [v3.x branch](https://github.com/goto-bus-stop/recanalyst/tree/v3.x) for the current version.

RecAnalyst is a PHP package for analyzing Age of Kings, The Conquerors, Forgotten Empires and Age of Empires 2 HD (The Forgotten) recorded games.

## Credits

Based on Biegleux's work, but now with `RecAnalyst\` namespace, Composer support and some fixes to actually make it work again.  
v2.1.0 (c) 2007-2010 biegleux <biegleux@gmail.com>  
[Original project homepage](http://recanalyst.sourceforge.net/)  
[Original project documentation](http://recanalyst.sourceforge.net/documentation/)

## Requirements

RecAnalyst needs PHP5 with GD installed, and zlib if you want to use `RecAnalyst\Archive`.

## Todo

  * Make `RecAnalyst\Archive` do things.
  * Proper documentation.

## Installation

### Sad Manual install

`git clone https://github.com/goto-bus-stop/recanalyst`.

You'll need to manually add an Autoloader, something like:

```php
spl_autoload_register(function ($class) {
    if (substr($class, 0, 11) === 'RecAnalyst\\') {
        $f = PATH_TO_RECANALYST . 'src/' . str_replace('RecAnalyst\\', '', $class) . '.php'
        if (file_exists($f)) include($f);
    }
});
```

## License

[GPL-3](https://www.tldrlegal.com/l/gpl-3.0). See also `./COPYING`.


## Usage Examples

### Quickly Show a Map Image

```php
$ra = new RecAnalyst\RecAnalyst();
$ra->load($filename, fopen($filename, 'r'));
$ra->analyze();
// generateMap returns a GD image resource
$gd = $ra->generateMap();

header('Content-Type: image/png');
imagepng($gd);
imagedestroy($gd);
```

### Examine The Chat
```php
// with $ra of above

foreach ($ra->pregameChat as $chat) {
  echo '<' . $chat->player->name . '> ' . $chat->msg . "\n";
}
foreach ($ra->ingameChat as $chat) {
  echo '[' . RecAnalyst\RecAnalyst::gameTimeToString($chat->time) . '] ' .
       '<' . $chat->player->name . '> ' . $chat->msg . "\n";
}
```

## API Documentation

### class Config

#### string Config::$resourcesDir

Location of RecAnalyst graphic resources. Defaults to `'path_to_RecAnalyst/resources/'`.

#### int Config::$mapWidth

Width of generated minimap images. Defaults to `204`.

#### int Config::$mapHeight

Height of generated minimap images. Defaults to `102`. For best results, this should be ½× mapWidth.

#### bool Config::$showPositions

Whether to highlight players' starting positions with a big fat circle in their colour. Defaults to `true`.

#### int Config::$researchTileSize

Size of research icons in generated research images. Defaults to `19`.

#### int Config::$researchVSpacing

Vertical distance between player/research rows in generated research images. Defaults to `8`.

#### string Config::$researchBackgroundImage

Background image for generated research images. Has to be jpeg! :( Defaults to `$config->resourcesDir . 'background.jpg'`.

#### array Config::$researchDAColor

Colour to use as research row background while in Dark age in research images. Array of `red, green, blue, alpha` values as taken in by `imagecolorallocatealpha`. (eg. `array(0, 0, 0, 127)`)

#### array Config::$researchFAColor

Colour to use as research row background while in Feudal age in research images. Array of `red, green, blue, alpha` values as taken in by `imagecolorallocatealpha`. (eg. `array(0, 0, 0, 127)`)

#### array Config::$researchCAColor

Colour to use as research row background while in Castle age in research images. Array of `red, green, blue, alpha` values as taken in by `imagecolorallocatealpha`. (eg. `array(0, 0, 0, 127)`)

#### array Config::$researchIAColor

Colour to use as research row background while in Imperial age in research images. Array of `red, green, blue, alpha` values as taken in by `imagecolorallocatealpha`. (eg. `array(0, 0, 0, 127)`)

### class RecAnalyst

#### void RecAnalyst::load(string $filename, resource|string $input)

Loads a recorded game. `$input` is a file resource (`fopen`) or a string containing file contents (eg from `file_get_contents`)

#### bool RecAnalyst::analyze()

Analyzes a recorded game. Returns true on success, false on error.

#### resource RecAnalyst::generateMap()

Generates a minimap image. Returns GD Image resource.

#### GameSettings RecAnalyst::$gameSettings

Contains game settings information, such as map type and map size.

#### GameInfo RecAnalyst::$gameInfo

Contains recorded game information, such as duration and game version.

#### array RecAnalyst::$players

Plain array of players. Items are instances of `RecAnalyst\Player`.

#### array RecAnalyst::$teams

Two-dimensional array of teams of players. Same data as above but also indexed by team. (`$teams[$teamId][$n]`)

#### static string RecAnalyst::gameTimeToString(int $gameTime)

Builds an hh:mm:ss string from the given number of seconds.

### class GameSettings

#### string GameSettings::getGameTypeString()

Returns game type string. (eg. "Random map", "Deathmatch") Names in `RecAnalystConst::$GAME_TYPES`.

#### string GameSettings::getMapStyleString()

Returns map style string. (eg. "Real World", "Standard") Names in `RecAnalystConst::$MAP_STYLES`.

#### string GameSettings::getDifficultyLevelString()

Returns difficulty level string. (eg. "Hard", "Standard") Names in `RecAnalystConst::$DIFFICULTY_LEVELS`.

#### string GameSettings::getGameSpeedString()

Returns game speed string. (eg. "Slow", "Fast") Names in `RecAnalystConst::$GAME_SPEEDS`.

#### string GameSettings::getRevealMapString()

Returns Reveal Map setting. (eg. "Explored", "All Visible") Names in `RecAnalystConst::$REVEAL_SETTINGS`.

#### string GameSettings::getMapSizeString()

Returns map size. (eg. "Tiny", "Large") Names in `RecAnalystConst::$MAP_SIZES`.

#### bool GameSettings::isScenario()

Returns whether the game type is Scenario.

#### string GameSettings::getMapName()

Returns the name of the map this was played on. Names for the built-in maps in `RecAnalystConst::$MAPS`.

#### int GameSettings::getPopLimit()

Returns the population limit. (eg. 150, 200)

#### bool GameSettings::getLockDiplomacy()

Returns whether diplomacy was locked.

#### VictorySettings GameSettings::getVictorySettings()

Returns a `RecAnalyst\VictorySettings` instance describing the victory settings used in this game.

### class GameInfo

#### string GameInfo::getGameVersionString()

Returns Game Version. (eg. "AOC 1.0c", "AOC UP1.4") Names in `RecAnalystConst::$GAME_VERSIONS`.

#### string GameInfo::getPlayersString()

Returns a string describing the teams. (eg. "1v1", "4v2v2", "FFA")

#### string GameInfo::getPOV()

Returns the POV player's name.

#### string GameInfo::getPOVEx()

Returns the POV player's name, with any co-oping players between brackets. (eg. "TheViper (Jordan_23, whack)")

#### bool GameInfo::ingameCoop()

Returns whether there is a co-oping player in the game.

#### int GameInfo::getPlayTime()

Returns the duration of the game in seconds.

#### string GameInfo::getObjectives()

Returns the scenario Objectives of this game. Only populated if the game type is Scenario.

#### string GameInfo::getScenarioFilename()

Returns the filename of the scenario played in this game. Only populated if the game type is Scenario.

### class Player

#### string Player::getCivString()

Returns the Civilization name played by this player. (eg. "Britons", "Italians") Names in `RecAnalystConst::$CIVILIZATIONS`.

#### bool Player::isHuman()

Returns true if player is human, false if it is an AI.

#### bool Player::isCooping()

Returns whether the player is co-oping.

#### int Player::getTeamID()

Returns the index of the player's team in `RecAnalyst::$teams`.

#### string Player::getName()

Returns this player's name.

#### int Player::getFeudalTime()

Returns this player's feudal age advance time.

#### int Player::getCastleTime()

Returns this player's castle age advance time.

#### int Player::getImperialTime()

Returns this player's imperial age advance time.

#### int Player::getResignTime()

Returns this player's resignation time.

### class Team

#### array Team::$players

Array of `Player`s in this team.

### class ChatMessage

#### int ChatMessage::$time

Game Time in seconds at which this message was sent.

#### Player ChatMessage::$player

Player that sent this message, or `null` for system messages (eg. age advance, resign)

#### string ChatMessage::$msg

Message content.

#### string ChatMessage::$group

Group this message was sent to, or `""` (empty string) if there is none. (eg. "Team", "Enemy", "All")
### class InitialState

#### string InitialState::getStartingAgeString()

Current player's Starting Age name. (eg. "Dark Age")

#### int InitialState::$food

Amount of food at the start of the game.

#### int InitialState::$wood

Amount of wood at the start of the game.

#### int InitialState::$gold

Amount of gold at the start of the game.

#### int InitialState::$stone

Amount of stone at the start of the game.

#### int InitialState::$startingAge

Current player's Starting Age.

#### int InitialState::$houseCapacity

Initial house capacity.

#### int InitialState::$population

Initial population. (4 in a standard game, 5 for Mayans, 7 for Chinese)

#### int InitialState::$civilianPop

Initial Civilian population. (villagers, trade units, …)

#### int InitialState::$militaryPop

Initial Military population. (Scout, usually)

#### int InitialState::$extraPop

Initial extra population. (???)

#### array InitialState::$position

Starting position, `[$x, $y]`.
