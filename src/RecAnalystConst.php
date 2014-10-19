<?php
/**
 * Defines RecAnalystConst class.
 *
 * @package recAnalyst
 */

namespace RecAnalyst;


/**
 * Class RecAnalystConst.
 *
 * RecAnalystConst implements constants used for RecAnalyst.
 *
 * @todo I don't like how this looks, not sure how to make it sexy yet. Maybe use an actual localization thing.
 * @package recAnalyst
 */

final class RecAnalystConst {

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
     * Map strings. Can be localized.
     * @var array
     */
    public static $MAPS = array(
        Map::ARABIA         => 'Arabia',
        Map::ARCHIPELAGO    => 'Archipelago',
        Map::BALTIC         => 'Baltic',
        Map::BLACKFOREST    => 'Black Forest',
        Map::COASTAL        => 'Coastal',
        Map::CONTINENTAL    => 'Continental',
        Map::CRATERLAKE     => 'Crater Lake',
        Map::FORTRESS       => 'Fortress',
        Map::GOLDRUSH       => 'Gold Rush',
        Map::HIGHLAND       => 'Highland',
        Map::ISLANDS        => 'Islands',
        Map::MEDITERRANEAN  => 'Mediterranean',
        Map::MIGRATION      => 'Migration',
        Map::RIVERS         => 'Rivers',
        Map::TEAMISLANDS    => 'Team Islands',
        Map::RANDOM         => 'Random',
        Map::SCANDINAVIA    => 'Scandinavia',
        Map::MONGOLIA       => 'Mongolia',
        Map::YUCATAN        => 'Yucatan',
        Map::SALTMARSH      => 'Salt Marsh',
        Map::ARENA          => 'Arena',
        Map::KINGOFTHEHILL  => 'King of the Hill',
        Map::OASIS          => 'Oasis',
        Map::GHOSTLAKE      => 'Ghost Lake',
        Map::NOMAD          => 'Nomad',
        Map::IBERIA         => 'Iberia',
        Map::BRITAIN        => 'Britain',
        Map::MIDEAST        => 'Mideast',
        Map::TEXAS          => 'Texas',
        Map::ITALY          => 'Italy',
        Map::CENTRALAMERICA => 'Central America',
        Map::FRANCE         => 'France',
        Map::NORSELANDS     => 'Norse Lands',
        Map::SEAOFJAPAN     => 'Sea of Japan (East Sea)',
        Map::BYZANTINUM     => 'Byzantinum',
        Map::CUSTOM         => 'Custom',
        Map::BLINDRANDOM    => 'Blind Random',
    );

    /**
     * Game version strings. Can be localized.
     * @var array
     */
    public static $GAME_VERSIONS = array(
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
    );

    /**
     * Map style strings. Can be localized
     * @var array
     */
    public static $MAP_STYLES = array(
        'Standard',
        'Real World',
        'Custom',
    );

    /**
     * Difficulty level strings. Can be localized.
     * @var array
     */
    public static $DIFFICULTY_LEVELS = array(
        'Hardest',
        'Hard',
        'Moderate',
        'Standard',
        'Easiest',
    );

    /**
     * Difficulty level strings for AOK. Can be localized.
     * @var array
     */
    public static $AOK_DIFFICULTY_LEVELS = array(
        'Hardest',
        'Hard',
        'Moderate',
        'Easy',
        'Easiest',
    );

    /**
     * Game type strings. Can be localized.
     * @var array
     */
    public static $GAME_TYPES = array(
        'Random map',
        'Regicide',
        'Death match',
        'Scenario',
        'Campaign',
        'King of the Hill',
        'Wonder race',
        'Defend Wonder',
        'Turbo Random map',
    );

    /**
     * Game speed strings. Can be localized.
     * @var array
     */
    public static $GAME_SPEEDS = array(
        100 => 'Slow',
        150 => 'Normal',
        200 => 'Fast',
    );

    /**
     * Reveal setting strings. Can be localized.
     * @var array
     */
    public static $REVEAL_SETTINGS = array(
        'Normal',
        'Explored',
        'All Visible',
    );

    /**
     * Map size strings. Can be localized.
     * @var array
     */
    public static $MAP_SIZES = array(
        'Tiny (2 players)',
        'Small (3 players)',
        'Medium (4 players)',
        'Normal (6 players)',
        'Large (8 players)',
        'Giant',
    );

    /**
     * Starting age strings. Can be localized.
     * @var array
     */
    public static $STARTING_AGES = array(
        'Dark Age',
        'Feudal Age',
        'Castle Age',
        'Imperial Age',
        'Post-Imperial Age',
    );

    /**
     * Victory condition strings. Can be localized.
     * @var array
     */
    public static $VICTORY_CONDITIONS = array(
        'Standard',
        'Conquest',
        'Time Limit',
        'Score Limit',
        'Custom',
    );

    /**
     * Civilization strings. Can be localized.
     * @var array
     */
    public static $CIVS = array(
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
    );

    /**
     * Short Civilization strings. Can be localized.
     * @var array
     */
    public static $SHORT_CIVS = array(
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
    );

    public static $COLORS = array(
        0x00 => '#0000ff',
        0x01 => '#ff0000',
        0x02 => '#00ff00',
        0x03 => '#ffff00',
        0x04 => '#00ffff',
        0x05 => '#ff00ff',
        0x06 => '#b9b9b9',
        0x07 => '#ff8201',
    );

    /**
     * Resource strings. Can be localized.
     * @var array
     */
    public static $RESOURCES = array(
        0x00 => 'food',
        0x01 => 'wood',
        0x02 => 'stone',
        0x03 => 'gold',
    );

    /**
     * Research strings. Can be localized.
     * @var array
     */
    public static $RESEARCHES = array(
        101 => array('Feudal Age',            'feudal_age'),
        102 => array('Castle Age',            'castle_age'),
        103 => array('Imperial Age',          'imperial_age'),
         22 => array('Loom',                  'loom'),
        213 => array('Wheelbarrow',           'wheel_barrow'),
        249 => array('Hand Cart',             'hand_cart'),
          8 => array('Town Watch',            'town_watch'),
        280 => array('Town Patrol',           'town_patrol'),
         14 => array('Horse Collar',          'horse_collar'),
         13 => array('Heavy Plow',            'heavy_plow'),
         12 => array('Crop Rotation',         'crop_rotation'),
        202 => array('Double Bit Axe',        'double_bit_axe'),
        203 => array('Bow Saw',               'bow_saw'),
        221 => array('Two Man Saw',           'two_man_saw'),
         55 => array('Gold Mining',           'gold_mining'),
        278 => array('Stone Mining',          'stone_mining'),
        182 => array('Gold Shaft Mining',     'gold_shaft_mining'),
        279 => array('Stone Shaft Mining',    'stone_shaft_mining'),
         19 => array('Cartography',           'cartography'),
         23 => array('Coinage',               'coinage'),
         48 => array('Caravan',               'caravan'),
         17 => array('Banking',               'banking'),
         15 => array('Guilds',                'guilds'),
        211 => array('Padded Archer Armor',   'padded_archer_armor'),
        212 => array('Leather Archer Armor',  'leather_archer_armor'),
        219 => array('Ring Archer Armor',     'ring_archer_armor'),
        199 => array('Fletching',             'fletching'),
        200 => array('Bodkin Arrow',          'bodkin_arrow'),
        201 => array('Bracer',                'bracer'),
         67 => array('Forging',               'forging'),
         68 => array('Iron Casting',          'iron_casting'),
         75 => array('Blast Furnace',         'blast_furnace'),
         81 => array('Scale Barding Armor',   'scale_barding_armor'),
         82 => array('Chain Barding Armor',   'chain_barding_armor'),
         80 => array('Plate Barding Armor',   'plate_barding_armor'),
         74 => array('Scale Mail Armor',      'scale_mail_armor'),
         76 => array('Chain Mail Armor',      'chain_mail_armor'),
         77 => array('Plate Mail Armor',      'plate_mail_armor'),
         50 => array('Masonry',               'masonry'),
        194 => array('Fortified Wall',        'fortified_wall'),
         93 => array('Ballistics',            'ballistics'),
        380 => array('Heated Shot',           'heated_shot'),
        322 => array('Murder Holes',          'murder_holes'),
         54 => array('Treadmill Crane',       'treadmill_crane'),
         51 => array('Architecture',          'architecture'),
         47 => array('Chemistry',             'chemistry'),
        377 => array('Siege Engineers',       'siege_engineers'),
        140 => array('Guard Tower',           'guard_tower'),
         63 => array('Keep',                  'keep'),
         64 => array('Bombard Tower',         'bombard_tower'),
        222 => array('Man At Arms',           'man_at_arms'),
        207 => array('Long Swordsman',        'long_swordsman'),
        217 => array('Two Handed Swordsman',  'two_handed_swordsman'),
        264 => array('Champion',              'champion'),
        197 => array('Pikeman',               'pikeman'),
        429 => array('Halberdier',            'halberdier'),
        434 => array('Elite Eagle Warrior',   'eagle_warrior'),
         90 => array('Tracking',              'tracking'),
        215 => array('Squires',               'squires'),
        100 => array('Crossbow',              'crossbow'),
        237 => array('Arbalest',              'arbalest'),
         98 => array('Elite Skirmisher',      'elite_skirmisher'),
        218 => array('Heavy Cavalry Archer',  'heavy_cavalry_archer'),
        437 => array('Thumb Ring',            'thumb_ring'),
        436 => array('Parthian Tactics',      'parthian_tactics'),
        254 => array('Light Cavalry',         'light_cavalry'),
        428 => array('Hussar',                'hussar'),
        209 => array('Cavalier',              'cavalier'),
        265 => array('Paladin',               'paladin'),
        236 => array('Heavy Camel',           'heavy_camel'),
        435 => array('Bloodlines',            'bloodlines'),
         39 => array('Husbandry',             'husbandry'),
        257 => array('Onager',                'onager'),
        320 => array('Siege Onager',          'siege_onager'),
         96 => array('Capped Ram',            'capped_ram'),
        255 => array('Siege Ram',             'siege_ram'),
        239 => array('Heavy Scorpion',        'heavy_scorpion'),
        316 => array('Redemption',            'redemption'),
        252 => array('Fervor',                'fervor'),
        231 => array('Sanctity',              'sanctity'),
        319 => array('Atonement',             'atonement'),
        441 => array('Herbal Medicine',       'herbal_medicine'),
        439 => array('Heresy',                'heresy'),
        230 => array('Block Printing',        'block_printing'),
        233 => array('Illumination',          'illumination'),
         45 => array('Faith',                 'faith'),
        438 => array('Theocracy',             'theocracy'),
         34 => array('War Galley',            'war_galley'),
         35 => array('Galleon',               'galleon'),
        246 => array('Fast Fire Ship',        'fast_fire_ship'),
        244 => array('Heavy Demolition Ship', 'heavy_demolition_ship'),
         37 => array('Cannon Galleon',        'cannon_galleon'),
        376 => array('Elite Cannon Galleon',  'cannon_galleon'),
        373 => array('Shipwright',            'shipwright'),
        374 => array('Careening',             'careening'),
        375 => array('Dry Dock',              'dry_dock'),
        379 => array('Hoardings',             'hoardings'),
        321 => array('Sappers',               'sappers'),
        315 => array('Conscription',          'conscription'),
        408 => array('Spies / Treason',       'spy'),
        // unique-unit-upgrade
        432 => array('Elite Jaguar Man',      'jaguar_man'),
        361 => array('Elite Cataphract',      'cataphract'),
        370 => array('Elite Woad Raider',     'woad_raider'),
        362 => array('Elite Chu-Ko-Nu',       'chu_ko_nu'),
        360 => array('Elite Longbowman',      'longbowman'),
        363 => array('Elite Throwing Axeman', 'throwing_axeman'),
        365 => array('Elite Huskarl',         'huskarl'),
          2 => array('Elite Tarkan',          'tarkan'),
        366 => array('Elite Samurai',         'samurai'),
        450 => array('Elite War Wagon',       'war_wagon'),
        448 => array('Elite Turtle Ship',     'turtle_ship'),
        //348 => array('Elite Turtle Ship',     'turtle_ship'),
         27 => array('Elite Plumed Archer',   'plumed_archer'),
        371 => array('Elite Mangudai',        'mangudai'),
        367 => array('Elite War Elephant',    'war_elephant'),
        368 => array('Elite Mameluke',        'mameluke'),
        //378 => array('Elite Mameluke',        'mameluke'),
         60 => array('Elite Conquistador',    'conquistador'),
        364 => array('Elite Teutonic Knight', 'teutonic_knight'),
        369 => array('Elite Janissary',       'janissary'),
        398 => array('Elite Berserk',         'berserk'),
        372 => array('Elite Longboat',        'longboat'),
        // unique-research
         24 => array('Garland Wars',          'unique_tech'),
         61 => array('Logistica',             'unique_tech'),
          5 => array('Furor Celtica',         'unique_tech'),
         52 => array('Rocketry',              'unique_tech'),
          3 => array('Yeomen',                'unique_tech'),
         83 => array('Bearded Axe',           'unique_tech'),
         16 => array('Anarchy',               'unique_tech'),
        457 => array('Perfusion',             'unique_tech'),
         21 => array('Atheism',               'unique_tech'),
         59 => array('Kataparuto',            'unique_tech'),
        445 => array('Shinkichon',            'unique_tech'),
          4 => array('El Dorado',             'unique_tech'),
          6 => array('Drill',                 'unique_tech'),
          7 => array('Mahouts',               'unique_tech'),
          9 => array('Zealotry',              'unique_tech'),
        440 => array('Supremacy',             'unique_tech'),
         11 => array('Crenellations',         'unique_tech'),
         10 => array('Artillery',             'unique_tech'),
         49 => array('Berserkergang',         'unique_tech'),
        // AoFE
        526 => array('Hunting Dogs',              'hunting_dogs'),
        521 => array('Imperial Camel',            'imperial_camel'),
        517 => array('Couriers',                  'unique_tech'),
        516 => array('Andean Sling',              'unique_tech2'),
        515 => array('Recurve Bow',               'unique_tech'),
        514 => array('Mercenaries',               'unique_tech2'),
        513 => array('Druzhina',                  'unique_tech'),
        512 => array('Orthodoxy',                 'unique_tech2'),
        507 => array('Shatagni',                  'unique_tech'),
        506 => array('Sultans',                   'unique_tech2'),
        499 => array('Silk Road',                 'unique_tech'),
        494 => array('Pavise',                    'unique_tech2'),
        493 => array('Chivalry',                  'unique_tech2'),
        492 => array('Inquisition',               'unique_tech2'),
        491 => array('Sipahi',                    'unique_tech2'),
        490 => array('Madrasah',                  'unique_tech2'),
        489 => array('Ironclad',                  'unique_tech2'),
        488 => array('Boiling Oil',               'unique_tech2'),
        487 => array('Nomads',                    'unique_tech2'),
        486 => array('Panokseon',                 'unique_tech2'),
        485 => array('Tlatoani',                  'unique_tech2'),
        484 => array('Marauders',                 'unique_tech2'),
        483 => array('Stronghold',                'unique_tech2'),
        464 => array('Greek Fire',                'unique_tech2'),
        463 => array('Chieftains',                'unique_tech2'),
        462 => array('Great Wall',                'unique_tech2'),
        461 => array('Warwolf',                   'unique_tech2'),
        460 => array('Atlatl',                    'unique_tech2'),
        384 => array('Eagle Warrior',             'heavy_eagle_warrior'),
        494 => array('Gillnets',                  'gillnets'),
        509 => array('Elite Kamayuk',             'kamayuk'),
        504 => array('Elite Boyar',               'boyar'),
        481 => array('Elite Elephant Archer',     'elephant_archer'),
        472 => array('Elite Magyar Huszar',       'magyar_huszar'),
        468 => array('Elite Genoese Crossbowman', 'genoese_crossbowman')
    );

    /**
     * Unit strings. Can be localized.
     * @var array
     */
    public static $UNITS = array(
          4 => array('Archer',                'archer'),
          5 => array('Hand Cannoneer',        'hand_cannoneer'),
          6 => array('Elite Skirmisher',      'elite_skirmisher'),
          7 => array('Skirmisher',            'skirmisher'),
          8 => array('Longbowman',            'longbowman'),
         11 => array('Mangudai',              'mangudai'),
         13 => array('Fishing Ship',          'fishing_ship'),
         17 => array('Trade Cog',             'trade_cog'),
         21 => array('War Galley',            'war_galley'),
         24 => array('Crossbowman',           'crossbowman'),
         25 => array('Teutonic Knight',       'teutonic_knight'),
         35 => array('Battering Ram',         'battering_ram'),
         36 => array('Bombard Cannon',        'bombard_cannon'),
         38 => array('Knight',                'knight'),
         39 => array('Cavalry Archer',        'cavalry_archer'),
         40 => array('Cataphract',            'cataphract'),
         41 => array('Huskarl',               'huskarl'),
         //42 => array('Trebuchet (Unpacked)',  'trebuchet'),
         46 => array('Janissary',             'janissary'),
         73 => array('Chu Ko Nu',             'chu_ko_nu'),
         74 => array('Militia',               'militiaman'),
         75 => array('Man At Arms',           'man_at_arms'),
         76 => array('Heavy Swordsman',       'heavy_swordsman'),
         77 => array('Long Swordsman',        'long_swordsman'),
         83 => array('Villager',              'villager'),
         93 => array('Spearman',              'spearman'),
        125 => array('Monk',                  'monk'),
        //128 => array('Trade Cart, Empty',     ''),
        128 => array('Trade Cart',            'trade_cart'),
        //204 => array('Trade Cart, Full',      ''),
        232 => array('Woad Raider',           'woad_raider'),
        239 => array('War Elephant',          'war_elephant'),
        250 => array('Longboat',              'longboat'),
        279 => array('Scorpion',              'scorpion'),
        280 => array('Mangonel',              'mangonel'),
        281 => array('Throwing Axeman',       'throwing_axeman'),
        282 => array('Mameluke',              'mameluke'),
        283 => array('Cavalier',              'cavalier'),
        //286 => array('Monk With Relic',       ''),
        291 => array('Samurai',               'samurai'),
        329 => array('Camel',                 'camel'),
        330 => array('Heavy Camel',           'heavy_camel'),
        //331 => array('Trebuchet, P',          'trebuchet'),
        331 => array('Trebuchet',             'trebuchet'),
        358 => array('Pikeman',               'pikeman'),
        359 => array('Halberdier',            'halberdier'),
        420 => array('Cannon Galleon',        'cannon_galleon'),
        422 => array('Capped Ram',            'capped_ram'),
        434 => array('King',                  'king'),
        440 => array('Petard',                'petard'),
        441 => array('Hussar',                'hussar'),
        442 => array('Galleon',               'galleon'),
        448 => array('Scout Cavalry',         'scout_cavalry'),
        473 => array('Two Handed Swordsman',  'two_handed_swordsman'),
        474 => array('Heavy Cavalry Archer',  'heavy_cavalry_archer'),
        492 => array('Arbalest',              'arbalest'),
        //493 => array('Adv Heavy Crossbowman',  ''),
        527 => array('Demolition Ship',       'demolition_ship'),
        528 => array('Heavy Demolition Ship', 'heavy_demolition_ship'),
        529 => array('Fire Ship',             'fire_ship'),
        530 => array('Elite Longbowman',      'longbowman'),
        531 => array('Elite Throwing Axeman', 'throwing_axeman'),
        532 => array('Fast Fire Ship',        'fast_fire_ship'),
        533 => array('Elite Longboat',        'longboat'),
        534 => array('Elite Woad Raider',     'woad_raider'),
        539 => array('Galley',                'galley'),
        542 => array('Heavy Scorpion',        'heavy_scorpion'),
        545 => array('Transport Ship',        'transport_ship'),
        546 => array('Light Cavalry',         'light_cavalry'),
        548 => array('Siege Ram',             'siege_ram'),
        550 => array('Onager',                'onager'),
        553 => array('Elite Cataphract',      'cataphract'),
        554 => array('Elite Teutonic Knight', 'teutonic_knight'),
        555 => array('Elite Huskarl',         'huskarl'),
        556 => array('Elite Mameluke',        'mameluke'),
        557 => array('Elite Janissary',       'janissary'),
        558 => array('Elite War Elephant',    'war_elephant'),
        559 => array('Elite Chu Ko Nu',       'chu_ko_nu'),
        560 => array('Elite Samurai',         'samurai'),
        561 => array('Elite Mangudai',        'mangudai'),
        567 => array('Champion',              'champion'),
        569 => array('Paladin',               'paladin'),
        588 => array('Siege Onager',          'siege_onager'),
        692 => array('Berserk',               'berserk'),
        694 => array('Elite Berserk',         'berserk'),
        725 => array('Jaguar Warrior',        'jaguar_man'),
        726 => array('Elite Jaguar Warrior',  'jaguar_man'),
        //748 => array('Cobra Car',             ''),
        751 => array('Eagle Warrior',         'eagle_warrior'),
        752 => array('Elite Eagle Warrior',   'eagle_warrior'),
        755 => array('Tarkan',                'tarkan'),
        757 => array('Elite Tarkan',          'tarkan'),
        759 => array('Huskarl',               'huskarl'),
        761 => array('Elite Huskarl',         'huskarl'),
        763 => array('Plumed Archer',         'plumed_archer'),
        765 => array('Elite Plumed Archer',   'plumed_archer'),
        771 => array('Conquistador',          'conquistador'),
        773 => array('Elite Conquistador',    'conquistador'),
        775 => array('Missionary',            'missionary'),
        //812 => array('Jaguar',                ''),
        827 => array('War Wagon',             'war_wagon'),
        829 => array('Elite War Wagon',       'war_wagon'),
        831 => array('Turtle Ship',           'turtle_ship'),
        832 => array('Elite Turtle Ship',     'turtle_ship'),
        // AoFE
        866 => array('Genoese Crossbowman',       'genoese_crossbowman'),
        868 => array('Elite Genoese Crossbowman', 'genoese_crossbowman'),
        886 => array('Tarkan',                    'tarkan'),
        887 => array('Elite Tarkan',              'tarkan'),
        882 => array('Condottiero',               'condottiero'),
        184 => array('Condottiero',               'condottiero'),
        879 => array('Kamayuk',                   'kamayuk'),
        881 => array('Elite Kamayuk',             'kamayuk'),
        876 => array('Boyar',                     'boyar'),
        878 => array('Elite Boyar',               'boyar'),
        873 => array('Elephant Archer',           'elephant_archer'),
        875 => array('Elite Elephant Archer',     'elephant_archer'),
        869 => array('Magyar Huszar',             'magyar_huszar'),
        871 => array('Elite Magyar Huszar',       'magyar_huszar'),
        753 => array('Eagle Warrior',             'eagle_warrior'),
        207 => array('Imperial Camel',            'imperial_camel'),
        185 => array('Slinger',                   'slinger'),
    );

    /**
     * Building strings. Can be localized.
     * @var array
     */
    public static $BUILDINGS = array(
         12 => array('Barracks',        'barracks'),
         45 => array('Dock',            'dock'),
         49 => array('Siege Workshop',  'siege_workshop'),
         50 => array('Farm',            'farm'),
         68 => array('Mill',            'mill'),
         70 => array('House',           'house'),
         72 => array('Wall, Palisade',  'palisade_wall'),
         79 => array('Watch Tower',     'watch_tower'),
         82 => array('Castle',          'castle'),
         84 => array('Market',          'market'),
         87 => array('Archery Range',   'archery_range'),
        101 => array('Stable',          'stable'),
        103 => array('Blacksmith',      'blacksmith'),
        104 => array('Monastery',       'monastery'),
        109 => array('Town Center',     'town_center'),
        117 => array('Wall, Stone',     'stone_wall'),
        155 => array('Wall, Fortified', 'fortified_wall'),
        199 => array('Fish Trap',       'fish_trap'),
        209 => array('University',      'university'),
        234 => array('Guard Tower',     'guard_tower'),
        235 => array('Keep',            'keep'),
        236 => array('Bombard Tower',   'bombard_tower'),
        276 => array('Wonder',          'wonder'),
        487 => array('Gate',            'gate'),
        490 => array('Gate',            'gate'),
        562 => array('Lumber Camp',     'lumber_camp'),
        584 => array('Mining Camp',     'mining_camp'),
        598 => array('Outpost',         'outpost'),
        621 => array('Town Center',     'town_center'),
        665 => array('Gate',            'gate'),
        673 => array('Gate',            'gate'),
        792 => array('Palisade Gate',   'palisade_gate'),
        796 => array('Palisade Gate',   'palisade_gate'),
        800 => array('Palisade Gate',   'palisade_gate'),
        804 => array('Palisade Gate',   'palisade_gate'),
    );

    /**
     * Terrain colors.
     * @var array
     */
    public static $TERRAIN_COLORS = array(
        array(0x33, 0x97, 0x27),
        array(0x30, 0x5d, 0xb6),
        array(0xe8, 0xb4, 0x78),
        array(0xe4, 0xa2, 0x52),
        array(0x54, 0x92, 0xb0),
        array(0x33, 0x97, 0x27),
        array(0xe4, 0xa2, 0x52),
        array(0x82, 0x88, 0x4d),//
        array(0x82, 0x88, 0x4d),//
        array(0x33, 0x97, 0x27),
        array(0x15, 0x76, 0x15),
        array(0xe4, 0xa2, 0x52),
        array(0x33, 0x97, 0x27),
        array(0x15, 0x76, 0x15),
        array(0xe8, 0xb4, 0x78),
        array(0x30, 0x5d, 0xb6),//
        array(0x33, 0x97, 0x27),//
        array(0x15, 0x76, 0x15),
        array(0x15, 0x76, 0x15),
        array(0x15, 0x76, 0x15),
        array(0x15, 0x76, 0x15),
        array(0x15, 0x76, 0x15),
        array(0x00, 0x4a, 0xa1),
        array(0x00, 0x4a, 0xbb),
        array(0xe4, 0xa2, 0x52),
        array(0xe4, 0xa2, 0x52),
        array(0xff, 0xec, 0x49),//
        array(0xe4, 0xa2, 0x52),
        array(0x30, 0x5d, 0xb6),//
        array(0x82, 0x88, 0x4d),//
        array(0x82, 0x88, 0x4d),//
        array(0x82, 0x88, 0x4d),//
        array(0xc8, 0xd8, 0xff),
        array(0xc8, 0xd8, 0xff),
        array(0xc8, 0xd8, 0xff),
        array(0x98, 0xc0, 0xf0),
        array(0xc8, 0xd8, 0xff),//
        array(0x98, 0xc0, 0xf0),
        array(0xc8, 0xd8, 0xff),
        array(0xc8, 0xd8, 0xff),
        array(0xe4, 0xa2, 0x52),
    );

    /**
     * Object colors.
     * @var array
     */
    public static $OBJECT_COLORS = array(
        Unit::GOLDMINE   => array(0xff, 0xc7, 0x00),
        Unit::STONEMINE  => array(0x91, 0x91, 0x91),
        Unit::CLIFF1     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF2     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF3     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF4     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF5     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF6     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF7     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF8     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF9     => array(0x71, 0x4b, 0x33),
        Unit::CLIFF10    => array(0x71, 0x4b, 0x33),
        Unit::RELIC      => array(0xff, 0xff, 0xff),
        Unit::TURKEY     => array(0xa5, 0xc4, 0x6c),
        Unit::SHEEP      => array(0xa5, 0xc4, 0x6c),
        Unit::DEER       => array(0xa5, 0xc4, 0x6c),
        Unit::BOAR       => array(0xa5, 0xc4, 0x6c),
        Unit::JAVELINA   => array(0xa5, 0xc4, 0x6c),
        Unit::FORAGEBUSH => array(0xa5, 0xc4, 0x6c),
    );

    /**
     * Player colors.
     * @var array
     */
    public static $PLAYER_COLORS = array(
        0x00 => array(0x00, 0x00, 0xff),
        0x01 => array(0xff, 0x00, 0x00),
        0x02 => array(0x00, 0xff, 0x00),
        0x03 => array(0xff, 0xff, 0x00),
        0x04 => array(0x00, 0xff, 0xff),
        0x05 => array(0xff, 0x00, 0xff),
        0x06 => array(0xb9, 0xb9, 0xb9),
        0x07 => array(0xff, 0x82, 0x01),
    );

    /**
     * Real world maps.
     * @var array
     */
    public static $REAL_WORLD_MAPS = array(
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
    );

    /**
     * Cliff units.
     * @var array
     */
    public static $CLIFF_UNITS = array(
        Unit::CLIFF1,
        Unit::CLIFF2,
        Unit::CLIFF3,
        Unit::CLIFF4,
        Unit::CLIFF5,
        Unit::CLIFF6,
        Unit::CLIFF7,
        Unit::CLIFF8,
        Unit::CLIFF9,
        Unit::CLIFF10,
    );

    /**
     * Gate units.
     * @var array
     */
    public static $GATE_UNITS = array(
        Unit::GATE,
        Unit::GATE2,
        Unit::GATE3,
        Unit::GATE4,
    );

    /**
     * Palisade gate units.
     * @var array
     */
    public static $PALISADE_GATE_UNITS = array(
        Unit::PALISADE_GATE,
        Unit::PALISADE_GATE2,
        Unit::PALISADE_GATE3,
        Unit::PALISADE_GATE4,
    );
}
