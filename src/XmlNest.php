<?php

namespace AydinHassan\XmlFuse;

use SimpleXMLElement;

/**
 * Class XmlNest
 * @package AydinHassan\XmlFuse
 */
class XmlNest
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
            return [[]];
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
     * @return array
     */
    public function parseXPath(array $xPaths, SimpleXMLElement $elem)
    {

        if (!isset($xPaths['xPath']) || !isset($xPaths['key'])) {
            throw new \Exception("xPath Should have 'xPath' && 'key' set");
        }

        $xPath          = $xPaths['xPath'];
        $childXPaths    = (isset($xPaths['children'])) ? $xPaths['children'] : null;

        $children = $elem->xpath($xPath);

        if (!$children) {
            throw new \Exception(sprintf("No Elements found for xPath '%s'", $xPath));
        }

        $return = [];
        foreach ($children as $child) {
            $data = $this->getScalarRecords($child);

            if (null !== $childXPaths) {
                foreach($childXPaths as $childXPath) {
                    $data[$childXPath['key']] = $this->parseXPath($childXPath, $child);
                }
            }

            $return[] = $data;
        }
        return $return;
    }
}
