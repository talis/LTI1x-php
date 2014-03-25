<?php

require_once 'TestBase.php';
class OAuthSignatureTest extends TestBase
{
    public function testGenerateBaseString()
    {
        $params = array(
            'foo_bar'=>array("AAABBBCC", "BBBCCCC"),
            'lti_message_type'=>'basic-lti-launch-request',
            'lti_version'=>'LTI-1p0',
            'context_id'=>'123456',
            'oauth_consumer_key'=>'A12345',
            'oauth_timestamp'=>'1395780004',
            'oauth_nonce'=>'5331e9fb9d2ec',
            'oauth_signature_method'=>'HMAC-SHA1',
            'oauth_version'=>'1.0',
            'oauth_signature'=>'vXcmquqHwKAcEq00eNZQCPHh1zI%3D',
            'user_id'=>'14312',
            'resource_link_id'=>'ABCDEF1'
        );

        $secret = "The secret to my success!";
        // Use ToolProvider
        $provider = new \LTI1\ToolProvider($params['oauth_consumer_key'], $secret, $params);

        $this->assertEquals('POST&http%3A%2F%2Fexample.com%2Fresource&context_id%3D123456%26foo_bar%3DAAABBBCC%26foo_bar%3DBBBCCCC%26lti_message_type%3Dbasic-lti-launch-request%26lti_version%3DLTI-1p0%26oauth_consumer_key%3DA12345%26oauth_nonce%3D5331e9fb9d2ec%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1395780004%26oauth_version%3D1.0%26resource_link_id%3DABCDEF1%26user_id%3D14312', $provider->generateBaseString('POST', 'http://example.com/resource', $params));
    }

    public function testSignature()
    {
        $params = array(
            'foo_bar'=>array("AAABBBCC", "BBBCCCC"),
            'lti_message_type'=>'basic-lti-launch-request',
            'lti_version'=>'LTI-1p0',
            'context_id'=>'987654321',
            'oauth_consumer_key'=>'A12345',
            'oauth_timestamp'=>'1395781965',
            'oauth_nonce'=>'5331f10db00f7',
            'oauth_signature_method'=>'HMAC-SHA1',
            'oauth_version'=>'1.0',
            'oauth_signature'=>'rb59fNtXW5aREe4nPK1KVAzFCVw=',
            'user_id'=>'14312',
            'resource_link_id'=>'ZYXWVUT9'
        );

        $secret = "I hear / the secrets that you keep / when you're talking in your sleep";
        // Use ToolProvider
        $provider = new \LTI1\ToolProvider($params['oauth_consumer_key'], $secret, $params);
        $this->assertEquals($params['oauth_signature'], $provider->generateSignature('POST', 'http://example.com/resource'));
    }
}
