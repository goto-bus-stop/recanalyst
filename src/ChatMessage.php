<?php
/**
 * Defines ChatMessage class.
 *
 * @package recAnalyst
 */

namespace RecAnalyst;

/**
 * Class ChatMessage.
 *
 * ChatMessage implements chat message.
 * @package recAnalyst
 */
class ChatMessage {

    /**
     * Time.
     * @var int
     */
    public $time;

    /**
     * Player.
     * @var Player
     */
    public $player;

    /**
     * Message text.
     * @var string
     */
    public $msg;

    /**
     * Group at which this chat is directed (<Team>, <Enemy>, <All>)
     * @var string
     */
    public $group;

    /**
     * Class constructor.
     * @return void
     */
    public function __construct($time = 0, Player $player = null, $msg = '', $group = '') {
        $this->time = $time;
        $this->player = $player;
        $this->msg = $msg;
        $this->group = $group;
    }

}
