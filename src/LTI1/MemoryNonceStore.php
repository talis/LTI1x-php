<?php
/**
 * VERY simplistic nonce management store.  Since PHP doesn't maintain state, the only thing it really does is confirm
 * that the timestamp is valid.  Mostly meant as a template to build a persistent store with.
 */
namespace LTI1;

class MemoryNonceStore implements iNonceStore
{
    /**
     * @var string The access key associated with the nonce/request
     */
    protected $consumerKey;
    /**
     * @var array An array of known consumer keys - not really relevant for a memory-based store in PHP
     */
    protected $consumerKeys = array();
    /**
     * The time period, in seconds, that the request is valid
     */
    const TIMEOUT = 300;

    /**
     * @param string $consumerKey The access key associated with the nonce/request
     * @param array $options Unused in this implementation
     */
    public function __construct($consumerKey, array $options = array())
    {
        if(!isset($this->consumerKeys[$consumerKey]))
        {
            $this->consumerKeys[$consumerKey] = array();
        }
        $this->consumerKey = $consumerKey;
    }

    /**
     * Checks to see if the nonce is new and that the request falls within the timestamp grace period.  Returns true
     * and expires the nonce if valid, throws a RequestValidationException, if not.
     *
     * @param string $nonce The unique nonce
     * @param int|string $timestamp Unix timestamp, in seconds. Must be either an int or a string
     * @param array $options Unused in this implementation
     * @return bool
     * @throws RequestValidationException
     */
    public function checkNonce($nonce, $timestamp, array $options = array())
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
        // We're just looking for something less than the difference of TIMEOUT: ±TIMEOUT
        if(abs((time() - $timestamp)) > self::TIMEOUT)
        {
            throw new RequestValidationException("Timestamp has expired");
        }
        $this->expireNonce($nonce, $timestamp);
        return true;
    }

    /**
     * Add the nonce to the array of nonces we've seen
     * @param string $nonce
     * @param int|string $timestamp Unix timestamp, in seconds. Must be either an int or a string
     * @param array $options Not used in this implementation
     * @return void
     */
    public function expireNonce($nonce, $timestamp, array $options=array())
    {
        $this->consumerKeys[$this->consumerKey][$nonce] = $timestamp;
    }
}