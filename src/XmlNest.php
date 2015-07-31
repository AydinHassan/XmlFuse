<?php

namespace AydinHassan\XmlFuse;

use SimpleXMLElement;

/**
 * Class XmlNest
 * @package AydinHassan\XmlFuse
 */
class XmlNest extends AbstractParser implements Parser
{

    /**
     * Recursively loop through XML using XPaths
     * Unit all the xPaths have been used or
     * those XPaths contain no records
     *
     * @param array $xPaths
     * @param SimpleXMLElement $elem
     * @return array
     * @throws \Exception
     */
    public function parseXPath(array $xPaths, SimpleXMLElement $elem, $asArray = true)
    {

        if (!isset($xPaths['xPath']) || !isset($xPaths['key'])) {
            throw new \Exception('xPath Should have "xPath" & "key" set');
        }

        $xPath          = $xPaths['xPath'];
        $childXPaths    = (isset($xPaths['children'])) ? $xPaths['children'] : null;

        $children = $elem->xpath($xPath);

        if (false === $children) {
            throw new \Exception(sprintf('Invalid xPath: "%s"', $xPath));
        }

        $return = [];
        foreach ($children as $child) {
            $data = $this->getScalarRecords($child, $asArray);

            if (null !== $childXPaths) {
                foreach ($childXPaths as $childXPath) {
                    $getTextContent = true;
                    if (preg_match('#\/text\(\)$#', $childXPath['xPath'])) {
                        //we want to extract the value
                        //not an array of values
                        $getTextContent = false;
                    }

                    $childData = $this->parseXPath($childXPath, $child, $getTextContent);
                    if ($getTextContent) {
                        $data[$childXPath['key']] = $childData;
                    } else {
                        $data[$childXPath['key']] = current($childData);
                    }
                }
            }

            $return[] = $data;
        }
        return $return;
    }
}
