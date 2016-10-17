<?php

namespace RecAnalyst\Model;

class Version
{
    private $rec;

    public function __construct($rec, $string, $subVersion)
    {
        $this->rec = $rec;
        $this->versionString = $string;
        $this->subVersion = $subVersion;
    }

    public function name()
    {
        return $this->rec->trans('game_versions', $this->version);
    }
}
