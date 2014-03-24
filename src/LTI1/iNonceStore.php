<?php

namespace LTI1;

interface iNonceStore {
    public function __construct($consumerKey, array $options = array());
    public function checkNonce($nonce, $timestamp, $options = array());
    public function expireNonce($nonce, $timestamp);
}