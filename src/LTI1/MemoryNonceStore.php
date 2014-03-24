<?php

namespace LTI1;

class MemoryNonceStore implements iNonceStore
{
    protected $consumerKey;
    protected $consumerKeys = array();
    const TIMEOUT = 300;

    public function __construct($consumerKey, array $options = array())
    {
        if(!isset($this->consumerKeys[$consumerKey]))
        {
            $this->consumerKeys[$consumerKey] = array();
        }
        $this->consumerKey = $consumerKey;
    }

    public function checkNonce($nonce, $timestamp, $options = array())
    {
        if(empty($nonce) || !is_string($nonce))
        {
            throw new RequestValidationException("Invalid nonce sent");
        }

        if(empty($timestamp) || !is_numeric($timestamp))
        {
            throw new RequestValidationException("Invalid timestamp sent");
        }
        if(array_key_exists($nonce, $this->consumerKeys[$this->consumerKey]))
        {
            throw new RequestValidationException("Nonce has already been used");
        }
        // Make sure our timestamp is an int
        $timestamp = (int) $timestamp;
        if((time() - $timestamp) > self::TIMEOUT)
        {
            throw new RequestValidationException("Timestamp has expired");
        }
        $this->expireNonce($nonce, $timestamp);
    }

    public function expireNonce($nonce, $timestamp)
    {
        $this->consumerKeys[$this->consumerKey][$nonce] = $timestamp;
    }
}