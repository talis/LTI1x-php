<?php

require_once 'TestBase.php';

class ToolProviderTest extends TestBase
{
    public function testConstructor()
    {
        $key = 'fooBar';
        $secret = 'My secret';
        $provider = $this->getMockBuilder('\LTI1\ToolProvider')
            ->setMethods(array('processParams', 'createMemoryNonceStore'))
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())->method('processParams');
        $provider->expects($this->once())->method('createMemoryNonceStore');

        $provider->__construct($key, $secret);

    }

    public function testConstructorBadConsumerKey()
    {
        $this->setExpectedException('\InvalidArgumentException', 'No consumerKey sent!');
        $provider = new \LTI1\ToolProvider('', 'Foo Bar');
    }

    public function testConstructorBadSharedSecret()
    {
        $this->setExpectedException('\InvalidArgumentException', 'No consumerSecret sent!');
        $provider = new \LTI1\ToolProvider('fooBar', '');
    }

}