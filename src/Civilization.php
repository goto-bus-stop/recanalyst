<?php

namespace RecAnalyst;

/**
 * Represents a player's Civilization.
 *
 * TODO add hooks for ~special~ behaviour to this (eg. civ research bonuses).
 */
class Civilization
{
    const NONE       = 0;

    /**
     * Not instantiable.
     *
     * @return void
     */
    private function __construct()
    {
    }
}
