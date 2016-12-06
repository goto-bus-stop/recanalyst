<?php

namespace RecAnalyst\Processors;

use RecAnalyst\RecordedGame;

/**
 * Extracts the map name from the Objectives tab of a recorded game. That's the
 * only place where the name of a custom random map is stored.
 */
class MapName
{
    /**
     * Recorded game file to use.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Possible formats for map type lines in the Objectives tab.
     *
     * @var string[]
     */
    private $mapTypeRegexes;

    /**
     * Create a map name extractor.
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     */
    public function __construct(RecordedGame $rec)
    {
        $this->rec = $rec;

        // The Map Type strings used in the Objectives message tab are stored in
        // string id 9654. This array was generated using:
        // grep "9654" "SteamApps/common/Age2HD/resources/*/strings/key-value/key-value-strings-utf8.txt"
        //
        // HD Edition uses UTF-8, but older versions used localization-specific code pages.
        // Code page information for zh, jp and ko was taken from:
        // https://msdn.microsoft.com/en-us/library/cc194886.aspx
        $this->mapTypeRegexes = [
            'br' => '/Tipo de Mapa: (.*)/',
            'de' => '/Kartentyp: (.*)/',
            'en' => '/Map Type: (.*)/',
            'es' => '/Tipo de mapa: (.*)/',
            'fr' => '/Type de carte : (.*)/',
            'it' => '/Tipo di mappa: (.*)/',
            'jp' => '/' . mb_convert_encoding('マップの種類', 'cp932', 'utf-8') . ': (.*)/',
            'jp_utf8' => '/マップの種類: (.*)/',
            'ko' => '/' . mb_convert_encoding('지도 종류', 'cp949', 'utf-8') . ': (.*)/',
            'ko_utf8' => '/지도 종류: (.*)/',
            'nl' => '/Kaarttype: (.*)/',
            'ru' => '/' . mb_convert_encoding('Тип карты', 'windows-1251', 'utf-8') . ': (.*)/',
            'ru_utf8' => '/Тип карты: (.*)/',
            'zh' => '/' . mb_convert_encoding('地图类别', 'cp936', 'utf-8') . ': (.*)/',
            'zh_utf8' => '/地图类型: (.*)/',
            'zh_wide' => '/' . mb_convert_encoding('地图类别：', 'cp936', 'utf-8') . '(.*)/',
        ];
    }

    /**
     * Run the processor.
     *
     * @return string|null The map name, if found.
     */
    public function run()
    {
        $header = $this->rec->header();
        $messages = $header->messages;
        $instructions = $messages->instructions;
        $lines = explode("\n", $instructions);
        foreach ($lines as $line) {
            // We don't know what language the game was played in, so we try
            // every language we know.
            foreach ($this->mapTypeRegexes as $rx) {
                $matches = [];
                if (preg_match($rx, $line, $matches)) {
                    return $matches[1];
                }
            }
        }
    }
}
