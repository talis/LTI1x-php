<?php

namespace LTI1;

interface iNonceStore {
    /**
     * @param string $consumerKey The identifier for the request credentials
     * @param array $options An optional array of implementation-specific options
     * @return iNonceStore
     */
    public function __construct($consumerKey, array $options = array());

    /**
     * The define method should determine the validity of the nonce or throw a RequestValidationException if invalid
     *
     * @param string $nonce
     * @param int|string $timestamp The timestamp should be a numeric of UNIX epoch seconds
     * @param array $options An optional array of implementation-specific options
     * @return mixed This is implementation-specific
     * @throws RequestValidationException
     */
    public function checkNonce($nonce, $timestamp, array $options = array());

    /**
     * The defined method should provide a way to take the nonce out of circulation, so it cannot be used again.
     *
     * @param string $nonce
     * @param int|string $timestamp The timestamp should be a numeric of UNIX epoch seconds
     * @param array $options An optional array of implementation-specific options
     * @return mixed This is implementation-specific
     */
    public function expireNonce($nonce, $timestamp, array $options = array());
}