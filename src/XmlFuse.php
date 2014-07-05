<?php

namespace AydinHassan\XmlFuse;

use SimpleXMLElement;

/**
 * Class XmlFuse
 * @package AydinHassan\XmlFuse
 */
class XmlFuse
{

    /**
     * XML Object
     *
     * @var SimpleXMLElement
     */
    protected $xml;

    /**
     * Raw XML String
     *
     * @var string
     */
    protected $rawXml;

    /**
     * xPaths to search
     * for
     *
     * @var array
     */
    protected $xPaths;

    /**
     * @param string $xml
     * @param array $xPaths
     * @throws \UnexpectedValueException
     */
    public function __construct($xml, array $xPaths = array())
    {

        if (!is_string($xml)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'XML Should be a string. Received: "%s"',
                    (is_object($xml)) ? get_class($xml) : gettype($xml)
                )
            );
        }

        libxml_use_internal_errors(true);
        $this->xml = simplexml_load_string($xml);

        if ($this->xml === false) {
            $message = sprintf(
                "XML Parsing Failed. Errors: '%s'",
                implode(
                    "', '",
                    array_map(function (\LibXMLError $xmlError) {
                        return trim($xmlError->message);
                    }, libxml_get_errors())
                )
            );

            throw new \UnexpectedValueException($message);
        }

        $this->rawXml   = $xml;
        $this->xPaths   = $xPaths;
    }

    /**
     * @return array
     */
    public function getXPaths()
    {
        return $this->xPaths;
    }

    /**
     * @return string
     */
    public function getRawXml()
    {
        return $this->rawXml;
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
                    $data[$name] = (string) $property;
                }
            }

            return $data;
        //only 1 element
        } else {
            return [$element->getName() => (string) $element];
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
            return [];
        }

        //create copy of xPaths
        $xPaths = $this->xPaths;
        return $this->parseXPath($xPaths, $this->xml);
    }

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

        if (!count($children) && !count($parentData)) {
            //this is the top level so we don't need to nest
            return $parentData;
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
