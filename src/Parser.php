<?php

namespace AydinHassan\XmlFuse;

/**
 * Interface Parser
 * @package AydinHassan\XmlFuse
 */
interface Parser
{
    /**
     * @return array
     */
    public function getXPaths();

    /**
     * @param array $xPaths
     */
    public function setXPaths(array $xPaths);

    /**
     * @return array
     */
    public function parse();
}
