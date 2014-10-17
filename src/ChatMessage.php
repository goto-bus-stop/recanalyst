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

}
