<?php

namespace RecAnalyst;

class RecordedGame
    // temporary, to keep compat with some other classes
    extends RecAnalyst
{

    const MGX_EXT = 'mgx';
    const MGL_EXT = 'mgl';
    const MGZ_EXT = 'mgz';
    const MGX2_EXT = 'mgx2';

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
    protected $mapData;

    /**
     * Map width.
     * @var int
     */
    protected $mapWidth;

    /**
     * Map height.
     * @var int
     */
    protected $mapHeight;

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
     * An associative multi-dimensional array containing building_type_id → building_num
     * pairs for each player.
     * @var array
     */
    public $buildings;

    /**
     * Elapsed time for analyzing in milliseconds.
     * @var int
     */
    protected $analyzeTime;

    /**
     * True, if the file being analyzed is mgx. False otherwise.
     * @var bool
     */
    protected $isMgx;

    /**
     * True, if the file being analyzed is mgl. False otherwise.
     * @var bool
     */
    protected $isMgl;

    /**
     * True, if the file being analyzed is mgz. False otherwise.
     * @var bool
     */
    protected $isMgz;

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

    private $fd;
    private $_headerLen;
    private $_nextPos;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->ext = strtolower(pathinfo($this->filename, PATHINFO_EXTENSION));
        $this->reset();
    }

    public function open()
    {
        $this->fd = fopen($this->filename, 'r');
    }

    /**
     * Resets the internal state.
     *
     * @return void
     */
    public function reset()
    {
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
        $this->mapData = array();
        $this->mapWidth = $this->mapHeight = 0;
        $this->analyzeTime = 0;
        $this->isMgx = false;
        $this->isMgl = false;
        $this->isMgz = false;
        $this->gaiaObjects = array();
        $this->playerObjects = array();

        $this->tributes = array();
        $this->_headerLen = 0;
        $this->_nextPos = 0;
    }

    public function analyze()
    {
        $starttime = microtime(true);
        if (empty($this->header)) {
            $this->extractStreams();
        }
        if (!$this->analyzeHeaderStream()) {
            return false;
        }
        if (!$this->analyzeBodyStream()) {
            return false;
        }

        $this->postAnalyze();
        $endtime = microtime(true);
        $this->analyzeTime = round(($endtime - $starttime) * 1000);
        return true;
    }

    /**
     * Extracts header and body streams from recorded game.
     *
     * @param string $ext   File extension.
     * @param mixed  $input File handler or file contents.
     *
     * @return void
     * @throws RecAnalystException
     * @todo input as file contents
     * @todo figure out $ext based on file contents
     */
    protected function extractStreams($a = null, $b = null)
    {
        if (empty($this->filename)) {
            throw new RecAnalystException(
                'No file has been specified for analyzing',
                RecAnalystException::FILE_NOT_SPECIFIED
            );
        }
        if (empty($this->fd)) {
            $this->open();
        }
        $fp = $this->fd;
        if (($packed_data = fread($fp, 4)) === false || strlen($packed_data) < 4) {
            throw new RecAnalystException(
                'Unable to read the header length',
                RecAnalystException::HEADERLEN_READERROR
            );
        }
        $unpacked_data = unpack('V', $packed_data);
        $this->_headerLen = $unpacked_data[1];
        if (!$this->_headerLen) {
            throw new RecAnalystException(
                'Header length is zero',
                RecAnalystException::EMPTY_HEADER
            );
        }
        $packed_data = fread($fp, 4);
        if ($packed_data === false || strlen($packed_data) < 4) {
            $this->_nextPos = 0;
        } else {
            $unpacked_data = unpack('V', $packed_data);
            $this->_nextPos = $unpacked_data[1];
        }

        // Version detection heuristic
        // TODO find something more accurate?
        $this->isMgx = $this->_nextPos < filesize($this->filename);
        $this->isMgl = !$this->isMgx;

        $this->_headerLen -= $this->isMgx ? 8 : 4;
        if ($this->isMgl) {
            fseek($fp, -4, SEEK_CUR);
        }
        $read = 0;
        $bindata = '';
        while ($read < $this->_headerLen && ($buff = fread($fp, $this->_headerLen - $read))) {
            $read += strlen($buff);
            $bindata .= $buff;
        }
        $read = 0;
        $this->body = '';
        while (!feof($fp)) {
            $buff = fread($fp, 8192);
            $this->body .= $buff;
        }
        $this->bodyStream->write($this->body);
        unset($buff);
        fclose($fp);

        $this->header = gzinflate($bindata, 8388608);  // 8MB
        $this->headerStream->write($this->header);
        unset($bindata);

        if (!strlen($this->header)) {
            throw new RecAnalystException(
                'Cannot decompress header section',
                RecAnalystException::HEADER_DECOMPRESSERROR
            );
        }
    }

    public function getHeaderContents() {
        return $this->header;
    }

    public function getBodyContents() {
        return $this->body;
    }

    /**
     * Analyzes header stream.
     *
     * @return bool True if the stream was analyzed successfully, false otherwise.
     * @throws RecAnalystException
     */
    protected function analyzeHeaderStream()
    {
        $constant2                 = pack('c*', 0x9A, 0x99, 0x99, 0x99, 0x99, 0x99, 0xF9, 0x3F);
        $separator                 = pack('c*', 0x9D, 0xFF, 0xFF, 0xFF);
        $scenario_constant         = pack('c*', 0xF6, 0x28, 0x9C, 0x3F);
        $aok_separator             = pack('c*', 0x9A, 0x99, 0x99, 0x3F);
        $player_info_end_separator = pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);

        $gameInfo = $this->gameInfo;
        $gameSettings = $this->gameSettings;
        $header = $this->headerStream;

        $header->setPosition(0);
        $size = $header->getSize();

        /* getting version */
        $header->readBuffer($version, 8);
        $version = rtrim($version); // throw null-termination character
        $header->readFloat($subVersion);
        $subVersion = round($subVersion, 2);
        switch ($version) {
        case RecAnalystConst::TRL_93:
            $gameInfo->gameVersion = $this->isMgx ? GameInfo::VERSION_AOCTRIAL : GameInfo::VERSION_AOKTRIAL;
            break;
        case RecAnalystConst::VER_93:
            $gameInfo->gameVersion = GameInfo::VERSION_AOK;
            break;
        case RecAnalystConst::VER_94:
            if ($this->isMgz) {
                $gameInfo->gameVersion = GameInfo::VERSION_UserPatch11;
            } else if ($subVersion > 11.76) {
                $gameInfo->gameVersion = GameInfo::VERSION_HD;
            } else {
                $gameInfo->gameVersion = GameInfo::VERSION_AOC;
            }
            break;
        case RecAnalystConst::VER_95:
            $gameInfo->gameVersion = GameInfo::VERSION_AOFE21;
            break;
        case RecAnalystConst::VER_98:
            $gameInfo->gameVersion = GameInfo::VERSION_UserPatch12;
            break;
        case RecAnalystConst::VER_99:
            $gameInfo->gameVersion = GameInfo::VERSION_UserPatch13;
            break;
        case RecAnalystConst::VER_9A: // RC 1
        case RecAnalystConst::VER_9B: // RC 2
        case RecAnalystConst::VER_9C:
            $gameInfo->gameVersion = GameInfo::VERSION_UserPatch14;
            break;
        default:
            $gameInfo->gameVersion = $version;
            break;
        }

        $gameInfo->isUserPatch = $gameInfo->gameVersion >= GameInfo::VERSION_UserPatch11 &&
                                 $gameInfo->gameVersion <= GameInfo::VERSION_UserPatch14;

        if ($gameInfo->gameVersion === GameInfo::VERSION_HD) {
            if ($subVersion === 11.80) {
                $gameInfo->gameSubVersion = '2.0';
            } else if ($subVersion === 11.90) {
                $gameInfo->gameSubVersion = '2.3';
            } else if ($subVersion === 11.91) {
                $gameInfo->gameSubVersion = '2.6';
            } else if ($subVersion === 11.93) {
                $gameInfo->gameSubVersion = '2.8';
            } else if ($subVersion === 11.96) {
                $gameInfo->gameSubVersion = '3.0';
            } else if ($subVersion === 12.34) {
                // TODO Which other versions?
                $gameInfo->gameSubVersion = '4.???';
            }
        } else if ($gameInfo->gameVersion === GameInfo::VERSION_UserPatch14) {
            if ($version === RecAnalystConst::VER_9A) {
                $gameInfo->gameSubVersion = 'RC1';
            } else if ($version === RecAnalystConst::VER_9B) {
                $gameInfo->gameSubVersion = 'RC2';
            }
        }

        switch ($gameInfo->gameVersion) {
        case GameInfo::VERSION_AOK:
        case GameInfo::VERSION_AOKTRIAL:
            $this->isMgl = true;
            $this->isMgx = false;
            $this->isMgz = false;
            break;
        case GameInfo::VERSION_AOC:
        case GameInfo::VERSION_AOCTRIAL:
            $this->isMgx = true;
            $this->isMgl = false;
            $this->isMgz = false;
            break;
        case GameInfo::VERSION_UserPatch11:
        case GameInfo::VERSION_UserPatch12:
        case GameInfo::VERSION_UserPatch13:
        case GameInfo::VERSION_UserPatch14:
        case GameInfo::VERSION_AOFE21:
            $this->isMgx = true;
            $this->isMgl = false;
            $this->isMgz = true;
            break;
        }

        /* getting Trigger_info position */
        $trigger_info_pos = $header->rfind($constant2);
        if ($trigger_info_pos == -1) {
            throw new RecAnalystException(
                '"Trigger Info" block has not been found',
                RecAnalystException::TRIGGERINFO_NOTFOUND
            );
        }
        $trigger_info_pos += strlen($constant2);

        /* getting Game_setting position */
        $game_setting_pos = $header->rfind($separator, -($size - $trigger_info_pos));
        if ($game_setting_pos == -1) {
            throw new RecAnalystException(
                '"Game Settings" block has not been found',
                RecAnalystException::GAMESETTINGS_NOTFOUND
            );
        }
        $game_setting_pos += strlen($separator);

        /* getting Scenario_header position */
        $scenario_separator = $this->isMgx ? $scenario_constant : $aok_separator;
        $scenario_header_pos = $header->rfind($scenario_separator, -($size - $game_setting_pos));
        if ($scenario_header_pos != -1) {
            $scenario_header_pos -= 4;  // next_unit_id
        }

        /* getting Game_Settings data */
        /* skip negative[2] */
        $header->setPosition($game_setting_pos + 8);
        if ($this->isMgx) {
            // doesn't exist in AOK
            $header->readInt($map_id);
        }
        $header->readInt($difficulty);
        $header->readBool($lock_teams);

        if ($this->isMgx) {
            if (isset(RecAnalystConst::$MAPS[$map_id])) {
                $gameSettings->mapId = $map_id;
                $gameSettings->map = RecAnalystConst::$MAPS[$map_id];

                if ($map_id == Map::CUSTOM) {
                    $gameSettings->mapStyle = GameSettings::MAPSTYLE_CUSTOM;
                } elseif (in_array($map_id, RecAnalystConst::$REAL_WORLD_MAPS)) {
                    $gameSettings->mapStyle = GameSettings::MAPSTYLE_REALWORLD;
                } else {
                    $gameSettings->mapStyle = GameSettings::MAPSTYLE_STANDARD;
                }
            }
        }

        $gameSettings->difficultyLevel = $difficulty;
        $gameSettings->lockDiplomacy = $lock_teams;

        // TODO is this really versions ≥12?
        if ($subVersion >= 12) {
            // TODO is this always 16? what is in these 16 bytes?
            $header->skip(16);
        }

        /* getting Player_info data */
        for ($i = 0; $i < 9; $i++) {
            $header->readInt($player_data_index);
            $header->readInt($human);
            $header->readString($playername);

            /* sometimes very rarely index is 1 */
            if ($human == 0x00 || $human == 0x01) {
                continue;
            }

            if ($i) {
                $player = new Player();
                $player->name  = $playername;
                $player->index = $player_data_index;
                $player->human = ($human == 0x02);
                $player->spectator = ($human == 0x06);
                $this->players[] = $player;
                if (!isset($this->playersByIndex[$player->index])) {
                    $this->playersByIndex[$player->index] = $player;
                }
            }
        }

        /* getting game type for AOK */
        if ($this->isMgl) {
            $header->setPosition($trigger_info_pos - strlen($constant2));
            $header->skip(-6);
            // unknown25
            $header->readInt($unknown25);
            switch ($unknown25) {
            case 1:
                $gameSettings->gameType = GameSettings::TYPE_DEATHMATCH;
                break;
            case 256:
                $gameSettings->gameType = GameSettings::TYPE_REGICIDE;
                break;
            }
        }

        /* getting victory */
        $header->setPosition($trigger_info_pos - strlen($constant2));
        if ($this->isMgx) {
            $header->skip(-7);
        }
        $header->skip(-110);
        $header->readInt($victory_condition);
        $header->skip(8);
        $header->readChar($istimeLimit);
        if ($istimeLimit) {
            $header->readFloat($time_limit);
        }

        $gameSettings->victory->victoryCondition = $victory_condition;
        if ($istimeLimit) {
            $gameSettings->victory->timeLimit = intval(round($time_limit) / 10);
        }

        /* Trigger_info */
        $header->setPosition($trigger_info_pos + 1);

        // always zero in mgl? or not really a trigger_info here for aok
        $header->readInt($num_trigger);
        if ($num_trigger) {
            /* skip Trigger_info data */
            for ($i = 0; $i < $num_trigger; $i++) {
                $header->skip(18);
                $header->readInt($desc_len);
                $header->skip($desc_len);
                $header->readInt($name_len);
                $header->skip($name_len);
                $header->readInt($num_effect);

                for ($j = 0; $j < $num_effect; $j++) {
                    $header->skip(24);
                    $header->readInt($num_selected_object);
                    if ($num_selected_object == -1) {
                        $num_selected_object = 0;
                    }

                    $header->skip(72);
                    $header->readInt($text_len);
                    $header->skip($text_len);
                    $header->readInt($sound_len);
                    $header->skip($sound_len);
                    $header->skip($num_selected_object << 2);
                }
                $header->skip($num_effect << 2);
                $header->readInt($num_condition);
                $header->skip(72 * $num_condition);
                $header->skip($num_condition << 2);
            }
            $header->skip($num_trigger << 2);

            $gameSettings->map = '';
            $gameSettings->gameType = GameSettings::TYPE_SCENARIO;
        }

        /* Other_data */
        $team_indexes = array();
        for ($i = 0; $i < 8; $i++) {
            $header->readChar($team_indexes[]);
        }

        for ($i = 0, $l = count($this->players); $i < $l; $i++) {
            if ($player = $this->players[$i]) {
                $player->team = $team_indexes[$i] - 1;
            }
        }

        // TODO is <12 the correct cutoff point?
        if ($subVersion < 12) {
            $header->skip(1);
        }
        $header->readInt($reveal_map);
        $header->skip(4);  // always 1?
        $header->readInt($map_size);
        $header->readInt($pop_limit);
        if ($this->isMgx) {
            $header->readChar($game_type);
            $header->readChar($lock_diplomacy);
        }
        $gameSettings->revealMap = $reveal_map;
        $gameSettings->mapSize = $map_size;
        $gameSettings->popLimit = $pop_limit;
        if ($this->isMgx) {
            $gameSettings->lockDiplomacy = ($lock_diplomacy == 0x01);
            $gameSettings->gameType = $game_type;
        }

        if ($subVersion >= 11.96) {
            $header->skip(1);
        }

        // multiplayer pregame chat is only stored in MGX and later
        if ($this->isMgx) {
            $header->readInt($num_chat);
            for ($i = 0; $i < $num_chat; $i++) {
                $header->readString($chat);
                // 0-length chat exists
                if ($chat == '') {
                    continue;
                }

                // pre-game chat messages are stored as @#%dPlayerName: Message, where %d is a digit from 1 to 8 indicating player's index,
                // "PlayerName" is a name of the player, "Message" is a chat message itself, messages usually ends with #0, but not always
                if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
                    $chat = rtrim($chat); // throw null-termination character
                    if (!empty($this->playersByIndex[$chat[2]])) {
                        $player = $this->playersByIndex[$chat[2]];
                    } else {
                        // this player left before the game started
                        $player = null;
                    }
                    $this->pregameChat[] = ChatMessage::create(null, $player, substr($chat, 3));
                }
            }
            unset($chat);
        }

        /* skip AI_info if exists */
        $header->setPosition(0x0C);
        $header->readBool($include_ai);
        if ($include_ai) {
            $header->skip(2);
            $header->readWord($num_string);
            $header->skip(4);
            for ($i = 0; $i < $num_string; $i++) {
                $header->readInt($string_length);
                $header->skip($string_length);
            }
            $header->skip(6);
            for ($i = 0; $i < 8; $i++) {
                $header->skip(10);
                $header->readWord($num_rule);
                $header->skip(4);
                $header->skip(400 * $num_rule);
            }
            $header->skip(5544);
            if ($subVersion >= 11.96) {
                $header->skip(1280);
            }
        }

        /* getting data */
        $header->skip(4);
        $header->readInt($game_speed);
        $header->skip(37);
        $header->readWord($rec_player_ref);
        $header->readChar($num_player);
        if ($this->isMgx) {
            $header->skip(2);
        }
        $header->readWord($game_mode);

        $gameSettings->gameSpeed = $game_speed;
        if ($game_mode == 1) {
            $gameSettings->gameMode = GameSettings::MODE_SINGLEPLAYER;
        } else {
            $gameSettings->gameMode = GameSettings::MODE_MULTIPLAYER;
        }

        if ($player = $this->playersByIndex[$rec_player_ref]) {
            $player->owner = true;
        }

        /* getting map */
        $header->skip(58);
        $header->readInt($map_size_x);
        $header->readInt($map_size_y);
        $this->mapWidth = $map_size_x;
        $this->mapHeight = $map_size_y;

        $header->readInt($num_unknown_data);
        /* unknown data */
        for ($i = 0; $i < $num_unknown_data; $i++) {
            if ($subVersion >= 11.93) {
                $header->skip(2048 + $map_size_x * $map_size_y * 2);
            } else {
                $header->skip(1275 + $map_size_x * $map_size_y);
            }
            $header->readInt($num_float);
            $header->skip(($num_float * 4) + 4);
        }
        $header->skip(2);

        /* map data */
        for ($y = 0; $y < $map_size_y; $y++) {
            for ($x = 0; $x < $map_size_x; $x++) {
                $header->readChar($terrain_id);
                $header->readChar($elevation);
                $this->mapData[$x][$y] = $terrain_id;
            }
        }

        $header->readInt($num_data);
        $header->skip(4 + ($num_data * 4));
        for ($i = 0; $i < $num_data; $i++) {
            $header->readInt($num_couples);
            $header->skip($num_couples * 8);
        }
        $header->readInt($map_size_x2);
        $header->readInt($map_size_y2);
        $header->skip(($map_size_x2 * $map_size_y2 * 4) + 4);
        $header->readInt($num_unknown_data2);
        $header->skip(27 * $num_unknown_data2 + 4);

        /* getting Player_info */
        $pos = $header->getPosition();
        if (!$this->readPlayerInfoBlockEx($num_player)) {
            $header->setPosition($pos);
            $this->readPlayerInfoBlock($num_player);
        }

        if ($scenario_header_pos > 0) {
            /* getting objectives or instructions */
            $header->setPosition($scenario_header_pos + 4433);
            /* original scenario file name */
            $header->readString($original_sc_filename, 2);
            if ($original_sc_filename != '') {
                $gameInfo->scFileName = $original_sc_filename;
                if ($this->isMgl) {
                    $gameSettings->gameType = GameSettings::TYPE_SCENARIO;  // this way we detect scenarios in mgl, is there any other way?
                }
            }
            $header->skip($this->isMgx ? 24 : 20);
        }

        /* scenario instruction or Objectives string, depends on game type */
        $objectives_pos = $header->getPosition();
        $header->readString($instructions, 2);
        if ($instructions != '' && !$gameSettings->isScenario()) {
            $gameInfo->objectivesString = rtrim($instructions);
        }

        return true;
    }

    /**
     * Analyzes body stream.
     * This method is slower and is not used, just for demonstration.
     *
     * @see RecAnalyst::analyzeBodyStreamF()
     * Both methods have same functionality, but analyzeBodyStream() uses MemoryStream() methods to read bodyStream,
     * and analyzeBodyStreamF() uses raw string manipulation
     * @return bool True if the stream was successfully analyzed, false otherwise.
     */
    protected function analyzeBodyStream()
    {
        $pos = 0;
        $time_cnt = $this->gameSettings->gameSpeed;
        $age_flag = array(0, 0, 0, 0, 0, 0, 0, 0);

        $body = $this->bodyStream;
        $body->setPosition(0);
        $size = $body->getSize();

        while ($body->getPosition() < $size - 3) {
            if ($body->getPosition() == 0 && !$this->isMgx) {
                $od_type = 0x04;
            } else {
                $body->readInt($od_type);
            }
            // ope_data types: 4(Game_start or Chat), 2(Sync), or 1(Command)
            switch ($od_type) {
            // Game_start or Chat command
            case 0x04:
            case 0x03:
                $body->readInt($command);
                if ($command == 0x01F4) {
                    // Game_start
                    if ($this->isMgl) {
                        $body->skip(28);
                        $body->readChar($ver);
                        switch ($ver) {
                        case 0:
                            if ($this->gameInfo->gameVersion != GameInfo::VERSION_AOKTRIAL) {
                                $this->gameInfo->gameVersion = GameInfo::VERSION_AOK20;
                            }
                            break;
                        case 1:
                            $this->gameInfo->gameVersion = GameInfo::VERSION_AOK20A;
                            break;
                        }
                        $body->skip(3);
                    } else {
                        switch ($od_type) {
                        case 0x03:
                            if ($this->gameInfo->gameVersion != GameInfo::VERSION_AOCTRIAL) {
                                $this->gameInfo->gameVersion = GameInfo::VERSION_AOC10;
                            }
                            break;
                        case 0x04:
                            if ($this->gameInfo->gameVersion == GameInfo::VERSION_AOC) {
                                $this->gameInfo->gameVersion = GameInfo::VERSION_AOC10C;
                            }
                            break;
                        }
                        $body->skip(20);
                    }
                } elseif ($command == -1) {
                    // Chat
                    foreach ($this->players as $i => $player) {
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

                    $body->readString($chat);
                    // see reading pre-game messages
                    if ($chat[0] == '@' && $chat[1] == '#' && $chat[2] >= '1' && $chat[2] <= '8') {
                        $chat = rtrim($chat); // throw null-termination character
                        if (substr($chat, 3, 2) == '--' && substr($chat, -2) == '--') {
                            // skip messages like "--Warning: You are being under attack... --"
                        } else {
                            if (!empty($this->players[$chat[2] - 1])) {
                                $player = $this->players[$chat[2] - 1];
                            } else {
                                $player = null;
                            }
                            $this->ingameChat[] = ChatMessage::create($time_cnt, $player, substr($chat, 3));
                        }
                    }
                }
                break;
            // Sync
            case 0x02:
                $body->readInt($time);
                $time_cnt += $time; // time_cnt is in miliseconds
                $body->readInt($unknown);
                if ($unknown == 0) {
                    $body->skip(28);
                }
                $body->skip(12);
                break;
            // Command
            case 0x01:
                $body->readInt($length);
                $body->readChar($command);
                $body->skip(-1);
                switch ($command) {
                case 0x0B: // player resign
                    $body->skip(1);
                    $body->readChar($player_index);
                    $body->readChar($player_number);
                    $body->readChar($disconnected);
                    if (($player = $this->playersByIndex[$player_index]) && $player->resignTime == 0) {
                        $player->resignTime = $time_cnt;
                        $this->ingameChat[] = new ChatMessage($time_cnt, null, $player->name . ' resigned');
                    }
                    $body->skip($length - 4);
                    break;
                case 0x65: // researches
                    $body->skip(4);
                    $body->readInt($building_id);
                    $body->readWord($player_id);
                    $body->readWord($research_id);
                    if (!($player = $this->playersByIndex[$player_id])) {
                        $body->skip($length - 12);
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
                    $body->skip($length - 12);
                    break;
                case 0x77: // training unit
                    $body->skip(4);
                    $body->readInt($building_id);
                    $body->readWord($unit_type_id);
                    $body->readWord($unit_num);

                    if (!isset($this->units[$unit_type_id])) {
                        $this->units[$unit_type_id] = $unit_num;
                    } else {
                        $this->units[$unit_type_id] += $unit_num;
                    }
                    $body->skip($length - 12);
                    break;
                case 0x64: // pc trains unit
                    $body->skip(10);
                    $body->readWord($unit_type_id);
                    $unit_num = 1; // always for pc?
                    if (!isset($this->units[$unit_type_id])) {
                        $this->units[$unit_type_id] = $unit_num;
                    } else {
                        $this->units[$unit_type_id] += $unit_num;
                    }
                    $body->skip($length - 12);
                    break;
                case 0x66: // building
                    $body->skip(2);
                    $body->readWord($player_id);
                    $body->skip(8);
                    $body->readWord($building_type_id);

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
                    $body->skip($length - 14);
                    break;
                case 0x6C: // tributing
                    $body->skip(1);
                    $body->readChar($player_id_from);
                    $body->readChar($player_id_to);
                    $body->readChar($resource_id);
                    $body->readFloat($amount_tributed);
                    $body->readFloat($market_fee);

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
                    $body->skip($length - 12);
                    break;
                case 0xFF: // multiplayer postgame data in UP1.4 RC2+
                    $body->skip(1);
                    $this->readPostgameData($body);
                    break;
                default:
                    $body->skip($length);
                    break;
                }
                $body->skip(4);
                break;
            default:
                /* detect if this is a header of saved chapter */
                /* sometimes header of the saved chapter is in $03 command, instead of $20 as it should be,
                   when this happens the length of $20 command is $0E, otherwise it is $02 (always?, rule?),
                   we do not rely on it, that's why we are skipping saved chapter data here and not in $20 command */
                if ($body->getPosition() === $this->_nextPos - $this->_headerLen - 4) {
                    /* this is a header of saved chapter data, we have already read next_command_block
                       that's why -4 in the if-statement */
                    /* next_pos - header_len = offset of compressed chapter data */
                    $next_command_block = $od_type;
                    $body->readInt($this->_nextPos); // next_chapter_pos
                    $body->setPosition($next_command_block - $this->_headerLen - 8);
                } else {
                    // shouldn't occur, just to prevent unexpected endless cycling
                    $body->skip(1);
                }
                break;
            }
        }

        $this->gameInfo->playTime = $time_cnt;

        return true;
    }

    /**
     * Extended analysis of the PlayerInfo block.
     *
     * @param int $num_player Amount of player blocks to read.
     *
     * @return boolean True if the info blocks were read successfully, false otherwise.
     */
    protected function readPlayerInfoBlockEx($num_player)
    {
        $exist_object_separator     = pack('c*', 0x0B, 0x00, 0x08, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00);
        $object_end_separator       = pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00, 0x00, 0x80, 0xBF,
            0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00);
        $aok_object_end_separator   = pack('c*', 0xFF, 0xFF, 0xFF, 0xFF, 0x00, 0x00, 0x80, 0xBF, 0x00, 0x00, 0x80, 0xBF,
            0x00, 0x00, 0x00, 0x00, 0x00);
        $player_info_end_separator  = pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);
        $objects_mid_separator_gaia = pack('c*', 0x00, 0x0B, 0x00, 0x40, 0x00, 0x00, 0x00, 0x20, 0x00, 0x00);

        $map_size_x = $this->mapWidth;
        $map_size_y = $this->mapHeight;

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
                if ($this->gameInfo->gameVersion == GameInfo::VERSION_AOKTRIAL
                    || $this->gameInfo->gameVersion == GameInfo::VERSION_AOCTRIAL
                ) {
                    $this->headerStream->skip(4);
                }
                $this->headerStream->skip($num_player + 43);

                // skip playername
                $this->headerStream->readWord($player_name_len);
                $this->headerStream->skip($player_name_len + 6);

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
                $this->headerStream->skip($this->isMgx ? 629 : 593);
                $this->headerStream->readFloat($init_camera_pos_x);
                $this->headerStream->readFloat($init_camera_pos_y);
                $this->headerStream->skip($this->isMgx ? 9 : 5);
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
                if ($this->gameInfo->gameVersion == GameInfo::VERSION_AOKTRIAL
                    || $this->gameInfo->gameVersion == GameInfo::VERSION_AOCTRIAL
                ) {
                    $this->headerStream->skip(4);
                }
                $this->headerStream->skip($num_player + 70);
                $this->headerStream->skip($this->isMgx ? 792 : 756);
            }
            $this->headerStream->skip($this->isMgx ? 41249 : 34277);
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
                    if ($this->isMgl) {
                        $this->headerStream->skip(1);
                    }
                    break;
                case 20:
                    if ($this->isMgx) {
                        $this->headerStream->skip(59);
                        $this->headerStream->readChar($b);
                        $this->headerStream->skip(-60);
                        $this->headerStream->skip(68-4);
                        if ($b == 2) {
                            $this->headerStream->skip(34);
                        }
                    } else {
                        $this->headerStream->skip(103-4);
                    }
                    break;
                case 30:
                    if ($this->isMgx) {
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
                    if ($this->isMgx) {
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
                    if ($this->isMgx) {
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
                    if ($this->isMgx) {
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

                    if ($buff[0] == $objects_mid_separator_gaia[0]
                        && $buff[1] == $objects_mid_separator_gaia[1]
                    ) {
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
     * Standard PlayerInfo block analysis. Used for HD.
     *
     * @param int $num_player Amount of player blocks.
     *
     * @return void
     */
    protected function readPlayerInfoBlock($num_player)
    {
        $player_info_end_separator  = pack('c*', 0x00, 0x0B, 0x00, 0x02, 0x00, 0x00, 0x00, 0x02, 0x00, 0x00, 0x00, 0x0B);

        $gameInfo = $this->gameInfo;
        $header = $this->headerStream;

        $this->gaiaObjects = [];
        $this->playerObjects = [];
        // first is GAIA, skip some useless bytes
        if ($gameInfo->gameVersion == GameInfo::VERSION_AOKTRIAL || $gameInfo->gameVersion == GameInfo::VERSION_AOCTRIAL) {
            $header->skip(4);
        }
        $header->skip($num_player + 70); // + 2 len of playerlen
        $header->skip($this->isMgx ? 792 : 756);
        $header->skip($this->isMgx ? 41249 : 34277);
        $header->skip($this->mapWidth * $this->mapHeight);
        foreach ($this->players as &$player) {
            // skip cooping player, they have no data in Player_info
            $player_ = $this->playersByIndex[$player->index];
            if ($player_ && $player_ !== $player && $player_->civId) {
                $player->civId = $player_->civId;
                $player->colorId = $player_->colorId;
                $player->team = $player_->team;
                $player->isCooping = true;
                continue;
            }

            $pos = $header->find($player_info_end_separator);
            $header->skip(strlen($player_info_end_separator));

            if ($gameInfo->gameVersion == GameInfo::VERSION_AOKTRIAL || $gameInfo->gameVersion == GameInfo::VERSION_AOCTRIAL) {
                $header->skip(4);
            }
            $header->skip($num_player + 52 + strlen($player->name)); // + null-terminator

            /* Civ_header */
            $header->readFloat($food);
            $header->readFloat($wood);
            $header->readFloat($stone);
            $header->readFloat($gold);
            /* headroom = (house capacity - population) */
            $header->readFloat($headroom);
            $header->skip(4);
            /* Starting Age, note: PostImperial Age = Imperial Age here */
            $header->readFloat($data6);
            $header->skip(16);
            $header->readFloat($population);
            $header->skip(100);
            $header->readFloat($civilian_pop);
            $header->skip(8);
            $header->readFloat($military_pop);
            $header->skip($this->isMgx ? 629 : 593);
            $header->readFloat($init_camera_pos_x);
            $header->readFloat($init_camera_pos_y);
            $header->skip($this->isMgx ? 9 : 5);
            $header->readChar($civilization);
            // sometimes(?) civilization is zero in scenarios when the first player is briton (only? always? rule?)
            if (!$civilization) {
                $civilization++;
            }
            /* skip unknown9[3] */
            $header->skip(3);
            $header->readChar($player_color);

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

            $header->skip($this->isMgx ? 41249 : 34277);
            $header->skip($this->mapWidth * $this->mapHeight);
        }
    }

    /**
     * Extracts post-game data (achievements etc) from the body stream.
     *
     * Post-game data will be set on $this->postgameData.
     *
     * @param Stream $stream Body stream to extract from.
     *
     * @return void
     */
    protected function readPostgameData($stream)
    {
        // Prize for ugliest, most boring method of the project goes to…
        $data = new \stdClass;

        $stream->skip(3);
        $stream->read($scenarioFilename, 32);
        $data->scenarioFilename = rtrim($scenarioFilename);
        $stream->skip(4);
        $stream->readInt($data->duration);
        $stream->readChar($data->allowCheats);
        $stream->readChar($data->complete);
        $stream->skip(14);
        $stream->readChar($data->mapSize);
        $stream->readChar($data->mapId);
        $stream->readChar($data->population);
        $stream->skip(1);
        $stream->readChar($data->victory);
        $stream->readChar($data->startingAge);
        $stream->readChar($data->resources);
        $stream->readChar($data->allTechs);
        $stream->readChar($data->teamTogether);
        $stream->readChar($data->revealMap);
        $stream->skip(3);
        $stream->readChar($data->lockTeams);
        $stream->readChar($data->lockSpeed);
        $stream->skip(1);

        $players = array();
        for ($i = 0; $i < 8; $i++) {
            $playerStats = new \stdClass;
            $stream->read($playerName, 16);
            $playerStats->name = rtrim($playerName);
            $stream->readWord($playerStats->totalScore);
            $totalScores = array();
            for ($j = 0; $j < 8; $j++) {
                $stream->readWord($totalScores[$j]);
            }
            $playerStats->totalScores = $totalScores;
            $stream->readChar($playerStats->victory);
            $stream->readChar($playerStats->civId);
            $stream->readChar($playerStats->colorId);
            $stream->readChar($playerStats->team);
            $stream->skip(2);
            $stream->readChar($playerStats->mvp);
            $stream->skip(3);
            $stream->readChar($playerStats->result);
            $stream->skip(3);

            $militaryStats = new \stdClass;
            $stream->readWord($militaryStats->score);
            $stream->readWord($militaryStats->unitsKilled);
            $stream->readWord($militaryStats->u0);
            $stream->readWord($militaryStats->unitsLost);
            $stream->readWord($militaryStats->buildingsRazed);
            $stream->readWord($militaryStats->u1);
            $stream->readWord($militaryStats->buildingsLost);
            $stream->readWord($militaryStats->unitsConverted);
            $playerStats->militaryStats = $militaryStats;

            $stream->skip(32);

            $economyStats = new \stdClass;
            $stream->readWord($economyStats->score);
            $stream->readWord($economyStats->u0);
            $stream->readInt($economyStats->foodCollected);
            $stream->readInt($economyStats->woodCollected);
            $stream->readInt($economyStats->stoneCollected);
            $stream->readInt($economyStats->goldCollected);
            $stream->readWord($economyStats->tributeSent);
            $stream->readWord($economyStats->tributeReceived);
            $stream->readWord($economyStats->tradeProfit);
            $stream->readWord($economyStats->relicGold);
            $playerStats->economyStats = $economyStats;

            $stream->skip(16);

            $techStats = new \stdClass;
            $stream->readWord($techStats->score);
            $stream->readWord($techStats->u0);
            $stream->readInt($techStats->feudalTime);
            $stream->readInt($techStats->castleTime);
            $stream->readInt($techStats->imperialTime);
            $stream->readChar($techStats->mapExploration);
            $stream->readChar($techStats->researchCount);
            $stream->readChar($techStats->researchPercent);
            $playerStats->techStats = $techStats;

            $stream->skip(1);

            $societyStats = new \stdClass;
            $stream->readWord($societyStats->score);
            $stream->readChar($societyStats->totalWonders);
            $stream->readChar($societyStats->totalCastles);
            $stream->readChar($societyStats->relicsCaptured);
            $stream->readChar($societyStats->u0);
            $stream->readWord($societyStats->villagerHigh);
            $playerStats->societyStats = $societyStats;

            $stream->skip(84);

            $players[] = $playerStats;
        }
        $data->players = $players;

        $stream->skip(4);
        $this->postgameData = $data;
    }

    /**
     * Builds teams.
     *
     * @return void
     */
    public function buildTeams()
    {
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
                if (array_key_exists($player->team, $teamsByIndex)) {
                    $teamsByIndex[$player->team]->addPlayer($player);
                } else {
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
     *
     * @return void
     */
    protected function postAnalyze()
    {
        if (!$this->gameSettings->isScenario()) {
            $lines = explode("\n", $this->gameInfo->objectivesString);
            // get map
            if (!$this->isMgx || $this->gameSettings->mapId == Map::CUSTOM) {
                if (count($lines) > 2) {
                    $this->gameSettings->map = ltrim(strstr($lines[2], ': '), ': ');
                    if (!$this->isMgx) {
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

        if (!empty($this->ingameChat)) {
            $compareTime = function ($a, $b) { return $a->time - $b->time; };
            usort($this->ingameChat, $compareTime);
        }

        if (!empty($this->buildings)) {
            ksort($this->buildings);
        }

        // we sort gaia objects, so we can draw first ciffs than relics,
        // this ensures that relics will overlap cliffs and not vice versa
        usort($this->gaiaObjects, function ($item1, $item2) {
            // relics show on top of everything else
            if ($item1->id === Unit::RELIC && $item2->id !== Unit::RELIC) {
                return 1;
            }
            // cliffs show below everything else
            if (in_array($item1->id, RecAnalystConst::$CLIFF_UNITS)
                && !in_array($item2->id, RecAnalystConst::$CLIFF_UNITS)
            ) {
                return -1;
            }
            if ($item2->id === Unit::RELIC && $item1->id !== Unit::RELIC) {
                return -1;
            }
            if (in_array($item2->id, RecAnalystConst::$CLIFF_UNITS)
                && !in_array($item1->id, RecAnalystConst::$CLIFF_UNITS)
            ) {
                return 1;
            }
            return 0;
        });

        // UserPatch supports pop limits of up to 1000, so that won't normally fit in a byte.
        // Instead it stores N so that the pop limit is 25*N.
        if ($this->gameInfo->isUserPatch) {
            $this->gameSettings->popLimit = 25 * $this->gameSettings->popLimit;
        }
    }

    /**
     * Returns analyze time (in ms).
     *
     * @return int
     */
    public function getAnalyzeTime()
    {
        return $this->analyzeTime;
    }

}
