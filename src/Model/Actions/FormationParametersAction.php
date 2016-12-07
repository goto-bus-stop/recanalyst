<?php

namespace RecAnalyst\Model\Actions;

use RecAnalyst\RecordedGame;

/**
 * Represents ...
 */
class FormationParametersAction extends Action
{
    /**
     * The action ID.
     *
     * @var int
     */
    const ID = 0x1C;

    // FormationParams(pId=%d, lineRatio=%d, colRatio=%d, minColDist=%d,
    //                 colToLineDist=%d, formInfluenceDist=%.10f, breakAutoFormationsBySpeed=%d)
    private $playerId;
    private $lineRatio;
    private $columnRatio;
    private $minColumnDistance;
    private $columnToLineDistance;
    private $formInfluenceDistance;
    private $breakAutoFormationsBySpeed;

    /**
     * Create a ...
     *
     * @param \RecAnalyst\RecordedGame  $rec  Recorded game instance.
     * @param int  $time  Recorded game instance.
     */
    public function __construct(RecordedGame $rec, $time)
    {
        parent::__construct($rec, $time);
    }
}
