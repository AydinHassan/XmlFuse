<?php

namespace AydinHassan\XmlFuse;

/**
 * Class XmlFuse
 * @package AydinHassan\XmlFuse
 */
class XmlFuse
{
    /**
     * @var array
     */
    protected static $types = [
        'nest'  => 'XmlNest',
        'merge' => 'XmlMerge',
    ];

    /**
     * @param $type
     * @param $xml
     * @param array $xPaths
     * @return mixed
     */
    public static function factory($type, $xml, array $xPaths = array())
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected string for type, received: "%s"',
                    (is_object($type) ? get_class($type) : gettype($type))
                )
            );
        }

        if (!isset(self::$types[$type])) {
            throw new \InvalidArgumentException(
                sprintf('Type: "%s" is not supported. Valid types are: "merge, nest"', $type)
            );
        }

        if (!is_string($xml)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'XML Should be a string. Received: "%s"',
                    (is_object($xml)) ? get_class($xml) : gettype($xml)
                )
            );
        }

        libxml_use_internal_errors(true);
        $xmlObj = simplexml_load_string($xml);

        if ($xmlObj=== false) {
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

        $class = 'AydinHassan\XmlFuse\\' . self::$types[$type];
        return new $class($xmlObj, $xPaths);
    }
}
