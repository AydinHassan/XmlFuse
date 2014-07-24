<?php

namespace AydinHassan\XmlFuse;

use SimpleXMLElement;

/**
 * Class XmlMerge
 * @package AydinHassan\XmlFuse
 */
class XmlMerge extends AbstractParser implements Parser
{

    /**
     * Recursively loop through XML using XPaths
     * Unit all the xPaths have been used or
     * those XPaths contain no records
     *
     * @param array $xPaths
     * @param SimpleXMLElement $elem
     * @param array $parentData
     * @return array
     */
    public function parseXPath(array $xPaths, SimpleXMLElement $elem, array $parentData = [])
    {

        $xPath      = array_shift($xPaths);
        $children   = $elem->xpath($xPath);

        if (false === $children) {
            throw new \Exception(sprintf('Invalid xPath: "%s"', $xPath));
        }

        if (!count($children) && !count($parentData)) {
            //this is the top level so we don't need to nest
            return [$parentData];
        } elseif (!count($children)) {
            return [$parentData];
        }

        $return = [];
        foreach ($children as $child) {
            $data = $this->getScalarRecords($child);
            $data = array_merge($parentData, $data);

            if (count($xPaths) > 0) {
                $return = array_merge($return, $this->parseXPath($xPaths, $child, $data));
            } else {
                $return[] = $data;
            }
        }

        return $return;
    }
}
