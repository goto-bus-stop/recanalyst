<?php
namespace RecAnalyst\Processors;

use RecAnalyst\RecordedGame;
use RecAnalyst\Analyzers\BodyAnalyzer;

/**
 */
class Achievements
{
    /**
     * Recorded game file to use.
     *
     * @var \RecAnalyst\RecordedGame
     */
    private $rec;

    /**
     * Create an Achievements processor.
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param array  $options  Options to use. Unused.
     */
    public function __construct(RecordedGame $rec, array $options = [])
    {
        $this->rec = $rec;
    }

    /**
     * @return \StdClass[]
     */
    public function run()
    {
        $postGameData = $this->rec->body()->postGameData;
        if (!$postGameData) {
            return null;
        }

        $achievements = [];

        foreach ($postGameData->players as $i => $player) {
            if (!$player->name) {
                continue;
            }

            $achievements[$i] = (object) [
                'score' => $player->totalScore,
                'victory' => $player->victory ? true : false,
                'mvp' => $player->mvp ? true : false,
                'military' => $player->militaryStats,
                'economy' => $player->economyStats,
                'tech' => $player->techStats,
                'society' => $player->societyStats,
            ];
        }

        return $achievements;
    }
}
