<?php
/**
 * Defines ChatMessage class.
 *
 * @package RecAnalyst
 */

namespace RecAnalyst;

/**
 * The ChatMessage class represents a single chat message in the game.
 *
 * @package RecAnalyst
 */
class ChatMessage {

    /**
     * Sent time in milliseconds since the start of the game.
     *
     * @var int
     */
    public $time;

    /**
     * Player who sent this message.
     * This might be a player that is not actually in the game, if they joined
     * the lobby but left before the game started. In that case the Player
     * object will be empty except for `$name`.
     *
     * @var Player
     */
    public $player;

    /**
     * Message text.
     *
     * @var string
     */
    public $msg;

    /**
     * Group at which this chat is directed (<Team>, <Enemy>, <All>), if any.
     *
     * @var string
     */
    public $group;

    /**
     * Class constructor.
     *
     * @param int    $time   When this message was sent, in milliseconds since the start of the game.
     * @param Player $player Player that sent the message.
     * @param string $msg    Message content.
     * @param string $group  Group this message was directed to.
     *
     * @return void
     */
    public function __construct($time = 0, Player $player = null, $msg = '', $group = '')
    {
        $this->time = $time;
        $this->player = $player;
        $this->msg = $msg;
        $this->group = $group;
    }

    /**
     * Helper method to create a chat message from a chat string more easily.
     *
     * Messages actually have the player name and sometimes a group specifier (<Team>, <Enemy>, etc)
     * included in their message body which is lame. Sometimes players that don't end up in the
     * player info blocks of the recorded games sent messages anyway (particularly pre-game chat by people
     * who joined the multiplayer lobby and then left) so we deal with that too.
     *
     * @param int    $time   Time at which this message was sent in milliseconds since the start of the game.
     * @param Player $player Message Sender.
     * @param string $chat   Message contents.
     *
     * @return ChatMessage
     * @static
     */
    public static function create($time, $player, $chat)
    {
        $group = '';
        // this is directed someplace
        if ($chat[0] === '<') {
            $end = strpos($chat, '>');
            $group = substr($chat, 1, $end - 1);
            $chat = substr($chat, $end + 1);
        }
        if (is_null($player)) {
            $player = new Player();
            $player->name = substr($chat, 0, strpos($chat, ': '));
            if ($player->name[0] === ' ') {
                $player->name = substr($player->name, 1);
            }
        }
        $chat = substr($chat, strlen($player->name) + 2);
        return new ChatMessage($time, $player, $chat, $group);
    }
}
