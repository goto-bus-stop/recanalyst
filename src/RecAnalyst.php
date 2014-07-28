<?php
/* *************************************************************************
 *                       AOC Recorded Games Analyzer
 *                       ---------------------------
 *    begin            : Monday, December 3, 2007
 *    copyright        : (c) 2007-2013 biegleux
 *    email            : biegleux(at)gmail(dot)com
 *
 *    recAnalyst v2.1.0 2012/06/21
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see http://www.gnu.org/licenses/.
 *
 *    Thanks to bari [aocai-lj(at)infoseek.jp] for sharing mgx file format
 *    description.
 ************************************************************************* */
/**
 * Defines RecAnalyst class.
 *
 * @package recAnalyst
 */

namespace RecAnalyst;

/**
 * Class RecAnalyst.
 *
 * RecAnalyst implements analyzing of recorded games for both AOK and AOC.
 *
 * @package recAnalyst
 */
class RecAnalyst {

    /**
     * Internal stream containing header information.
     * @var string
     */
    protected $headerStream;

    /**
     * Internal stream containing body information.
     * @var string
     */
    protected $bodyStream;

    /**
     * An array containing map data.
     * $var array
     */
    protected $_mapData;

    /**
     * Map width.
     * @var int
     */
    protected $_mapWidth;

    /**
     * Map height.
     * @var int
     */
    protected $_mapHeight;

    /**
     * Game settings information.
     * @var GameSettings
     */
    public $gameSettings;

    /**
     * Game information.
     * @var GameInfo
     */
    public $gameInfo;

    /**
     * List of players in the game.
     */
    public $players;
    /**
     * List of players in the game, indexed by their ingame index.
     */
    public $playersByIndex;

    /**
     * List of teams in the game.
     * @var TeamList
     */
    public $teams;

    /**
     * An array containing pre-game chat.
     * @var array
     */
    public $pregameChat;

    /**
     * An array containing in-game chat.
     * @var array
     */
    public $ingameChat;

    /**
     * An associative array containing "unit_type_id - unit_num" pairs.
     * @var array
     */
    public $units;

    /**
     * An associative multi-dimesional array containing "building_type_id - building_num" pairs for each player.
     * @var array
     */
    public $buildings;

    /**
     * Elapsed time for analyzing in miliseconds.
     * @var int
     */
    protected $_analyzeTime;

    /**
     * Internal queue.
     * @var array
     */
    protected $_queue;

    /**
     * True, if the file being analyzed is mgx. False otherwise.
     * @var bool
     */
    protected $_isMgx;

    /**
     * True, if the file being analyzed is mgl. False otherwise.
     * @var bool
     */
    protected $_isMgl;

    /**
     * True, if the file being analyzed is mgz. False otherwise.
     * @var bool
     */
    protected $_isMgz;

    /**
     * List of GAIA objects.
     * @var array
     */
    protected $gaiaObjects;

    /**
     * List of any player objects.
     * @var array
     */
    protected $playerObjects;

    /**
     * List of tributes.
     * @var array
     */
    public $tributes;

    /**
     * Configuration object.
     * @var Config
     */
    public $config = null;

    private $_headerLen;
    private $_nextPos;

    const MGX_EXT = 'mgx';
    const MGL_EXT = 'mgl';
    const MGZ_EXT = 'mgz';

    /**
     * Class constructor.
     * @see RecAnalyst::reset().
     * @return void
     */
    public function __construct($config = null) {
        $this->reset();
        if ($config == null) $config = new Config();
        $this->config = $config;
    }

    /**
     * Resets the internal state.
     * @return void
     */
    public function reset() {
        $this->headerStream = new MemoryStream();
        $this->bodyStream = new MemoryStream();
        $this->gameSettings = new GameSettings($this);
        $this->gameInfo = new GameInfo($this);
        $this->players = array();
        $this->playersByIndex = array();
        $this->teams = array();
        $this->pregameChat = array();
        $this->ingameChat = array();
        $this->units = array();
        $this->buildings = array();
        $this->_mapData = array();
        $this->_mapWidth = $this->_mapHeight = 0;
        $this->_analyzeTime = 0;
        $this->_queue = array();
        $this->_isMgx = false;
        $this->_isMgl = false;
        $this->_isMgz = false;
        $this->gaiaObjects = array();
        $this->playerObjects = array();

        $this->tributes = array();
        $this->_headerLen = 0;
        $this->_nextPos = 0;
    }

    /**
     * Converts game's time to string representation.
     * @param int $time Game time.
     * @param string $format Desired string format.
     * @return string Time in formatted string.
     * @static
     */
    public static function gameTimeToString($time, $format = '%02d:%02d:%02d') {
        if ($time == 0) {
            return '-';
        }
        $hour   =  (int)($time / 1000 / 3600);
        $minute = ((int)($time / 1000 / 60)) % 60;
        $second = ((int)($time / 1000)) % 60;
        return sprintf($format, $hour, $minute, $second);
    }

    /**
     * Loads the file for analysis.
     * @param string $filename
     * @param mixed $input File handler or file contents.
     * @return void
     */
    public function load($filename, $input) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $this->extractStreams($ext, $input);
    }

    /**
     * Extracts header and body streams from recorded game.
     * @param string $ext File extension.
     * @param mixed $input File handler or file contents.
     * @return void
     * @throws RecAnalystException
     * @todo input as file contents
     */
    protected function extractStreams($ext, $input) {
        if (empty($input)) {
            throw new RecAnalystException('No file has been specified for analyzing',
                RecAnalystException::FILE_NOT_SPECIFIED);
        }
        if ($ext == self::MGL_EXT) {
            $this->_isMgl = true;
            $this->_isMgx = false;
            $this->_isMgz = false;
        } elseif ($ext == self::MGX_EXT) {
            $this->_isMgx = true;
            $this->_isMgl = false;
            $this->_isMgz = false;
        } elseif ($ext == self::MGZ_EXT) {
            $this->_isMgx = true;
            $this->_isMgl = false;
            $this->_isMgz = true;
        } else {
            throw new RecAnalystException('Wrong file extension, file format is not supported',
                RecAnalystException::FILEFORMAT_NOT_SUPPORTED);
        }
        if (is_string($input)) {
            // Create file stream from input string
            $fp = fopen('php://memory', 'r+');
            fwrite($fp, $input);
            rewind($fp);
            unset($input);
        } else {
           $fp = $input;
        }
        if (($packed_data = fread($fp, 4)) === false || strlen($packed_data) < 4) {
            throw new RecAnalystException('Unable to read the header length',
                RecAnalystException::HEADERLEN_READERROR);
        }
        $unpacked_data = unpack('V', $packed_data);
        $this->_headerLen = $unpacked_data[1];
        if (!$this->_headerLen) {
            throw new RecAnalystException('Header length is zero',
                RecAnalystException::EMPTY_HEADER);
        }
        if ($this->_isMgx) {
            $packed_data = fread($fp, 4);
            if ($packed_data === false || strlen($packed_data) < 4) {
                $this->_nextPos = 0;
            } else {
                $unpacked_data = unpack('V', $packed_data);
                $this->_nextPos = $unpacked_data[1];
            }
        }
        $this->_headerLen -= $this->_isMgx ? 8 : 4;
        $read = 0;
        $bindata = '';
        while ($read < $this->_headerLen && ($buff = fread($fp, $this->_headerLen - $read))) {
            $read += strlen($buff);
            $bindata .= $buff;
        }
        $read = 0;
        while (!feof($fp)) {
            $buff = fread($fp, 8192);
            $this->bodyStream->write($buff);
        }
        unset($buff);
        fclose($fp);

        $this->headerStream->write(gzinflate($bindata));//, 8388608));  // 8MB
        unset($bindata);

        if (!$this->headerStream->getSize()) {
            throw new RecAnalystException('Cannot decompress header section',
                RecAnalystException::HEADER_DECOMPRESSERROR);
        }
    }

    /**
     * Analyzes header stream.
     * @return bool True if the stream was analyzed successfully, false otherwise.
     * @throws RecAnalystException
     */
    protected function analyzeheaderStream() {
        $constant2                 = pack('c*', 0x9A, 0x99, 0x99, 0x99, 0x99, 0x99, 0xF9, 0x3F);
        $separator                 = pack('c*', 0x9D, 0xFF, 0xFF, 0xFF);
        $scenario_constant         = pack('c*', 0xF6, 0x28, 0x9C, 0x3F);
        $aok_separator             = pack('c*', 0x9A, 0x99, 0x99, 0x3F);
        $player_info_end_separator = pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);

        $this->headerStream->setPosition(0);
        $size = $this->headerStream->getSize();

        /* getting version */
        $this->headerStream->readBuffer($version, 8);
        $version = rtrim($version); // throw null-termination character
        switch ($version) {
            case RecAnalystConst::TRL_93:
                $this->gameInfo->_gameVersion = $this->_isMgx ? GameInfo::VERSION_AOCTRIAL : GameInfo::VERSION_AOKTRIAL;
                break;
            case RecAnalystConst::VER_93:
                $this->gameInfo->_gameVersion = GameInfo::VERSION_AOK;
                break;
            case RecAnalystConst::VER_94:
                $this->gameInfo->_gameVersion = $this->_isMgz ? GameInfo::VERSION_AOC11 : GameInfo::VERSION_AOC;
                break;
            case RecAnalystConst::VER_95:
                $this->gameInfo->_gameVersion = GameInfo::VERSION_AOC21;
                break;
            case RecAnalystConst::VER_9C:
                $this->gameInfo->_gameVersion = GameInfo::VERSION_UserPatch14;
                break;
            default:
                $this->gameInfo->_gameVersion = $version;
                break;
        }

        switch ($this->gameInfo->_gameVersion) {
            case GameInfo::VERSION_AOK:
            case GameInfo::VERSION_AOKTRIAL:
                $this->_isMgl = true;
                $this->_isMgx = false;
                $this->_isMgz = false;
                break;
            case GameInfo::VERSION_AOC:
            case GameInfo::VERSION_AOCTRIAL:
                $this->_isMgx = true;
                $this->_isMgl = false;
                $this->_isMgz = false;
                break;
            case GameInfo::VERSION_AOC11:
            case GameInfo::VERSION_AOC21:
                $this->_isMgx = true;
                $this->_isMgl = false;
                $this->_isMgz = true;
                break;
        }

        /* getting Trigger_info position */
        $trigger_info_pos = $this->headerStream->rfind($constant2);
        if ($trigger_info_pos == -1) {
            throw new RecAnalystException('"Trigger Info" block has not been found',
                                            RecAnalystException::TRIGGERINFO_NOTFOUND);
        }
        $trigger_info_pos += strlen($constant2);

        /* getting Game_setting position */
        $game_setting_pos = $this->headerStream->rfind($separator,
                                -($size - $trigger_info_pos));
        if ($game_setting_pos == -1) {
            throw new RecAnalystException('"Game Settings" block has not been found',
                                            RecAnalystException::GAMESETTINGS_NOTFOUND);
        }
        $game_setting_pos += strlen($separator);

        /* getting Scenario_header position */
        $scenario_separator = $this->_isMgx ? $scenario_constant : $aok_separator;
        $scenario_header_pos = $this->headerStream->rfind($scenario_separator,
                                    -($size - $game_setting_pos));
        if ($scenario_header_pos != -1) {
            $scenario_header_pos -= 4;  // next_unit_id
        }

        /* getting Game_Settings data */
        /* skip negative[2] */
        $this->headerStream->setPosition($game_setting_pos + 8);
        if ($this->_isMgx) {
            // doesn't exist in AOK
            $this->headerStream->readInt($map_id);
        }
        $this->headerStream->readInt($difficulty);
        $this->headerStream->readBool($lock_teams);

        if ($this->_isMgx) {
            if (isset(RecAnalystConst::$MAPS[$map_id])) {
                $this->gameSettings->map = RecAnalystConst::$MAPS[$map_id];
                if ($map_id == Map::CUSTOM) {
                    $this->gameSettings->_mapStyle = GameSettings::MAPSTYLE_CUSTOM;
                } elseif (in_array($map_id, RecAnalystConst::$REAL_WORLD_MAPS)) {
                    $this->gameSettings->_mapStyle = GameSettings::MAPSTYLE_REALWORLD;
                } else {
                    $this->gameSettings->_mapStyle = GameSettings::MAPSTYLE_STANDARD;
                }

                $this->gameSettings->_mapId = $map_id;
            }
        }

        $this->gameSettings->_difficultyLevel = $difficulty;
        $this->gameSettings->lockDiplomacy = $lock_teams;

        /* getting Player_info data */
        for ($i = 0; $i < 9; $i++) {
            $this->headerStream->readInt($player_data_index);
            $this->headerStream->readInt($human);
            $this->headerStream->readString($playername);

            /* sometimes very rarely index is 1 */
            if ($human == 0x00 || $human == 0x01) {
                continue;
            }

            if ($i) {
                $player = new Player();
                $player->name  = $playername;
                $player->index = $player_data_index;
                $player->human = ($human == 0x02);
                $this->players[] = $player;
                $this->playersByIndex[$player->index] = $player;
            }
        }

        /* getting game type for AOK */
        if ($this->_isMgl) {
            $this->headerStream->setPosition($trigger_info_pos - strlen($constant2));
            $this->headerStream->skip(-6);
            // unknown25
            $this->headerStream->readInt($unknown25);
            switch ($unknown25) {
                case 1:
                    $this->gameSettings->_gameType = GameSettings::TYPE_DEATHMATCH;
                    break;
                case 256:
                    $this->gameSettings->_gameType = GameSettings::TYPE_REGICIDE;
                    break;
            }
        }

        /* getting victory */
        $this->headerStream->setPosition($trigger_info_pos - strlen($constant2));
        if ($this->_isMgx) {
            $this->headerStream->skip(-7);
        }
        $this->headerStream->skip(-110);
        $this->headerStream->readInt($victory_condition);
        $this->headerStream->skip(8);
        $this->headerStream->readChar($is_timelimit);
        if ($is_timelimit) {
            $this->headerStream->readFloat($time_limit);
        }

        $this->gameSettings->victory->_victoryCondition = $victory_condition;
        if ($is_timelimit) {
            $this->gameSettings->victory->_timeLimit = intval(round($time_limit) / 10);
        }

        /* Trigger_info */
        $this->headerStream->setPosition($trigger_info_pos + 1);

        // always zero in mgl? or not really a trigger_info here for aok
        $this->headerStream->readInt($num_trigger);
        if ($num_trigger) {
            /* skip Trigger_info data */
            for ($i = 0; $i < $num_trigger; $i++) {
                $this->headerStream->skip(18);
                $this->headerStream->readInt($desc_len);
                $this->headerStream->skip($desc_len);
                $this->headerStream->readInt($name_len);
                $this->headerStream->skip($name_len);
                $this->headerStream->readInt($num_effect);

                for ($j = 0; $j < $num_effect; $j++) {
                    $this->headerStream->skip(24);
                    $this->headerStream->readInt($num_selected_object);
                    if ($num_selected_object == -1) {
                        $num_selected_object = 0;
                    }

                    $this->headerStream->skip(72);
                    $this->headerStream->readInt($text_len);
                    $this->headerStream->skip($text_len);
                    $this->headerStream->readInt($sound_len);
                    $this->headerStream->skip($sound_len);
                    $this->headerStream->skip($num_selected_object << 2);
                }
                $this->headerStream->skip($num_effect << 2);
                $this->headerStream->readInt($num_condition);
                $this->headerStream->skip(72 * $num_condition);
                $this->headerStream->skip($num_condition << 2);
            }
            $this->headerStream->skip($num_trigger << 2);

            $this->gameSettings->map = '';
            $this->gameSettings->_gameType = GameSettings::TYPE_SCENARIO;
        }

        /* Other_data */
        $team_indexes = array();
        for ($i = 0; $i < 8; $i++) {
            $this->headerStream->readChar($team_indexes[]);
        }

        for ($i = 0, $l = count($this->players); $i < $l; $i++) {
            if ($player = $this->players[$i]) {
                $player->team = $team_indexes[$i] - 1;
            }
        }

        $this->headerStream->skip(1);  // always 1?
        $this->headerStream->readInt($reveal_map);
        $this->headerStream->skip(4);  // always 1?
        $this->headerStream->readInt($map_size);
        $this->headerStream->readInt($pop_limit);
        if ($this->_isMgx) {
            $this->headerStream->readChar($game_type);
            $this->headerStream->readChar($lock_diplomacy);
        }
        $this->gameSettings->_revealMap = $reveal_map;
        $this->gameSettings->_mapSize = $map_size;
        $this->gameSettings->popLimit = $pop_limit;
        if ($this->_isMgx) {
            $this->gameSettings->lockDiplomacy = ($lock_diplomacy == 0x01);
            $this->gameSettings->_gameType = $game_type;
        }

        // here comes pre-game chat (mgl doesn't keep this information)
        if ($this->_isMgx) {
            $this->headerStream->readInt($num_chat);
            for ($i = 0; $i < $num_chat; $i++) {
                $this->headerStream->readString($chat);
                // 0-length chat exists
                if ($chat == '') {
                    continue;
                }

                // pre-game chat messages are stored as @#%dPlayerName: Message, where %d is a digit from 1 to 8 indicating player's index,
                // "PlayerName" is a name of the player, "Message" is a chat message itself, messages usually ends with #0, but not always
                if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
                    $chat = rtrim($chat);  // throw null-termination character
                    $this->pregameChat[] = self::createChatMessage(null, $this->playersByIndex[$chat[2]], substr($chat, 3));
                }
            }
            unset($chat);
        }

        /* skip AI_info if exists */
        $this->headerStream->setPosition(0x0C);
        $this->headerStream->readBool($include_ai);
        if ($include_ai) {
            $this->headerStream->skip(2);
            $this->headerStream->readWord($num_string);
            $this->headerStream->skip(4);
            for ($i = 0; $i < $num_string; $i++) {
                $this->headerStream->readInt($string_length);
                $this->headerStream->skip($string_length);
            }
            $this->headerStream->skip(6);
            for ($i = 0; $i < 8; $i++) {
                $this->headerStream->skip(10);
                $this->headerStream->readWord($num_rule);
                $this->headerStream->skip(4);
                $this->headerStream->skip(400 * $num_rule);
            }
            $this->headerStream->skip(5544);
        }

        /* getting data */
        $this->headerStream->skip(4);
        $this->headerStream->readInt($game_speed);
        $this->headerStream->skip(37);
        $this->headerStream->readWord($rec_player_ref);
        $this->headerStream->readChar($num_player);

        $this->gameSettings->_gameSpeed = $game_speed;

        if ($player = $this->playersByIndex[$rec_player_ref]) {
            $player->owner = true;
        }

        /* getting map */
        $this->headerStream->skip(62);
        if ($this->_isMgl) {
            $this->headerStream->skip(-2);
        }
        $this->headerStream->readInt($map_size_x);
        $this->headerStream->readInt($map_size_y);
        $this->_mapWidth = $map_size_x;
        $this->_mapHeight = $map_size_y;

        $this->headerStream->readInt($num_unknown_data);
        /* unknown data */
        for ($i = 0; $i < $num_unknown_data; $i++) {
            $this->headerStream->skip(1275 + $map_size_x * $map_size_y);
            $this->headerStream->readInt($num_float);
            $this->headerStream->skip(($num_float << 2) + 4);
        }
        $this->headerStream->skip(2);

        /* map data */
        for ($y = 0; $y < $map_size_y; $y++) {
            for ($x = 0; $x < $map_size_x; $x++) {
                $this->headerStream->readChar($terrain_id);
                $this->headerStream->readChar($elevation);
                $this->_mapData[$x][$y] = $terrain_id + 1000 * ($elevation + 1); // hack to save memory
            }
        }

        $this->headerStream->readInt($num_data);
        $this->headerStream->skip(4 + ($num_data << 2));
        for ($i = 0; $i < $num_data; $i++) {
            $this->headerStream->readInt($num_couples);
            $this->headerStream->skip($num_couples << 2);
        }
        $this->headerStream->readInt($map_size_x2);
        $this->headerStream->readInt($map_size_y2);
        $this->headerStream->skip(($map_size_x2 * $map_size_y2 << 2) + 4);
        $this->headerStream->readInt($num_unknown_data2);
        $this->headerStream->skip(27 * $num_unknown_data2 + 4);

        $this->_queue[] = $num_player;
        $this->_queue[] = $this->headerStream->getPosition();

        /* getting Player_info */
        if (!$this->readPlayerInfoBlockEx()) {

            // something went wrong with extended analysis, use this older one
            $this->gaiaObjects = [];
            $this->playerObjects = [];

            array_shift($this->_queue);
            $this->headerStream->setPosition(array_shift($this->_queue));
            // first is GAIA, skip some useless bytes
            if ($this->gameInfo->_gameVersion == GameInfo::VERSION_AOKTRIAL || $this->gameInfo->_gameVersion == GameInfo::VERSION_AOCTRIAL) {
                $this->headerStream->skip(4);
            }
            $this->headerStream->skip($num_player + 70); // + 2 len of playerlen
            $this->headerStream->skip($this->_isMgx ? 792 : 756);
            $this->headerStream->skip($this->_isMgx ? 41249 : 34277);
            $this->headerStream->skip($map_size_x * $map_size_y);

            foreach ($this->players as $player) {

                // skip cooping player, he/she has no data in Player_info
                $player_ = $this->playersByIndex[$player->index];
                if ($player_ && $player_ !== $player && $player_->civId) {

                    $player->civId = $player_->civId;
                    $player->colorId = $player_->colorId;
                    $player->team = $player_->team;
                    $player->isCooping = true;
                    continue;
                }

                if (count($this->_queue) >= 1) {  // we have already found a position in the extended analysis, saves us from re-searching it again
                    $this->headerStream->setPosition(array_shift($this->_queue));
                } else {
                    $pos = $this->headerStream->find($player_info_end_separator);
                    $this->headerStream->skip(strlen($player_info_end_separator));

                    if ($this->gameInfo->_gameVersion == GameInfo::VERSION_AOKTRIAL || $this->gameInfo->_gameVersion == GameInfo::VERSION_AOCTRIAL) {
                        $this->headerStream->skip(4);
                    }
                      $this->headerStream->skip($num_player + 52 + strlen($player->name)); // + null-terminator
                }

                /* Civ_header */
                $this->headerStream->readFloat($food);
                $this->headerStream->readFloat($wood);
                $this->headerStream->readFloat($stone);
                $this->headerStream->readFloat($gold);
                /* headroom = (house capacity - population) */
                $this->headerStream->readFloat($headroom);
                $this->headerStream->skip(4);
                /* Starting Age, note: PostImperial Age = Imperial Age here */
                $this->headerStream->readFloat($data6);
                $this->headerStream->skip(16);
                $this->headerStream->readFloat($population);
                $this->headerStream->skip(100);
                $this->headerStream->readFloat($civilian_pop);
                $this->headerStream->skip(8);
                $this->headerStream->readFloat($military_pop);
                $this->headerStream->skip($this->_isMgx ? 629 : 593);
                $this->headerStream->readFloat($init_camera_pos_x);
                $this->headerStream->readFloat($init_camera_pos_y);
                $this->headerStream->skip($this->_isMgx ? 9 : 5);
                $this->headerStream->readChar($civilization);
                // sometimes(?) civilization is zero in scenarios when the first player is briton (only? always? rule?)
                if (!$civilization) {
                    $civilization++;
                }
                /* skip unknown9[3] */
                $this->headerStream->skip(3);
                $this->headerStream->readChar($player_color);

                $player->civId = $civilization;
                $player->colorId = $player_color;
                $player->initialState->position = array(round($init_camera_pos_x), round($init_camera_pos_y));
                $player->initialState->food = round($food);
                $player->initialState->wood = round($wood);
                $player->initialState->stone = round($stone);
                $player->initialState->gold = round($gold);
                $player->initialState->startingAge = round($data6);
                $player->initialState->houseCapacity = round($headroom) + round($population);
                $player->initialState->population = round($population);
                $player->initialState->civilianPop = round($civilian_pop);
                $player->initialState->militaryPop = round($military_pop);
                $player->initialState->extraPop = $player->initialState->population -
                    ($player->initialState->civilianPop + $player->initialState->militaryPop);

                $this->headerStream->skip($this->_isMgx ? 41249 : 34277);
                $this->headerStream->skip($map_size_x * $map_size_y);
            }
        }

        if ($scenario_header_pos > 0) {
            $this->headerStream->setPosition($scenario_header_pos - count($this->players) * 1473/* achievement length */);
            $this->headerStream->skip(13);
            $this->headerStream->readInt($total_point);
            $this->headerStream->skip(26);
            $this->headerStream->readInt($war_point);
            $this->headerStream->skip(34);
            $this->headerStream->readInt($num_kill);
            $this->headerStream->skip(28);
            $this->headerStream->readInt($num_killed);
            $this->headerStream->skip(28);
            $this->headerStream->readInt($num_kill2);
            $this->headerStream->skip(4);
            $this->headerStream->readInt($num_destroyed_bldg);

            /* getting objectives or instructions */
            $this->headerStream->setPosition($scenario_header_pos + 4433);
            /* original scenario file name */
            $this->headerStream->readString($original_sc_filename, 2);
            if ($original_sc_filename != '') {
                $this->gameInfo->scFileName = $original_sc_filename;
                if (!$this->_Mgx) {
                    $this->gameSettings->gameType = GameSettings::TYPE_SCENARIO;  // this way we detect scenarios in mgl, is there any other way?
                }
            }
            $this->headerStream->skip($this->_isMgx ? 24 : 20);
        }

        /* scenario instruction or Objectives string, depends on game type */
        $objectives_pos = $this->headerStream->getPosition();
        $this->headerStream->readString($instructions, 2);
        if ($instructions != '' && !$this->gameSettings->isScenario()) {
            $this->gameInfo->objectivesString = rtrim($instructions);
        }

        return true;
    }

    /**
     * Analyzes body stream.
     * This method is slower and is not used, just for demonstration.
     * @see RecAnalyst::analyzeBodyStreamF()
     * Both methods have same functionality, but analyzeBodyStream() uses MemoryStream() methods to read bodyStream,
     * and analyzeBodyStreamF() uses raw string manipulation
     * @return bool True if the stream was successfully analyzed, false otherwise.
     */
    protected function analyzeBodyStream() {
        $pos = 0;
        $time_cnt = $this->gameSettings->_gameSpeed;
        $age_flag = array(0, 0, 0, 0, 0, 0, 0, 0);

        $this->bodyStream->setPosition(0);
        $size = $this->bodyStream->getSize();

        while ($this->bodyStream->getPosition() < $size - 3) {
            if ($this->bodyStream->getPosition() == 0 && !$this->_isMgx) {
                $od_type = 0x04;
            } else {
                $this->bodyStream->readInt($od_type);
            }
            // ope_data types: 4(Game_start or Chat), 2(Sync), or 1(Command)
            switch ($od_type) {
                // Game_start or Chat command
                case 0x04:
                case 0x03:
                    $this->bodyStream->readInt($command);
                    if ($command == 0x01F4) {
                        // Game_start
                        if ($this->_isMgl) {
                            $this->bodyStream->skip(28);
                            $this->bodyStream->readChar($ver);
                            switch ($ver) {
                                case 0:
                                    if ($this->gameInfo->_gameVersion != GameInfo::VERSION_AOKTRIAL) {
                                        $this->gameInfo->_gameVersion = GameInfo::VERSION_AOK20;
                                    }
                                    break;
                                case 1:
                                    $this->gameInfo->_gameVersion = GameInfo::VERSION_AOK20A;
                                    break;
                            }
                            $this->bodyStream->skip(3);
                        } else {
                            switch ($od_type) {
                                case 0x03:
                                    if ($this->gameInfo->_gameVersion != GameInfo::VERSION_AOCTRIAL) {
                                        $this->gameInfo->_gameVersion = GameInfo::VERSION_AOC10;
                                    }
                                    break;
                                case 0x04:
                                    if ($this->gameInfo->_gameVersion == GameInfo::VERSION_AOC) {
                                        $this->gameInfo->_gameVersion = GameInfo::VERSION_AOC10C;
                                    }
                                    break;
                            }
                            $this->bodyStream->skip(20);
                        }
                    } elseif ($command == -1) {
                        // Chat
//                        for ($i = 0, $l = count($this->players); $i < $l; $i++) {
                        foreach ($this->players as $i => $player) {
//                            if (!($player = $this->players[$i])) {
//                                continue;
//                            }

                            if ($player->feudalTime != 0 && $player->feudalTime < $time_cnt && $age_flag[$i] < 1) {
                                // see reading pre-game messages, 0 indicates game's message
                                $this->ingameChat[] = new ChatMessage($player->feudalTime, null, $player->name . ' advanced to Feudal Age');
                                $age_flag[$i] = 1;
                            }
                            if ($player->castleTime != 0 && $player->castleTime < $time_cnt && $age_flag[$i] < 2) {
                                $this->ingameChat[] = new ChatMessage($player->castleTime, null, $player->name . ' advanced to Castle Age');
                                $age_flag[$i] = 2;
                            }
                            if ($player->imperialTime != 0 && $player->imperialTime < $time_cnt && $age_flag[$i] < 3) {
                                $this->ingameChat[] = new ChatMessage($player->imperialTime, null, $player->name . ' advanced to Imperial Age');
                                $age_flag[$i] = 3;
                            }
                        }

                        $this->bodyStream->readString($chat);
                        // see reading pre-game messages
                        if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
                            $chat = rtrim($chat); // throw null-termination character
                            if (substr($chat, 3, 2) == '--' && substr($chat, -2) == '--') {
                                // skip messages like "--Warning: You are being under attack... --"
                            } else {
                                $this->ingameChat[] = createChatMessage($time_cnt, $this->playersByIndex[$chat[2]], substr($chat, 3));
                            }
                        }
                    }
                    break;
                // Sync
                case 0x02:
                    $this->bodyStream->readInt($time);
                    $time_cnt += $time; // time_cnt is in miliseconds
                    $this->bodyStream->readInt($unknown);
                    if ($unknown == 0) {
                        $this->bodyStream->skip(28);
                    }
                    $this->bodyStream->skip(12);
                    break;
                // Command
                case 0x01:
                    $this->bodyStream->readInt($length);
                    $this->bodyStream->readChar($command);
                    $this->bodyStream->skip(-1);
                    switch ($command) {
                        case 0x0B: // player resign
                            $this->bodyStream->skip(1);
                            $this->bodyStream->readChar($player_index);
                            if (($player = $this->playersByIndex[$player_index]) && $player->resignTime == 0) {
                                $player->resignTime = $time_cnt;
                                $this->ingameChat[] = new ChatMessage($time_cnt, null, $player->name . ' resigned');
                            }
                            $this->bodyStream->skip($length - 2);
                            break;
                        case 0x65: // researches
                            $this->bodyStream->skip(8);
                            $this->bodyStream->readWord($player_id);
                            $this->bodyStream->readWord($research_id);
                            if (!($player = $this->playersByIndex[$player_id])) {

                                $this->bodyStream->skip($length - 12);
                                break;
                            }
                            switch ($research_id) {
                                case 101:
                                    $player->feudalTime = $time_cnt + 130000; // + research time
                                    break;
                                case 102:
                                    // persians have faster research time
                                    $player->castleTime = ($player->civId == Civilization::PERSIANS) ?
                                        $time_cnt + round(160000 / 1.10) : $time_cnt + 160000;
                                    break;
                                case 103:
                                    // persians have faster research time
                                    $player->imperialTime = ($player->civId == Civilization::PERSIANS) ?
                                        $time_cnt + round(190000 / 1.15) : $time_cnt + 190000;
                                    break;
                            }
                            $player->researches[$research_id] = $time_cnt;
                            $this->bodyStream->skip($length - 12);
                            break;
                        case 0x77: // training unit
                            $this->bodyStream->skip(8);
                            $this->bodyStream->readWord($unit_type_id);
                            $this->bodyStream->readWord($unit_num);

                            if (!isset($this->units[$unit_type_id])) {
                                $this->units[$unit_type_id] = $unit_num;
                            } else {
                                $this->units[$unit_type_id] += $unit_num;
                            }
                            $this->bodyStream->skip($length - 12);
                            break;
                        case 0x64: // pc trains unit
                            $this->bodyStream->skip(10);
                            $this->bodyStream->readWord($unit_type_id);
                            $unit_num = 1; // always for pc?
                            if (!isset($this->units[$unit_type_id])) {
                                $this->units[$unit_type_id] = $unit_num;
                            } else {
                                $this->units[$unit_type_id] += $unit_num;
                            }
                            $this->bodyStream->skip($length - 12);
                            break;
                        case 0x66: // building
                            $this->bodyStream->skip(2);
                            $this->bodyStream->readWord($player_id);
                            $this->bodyStream->skip(8);
                            $this->bodyStream->readWord($building_type_id);

                            if (in_array($building_type_id, RecAnalystConst::$GATE_UNITS)) {
                                $building_type_id = Unit::GATE;
                            } elseif (in_array($building_type_id, RecAnalystConst::$PALISADE_GATE_UNITS)) {
                                $building_type_id = Unit::PALISADE_GATE;
                            }

                            if (!isset($this->buildings[$player_id][$building_type_id])) {
                                $this->buildings[$player_id][$building_type_id] = 1;
                            } else {
                                $this->buildings[$player_id][$building_type_id]++;
                            }
                            $this->bodyStream->skip($length - 14);
                            break;
                        case 0x6C: // tributing
                            $this->bodyStream->skip(1);
                            $this->bodyStream->readChar($player_id_from);
                            $this->bodyStream->readChar($player_id_to);
                            $this->bodyStream->readChar($resource_id);
                            $this->bodyStream->readFloat($amount_tributed);
                            $this->bodyStream->readFloat($market_fee);

                            $playerFrom = $this->playersByIndex[$player_id_from];
                            $playerTo = $this->playersByIndex[$player_id_to];

                            if ($playerFrom && $playerTo) {
                                $tribute = new Tribute();
                                $tribute->time = $time_cnt;
                                $tribute->playerFrom = $playerFrom;
                                $tribute->playerTo = $playerTo;
                                $tribute->resourceId = $resource_id;
                                $tribute->amount = floor($amount_tributed);
                                $tribute->fee = $market_fee;
                                $this->tributes[] = $tribute;
                            }
                            $this->bodyStream->skip($length - 12);
                            break;
                        default:
                            $this->bodyStream->skip($length);
                            break;
                    }
                    $this->bodyStream->skip(4);
                    break;
                default:
                    /* detect if this is a header of saved chapter */
                    /* sometimes header of the saved chapter is in $03 command, instead of $20 as it should be,
                       when this happens the length of $20 command is $0E, otherwise it is $02 (always?, rule?),
                       we do not rely on it, that's why we are skipping saved chapter data here and not in $20 command */
                    if ($this->bodyStream->getPosition() == $this->_nextPos - $this->_headerLen - 4) {
                        /* this is a header of saved chapter data, we have already read next_command_block
                           that's why -4 in the if-statement */
                        /* next_pos - header_len = offset of compressed chapter data */
                        $next_command_block = $od_type;
                        $this->bodyStream->readInt($this->_nextPos); // next_chapter_pos
                        $this->bodyStream->seek($next_command_block - $this->_headerLen - 8, Stream::soFromBeginning);
                    } else {
                        // shouldn't occure, just to prevent unexpected endless cycling
                        $this->bodyStream->skip(1);
                    }
                    break;
            }
        }

        $this->gameInfo->playTime = $time_cnt;

        return true;
    }

    /**
     * Analyzes body stream.
     *
     * @return bool true if the stream was successfully analyzed, false otherwise
     */
    protected function analyzeBodyStreamF() {
        $time_cnt = $this->gameSettings->_gameSpeed;
        $age_flag = array(0, 0, 0, 0, 0, 0, 0, 0);

        $bodyStream = $this->bodyStream->getDataString();
        $size = $this->bodyStream->getSize();
        $pos = 0;

        while ($pos < $size - 3) {
            if ($pos == 0 && !$this->_isMgx) {
                $od_type = 0x04;
            } else {
                $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                $unpacked_data = unpack('l', $packed_data);
                $od_type = $unpacked_data[1];
            }

            // ope_data types: 4(Game_start or Chat), 2(Sync), or 1(Command)
            switch ($od_type) {
                // Game_start or Chat command
                case 0x04:
                case 0x03:
                    $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                    $unpacked_data = unpack('l', $packed_data);
                    $command = $unpacked_data[1];
                    if ($command == 0x01F4) {
                        // Game_start
                        if ($this->_isMgl) {
                            $pos += 28;
                            $packed_data = substr($bodyStream, $pos, 1); $pos++;
                            $unpacked_data = unpack('C', $packed_data);
                            $ver = $unpacked_data[1];
                            switch ($ver) {
                                case 0:
                                    if ($this->gameInfo->_gameVersion != GameInfo::VERSION_AOKTRIAL) {
                                        $this->gameInfo->_gameVersion = GameInfo::VERSION_AOK20;
                                    }
                                    break;
                                case 1:
                                    $this->gameInfo->_gameVersion = GameInfo::VERSION_AOK20A;
                                    break;
                            }
                            $pos += 3;
                        } else {
                            switch ($od_type) {
                                case 0x03:
                                    if ($this->gameInfo->_gameVersion != GameInfo::VERSION_AOCTRIAL) {
                                        $this->gameInfo->_gameVersion = GameInfo::VERSION_AOC10;
                                    }
                                    break;
                                case 0x04:
                                    if ($this->gameInfo->_gameVersion == GameInfo::VERSION_AOC) {
                                        $this->gameInfo->_gameVersion = GameInfo::VERSION_AOC10C;
                                    }
                                    break;
                            }
                            $pos += 20;
                        }
                    } elseif ($command == -1) {
                        // Chat
//                        for ($i = 0, $l = count($this->players); $i < $l; $i++) {
                        foreach ($this->players as $i => $player) {
//                            if (!($player = $this->players[$i])) {
//                                continue;
//                            }

                            if ($player->feudalTime != 0 && $player->feudalTime < $time_cnt && $age_flag[$i] < 1) {
                                $this->ingameChat[] = new ChatMessage($player->feudalTime, null, $player->name . ' advanced to Feudal Age');
                                $age_flag[$i] = 1;
                            }
                            if ($player->castleTime != 0 && $player->castleTime < $time_cnt && $age_flag[$i] < 2) {
                                $this->ingameChat[] = new ChatMessage($player->castleTime, null, $player->name . ' advanced to Castle Age');
                                $age_flag[$i] = 2;
                            }
                            if ($player->imperialTime != 0 && $player->imperialTime < $time_cnt && $age_flag[$i] < 3) {
                                $this->ingameChat[] = new ChatMessage($player->imperialTime, null, $player->name . ' advanced to Imperial Age');
                                $age_flag[$i] = 3;
                            }
                        }

                        $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                        $unpacked_data = unpack('l', $packed_data);
                        $chat_len = $unpacked_data[1];
                        $chat = substr($bodyStream, $pos, $chat_len); $pos += $chat_len;

                        if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
                            $chat = rtrim($chat); // throw null-termination character
                            if (substr($chat, 3, 2) == '--' && substr($chat, -2) == '--') {
                                // skip messages like "--Warning: You are being under attack... --"
                            } else {
                                $this->ingameChat[] = self::createChatMessage($time_cnt, $this->playersByIndex[$chat[2]], substr($chat, 3));
                            }
                        }
                    }
                    break;
                // Sync
                case 0x02:
                    $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                    $unpacked_data = unpack('l', $packed_data);
                    $time_cnt += $unpacked_data[1]; // time_cnt is in miliseconds
                    $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                    $unpacked_data = unpack('l', $packed_data);
                    $unknown = $unpacked_data[1];
                    if ($unknown == 0) {
                        $pos += 28;
                    }
                    $pos += 12;
                    break;
                // Command
                case 0x01:
                    $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                    $unpacked_data = unpack('l', $packed_data);
                    $length = $unpacked_data[1];

                    $packed_data = substr($bodyStream, $pos, 1); $pos++;
                    $unpacked_data = unpack('C', $packed_data);
                    $command = $unpacked_data[1];
                    $pos--;

                    switch ($command) {
                        case 0x0B: // player resign
                            $pos++;
                            $packed_data = substr($bodyStream, $pos, 1); $pos++;
                            $unpacked_data = unpack('C', $packed_data);
                            $player_index = $unpacked_data[1];

                            if (($player = $this->playersByIndex[$player_index]) && $player->resignTime == 0) {
                                $player->resignTime = $time_cnt;
                                $this->ingameChat[] = new ChatMessage($time_cnt, null, $player->name . ' resigned');
                            }
                            $pos += $length - 2;
                            break;
                        case 0x65: // researches
                            $pos += 8;
                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $player_id = $unpacked_data[1];

                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $research_id = $unpacked_data[1];

                            if (!($player = $this->playersByIndex[$player_id])) {
                                $pos += $length - 12;
                                break;
                            }
                            switch ($research_id) {
                                case 101:
                                    $player->feudalTime = $time_cnt + 130000;
                                    break;
                                case 102:
                                    $player->castleTime = ($player->civId == Civilization::PERSIANS) ?
                                        $time_cnt + round(160000 / 1.10) : $time_cnt + 160000;
                                    break;
                                case 103:
                                    $player->imperialTime = ($player->civId == Civilization::PERSIANS) ?
                                        $time_cnt + round(190000 / 1.15) : $time_cnt + 190000;
                                    break;
                            }
                            $player->researches[$research_id] = $time_cnt;
                            $pos += $length - 12;
                            break;
                        case 0x77: // training unit
                            $pos += 8;
                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $unit_type_id = $unpacked_data[1];

                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $unit_num = $unpacked_data[1];

                            if (!isset($this->units[$unit_type_id])) {
                                $this->units[$unit_type_id] = $unit_num;
                            } else {
                                $this->units[$unit_type_id] += $unit_num;
                            }
                            $pos += $length - 12;
                            break;
                        case 0x64: // pc trains unit
                            $pos += 10;
                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $unit_type_id = $unpacked_data[1];
                            $unit_num = 1; // always for pc?
                            if (!isset($this->units[$unit_type_id])) {
                                $this->units[$unit_type_id] = $unit_num;
                            } else {
                                $this->units[$unit_type_id] += $unit_num;
                            }
                            $pos += $length - 12;
                            break;
                        case 0x66: // building
                            $pos += 2;
                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $player_id = $unpacked_data[1];
                            $pos += 8;
                            $packed_data = substr($bodyStream, $pos, 2); $pos += 2;
                            $unpacked_data = unpack('v', $packed_data);
                            $building_type_id = $unpacked_data[1];

                            if (in_array($building_type_id, RecAnalystConst::$GATE_UNITS)) {
                                $building_type_id = Unit::GATE;
                            } elseif (in_array($building_type_id, RecAnalystConst::$PALISADE_GATE_UNITS)) {
                                $building_type_id = Unit::PALISADE_GATE;
                            }

                            if (!isset($this->buildings[$player_id][$building_type_id])) {
                                $this->buildings[$player_id][$building_type_id] = 1;
                            } else {
                                $this->buildings[$player_id][$building_type_id]++;
                            }
                            $pos += $length - 14;
                            break;
                        case 0x6C: // tributing
                            $pos++;
                            // player_id_from
                            $packed_data = substr($bodyStream, $pos, 1); $pos++;
                            $unpacked_data = unpack('C', $packed_data);
                            $player_id_from = $unpacked_data[1];
                            // player_id_to
                            $packed_data = substr($bodyStream, $pos, 1); $pos++;
                            $unpacked_data = unpack('C', $packed_data);
                            $player_id_to = $unpacked_data[1];
                            // resource_id
                            $packed_data = substr($bodyStream, $pos, 1); $pos++;
                            $unpacked_data = unpack('C', $packed_data);
                            $resource_id = $unpacked_data[1];
                            // amount_tributed
                            $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                            $unpacked_data = unpack('f', $packed_data);
                            $amount_tributed = $unpacked_data[1];
                            // market_fee
                            $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                            $unpacked_data = unpack('f', $packed_data);
                            $market_fee = $unpacked_data[1];

                            $playerFrom = $this->playersByIndex[$player_id_from];
                            $playerTo = $this->playersByIndex[$player_id_to];

                            if ($playerFrom && $playerTo) {
                                $tribute = new Tribute();
                                $tribute->time = $time_cnt;
                                $tribute->playerFrom = $playerFrom;
                                $tribute->playerTo = $playerTo;
                                $tribute->resourceId = $resource_id;
                                $tribute->amount = floor($amount_tributed);
                                $tribute->fee = $market_fee;
                                $this->tributes[] = $tribute;
                            }
                            $pos += $length - 12;
                            break;
                        default:
                            $pos += $length;
                            break;
                    }
                    $pos += 4;
                    break;
                default:
                    /* detect if this is a header of saved chapter */
                    /* sometimes header of the saved chapter is in $03 command, instead of $20 as it should be,
                       when this happens the length of $20 command is $0E, otherwise it is $02 (always?, rule?),
                       we do not rely on it, that's why we are skipping saved chapter data here and not in $20 command */
                    if ($pos == $this->_nextPos - $this->_headerLen - 4) {
                        /* this is a header of saved chapter data, we have already read next_command_block
                           that's why -4 in the if-statement */
                        /* next_pos - header_len = offset of compressed chapter data */
                        $next_command_block = $od_type;

                        $packed_data = substr($bodyStream, $pos, 4); $pos += 4;
                        $unpacked_data = unpack('l', $packed_data);
                        $this->_nextPos = $unpacked_data[1]; // next_chapter_pos
                        $pos = $next_command_block - $this->_headerLen - 8;
                    } else {
                        // shouldn't occure, just to prevent unexpected endless cycling
                        $pos++;
                    }
                    break;
            }
        }

        unset($bodyStream);
        $this->gameInfo->playTime = $time_cnt;

        return true;
    }

    /**
     * Analyzes recorded game.
     *
     * @return bool true if successfully analyzed, false otherwise
     */
    public function analyze() {
        $starttime = microtime(true);
        if (!$this->analyzeheaderStream()) {
            return false;
        }
        if (!$this->analyzeBodyStreamF()) {
            return false;
        }
        //TODO: triedit units, buildings v post analyze?
        $this->postAnalyze();
        $endtime = microtime(true);
        $this->_analyzeTime = round(($endtime - $starttime) * 1000);
        return true;
    }

    /**
     * Extended analysis of the PlayerInfo block.
     *
     */
    protected function readPlayerInfoBlockEx() {
        $exist_object_separator     = pack('c*', 0x0B, 0x00, 0x08, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00);
        $object_end_separator       = pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00, 0x00, 0x80, 0xBF,
            0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00);
        $aok_object_end_separator   = pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00, 0x00, 0x80, 0xBF,
            0x00, 0x00, 0x00, 0x00, 0x00);
        $player_info_end_separator  = pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);
        $objects_mid_separator_gaia = pack('c*', 0x00, 0x0B, 0x00, 0x40, 0x00, 0x00, 0x00, 0x20, 0x00, 0x00);

        $num_player = array_shift($this->_queue);
        $map_size_x = $this->_mapWidth;
        $map_size_y = $this->_mapHeight;

          for ($i = 0, $l = count($this->players); $i <= $l; $i++) { // first is GAIA
            if ($i > 0) {
                // skip GAIA player
                $player = $this->players[$i - 1];
                // skip cooping player, they have no data in Player_info
                $player_ = $this->playersByIndex[$player->index];

                if ($player_ && ($player_ !== $player) && $player_->civId) {
                    $player->civId = $player_->civId;
                    $player->colorId = $player_->colorId;
                    $player->team = $player_->team;
                    $player->isCooping = true;
                    continue;
                }
                if ($this->gameInfo->_gameVersion == GameInfo::VERSION_AOKTRIAL ||
                    $this->gameInfo->_gameVersion == GameInfo::VERSION_AOCTRIAL) {
                    $this->headerStream->skip(4);
                }
                $this->headerStream->skip($num_player + 43);

                // skip playername
                $this->headerStream->readWord($player_name_len);
                $this->headerStream->skip($player_name_len + 6);

                // save position of PlayerInfo block, so we don't need to re-search it again in case this analysis fails
                $this->_queue[] = $this->headerStream->getPosition();
                // Civ header
                $this->headerStream->readFloat($food);
                $this->headerStream->readFloat($wood);
                $this->headerStream->readFloat($stone);
                $this->headerStream->readFloat($gold);
                // headroom = (house capacity - population)
                $this->headerStream->readFloat($headroom);
                $this->headerStream->skip(4);
                // Starting Age, note: PostImperial Age = Imperial Age here
                $this->headerStream->readFloat($data6);
                $this->headerStream->skip(16);
                $this->headerStream->readFloat($population);
                $this->headerStream->skip(100);
                $this->headerStream->readFloat($civilian_pop);
                $this->headerStream->skip(8);
                $this->headerStream->readFloat($military_pop);
                $this->headerStream->skip($this->_isMgx ? 629 : 593);
                $this->headerStream->readFloat($init_camera_pos_x);
                $this->headerStream->readFloat($init_camera_pos_y);
                $this->headerStream->skip($this->_isMgx ? 9 : 5);
                $this->headerStream->readChar($civilization);
                if (!$civilization) {
                    $civilizaton++;
                }
                $this->headerStream->skip(3);
                $this->headerStream->readChar($player_color);

                $player->civId = $civilization;
                $player->colorId = $player_color;
                $player->initialState->position = array(round($init_camera_pos_x), round($init_camera_pos_y));
                $player->initialState->food = round($food);
                $player->initialState->wood = round($wood);
                $player->initialState->stone = round($stone);
                $player->initialState->gold = round($gold);
                $player->initialState->startingAge = round($data6);
                $player->initialState->houseCapacity = round($headroom) + round($population);
                $player->initialState->population = round($population);
                $player->initialState->civilianPop = round($civilian_pop);
                $player->initialState->militaryPop = round($military_pop);
                $player->initialState->extraPop = $player->initialState->population -
                    ($player->initialState->civilianPop + $player->initialState->militaryPop);
            } else {
                // GAIA
                if ($this->gameInfo->_gameVersion == GameInfo::VERSION_AOKTRIAL ||
                    $this->gameInfo->_gameVersion == GameInfo::VERSION_AOCTRIAL) {
                    $this->headerStream->skip(4);
                }
                $this->headerStream->skip($num_player + 70);
                $this->headerStream->skip($this->_isMgx ? 792 : 756);
            }
            $this->headerStream->skip($this->_isMgx ? 41249 : 34277);
            $this->headerStream->skip($map_size_x * $map_size_y);

            // getting exist_object_pos
            if ($this->headerStream->find($exist_object_separator) == -1) {
                return false;
            }
            $this->headerStream->skip(strlen($exist_object_separator));

            $breakflag = false;
            while (true) {
                $this->headerStream->readChar($object_type);
                $this->headerStream->readChar($owner);
                $this->headerStream->readWord($unit_id);

                switch ($object_type) {
                    case 10:
                        switch ($unit_id) {
                            case Unit::GOLDMINE:
                            case Unit::STONEMINE:
                            case Unit::CLIFF1:
                            case Unit::CLIFF2:
                            case Unit::CLIFF3:
                            case Unit::CLIFF4:
                            case Unit::CLIFF5:
                            case Unit::CLIFF6:
                            case Unit::CLIFF7:
                            case Unit::CLIFF8:
                            case Unit::CLIFF9:
                            case Unit::CLIFF10:
                            case Unit::FORAGEBUSH:
                                $this->headerStream->skip(19);
                                $this->headerStream->readFloat($pos_x);
                                $this->headerStream->readFloat($pos_y);
                                $go = new Unit();
                                $go->id = $unit_id;
                                $go->position = array(round($pos_x), round($pos_y));
                                $this->gaiaObjects[] = $go;
                                $this->headerStream->skip(-27);
                                break;
                        }
                        $this->headerStream->skip(63-4);
                        if ($this->_isMgl) {
                            $this->headerStream->skip(1);
                        }
                        break;
                    case 20:
                        if ($this->_isMgx) {
                            $this->headerStream->skip(59);
                            $this->headerStream->readChar($b);
                            $this->headerStream->skip(-60);
                            $this->headerStream->skip(68-4);
                            if ($b == 2) {
                                $this->headerStream->skip(34);
                            }
                        }
                        else
                            $this->headerStream->skip(103-4);
                        break;
                    case 30:
                        if ($this->_isMgx) {
                            $this->headerStream->skip(59);
                            $this->headerStream->readChar($b);
                            $this->headerStream->skip(-60);
                            $this->headerStream->skip(204-4);
                            if ($b == 2) {
                                $this->headerStream->skip(17);
                            }
                        } else {
                            $this->headerStream->skip(60);
                            $this->headerStream->readChar($b);
                            $this->headerStream->skip(-61);
                            $this->headerStream->skip(205-4);
                            if ($b == 2) {
                                $this->headerStream->skip(17);
                            }
                        }
                        break;
                    case 60:
                        $this->headerStream->skip(204);
                        $this->headerStream->readChar($b);
                        $this->headerStream->skip(-205);
                        $this->headerStream->skip(233-4);
                        if ($b) {
                            $this->headerStream->skip(67);
                        }
                        break;
                    case 70:
                        switch ($unit_id) {
                            case Unit::RELIC:
                            case Unit::DEER:
                            case Unit::BOAR:
                            case Unit::JAVELINA:
                            case Unit::TURKEY:
                            case Unit::SHEEP:
                                $this->headerStream->skip(19);
                                $this->headerStream->readFloat($pos_x);
                                $this->headerStream->readFloat($pos_y);
                                $go = new Unit();
                                $go->id = $unit_id;
                                $go->position = array(round($pos_x), round($pos_y));
                                $this->gaiaObjects[] = $go;
                                break;
                        }
                        if ($owner && $unit_id != Unit::TURKEY && $unit_id != Unit::SHEEP) {
                            // exclude convertable objects
                            $this->headerStream->skip(19);
                            $this->headerStream->readFloat($pos_x);
                            $this->headerStream->readFloat($pos_y);
                            $uo = new Unit();
                            $uo->id = $unit_id;
                            $uo->owner = $owner;
                            $uo->position = array(round($pos_x), round($pos_y));
                            $this->playerObjects[] = $uo;
                        }
                        if ($this->_isMgx) {
                            $separator_pos = $this->headerStream->find($object_end_separator);
                            $this->headerStream->skip(strlen($object_end_separator));
                        } else {
                            $separator_pos = $this->headerStream->find($aok_object_end_separator);
                            $this->headerStream->skip(strlen($aok_object_end_separator));
                        }
                        if ($separator_pos == -1) {
                            return false;
                        }
                        break;
                    case 80:
                        if ($owner) {
                            $this->headerStream->skip(19);
                            $this->headerStream->readFloat($pos_x);
                            $this->headerStream->readFloat($pos_y);
                            $uo = new Unit();
                            $uo->id = $unit_id;
                            $uo->owner = $owner;
                            $uo->position = array(round($pos_x), round($pos_y));
                            $this->playerObjects[] = $uo;
                        }
                        if ($this->_isMgx) {
                            $separator_pos = $this->headerStream->find($object_end_separator);
                            $this->headerStream->skip(strlen($object_end_separator));
                        } else {
                            $separator_pos = $this->headerStream->find($aok_object_end_separator);
                            $this->headerStream->skip(strlen($aok_object_end_separator));
                        }
                        if ($separator_pos == -1) {
                            return false;
                        }
                        $this->headerStream->skip(126);
                        if ($this->_isMgx) {
                            $this->headerStream->skip(1);
                        }
                        break;
                    case 00:
                        $this->headerStream->skip(-4);
                        $this->headerStream->readBuffer($buff, strlen($player_info_end_separator));
                        $this->headerStream->skip(-strlen($player_info_end_separator));
                        if ($buff == $player_info_end_separator) {
                            $this->headerStream->skip(strlen($player_info_end_separator));
                            $breakflag = true;
                            break;
                        }

                        if ($buff[0] == $objects_mid_separator_gaia[0] &&
                            $buff[1] == $objects_mid_separator_gaia[1]) {
                            $this->headerStream->skip(strlen($objects_mid_separator_gaia));
                        } else {
                            return false;
                        }
                        break;
                    default:
                        return false;
                        break;
                }
                if ($breakflag) {
                    break;
                }
            }
          }

          return true;
    }

    /**
     * Generates a map image.
     *
     * @return resource GD image resource.
     */
    public function generateMap() {
        $config = $this->config;

        if (!isset($this->_mapData)) {
            return false;
        }

        if (!($gd = imagecreatetruecolor($this->_mapWidth, $this->_mapHeight))) {
            //TODO unset($this->_mapData, $this->_mapWidth, $this->_mapHeight);
            return false;
        }

        $colors = array();
        foreach (RecAnalystConst::$TERRAIN_COLORS as $col) {
            $colors[] = imagecolorallocate($gd, $col[0], $col[1], $col[2]);
        }

        for ($x = 0; $x < $this->_mapWidth; $x++) {
            for ($y = 0; $y < $this->_mapHeight; $y++) {
                $terrain_id = $this->_mapData[$x][$y]%1000;
                $elevation = (int)(($this->_mapData[$x][$y] - $terrain_id) / 1000);
                $elevation--;

                if (isset($colors[$terrain_id])) {
                    imagesetpixel($gd, $x, $y, $colors[$terrain_id]);
                } else { // fuchsia, so we can see the unknown terrain id on a map and add it in the future updates
                    imagesetpixel($gd, $x, $y, imagecolorallocate($gd, 0xff, 0x00, 0xff));
                }
            }
        }

        // we do not need them anymore
        // TODO array() unset($this->_mapData, $this->_mapWidth, $this->_mapHeight);

        // draw gaia objects
        foreach ($this->gaiaObjects as $obj) {
            $c = RecAnalystConst::$OBJECT_COLORS[$obj->id];
            $c = imagecolorallocate($gd, $c[0], $c[1], $c[2]);
            $x = $obj->position[0];
            $y = $obj->position[1];
            imagefilledrectangle($gd, $x - 1, $y - 1, $x + 1, $y + 1, $c);
        }

        // draw positions
        if ($config->showPositions && !$this->gameSettings->isScenario()
            && $this->gameSettings->_mapId != Map::CUSTOM) {

            foreach ($this->players as $player) {

                if ($player->isCooping) {
                    continue;
                }

                $c = RecAnalystConst::$PLAYER_COLORS[$player->colorId];
                $c = imagecolorallocate($gd, $c[0], $c[1], $c[2]);
                $x = $player->initialState->position[0];
                $y = $player->initialState->position[1];

                imageellipse($gd, $x, $y, 18, 18, $c);
                imagefilledellipse($gd, $x, $y, 8, 8, $c);
            }
        }

        // draw player objects
        if ($config->showPositions) {

            foreach ($this->playerObjects as $obj) {

                if (!($player = $this->playersByIndex[$obj->owner])) {
                    continue;
                }
                $c = RecAnalystConst::$PLAYER_COLORS[$player->colorId];
                $c = imagecolorallocate($gd, $c[0], $c[1], $c[2]);
                $x = $obj->position[0];
                $y = $obj->position[1];
                imagefilledrectangle($gd, $x - 1, $y - 1, $x + 1, $y + 1, $c);
            }
        }

        $gd = imagerotate($gd, 45, imagecolorallocatealpha($gd, 0, 0, 0 , 127));

        $width = imagesx($gd);
        $height = imagesy($gd);

        if (!($mapim = imagecreatetruecolor($config->mapWidth, $config->mapHeight))) {
            imagedestroy($gd);
            return false;
        }

        imagealphablending($mapim, false);
        imagesavealpha($mapim, true);
        imagecopyresampled($mapim, $gd, 0, 0, 0, 0, $config->mapWidth, $config->mapHeight, $width, $height);

        return $mapim;
    }

    /**
     * Generates a research timelines image.
     *
     * @param string $researchesFileName image filename
     * @return resource GD image resource
     * @todo implement use of custom fonts
     * @todo DRY this + generateResearchesImageMap
     * @todo Maybe just remove this >_<
     */
    public function generateResearches($researchesFileName) {
        $config = $this->config;
        // We rely on researches to be logically time-sorted, but there are recorded games,
        // where it doesn't need to be true, that's why asort() is used
        // to use a better structure to avoid using asort()?
        foreach ($this->players as $player) {
            asort($player->researches, SORT_NUMERIC);
        }

        $total_mins = ceil($this->gameInfo->playTime / 1000 / 60);
        // original width / height of image representing one research
        $orw = $orh = 38;
        // new width / height of image representing one research
        $rw = $rh = $config->researchTileSize;

        // reserve in case player clicked a research, but game finished before researching a technology
        $total_mins += 5;

        // $mins will contain the max amount of researches done by any player in a given minute
        // eg when p1 clicked 1 research in min 22, and p2 clicked 3, $mins[22] == 3
        $mins = array_fill(0, $total_mins, 0);

        foreach ($this->players as $player) {
            $prev_min = -1;
            $tmp_mins = array_fill(0, $total_mins, 0);
            foreach ($player->researches as $research_id => $min) {
                if (array_key_exists($research_id, RecAnalystConst::$RESEARCHES)) {
                    $min = floor($min / 1000 / 60); // in minutes
                    $tmp_mins[$min]++;
                }
            }
            foreach ($mins as $min => &$cnt) {
                if ($cnt < $tmp_mins[$min]) {
                    $cnt = $tmp_mins[$min];
                }
            }
        }

        // calculate max username width
        $max_username_width = 0; // max width for username
        $font = 3; // font used for usernames
        $real_cnt = 0;
        foreach ($this->players as $player) {
            if (empty($player->researches)) {
                continue;
            }
            if (strlen($player->name) * imagefontwidth($font) > $max_username_width) {
                $max_username_width = strlen($player->name) * imagefontwidth($font);
            }
            $real_cnt++;
        }

        $padding = 8;
        $spacing = $config->researchVSpacing;
        $max_username_width += $padding;
        // image width will be sum over min * reseach width + padding-left + padding-right
        $gd_width = array_sum($mins) * $rw + 2 * $padding + $max_username_width;
        $gd_height = ($rw + $spacing) * $real_cnt + 50;

        if (!($gd = imagecreatetruecolor($gd_width, $gd_height))) {
            return false;
        }

        // fill gd with background
        // TODO: fciu volat podla koncovky
        if (!($bkgim = imagecreatefromjpeg($config->researchBackgroundImage))) {
            imagedestroy($gd);
            return false;
        }

        $bkgim_w = imagesx($bkgim);
        $bkgim_h = imagesy($bkgim);

        $dst_x = $dst_y = 0;
        while ($dst_y < $gd_height) {
            while ($dst_x < $gd_width) {
                imagecopy($gd, $bkgim, $dst_x, $dst_y, 0, 0, $bkgim_w, $bkgim_h);
                $dst_x += $bkgim_w;
            }
            $dst_x = 0;
            $dst_y += $bkgim_h;
        }
        imagedestroy($bkgim);

        // fill gd with usernames
        $idx = 0;
        $black = imagecolorallocate($gd, 0x00, 0x00, 0x00);
        foreach ($this->players as $player) {
            if (empty($player->researches)) {
                continue;
            }

            $dst_y = $idx * ($rh + $spacing) + $padding + round(imagefontheight($font) / 2); $dst_x = 0 + $padding;
            $idx++;

            list ($r, $g, $b) = array(
                RecAnalystConst::$COLORS[$player->colorId][1].RecAnalystConst::$COLORS[$player->colorId][2],
                RecAnalystConst::$COLORS[$player->colorId][3].RecAnalystConst::$COLORS[$player->colorId][4],
                RecAnalystConst::$COLORS[$player->colorId][5].RecAnalystConst::$COLORS[$player->colorId][6]
            );
            $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

            $color = imagecolorallocate($gd, $r, $g, $b);
            imagestring($gd, $font, $dst_x + 1, $dst_y + 1, $player->name, $black);
            imagestring($gd, $font, $dst_x, $dst_y, $player->name, $color);
        }

        // x_offsets will contain x-offset of first research in particular minute (variable length of minute)
        $x_offsets = array();
        $sum = 0 + $padding + $max_username_width;
        foreach ($mins as $min => $cnt) {
            $x_offsets[$min] = $sum;
            $sum += $cnt * $rw;
        }

        // fill gd with colors for specific ages
        list($r, $g, $b, $a) = $config->researchDAColor;
        $darkage_color = imagecolorallocatealpha($gd, $r, $g, $b, $a);
        list($r, $g, $b, $a) = $config->researchFAColor;
        $feudalage_color = imagecolorallocatealpha($gd, $r, $g, $b, $a);
        list($r, $g, $b, $a) = $config->researchCAColor;
        $castleage_color = imagecolorallocatealpha($gd, $r, $g, $b, $a);
        list($r, $g, $b, $a) = $config->researchIAColor;
        $imperialage_color = imagecolorallocatealpha($gd, $r, $g, $b, $a);

        $idx = 0;
        foreach ($this->players as $player) {
            if (empty ($player->researches)) {
                continue;
            }
            $dst_y = $idx * ($rh + $spacing) + $padding; $dst_x = 0; $prev_min = -1; $cnt = 0;
            $idx++;

            $age_flag = array(0, 0, 0);
            $age_x = array(0, 0, 0);
            foreach ($player->researches as $research_id => $min) {
                // if (array_key_exists ($research_id, RecAnalystConst::$RESEARCHES))
                {
                    $min = floor($min / 1000 / 60); // in minutes
                    if ($prev_min == $min) {
                        $cnt ++;
                        $dst_x = $x_offsets[$min] + ($cnt * $rw);
                    } else {
                        $cnt = 0;
                        $dst_x = $x_offsets[$min];
                    }
                    $prev_min = $min;
                    if ($research_id == 101) { // feudal age
                        $age_flag[0] = 1;
                        $x1 = 0 + $padding + $max_username_width;
                        $y1 = $dst_y - 2;
                        $x2 = $dst_x;
                        $y2 = $dst_y + $rh + 2;
                        imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $darkage_color);
                        $age_x[0] = $x2;
                    } elseif ($research_id == 102) { // castle age
                        $age_flag[1] = 1;
                        $x1 = $x2;// + $rw;
                        $y1 = $dst_y - 2;
                        $x2 = $dst_x;
                        $y2 = $dst_y + $rh + 2;
                        imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $feudalage_color);
                        $age_x[1] = $x2;
                    } elseif ($research_id == 103) { // imperial age
                        $age_flag[2] = 1;
                        $x1 = $x2;// + $rw;
                        $y1 = $dst_y - 2;
                        $x2 = $dst_x;
                        $y2 = $dst_y + $rh + 2;
                        imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $castleage_color);
                        $age_x[2] = $x2;

                        $x1 = $x2;// + $rw;
                        $y1 = $dst_y - 2;
                        $x2 = $gd_width - $padding;
                        $y2 = $dst_y + $rh + 2;
                        imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $imperialage_color);
                    }
                }
            }
            if (!$age_flag[0]) {
                $x1 = 0 + $padding + $max_username_width;
                $y1 = $dst_y - 2;
                $x2 = $gd_width - $padding;
                $y2 = $dst_y + $rh + 2;
                imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $darkage_color);
            } elseif (!$age_flag[1]) {
                $x1 = $age_x[0];
                $y1 = $dst_y - 2;
                $x2 = $gd_width - $padding;
                $y2 = $dst_y + $rh + 2;
                imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $feudalage_color);
            } elseif (!$age_flag[2]) {
                $x1 = $age_x[1];
                $y1 = $dst_y - 2;
                $x2 = $gd_width - $padding;
                $y2 = $dst_y + $rh + 2;
                imagefilledrectangle($gd, $x1, $y1, $x2, $y2, $castleage_color);
            }
        }

        // fill gd with researches
        $idx = 0;
        foreach ($this->players as $player) {
            // skip cooping player
            if (empty($player->researches)) {
                continue;
            }
            $dst_y = $idx * ($rh + $spacing) + $padding; $dst_x = 0; $prev_min = -1; $cnt = 0;
            $idx++;

            foreach ($player->researches as $research_id => $min) {
                if (array_key_exists($research_id, RecAnalystConst::$RESEARCHES)) {
                    $min = floor($min / 1000 / 60); // in minutes
                    if ($prev_min == $min) {
                        $cnt ++;
                        $dst_x = $x_offsets[$min] + ($cnt * $rw);
                    } else {
                        $cnt = 0;
                        $dst_x = $x_offsets[$min];
                    }
                    if ($im = imagecreatefromgif($config->resourcesDir . 'researches' . DIRECTORY_SEPARATOR .
                        RecAnalystConst::$RESEARCHES[$research_id][1] . RecAnalystConst::IMG_EXT)) {
                        imagecopyresampled($gd, $im, $dst_x, $dst_y, 0, 0, $rw, $rh, $orw, $orh);
                        imagedestroy($im);
                    }
                    $prev_min = $min;
                }
            }
        }

        // fill gd with timeline
        $white = imagecolorallocate($gd, 0xff, 0xff, 0xff);
        $shift = round(floor($rw / 2) - imagefontheight(1) / 2);
        foreach ($mins as $min => $cnt) {
            if ($cnt == 0) {
                continue;
            }
            $x = $x_offsets[$min] + $shift;
            $y = $real_cnt * ($rh + $spacing) + $padding + 30;
            $label = sprintf('%d min', $min);
            $font = 1;
            imagestringup($gd, $font, $x + 1, $y + 1, $label, $black);
            imagestringup($gd, $font, $x, $y, $label, $white);
            $x_offsets[$min] = $sum;
            $sum += $cnt * $rw;
        }

        return $gd;
    }

    /**
     * Generates image map for research timelines.
     * @return string Generated image map.
     */
    public function generateResearchesImageMap () {
        $config = $this->config;

        foreach ($this->players as $player) {
            asort ($player->researches, SORT_NUMERIC);
        }

        $total_mins = ceil ($this->gameInfo->playTime / 1000 / 60);
        // original width / height of image representing one research
        $orw = $orh = 38;
        // new width / height of image representing one research
        $rw = $rh = $config->researchTileSize;

        // reserve in case player clicked a research, but game finished before researching a technology
        $total_mins += 5;

        // $mins will contain the max amount of researches done by any player in a given minute
        // eg when p1 clicked 1 research in min 22, and p2 clicked 3, $mins[22] == 3
        $mins = array_fill(0, $total_mins, 0);

        foreach ($this->players as $player) {
            $prev_min = -1;
            $tmp_mins = array_fill(0, $total_mins, 0);
            foreach ($player->researches as $research_id => $min) {
                if (array_key_exists($research_id, RecAnalystConst::$RESEARCHES)) {
                    $min = floor($min / 1000 / 60); // in minutes
                    $tmp_mins[$min]++;
                }
            }
            foreach ($mins as $min => &$cnt) {
                if ($cnt < $tmp_mins[$min]) {
                    $cnt = $tmp_mins[$min];
                }
            }
        }

        // calculate max username width
        $max_username_width = 0; // max width for username
        $font = 3; // font used for usernames
        $real_cnt = 0;
        foreach ($this->players as $player) {
            // skip cooping players
            if (empty ($player->researches)) {
                continue;
            }
            if (strlen ($player->name) * imagefontwidth ($font) > $max_username_width) {
                $max_username_width = strlen ($player->name) * imagefontwidth ($font);
            }
            $real_cnt++;
        }

        $padding = 8;
        $spacing = $config->researchVSpacing;
        $max_username_width += $padding;
        // image width will be sum over min * reseach width + padding-left + padding-right
        $gd_width = array_sum ($mins) * $rw + 2 * $padding + $max_username_width;
        $gd_height = ($rw + $spacing) * $real_cnt + 50;

        // x_offsets will contain x-offset of first research in particular minute (variable length of minute)
        $x_offsets = array();
        $sum = 0 + $padding + $max_username_width;
        foreach ($mins as $min => $cnt) {
            $x_offsets[$min] = $sum;
            $sum += $cnt * $rw;
        }

        $imageMap = array();
        $idx = 0;
        foreach ($this->players as $player) {
            if (empty($player->researches)) {
                continue;
            }
            $dst_y = $idx * ($rh + $spacing) + $padding; $dst_x = 0; $prev_min = -1; $cnt = 0;
            $idx++;

            foreach ($player->researches as $research_id => $min) {
                if (array_key_exists($research_id, RecAnalystConst::$RESEARCHES)) {
                    $time = $min;
                    $min = floor($min / 1000 / 60); // in minutes

                    if ($prev_min == $min) {
                        $cnt ++;
                        $dst_x = $x_offsets[$min] + ($cnt * $rw);
                    } else {
                        $cnt = 0;
                        $dst_x = $x_offsets[$min];
                    }
                    $imageMap[] = array(
                        0 => sprintf('%d,%d,%d,%d', $dst_x, $dst_y, $dst_x + $rw, $dst_y + $rh),
                        1 => sprintf('%s %s', RecAnalystConst::$RESEARCHES[$research_id][0], self::gameTimeToString ($time, '(%02d:%02d:%02d)'))
                    );
                    $prev_min = $min;
                }
            }
        }

        return $imageMap;
    }

    /**
     * Builds teams.
     * @return void
     */
    public function buildTeams() {
        if (count($this->teams)) {
            // already built
            return;
        }
        $teamsByIndex = array();
        foreach ($this->players as $player) {
            if ($player->team == 0) {
                $found = false;
                foreach ($this->teams as $team) {
                    if ($team->getIndex() != $player->team) {
                        continue;
                    }
                    foreach ($team->players as $player_) {
                        if ($player_->index == $player->index) {
                            $team->addPlayer($player);
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        break;
                    }
                }
                if (!$found) {
                    $team = new Team();
                    $team->addPlayer($player);
                    $this->teams[] = $team;
                    $teamsByIndex[$player->team] = $team;
                }
            } else {
                if ($team = $teamsByIndex[$player->team]) {
                    $team->addPlayer($player);
                }
                else {
                    $team = new Team();
                    $team ->addPlayer($player);
                    $this->teams[] = $team;
                    $teamsByIndex[$player->team] = $team;
                }
            }
        }
    }

    /**
     * Performs post analyze actions.
     * @return void
     */
    protected function postAnalyze() {
        if (!$this->gameSettings->isScenario()) {
            $lines = explode("\n", $this->gameInfo->objectivesString);
            // get map
            if (!$this->_isMgx || $this->gameSettings->_mapId == Map::CUSTOM) {
                if (count($lines) > 2) {
                    $this->gameSettings->map = ltrim(strstr($lines[2], ': '), ': ');
                    if (!$this->_isMgx) {
                        $map_found = false;
                        //TODO get map from localized map strings
                    }
                }
            }
        }

        $this->buildTeams();

        // fix: player could click age advance, but game finished before reaching specific age
        foreach ($this->players as $player) {
            if ($player->feudalTime > $this->gameInfo->playTime) {
                $player->feudalTime = 0;
            }
            if ($player->castleTime > $this->gameInfo->playTime) {
                $player->castleTime = 0;
            }
            if ($player->imperialTime > $this->gameInfo->playTime) {
                $player->imperialTime = 0;
            }
        }

        //TODO: otestovat, ci je to OK
        //units pole
        if (!empty($this->ingameChat)) {
            usort($this->ingameChat, array('RecAnalyst\\RecAnalyst', 'chatMessagesCompare'));
        }

        if (!empty($this->buildings)) {
            ksort($this->buildings);
        }

        // we sort gaia objects, so we can draw first ciffs than relics,
        // this ensures that relics will overlap cliffs and not vice versa
        usort($this->gaiaObjects, array('RecAnalyst\\RecAnalyst', 'gaiaObjectsCompare'));

        // AOC11 (and above) bug or feature?
        if ($this->gameInfo->_gameVersion > GameInfo::VERSION_AOC10C) {
            $this->gameSettings->popLimit = 25 * $this->gameSettings->popLimit;
        }
    }

    /**
     * Returns analyze time (in ms).
     * @return int
     */
    public function getAnalyzeTime() {
        return $this->_analyzeTime;
    }

    /**
     * Comparision callback function for sorting gaia objects.
     * @param Unit $item1
     * @param Unit $item2
     * @return int
     * @static
     */
    protected static function gaiaObjectsCompare($item1, $item2) {
        if ($item1->id == Unit::RELIC && $item2->id != Unit::RELIC) {
            return 1;
        }
        if (in_array($item1->id, RecAnalystConst::$CLIFF_UNITS) &&
            !in_array($item2->id, RecAnalystConst::$CLIFF_UNITS)) {
            return -1;
        }
        if ($item2->id == Unit::RELIC && $item1->id != Unit::RELIC) {
            return -1;
        }
        if (in_array($item2->id, RecAnalystConst::$CLIFF_UNITS) &&
            !in_array($item1->id, RecAnalystConst::$CLIFF_UNITS)) {
            return 1;
        }
        return 0;
    }
    /**
     * Comparision callback function for chat message objects. Just accesses time.
     * @param ChatMessage $a
     * @param ChatMessage $b
     * @return int
     * @static
     */
    protected static function chatMessagesCompare($a, $b) {
        return $a->time - $b->time;
    }

    /**
     * Helper method to create a chat message correctly. Messages have the player name
     * and sometimes a group specifier (<Team>, <Enemy>, etc) included which is lame.
     * This strips that part.
     * @param int $time
     * @param Player $player
     * @param string $chat
     * @return ChatMessage
     * @static
     */
    protected static function createChatMessage($time, $player, $chat) {
        $group = '';
        // this is directed someplace
        if ($chat[0] === '<') {
            $end = strpos($chat, '>');
            $group = substr($chat, 1, $end - 1);
            $chat = substr($chat, $end + 2);
        }
        $chat = substr($chat, strlen($player->name) + 2);
        return new ChatMessage($time, $player, $chat, $group);
    }
}
