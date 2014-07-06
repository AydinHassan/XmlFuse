<?php

namespace AydinHassan\XmlFuseTest;

use AydinHassan\XmlFuse\XmlFuse;

/**
 * Class XmlFuseTest
 * @package test
 */
class XmlFuseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlFuse
     */
    protected $fuse;

    public function loadXmlFixture($fileName)
    {
        return file_get_contents(sprintf("%s/Fixtures/%s", __DIR__, $fileName));
    }

    public function setUp()
    {
    }

    public function testNonStringXmlArgumentThrowsException()
    {
        $message = 'XML Should be a string. Received: "stdClass"';
        $this->setExpectedException('InvalidArgumentException', $message);
        new XmlFuse(new \stdClass());
    }

    public function testInvalidXmlThrowsException()
    {
        $xml = $this->loadXmlFixture("invalid.xml");

        $message
            = "XML Parsing Failed. Errors: "
            . "'Premature end of data in tag order line 3', 'Premature end of data"
            . " in tag orderStatus line 2'";

        $this->setExpectedException('UnexpectedValueException', $message);
        $this->fuse = new XmlFuse($xml);
    }

    public function testValidXmlLoadsCorrectly()
    {
        $xml = $this->loadXmlFixture("valid.xml");
        $this->fuse = new XmlFuse($xml);

        $this->assertEquals($xml, $this->fuse->getRawXml());
        $this->assertEquals([], $this->fuse->getXPaths());
    }

    public function testCanSetXPaths()
    {
        $xml = $this->loadXmlFixture("valid.xml");
        $this->fuse = new XmlFuse($xml);

        $this->assertEquals($xml, $this->fuse->getRawXml());
        $this->assertEquals([], $this->fuse->getXPaths());

        $xPaths = [
            '//someXpath',
            '/someOtherXpath',
        ];

        $this->fuse->setXPaths($xPaths);
        $this->assertEquals($xPaths, $this->fuse->getXPaths());
    }

    public function testGetScalarRecordsIgnoresNestedElements()
    {
        $fuse = $this->getMockBuilder('AydinHassan\XmlFuse\XmlFuse')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $xml = $this->loadXmlFixture("ignore-nest.xml");
        $xmlElement = new \SimpleXMLElement($xml);

        $res = $fuse->getScalarRecords($xmlElement);
        $expected = [
            'name'  => 'Aydin',
            'id'    => 2,
        ];

        $this->assertEquals($expected, $res);
    }

    public function testGetScalarRecordsReturnsArrayIfoneElement()
    {
        $fuse = $this->getMockBuilder('AydinHassan\XmlFuse\XmlFuse')
            ->disableOriginalConstructor()
            ->setMethods(['__construct'])
            ->getMock();

        $xml = $this->loadXmlFixture("return-one-as-array.xml");
        $xmlElement = new \SimpleXMLElement($xml);

        $res = $fuse->getScalarRecords($xmlElement);
        $expected = [
            'name'  => 'Aydin',
        ];

        $this->assertEquals($expected, $res);
    }

    public function testTwoTierXPathSearch()
    {
        $xPaths = [
            '//orderStatus/order',
            'lines/line',
        ];

        $xml = $this->loadXmlFixture("two-tier.xml");
        $this->fuse = new XmlFuse($xml, $xPaths);

        $res = $this->fuse->parse();

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
        $this->fuse = new XmlFuse($xml, $xPaths);

        $res = $this->fuse->parse();

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
        $this->fuse = new XmlFuse($xml, $xPaths);

        $res = $this->fuse->parse();

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

    public function testWithNoXPaths()
    {
        $xml = $this->loadXmlFixture("three-tier.xml");
        $this->fuse = new XmlFuse($xml);

        $res = $this->fuse->parse();

        $this->assertEquals([[]], $res);
    }

    public function testWithInvalidXPath()
    {
        $xPaths = [
            '//orderStatus/nothere',
        ];

        $xml = $this->loadXmlFixture("three-tier.xml");
        $this->fuse = new XmlFuse($xml, $xPaths);

        $res = $this->fuse->parse();

        $this->assertEquals([[]], $res);
    }
}
