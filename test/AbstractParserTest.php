<?php

namespace AydinHassan\XmlFuseTest;

use AydinHassan\XmlFuse\XmlFuse;

/**
 * Class AbstractParserTest
 * @package AydinHassan\XmlFuseTest
 */
class AbstractParserTest extends \PHPUnit_Framework_TestCase
{
    public function loadXmlFixture($fileName)
    {
        return file_get_contents(sprintf("%s/Fixtures/%s", __DIR__, $fileName));
    }

    public function testGetScalarRecordsIgnoresNestedElements()
    {
        $xml = $this->loadXmlFixture("ignore-nest.xml");
        $xmlElement = new \SimpleXMLElement($xml);

        $parser = $this->getMockForAbstractClass(
            '\AydinHassan\XmlFuse\AbstractParser',
            [$xmlElement, []],
            '',
            true,
            true,
            true,
            ['__construct']
        );


        $res = $parser->getScalarRecords($xmlElement);
        $expected = [
            'name'  => 'Aydin',
            'id'    => 2,
        ];

        $this->assertEquals($expected, $res);
    }

    public function testGetScalarRecordsReturnsArrayIfOneElement()
    {
        $xml = $this->loadXmlFixture("return-one-as-array.xml");
        $xmlElement = new \SimpleXMLElement($xml);

        $parser = $this->getMockForAbstractClass(
            '\AydinHassan\XmlFuse\AbstractParser',
            [$xmlElement, []],
            '',
            true,
            true,
            true,
            ['__construct']
        );

        $res = $parser->getScalarRecords($xmlElement);
        $expected = [
            'name'  => 'Aydin',
        ];

        $this->assertEquals($expected, $res);
    }

    public function testValidXmlLoadsCorrectly()
    {
        $xml = $this->loadXmlFixture("valid.xml");
        $merger = XmlFuse::factory('merge', $xml);

        $this->assertEquals([], $merger->getXPaths());
    }

    public function testCanSetXPaths()
    {
        $xml = $this->loadXmlFixture("valid.xml");
        $merger = XmlFuse::factory('merge', $xml);

        $this->assertEquals([], $merger->getXPaths());

        $xPaths = [
            '//someXpath',
            '/someOtherXpath',
        ];

        $merger->setXPaths($xPaths);
        $this->assertEquals($xPaths, $merger->getXPaths());
    }

    public function testWithNoXPaths()
    {
        $xml = $this->loadXmlFixture("three-tier.xml");
        $merger = XmlFuse::factory('merge', $xml);

        $res = $merger->parse();

        $this->assertEquals([[]], $res);
    }
}
