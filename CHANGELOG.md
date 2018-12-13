# recanalyst change log

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/).

## 4.2.3
Features:
 - Read data from the "start game" message in the body. (https://github.com/goto-bus-stop/recanalyst/commit/0584112e6133bee561fbfce5aac0b48676f8fcd7)
 - Improve sync message parsing with new research. (https://github.com/goto-bus-stop/recanalyst/commit/7a15f551bb3c4452c4cf818f2f96e5e6adb258d8)

## 4.2.2
Support:
 - Support UserPatch 1.5 version identifier. (#65)

## 4.2.1
Support:
 - Support UserPatch 1.5 terrain reading. (#53)

## 4.2.0
Features:
 - Support for HD Edition 4.8 and 5.x (https://github.com/goto-bus-stop/recanalyst/pull/41)

Bugfixes:
 - Laravel: only throw when Laravel is not available but the ServiceProvider is actually being used (https://github.com/goto-bus-stop/recanalyst/commit/caaef2c5a313230c46e0af07241777b71f5d84ff)

## 4.1.3
Bugfixes:
 - Fix reading pre-game lobby chat from co-op players. (4cfae39ea843f7fbed4f4b035e64b13301b73266)

New stuff:
 - Add missing game type and map type constants. (e267bb935279537b625c30a1c4f135fbecdc3e8a)

Internal:
 - Extract Map data analyzer. (ce96b0cf6a5693f69e282b004154ebf74438addd)

## 4.1.2
Bugfixes:
- Fixed associating chat messages with players in co-op games. Earlier only chat messages sent by the main player in a co-op group would be linked correctly. (bd06dc5b984b6046ae65a4e78c2577864748322e)

Improvements:
- Improved performance of reading Sync packets in the recorded game body. There's a lot of them, so the body analyzer should be about 35-40% faster for most games. (c4266e20918ebbb2a57209b9e6d387203b2434c2)

## 4.1.1
This release adds support for reading map names from games that use custom random maps, and fixes a chat and co-op bugs.

Features:
- `->gameSettings()->mapName()` now attempts to read the map name from the Objectives tab if a custom random map is used. (503573895f89a67c1001fe3d7a617e4134826b84)

  This behaviour can be disabled using the new `extractRMSName` option:

  ``` php
  // Don't use the .rms name reader, instead returning "Custom" when a custom random map is used.
  $mapName = $rec->gameSettings()->mapName(['extractRMSName' => false]);
  ```
- Added some methods for dealing with co-op players. (ce396a00a5f00ce0aabf3ab50873024e76442e31)

Bugfixes:
- Pre-game multiplayer lobby chat is now also read from HD Edition games. (2621ef91285b6534ed18d48f525011901c8f3f18)
- Fixed detection of co-oping players. (ce396a00a5f00ce0aabf3ab50873024e76442e31)

## 4.1.0
This release adds the necessary image and language resources for African Kingdoms and fixes numerous bugs.

Features:
- Generated resources now include all African Kingdoms maps, terrains, researches and units.
- A new `$rec->getPlayer($index)` method to retrieve a Player object by the player index.

Changes:
- `$rec->players()` does not return Spectating players anymore in HD Edition. A `$rec->spectators()` method was added instead to retrieve the spectating players.

Bugfixes:
- Unique unit upgrades and unique techs now have icons.
- Tributes now have the correct resource ID and point to the correct players. Before, both the `playerFrom` and `playerTo` would point to the same player.
- `$player->achievements()` now returns the achievements for the correct player.

## 4.0.4
- Add support for Map IDs from .aoe2record files.

## 4.0.3
This is a small release with an important translations fix and some speed improvement.
- Fix map ID to map name translations. They were very wrong before.
- Fix an undefined variable error when a player objects list ("starting units") analysis fails. It'll now throw a more descriptive exception instead.
- Speed up objects list analysis.
- Always set the `scenarioFilename` property on the header analysis. If the recorded game file was not a scenario game, `$rec->header()->scenarioFilename` will be `NULL`.
- Add a `memoryLimit` option to customise the maximum amount of bytes of memory to allocate when decompressing recorded game headers. It's set to 16MB by default and I haven't encountered a game where this wasn't enough, but some custom scenarios with very many starting units or eye candy might be even larger. Best to set this to something your server will be able to reasonably handle (or keep it at 16MB which will be enough for a vast majority of cases).

## 4.0.2
This release fixes a few important things:
- Fix how researches are extracted from data files. Earlier, the Tech Effect ID was used instead of the research ID. Recorded games store the research ID. As such, RecAnalyst would use the wrong strings and research images for many researches. v4.0.2 uses the correct strings and images.
- Fix reading victory settings from HD Edition `.aoe2record` files.

You'll need to copy the image and language resources again to get the research fixes proper, or run `php artisan vendor:publish --tag=public` with Laravel.

Then there's a not-so-important fix:
- Fix reading Voobly's injected `<Rating>` chat messages. It's unlikely that anything was affected by the bug, since it would only prepend a space character to chat messages that shouldn't be there.

A few examples new usage examples were also added:
- The [chat](https://github.com/goto-bus-stop/recanalyst/blob/master/examples/chat.php) example shows how to read chat messages.
- The new Twig-less [tabbed](https://github.com/goto-bus-stop/recanalyst/tree/master/examples/tabbed-native) example is an alternative version of the Twig tabbed example, but in plain PHP.

## 4.0.1
This is a small release that reads some more data from post-game lobby data in UserPatch lobbied multiplayer games, and improves on how irrelevant resource data is skipped in player info blocks.

The `PostgameDataAnalyzer` now reads the amount of units killed, buildings razed and tribute sent from each player to each player. The post-game data object can be accessed using `$recordedGame->body()->postGameData`.

Internally, there are many "resources", not just food/wood/gold/stone. For example, the current population is a resource, and the amount of units a player has killed is a resource. Instead of hardcoding the amount of resource types for different game versions, v4.0.1 reads the amount of resource types from the recorded game file and skips the ones it doesn't know.

Thanks to @yvan-burrie for uploading the [Genie-Reverse](https://github.com/yvan-burrie/genie-reverse) repository, which contained this information :v:

Documentation is now versioned. The documentation for this release is available at http://goto-bus-stop.github.io/recanalyst/doc/v4.0.1.

## 4.0.0
This release features support for many of the newer HD Edition formats, particularly `.mgx2` and `.aoe2record` files.

RecAnalyst was essentially rewritten to use a modular reader and model structure, so the API is incompatible at every level with v3.

### Features
- Translations! All languages that are available in HD Edition are supported. The various `->somethingName()` methods return localised strings.
- Support for aoe2record files.
- Support for African Kingdoms.
- Included resource files such as research and civilization icons, extracted from HD Edition.
- A Laravel service provider that allows RecAnalyst to work with the Laravel translation system, if available. Resources can be published to your `public/vendor` directory using `php artisan vendor:publish --tag=public`.

### Improvements
- Modular reader: only reads the data you need, when you need it.
- Terrain, unit and player colors are now taken directly from the AoE2 data files, so they're hopefully always correct and can easily be updated, should Skybox release a new expansion…

New API documentation is at https://goto-bus-stop.github.io/recanalyst/doc/. Usage examples can be found [here](https://github.com/goto-bus-stop/recanalyst/tree/master/examples).

## 3.2.0
Improve support for some of HD Edition's mgx2 and msx2 recordings.
