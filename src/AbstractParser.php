<?php

namespace AydinHassan\XmlFuse;

use SimpleXMLElement;

/**
 * Class AbstractParser
 * @package AydinHassan\XmlFuse
 */
class AbstractParser
{
    /**
     * XML Object
     *
     * @var SimpleXMLElement
     */
    protected $xml;
    /**
     * xPaths to search
     * for
     *
     * @var array
     */
    protected $xPaths;

    /**
     * @param \SimpleXMLElement $xml
     * @param array $xPaths
     * @throws \UnexpectedValueException
     */
    public function __construct(\SimpleXMLElement $xml, array $xPaths = array())
    {
        $this->xml = $xml;
        $this->xPaths = $xPaths;
    }

    /**
     * @return array
     */
    public function getXPaths()
    {
        return $this->xPaths;
    }

    /**
     * @param array $xPaths
     */
    public function setXPaths(array $xPaths)
    {
        $this->xPaths = $xPaths;
    }

    /**
     * Get all the Scalar records in the current
     * element. Ignore all nested data. The XPaths should be
     * used to include this data.
     *
     * @param SimpleXMLElement $element
     * @return array
     */
    public function getScalarRecords(SimpleXMLElement $element)
    {
        $data = [];

        //multiple elements
        if ($element->count()) {
            foreach ($element->children() as $name => $property) {
                if (!$property->count()) {
                    $data[$name] = (string)$property;
                }
            }

            return $data;
            //only 1 element
        } else {
            return [$element->getName() => (string)$element];
        }
    }

    /**
     * Start the Fusing process
     * Using the XPaths try to merge
     * nested data with child data, recursively
     */
    public function parse()
    {

        if (!count($this->xPaths)) {
            return [[]];
        }

        //create copy of xPaths
        $xPaths = $this->xPaths;
        return $this->parseXPath($xPaths, $this->xml);
    }
}
