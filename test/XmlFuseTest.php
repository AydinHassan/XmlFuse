<?php

namespace AydinHassan\XmlFuseTest;

use AydinHassan\XmlFuse\XmlFuse;

/**
 * Class XmlFuseTest
 * @package AydinHassan\XmlFuseTest
 */
class XmlFuseTest extends \PHPUnit_Framework_TestCase
{
    public function loadXmlFixture($fileName)
    {
        return file_get_contents(sprintf("%s/Fixtures/%s", __DIR__, $fileName));
    }

    public function testNonStringXmlArgumentThrowsException()
    {
        $message = 'XML Should be a string. Received: "stdClass"';
        $this->setExpectedException('InvalidArgumentException', $message);
        XmlFuse::factory('merge', new \stdClass());
    }

    public function testInvalidXmlThrowsException()
    {
        $xml = $this->loadXmlFixture("invalid.xml");

        $message
            = "XML Parsing Failed. Errors: "
            . "'Premature end of data in tag order line 3', 'Premature end of data"
            . " in tag orderStatus line 2'";

        $this->setExpectedException('UnexpectedValueException', $message);
        XmlFuse::factory('merge', $xml);
    }

    public function testInvalidTypeThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Expected string for type, received: "stdClass"');
        XmlFuse::factory(new \stdClass, '');
    }

    public function testNonRegisteredTypeThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Type: "notAType" is not supported. Valid types are: "merge, nest"'
        );
        XmlFuse::factory('notAType', '');
    }
}
