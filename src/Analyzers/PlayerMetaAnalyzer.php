<?php

namespace RecAnalyst\Analyzers;

use RecAnalyst\Model\Player;

/**
 * Analyze the small player metadata block. Can be composed or run
 * independently.
 */
class PlayerMetaAnalyzer extends Analyzer
{
    /**
     * Run the analysis.
     *
     * @return \RecAnalyst\Model\Player[] Players.
     */
    protected function run()
    {
        $isComposed = $this->position > 0;
        if (!$isComposed) {
            // If this analyzer was not called from another analyzer at a
            // specific position, we find the correct position here.
            $this->seek();
            return $this->read(self::class);
        }

        $players = [];
        // The first player in this list will be the "main" co-op player.
        $coopPartners = [];
        for ($i = 0; $i <= 8; $i += 1) {
            $player = $this->readPlayerMeta($i);
            if ($player->humanRaw === 0 || $player->humanRaw === 1) {
                continue;
            }
            $players[] = $player;
            $coopPartners[$player->index][] = $player;
        }

        foreach ($players as $player) {
            $player->setCoopPartners($coopPartners[$player->index]);
        }

        return $players;
    }

    /**
     * Reads a player meta info block for a single player. This just includes
     * their nickname, index and "human" status. More information about players
     * is stored later on in the recorded game file and is read by the
     * PlayerInfoBlockAnalyzer.
     *
     * Player meta structure:
     *     int32 index;
     *     int32 human; // indicates whether player is AI/human/spectator
     *     uint32 nameLength;
     *     char name[nameLength];
     *
     * @return \RecAnalyst\Model\Player
     */
    protected function readPlayerMeta($i)
    {
        $player = new Player($this->rec);
        $player->number = $i;
        $player->index = $this->readHeader('l', 4);
        $human = $this->readHeader('l', 4);
        $length = $this->readHeader('L', 4);
        if ($length) {
            $player->name = $this->readHeaderRaw($length);
        } else {
            $player->name = '';
        }
        $player->humanRaw = $human;
        $player->human = $human === 0x02;
        $player->spectator = $human === 0x06;
        return $player;
    }

    /**
     * Find the position of the small player metadata block.
     */
    private function seek()
    {
        $version = $this->get(VersionAnalyzer::class);

        $constant2 = pack('c*', 0x9A, 0x99, 0x99, 0x99, 0x99, 0x99, 0xF9, 0x3F);
        $separator = pack('c*', 0x9D, 0xFF, 0xFF, 0xFF);

        $playersByIndex = [];

        $size = strlen($this->header);
        $this->position = 0;

        $triggerInfoPos = strrpos($this->header, $constant2, $this->position) + strlen($constant2);
        $gameSettingsPos = strrpos($this->header, $separator, -($size - $triggerInfoPos)) + strlen($separator);

        $this->position = $gameSettingsPos + 8;
        if (!$version->isAoK) {
            // Skip Map ID.
            $this->position += 4;
        }
        // Skip difficulty & diplomacy lock.
        $this->position += 8;

        // TODO Is 12.3 the correct cutoff point?
        if ($version->subVersion >= 12.3) {
            // TODO what are theeeese?
            $this->position += 16;
        }
    }
}
