<?php

namespace RecAnalyst;

/**
 * RecAnalystConst implements constants used for RecAnalyst.
 *
 * TODO replace with localisation of some kind?
 * TODO support multiple "data packs", like SWGB and AoC
 * TODO add African Kingdoms units
 */
class RecAnalystConst
{
    const TRL_93 = 'TRL 9.3'; // AoK trial
    const VER_93 = 'VER 9.3'; // AoK
    const VER_94 = 'VER 9.4'; // AoC
    const VER_95 = 'VER 9.5'; // AoFE
    const VER_98 = 'VER 9.8'; // UserPatch 1.2
    const VER_99 = 'VER 9.9'; // UserPatch 1.3
    const VER_9A = 'VER 9.A'; // UserPatch 1.4 RC1
    const VER_9B = 'VER 9.B'; // UserPatch 1.4 RC2
    const VER_9C = 'VER 9.C'; // UserPatch 1.4

    const IMG_EXT = '.gif';

    /**
     * Game version names.
     * @var array
     */
    public static $GAME_VERSIONS = [
        'Unknown',
        'AOK',
        'AOK Trial',
        'AOK 2.0',
        'AOK 2.0a',
        'AOC',
        'AOC Trial',
        'AOC 1.0',
        'AOC 1.0c',
        'AOC 1.1',
        'AOFE 2.1',
        'AOC UP1.4',
        'Unknown',
        'Unknown',
        'HD'
    ];

    /**
     * Map style names.
     *
     * @var array
     */
    public static $MAP_STYLES = [
        'Standard',
        'Real World',
        'Custom',
    ];

    /**
     * Difficulty level names.
     *
     * @var array
     */
    public static $DIFFICULTY_LEVELS = [
        'Hardest',
        'Hard',
        'Moderate',
        'Standard',
        'Easiest',
    ];

    /**
     * Difficulty level names for AOK.
     *
     * @var array
     */
    public static $AOK_DIFFICULTY_LEVELS = [
        'Hardest',
        'Hard',
        'Moderate',
        'Easy',
        'Easiest',
    ];

    /**
     * Game mode names.
     *
     * @var array
     */
    public static $GAME_TYPES = [
        'Random map',
        'Regicide',
        'Death match',
        'Scenario',
        'Campaign',
        'King of the Hill',
        'Wonder race',
        'Defend Wonder',
        'Turbo Random map',
    ];

    /**
     * Game speed names.
     *
     * @var array
     */
    public static $GAME_SPEEDS = [
        100 => 'Slow',
        150 => 'Normal',
        200 => 'Fast',
    ];

    /**
     * Reveal setting names.
     *
     * @var array
     */
    public static $REVEAL_SETTINGS = [
        'Normal',
        'Explored',
        'All Visible',
    ];

    /**
     * Map size names.
     *
     * @var array
     */
    public static $MAP_SIZES = [
        'Tiny (2 players)',
        'Small (3 players)',
        'Medium (4 players)',
        'Normal (6 players)',
        'Large (8 players)',
        'Giant',
    ];

    /**
     * Starting age names.
     *
     * @var array
     */
    public static $STARTING_AGES = [
        'Dark Age',
        'Feudal Age',
        'Castle Age',
        'Imperial Age',
        'Post-Imperial Age',
    ];

    /**
     * Victory condition names.
     *
     * @var array
     */
    public static $VICTORY_CONDITIONS = [
        'Standard',
        'Conquest',
        'Time Limit',
        'Score Limit',
        'Custom',
    ];

    /**
     * Civilization names.
     *
     * @var array
     */
    public static $CIVS = [
        '',
        'Britons',
        'Franks',
        'Goths',
        'Teutons',
        'Japanese',
        'Chinese',
        'Byzantines',
        'Persians',
        'Saracens',
        'Turks',
        'Vikings',
        'Mongols',
        'Celts',
        'Spanish',
        'Aztecs',
        'Mayans',
        'Huns',
        'Koreans',
        'Italians',
        'Indians',
        'Incas',
        'Magyars',
        'Slavs',
    ];

    /**
     * Short Civilization names.
     *
     * @var array
     */
    public static $SHORT_CIVS = [
        '',
        'bri',
        'fra',
        'got',
        'teu',
        'jap',
        'chi',
        'byz',
        'per',
        'sar',
        'tur',
        'vik',
        'mon',
        'cel',
        'spa',
        'azt',
        'may',
        'hun',
        'kor',
        'ita',
        'ind',
        'inc',
        'mag',
        'sla',
    ];

    /**
     * Resource names.
     *
     * @var array
     */
    public static $RESOURCES = [
        0x00 => 'food',
        0x01 => 'wood',
        0x02 => 'stone',
        0x03 => 'gold',
    ];

    /**
     * Research names and image file names.
     *
     * @var array
     */
    public static $RESEARCHES = [
        101 => ['Feudal Age',            'feudal_age'],
        102 => ['Castle Age',            'castle_age'],
        103 => ['Imperial Age',          'imperial_age'],
         22 => ['Loom',                  'loom'],
        213 => ['Wheelbarrow',           'wheel_barrow'],
        249 => ['Hand Cart',             'hand_cart'],
          8 => ['Town Watch',            'town_watch'],
        280 => ['Town Patrol',           'town_patrol'],
         14 => ['Horse Collar',          'horse_collar'],
         13 => ['Heavy Plow',            'heavy_plow'],
         12 => ['Crop Rotation',         'crop_rotation'],
        202 => ['Double Bit Axe',        'double_bit_axe'],
        203 => ['Bow Saw',               'bow_saw'],
        221 => ['Two Man Saw',           'two_man_saw'],
         55 => ['Gold Mining',           'gold_mining'],
        278 => ['Stone Mining',          'stone_mining'],
        182 => ['Gold Shaft Mining',     'gold_shaft_mining'],
        279 => ['Stone Shaft Mining',    'stone_shaft_mining'],
         19 => ['Cartography',           'cartography'],
         23 => ['Coinage',               'coinage'],
         48 => ['Caravan',               'caravan'],
         17 => ['Banking',               'banking'],
         15 => ['Guilds',                'guilds'],
        211 => ['Padded Archer Armor',   'padded_archer_armor'],
        212 => ['Leather Archer Armor',  'leather_archer_armor'],
        219 => ['Ring Archer Armor',     'ring_archer_armor'],
        199 => ['Fletching',             'fletching'],
        200 => ['Bodkin Arrow',          'bodkin_arrow'],
        201 => ['Bracer',                'bracer'],
         67 => ['Forging',               'forging'],
         68 => ['Iron Casting',          'iron_casting'],
         75 => ['Blast Furnace',         'blast_furnace'],
         81 => ['Scale Barding Armor',   'scale_barding_armor'],
         82 => ['Chain Barding Armor',   'chain_barding_armor'],
         80 => ['Plate Barding Armor',   'plate_barding_armor'],
         74 => ['Scale Mail Armor',      'scale_mail_armor'],
         76 => ['Chain Mail Armor',      'chain_mail_armor'],
         77 => ['Plate Mail Armor',      'plate_mail_armor'],
         50 => ['Masonry',               'masonry'],
        194 => ['Fortified Wall',        'fortified_wall'],
         93 => ['Ballistics',            'ballistics'],
        380 => ['Heated Shot',           'heated_shot'],
        322 => ['Murder Holes',          'murder_holes'],
         54 => ['Treadmill Crane',       'treadmill_crane'],
         51 => ['Architecture',          'architecture'],
         47 => ['Chemistry',             'chemistry'],
        377 => ['Siege Engineers',       'siege_engineers'],
        140 => ['Guard Tower',           'guard_tower'],
         63 => ['Keep',                  'keep'],
         64 => ['Bombard Tower',         'bombard_tower'],
        222 => ['Man At Arms',           'man_at_arms'],
        207 => ['Long Swordsman',        'long_swordsman'],
        217 => ['Two Handed Swordsman',  'two_handed_swordsman'],
        264 => ['Champion',              'champion'],
        197 => ['Pikeman',               'pikeman'],
        429 => ['Halberdier',            'halberdier'],
        434 => ['Elite Eagle Warrior',   'eagle_warrior'],
         90 => ['Tracking',              'tracking'],
        215 => ['Squires',               'squires'],
        100 => ['Crossbow',              'crossbow'],
        237 => ['Arbalest',              'arbalest'],
         98 => ['Elite Skirmisher',      'elite_skirmisher'],
        218 => ['Heavy Cavalry Archer',  'heavy_cavalry_archer'],
        437 => ['Thumb Ring',            'thumb_ring'],
        436 => ['Parthian Tactics',      'parthian_tactics'],
        254 => ['Light Cavalry',         'light_cavalry'],
        428 => ['Hussar',                'hussar'],
        209 => ['Cavalier',              'cavalier'],
        265 => ['Paladin',               'paladin'],
        236 => ['Heavy Camel',           'heavy_camel'],
        435 => ['Bloodlines',            'bloodlines'],
         39 => ['Husbandry',             'husbandry'],
        257 => ['Onager',                'onager'],
        320 => ['Siege Onager',          'siege_onager'],
         96 => ['Capped Ram',            'capped_ram'],
        255 => ['Siege Ram',             'siege_ram'],
        239 => ['Heavy Scorpion',        'heavy_scorpion'],
        316 => ['Redemption',            'redemption'],
        252 => ['Fervor',                'fervor'],
        231 => ['Sanctity',              'sanctity'],
        319 => ['Atonement',             'atonement'],
        441 => ['Herbal Medicine',       'herbal_medicine'],
        439 => ['Heresy',                'heresy'],
        230 => ['Block Printing',        'block_printing'],
        233 => ['Illumination',          'illumination'],
         45 => ['Faith',                 'faith'],
        438 => ['Theocracy',             'theocracy'],
         34 => ['War Galley',            'war_galley'],
         35 => ['Galleon',               'galleon'],
        246 => ['Fast Fire Ship',        'fast_fire_ship'],
        244 => ['Heavy Demolition Ship', 'heavy_demolition_ship'],
         37 => ['Cannon Galleon',        'cannon_galleon'],
        376 => ['Elite Cannon Galleon',  'cannon_galleon'],
        373 => ['Shipwright',            'shipwright'],
        374 => ['Careening',             'careening'],
        375 => ['Dry Dock',              'dry_dock'],
        379 => ['Hoardings',             'hoardings'],
        321 => ['Sappers',               'sappers'],
        315 => ['Conscription',          'conscription'],
        408 => ['Spies / Treason',       'spy'],
        // unique-unit-upgrade
        432 => ['Elite Jaguar Man',      'jaguar_man'],
        361 => ['Elite Cataphract',      'cataphract'],
        370 => ['Elite Woad Raider',     'woad_raider'],
        362 => ['Elite Chu-Ko-Nu',       'chu_ko_nu'],
        360 => ['Elite Longbowman',      'longbowman'],
        363 => ['Elite Throwing Axeman', 'throwing_axeman'],
        365 => ['Elite Huskarl',         'huskarl'],
          2 => ['Elite Tarkan',          'tarkan'],
        366 => ['Elite Samurai',         'samurai'],
        450 => ['Elite War Wagon',       'war_wagon'],
        448 => ['Elite Turtle Ship',     'turtle_ship'],
        //348 => ['Elite Turtle Ship',     'turtle_ship'],
         27 => ['Elite Plumed Archer',   'plumed_archer'],
        371 => ['Elite Mangudai',        'mangudai'],
        367 => ['Elite War Elephant',    'war_elephant'],
        368 => ['Elite Mameluke',        'mameluke'],
        //378 => ['Elite Mameluke',        'mameluke'],
         60 => ['Elite Conquistador',    'conquistador'],
        364 => ['Elite Teutonic Knight', 'teutonic_knight'],
        369 => ['Elite Janissary',       'janissary'],
        398 => ['Elite Berserk',         'berserk'],
        372 => ['Elite Longboat',        'longboat'],
        // unique-research
         24 => ['Garland Wars',          'unique_tech'],
         61 => ['Logistica',             'unique_tech'],
          5 => ['Furor Celtica',         'unique_tech'],
         52 => ['Rocketry',              'unique_tech'],
          3 => ['Yeomen',                'unique_tech'],
         83 => ['Bearded Axe',           'unique_tech'],
         16 => ['Anarchy',               'unique_tech'],
        457 => ['Perfusion',             'unique_tech'],
         21 => ['Atheism',               'unique_tech'],
         59 => ['Kataparuto',            'unique_tech'],
        445 => ['Shinkichon',            'unique_tech'],
          4 => ['El Dorado',             'unique_tech'],
          6 => ['Drill',                 'unique_tech'],
          7 => ['Mahouts',               'unique_tech'],
          9 => ['Zealotry',              'unique_tech'],
        440 => ['Supremacy',             'unique_tech'],
         11 => ['Crenellations',         'unique_tech'],
         10 => ['Artillery',             'unique_tech'],
         49 => ['Berserkergang',         'unique_tech'],
        // AoFE
        526 => ['Hunting Dogs',              'hunting_dogs'],
        521 => ['Imperial Camel',            'imperial_camel'],
        517 => ['Couriers',                  'unique_tech'],
        516 => ['Andean Sling',              'unique_tech2'],
        515 => ['Recurve Bow',               'unique_tech'],
        514 => ['Mercenaries',               'unique_tech2'],
        513 => ['Druzhina',                  'unique_tech'],
        512 => ['Orthodoxy',                 'unique_tech2'],
        507 => ['Shatagni',                  'unique_tech'],
        506 => ['Sultans',                   'unique_tech2'],
        499 => ['Silk Road',                 'unique_tech'],
        494 => ['Pavise',                    'unique_tech2'],
        493 => ['Chivalry',                  'unique_tech2'],
        492 => ['Inquisition',               'unique_tech2'],
        491 => ['Sipahi',                    'unique_tech2'],
        490 => ['Madrasah',                  'unique_tech2'],
        489 => ['Ironclad',                  'unique_tech2'],
        488 => ['Boiling Oil',               'unique_tech2'],
        487 => ['Nomads',                    'unique_tech2'],
        486 => ['Panokseon',                 'unique_tech2'],
        485 => ['Tlatoani',                  'unique_tech2'],
        484 => ['Marauders',                 'unique_tech2'],
        483 => ['Stronghold',                'unique_tech2'],
        464 => ['Greek Fire',                'unique_tech2'],
        463 => ['Chieftains',                'unique_tech2'],
        462 => ['Great Wall',                'unique_tech2'],
        461 => ['Warwolf',                   'unique_tech2'],
        460 => ['Atlatl',                    'unique_tech2'],
        384 => ['Eagle Warrior',             'heavy_eagle_warrior'],
        494 => ['Gillnets',                  'gillnets'],
        509 => ['Elite Kamayuk',             'kamayuk'],
        504 => ['Elite Boyar',               'boyar'],
        481 => ['Elite Elephant Archer',     'elephant_archer'],
        472 => ['Elite Magyar Huszar',       'magyar_huszar'],
        468 => ['Elite Genoese Crossbowman', 'genoese_crossbowman'],
    ];

    /**
     * Unit names and image file names.
     *
     * @var array
     */
    public static $UNITS = [
          4 => ['Archer',                'archer'],
          5 => ['Hand Cannoneer',        'hand_cannoneer'],
          6 => ['Elite Skirmisher',      'elite_skirmisher'],
          7 => ['Skirmisher',            'skirmisher'],
          8 => ['Longbowman',            'longbowman'],
         11 => ['Mangudai',              'mangudai'],
         13 => ['Fishing Ship',          'fishing_ship'],
         17 => ['Trade Cog',             'trade_cog'],
         21 => ['War Galley',            'war_galley'],
         24 => ['Crossbowman',           'crossbowman'],
         25 => ['Teutonic Knight',       'teutonic_knight'],
         35 => ['Battering Ram',         'battering_ram'],
         36 => ['Bombard Cannon',        'bombard_cannon'],
         38 => ['Knight',                'knight'],
         39 => ['Cavalry Archer',        'cavalry_archer'],
         40 => ['Cataphract',            'cataphract'],
         41 => ['Huskarl',               'huskarl'],
         //42 => ['Trebuchet (Unpacked)',  'trebuchet'],
         46 => ['Janissary',             'janissary'],
         73 => ['Chu Ko Nu',             'chu_ko_nu'],
         74 => ['Militia',               'militiaman'],
         75 => ['Man At Arms',           'man_at_arms'],
         76 => ['Heavy Swordsman',       'heavy_swordsman'],
         77 => ['Long Swordsman',        'long_swordsman'],
         83 => ['Villager',              'villager'],
         93 => ['Spearman',              'spearman'],
        125 => ['Monk',                  'monk'],
        //128 => ['Trade Cart, Empty',     ''],
        128 => ['Trade Cart',            'trade_cart'],
        //204 => ['Trade Cart, Full',      ''],
        232 => ['Woad Raider',           'woad_raider'],
        239 => ['War Elephant',          'war_elephant'],
        250 => ['Longboat',              'longboat'],
        279 => ['Scorpion',              'scorpion'],
        280 => ['Mangonel',              'mangonel'],
        281 => ['Throwing Axeman',       'throwing_axeman'],
        282 => ['Mameluke',              'mameluke'],
        283 => ['Cavalier',              'cavalier'],
        //286 => ['Monk With Relic',       ''],
        291 => ['Samurai',               'samurai'],
        329 => ['Camel',                 'camel'],
        330 => ['Heavy Camel',           'heavy_camel'],
        //331 => ['Trebuchet, P',          'trebuchet'],
        331 => ['Trebuchet',             'trebuchet'],
        358 => ['Pikeman',               'pikeman'],
        359 => ['Halberdier',            'halberdier'],
        420 => ['Cannon Galleon',        'cannon_galleon'],
        422 => ['Capped Ram',            'capped_ram'],
        434 => ['King',                  'king'],
        440 => ['Petard',                'petard'],
        441 => ['Hussar',                'hussar'],
        442 => ['Galleon',               'galleon'],
        448 => ['Scout Cavalry',         'scout_cavalry'],
        473 => ['Two Handed Swordsman',  'two_handed_swordsman'],
        474 => ['Heavy Cavalry Archer',  'heavy_cavalry_archer'],
        492 => ['Arbalest',              'arbalest'],
        //493 => ['Adv Heavy Crossbowman',  ''],
        527 => ['Demolition Ship',       'demolition_ship'],
        528 => ['Heavy Demolition Ship', 'heavy_demolition_ship'],
        529 => ['Fire Ship',             'fire_ship'],
        530 => ['Elite Longbowman',      'longbowman'],
        531 => ['Elite Throwing Axeman', 'throwing_axeman'],
        532 => ['Fast Fire Ship',        'fast_fire_ship'],
        533 => ['Elite Longboat',        'longboat'],
        534 => ['Elite Woad Raider',     'woad_raider'],
        539 => ['Galley',                'galley'],
        542 => ['Heavy Scorpion',        'heavy_scorpion'],
        545 => ['Transport Ship',        'transport_ship'],
        546 => ['Light Cavalry',         'light_cavalry'],
        548 => ['Siege Ram',             'siege_ram'],
        550 => ['Onager',                'onager'],
        553 => ['Elite Cataphract',      'cataphract'],
        554 => ['Elite Teutonic Knight', 'teutonic_knight'],
        555 => ['Elite Huskarl',         'huskarl'],
        556 => ['Elite Mameluke',        'mameluke'],
        557 => ['Elite Janissary',       'janissary'],
        558 => ['Elite War Elephant',    'war_elephant'],
        559 => ['Elite Chu Ko Nu',       'chu_ko_nu'],
        560 => ['Elite Samurai',         'samurai'],
        561 => ['Elite Mangudai',        'mangudai'],
        567 => ['Champion',              'champion'],
        569 => ['Paladin',               'paladin'],
        588 => ['Siege Onager',          'siege_onager'],
        692 => ['Berserk',               'berserk'],
        694 => ['Elite Berserk',         'berserk'],
        725 => ['Jaguar Warrior',        'jaguar_man'],
        726 => ['Elite Jaguar Warrior',  'jaguar_man'],
        //748 => ['Cobra Car',             ''],
        751 => ['Eagle Warrior',         'eagle_warrior'],
        752 => ['Elite Eagle Warrior',   'eagle_warrior'],
        755 => ['Tarkan',                'tarkan'],
        757 => ['Elite Tarkan',          'tarkan'],
        759 => ['Huskarl',               'huskarl'],
        761 => ['Elite Huskarl',         'huskarl'],
        763 => ['Plumed Archer',         'plumed_archer'],
        765 => ['Elite Plumed Archer',   'plumed_archer'],
        771 => ['Conquistador',          'conquistador'],
        773 => ['Elite Conquistador',    'conquistador'],
        775 => ['Missionary',            'missionary'],
        //812 => ['Jaguar',                ''],
        827 => ['War Wagon',             'war_wagon'],
        829 => ['Elite War Wagon',       'war_wagon'],
        831 => ['Turtle Ship',           'turtle_ship'],
        832 => ['Elite Turtle Ship',     'turtle_ship'],
        // AoFE
        866 => ['Genoese Crossbowman',       'genoese_crossbowman'],
        868 => ['Elite Genoese Crossbowman', 'genoese_crossbowman'],
        886 => ['Tarkan',                    'tarkan'],
        887 => ['Elite Tarkan',              'tarkan'],
        882 => ['Condottiero',               'condottiero'],
        184 => ['Condottiero',               'condottiero'],
        879 => ['Kamayuk',                   'kamayuk'],
        881 => ['Elite Kamayuk',             'kamayuk'],
        876 => ['Boyar',                     'boyar'],
        878 => ['Elite Boyar',               'boyar'],
        873 => ['Elephant Archer',           'elephant_archer'],
        875 => ['Elite Elephant Archer',     'elephant_archer'],
        869 => ['Magyar Huszar',             'magyar_huszar'],
        871 => ['Elite Magyar Huszar',       'magyar_huszar'],
        753 => ['Eagle Warrior',             'eagle_warrior'],
        207 => ['Imperial Camel',            'imperial_camel'],
        185 => ['Slinger',                   'slinger'],
    ];

    /**
     * Building names and image file names.
     *
     * @var array
     */
    public static $BUILDINGS = [
         12 => ['Barracks',        'barracks'],
         45 => ['Dock',            'dock'],
         49 => ['Siege Workshop',  'siege_workshop'],
         50 => ['Farm',            'farm'],
         68 => ['Mill',            'mill'],
         70 => ['House',           'house'],
         72 => ['Wall, Palisade',  'palisade_wall'],
         79 => ['Watch Tower',     'watch_tower'],
         82 => ['Castle',          'castle'],
         84 => ['Market',          'market'],
         87 => ['Archery Range',   'archery_range'],
        101 => ['Stable',          'stable'],
        103 => ['Blacksmith',      'blacksmith'],
        104 => ['Monastery',       'monastery'],
        109 => ['Town Center',     'town_center'],
        117 => ['Wall, Stone',     'stone_wall'],
        155 => ['Wall, Fortified', 'fortified_wall'],
        199 => ['Fish Trap',       'fish_trap'],
        209 => ['University',      'university'],
        234 => ['Guard Tower',     'guard_tower'],
        235 => ['Keep',            'keep'],
        236 => ['Bombard Tower',   'bombard_tower'],
        276 => ['Wonder',          'wonder'],
        487 => ['Gate',            'gate'],
        490 => ['Gate',            'gate'],
        562 => ['Lumber Camp',     'lumber_camp'],
        584 => ['Mining Camp',     'mining_camp'],
        598 => ['Outpost',         'outpost'],
        621 => ['Town Center',     'town_center'],
        665 => ['Gate',            'gate'],
        673 => ['Gate',            'gate'],
        792 => ['Palisade Gate',   'palisade_gate'],
        796 => ['Palisade Gate',   'palisade_gate'],
        800 => ['Palisade Gate',   'palisade_gate'],
        804 => ['Palisade Gate',   'palisade_gate'],
    ];

    /**
     * Real world map IDs.
     *
     * @var array
     */
    public static $REAL_WORLD_MAPS = [
        Map::IBERIA,
        Map::BRITAIN,
        Map::MIDEAST,
        Map::TEXAS,
        Map::ITALY,
        Map::CENTRALAMERICA,
        Map::FRANCE,
        Map::NORSELANDS,
        Map::SEAOFJAPAN,
        Map::BYZANTINUM,
    ];
}
