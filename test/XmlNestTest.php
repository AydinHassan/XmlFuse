<?php

namespace AydinHassan\XmlFuseTest;

use AydinHassan\XmlFuse\XmlNest;
use AydinHassan\XmlFuse\XmlFuse;

/**
 * Class XmlNestTest
 * @package test
 */
class XmlNestTest extends \PHPUnit_Framework_TestCase
{
    public function loadXmlFixture($fileName)
    {
        return file_get_contents(sprintf("%s/Fixtures/%s", __DIR__, $fileName));
    }

    public function testXPathAndKeyMustBeSet()
    {
        $xml    = $this->loadXmlFixture("nested.xml");
        $xmlObj = simplexml_load_string($xml);
        $nester = new XmlNest($xmlObj, []);

        $this->setExpectedException(
            '\Exception',
            'xPath Should have "xPath" & "key" set'
        );

        $nester->parseXPath(['children' => []], $xmlObj);
    }

    public function testExceptionIsThrowIfNoElementsFoundFromXPath()
    {
        $xml    = $this->loadXmlFixture("nested.xml");
        $xmlObj = simplexml_load_string($xml);
        $nester = new XmlNest($xmlObj, []);

        $this->setExpectedException(
            '\Exception',
            'Invalid xPath: "notanxpath-/"'
        );

        $nester->parseXPath(['key' => 'items', 'xPath' => 'notanxpath-/'], $xmlObj);
    }

    public function testNestedParse()
    {
        $xPaths = [
            'key'       => 'returns',
            'xPath'     => '/returnStatus/return',
            'children'  => [
                [
                    'key'   => 'items',
                    'xPath' => 'lines/line',
                ],
            ]
        ];

        $xml = $this->loadXmlFixture("nested.xml");
        $xmlObj = simplexml_load_string($xml);
        $nester = new XmlNest($xmlObj, $xPaths);

        $res = $nester->parse();

        $expected = [
            [
                'orderId'   => '100000026',
                'rmaStatus' => 'RETURNED',
                'items'     => [
                    [
                        'id'    => 1,
                        'sku'   => 'SKU1',
                        'qty'   => 2,
                    ],
                    [
                        'id'    => 2,
                        'sku'   => 'SKU2',
                        'qty'   => 3,
                    ]
                ],
            ],
        ];

        $this->assertEquals($expected, $res);
    }

    public function testTwiceNestedParse()
    {
        $xPaths = [
            'key'       => 'orders',
            'xPath'     => '/orderStatus/order',
            'children'  => [
                [
                    'key'   => 'statuses',
                    'xPath' => 'statuses/status',
                ],
                [
                    'key'   => 'items',
                    'xPath' => 'lines/line',
                ],
            ]
        ];

        $xml = $this->loadXmlFixture("twice-nested.xml");
        $xmlObj = simplexml_load_string($xml);
        $nester = new XmlNest($xmlObj, $xPaths);

        $res = $nester->parse();

        $expected = [
            [
                'orderId'   => '100000026',
                'email'     => 'aydin@hotmail.co.uk',
                'statuses'  => [
                    [
                        'code'          => 'STATE1',
                        'description'   => 'STATE1 Description',
                    ],
                    [
                        'code'          => 'STATE2',
                        'description'   => 'STATE2 Description',
                    ],
                ],
                'items'     => [
                    [
                        'id'    => 1,
                        'sku'   => 'SKU1',
                        'qty'   => 2,
                    ],
                    [
                        'id'    => 2,
                        'sku'   => 'SKU2',
                        'qty'   => 3,
                    ],
                    [
                        'id'    => 3,
                        'sku'   => 'SKU3',
                        'qty'   => 3,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $res);
    }

    public function testNestScalar()
    {
        $xPaths = [
            'xPath' => '//SKUs/SKU',
            'key' => 'product',
            'children' => [
                [
                    'xPath' => 'Attributes/Colour/text()',
                    'key' => 'colour'
                ],
                [
                    'xPath' => 'Attributes/Size/text()',
                    'key' => 'size'
                ],
                [
                    'xPath' => 'Attributes/AvailableFrom/text()',
                    'key' => 'available_from'
                ]
            ]
        ];

        $xml = $this->loadXmlFixture("nest-scalar.xml");
        $xmlObj = simplexml_load_string($xml);
        $nester = new XmlNest($xmlObj, $xPaths);

        $res = $nester->parse();

        $expected = [
            [
                'node'              => 'RGI002XS',
                'colour'            => 'QmxhY2s=',
                'size'              => 'WFM=',
                'available_from'    => 'SW4gU3RvY2s=',
            ]
        ];

        $this->assertEquals($expected, $res);
    }
}
