<?php

namespace AydinHassan\XmlFuseTest;

use AydinHassan\XmlFuse\XmlMerge;
use AydinHassan\XmlFuse\Factory;
use AydinHassan\XmlFuse\XmlFuse;

/**
 * Class XmlFuseTest
 * @package test
 */
class XmlMergeTest extends \PHPUnit_Framework_TestCase
{

    public function loadXmlFixture($fileName)
    {
        return file_get_contents(sprintf("%s/Fixtures/%s", __DIR__, $fileName));
    }

    public function testExceptionIsThrowIfNoElementsFoundFromXPath()
    {
        $xml    = $this->loadXmlFixture("nested.xml");
        $xmlObj = simplexml_load_string($xml);
        $merger = new XmlMerge($xmlObj, []);

        $this->setExpectedException(
            '\Exception',
            'Invalid xPath: "notanxpath-/"'
        );

        $merger->parseXPath(['notanxpath-/'], $xmlObj);
    }

    public function testTwoTierXPathSearch()
    {
        $xPaths = [
            '//orderStatus/order',
            'lines/line',
        ];

        $xml = $this->loadXmlFixture("two-tier.xml");
        $xmlObj = simplexml_load_string($xml);
        $merger = new XmlMerge($xmlObj, $xPaths);

        $res = $merger->parse();

        $expected = [
            [
                'clientCode'          => '01',
                'orderNumber'         => '01',
                'customerOrderNumber' => '02',
                'id'                  => '1',
                'qty'                 => '2'
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '01',
                'customerOrderNumber' => '02',
                'id'                  => '5',
                'qty'                 => '1'
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '300',
                'qty'                 => '4'
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '265',
                'qty'                 => '2'
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '100',
                'qty'                 => '1'
            ],
        ];

        $this->assertEquals($expected, $res);
    }

    public function testThreeTierXPathSearch()
    {
        $xPaths = [
            '//orderStatus/order',
            'lines/line',
            'statuses/status'
        ];

        $xml = $this->loadXmlFixture("three-tier.xml");
        $xmlObj = simplexml_load_string($xml);
        $merger = new XmlMerge($xmlObj, $xPaths);

        $res = $merger->parse();

        $expected = [
            [
                'clientCode'          => '01',
                'orderNumber'         => '01',
                'customerOrderNumber' => '02',
                'id'                  => '1',
                'qty'                 => '2'
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '01',
                'customerOrderNumber' => '02',
                'id'                  => '5',
                'qty'                 => '1'
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '300',
                'qty'                 => '4',
                'status'              => 'Status1',
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '300',
                'qty'                 => '4',
                'status'              => 'Status2',
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '265',
                'qty'                 => '2',
                'status'              => 'Status1',
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
                'id'                  => '100',
                'qty'                 => '1',
                'status'              => 'Status1',
            ],
        ];

        $this->assertEquals($expected, $res);
    }

    public function testOneTierXPathSearch()
    {
        $xPaths = [
            '//orderStatus/order',
        ];

        $xml = $this->loadXmlFixture("three-tier.xml");
        $xmlObj = simplexml_load_string($xml);
        $merger = new XmlMerge($xmlObj, $xPaths);

        $res = $merger->parse();

        $expected = [
            [
                'clientCode'          => '01',
                'orderNumber'         => '01',
                'customerOrderNumber' => '02',
            ],
            [
                'clientCode'          => '01',
                'orderNumber'         => '02',
                'customerOrderNumber' => '675',
            ],
        ];

        $this->assertEquals($expected, $res);
    }

    public function testWithInvalidXPath()
    {
        $xPaths = [
            '//orderStatus/nothere',
        ];

        $xml = $this->loadXmlFixture("three-tier.xml");
        $xmlObj = simplexml_load_string($xml);
        $merger = new XmlMerge($xmlObj, $xPaths);

        $res = $merger->parse();

        $this->assertEquals([[]], $res);
    }
}
