<?php

namespace AydinHassan\XmlFuseTest;

use AydinHassan\XmlFuse\XmlNest;

/**
 * Class XmlNestTest
 * @package test
 */
class XmlNestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlNest
     */
    protected $nest;

    public function loadXmlFixture($fileName)
    {
        return file_get_contents(sprintf("%s/Fixtures/%s", __DIR__, $fileName));
    }

    public function setUp()
    {
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
        $this->nest = new XmlNest($xml, $xPaths);

        $res = $this->nest->parse();

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
        $this->nest = new XmlNest($xml, $xPaths);

        $res = $this->nest->parse();

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
}
