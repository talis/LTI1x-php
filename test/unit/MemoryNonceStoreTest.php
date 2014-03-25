<?php

require_once 'TestBase.php';

class MemoryNonceStoreTest extends TestBase {
    public function testStoreInitialization()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $this->assertInstanceOf('LTI1\iNonceStore', $store);
        $this->assertEquals(300, LTI1\MemoryNonceStore::TIMEOUT);
    }

    public function testGoodNonce()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $this->assertTrue($store->checkNonce(uniqid(), time()));
        $this->assertTrue($store->checkNonce(uniqid(), time()));
        $this->assertTrue($store->checkNonce(uniqid(), time()));
    }

    public function testTimestampTolerance()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $this->assertTrue($store->checkNonce(uniqid(), (time() - (\LTI1\MemoryNonceStore::TIMEOUT - 5))));
        $this->assertTrue($store->checkNonce(uniqid(), (time() + (\LTI1\MemoryNonceStore::TIMEOUT - 5))));
    }

    public function testCheckNonceExpiresRequest()
    {
        $store = $this->getMock('\LTI1\MemoryNonceStore', array('expireNonce'), array('fooBar'));
        $store->expects($this->once())->method('expireNonce');
        $this->assertTrue($store->checkNonce(uniqid(), time()));
    }

    public function testReusedNonce()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $nonce = uniqid();
        $this->assertTrue($store->checkNonce($nonce, time()));

        $this->setExpectedException('\LTI1\RequestValidationException', 'Nonce has already been used');
        $store->checkNonce($nonce, time());
    }

    public function testInvalidNonce()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $this->setExpectedException('\LTI1\RequestValidationException', 'Invalid nonce sent');
        $store->checkNonce('', time());
    }

    public function testInvalidTimestamp()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $this->setExpectedException('\LTI1\RequestValidationException', 'Invalid timestamp sent');
        $store->checkNonce(uniqid(), '');
    }

    public function testExpiredNonce()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $nonce = uniqid();
        $store->expireNonce($nonce, time());
        $this->setExpectedException('\LTI1\RequestValidationException', 'Nonce has already been used');
        $store->checkNonce($nonce, time());
    }

    public function testTimestampExpired()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $timestamp = time() - (\LTI1\MemoryNonceStore::TIMEOUT + 1);
        $this->setExpectedException('\LTI1\RequestValidationException', 'Timestamp has expired');
        $store->checkNonce(uniqid(), $timestamp);
    }

    public function testTimestampNotInThreshold()
    {
        $store = new \LTI1\MemoryNonceStore('fooBar');
        $timestamp = time() + (\LTI1\MemoryNonceStore::TIMEOUT + 1);
        $this->setExpectedException('\LTI1\RequestValidationException', 'Timestamp has expired');
        $store->checkNonce(uniqid(), $timestamp);
    }
}