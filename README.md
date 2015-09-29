# IMS LTI 1.x for PHP

Intended as a simple, generic LTI 1.x library for PHP 5.3+.

This library was based on IMS Global sample code from http://developers.imsglobal.org/phpcode.html and is used in [Talis Aspire Reading Lists](http://www.talis.com) to provide the LTI Tool Provider.

Usage:

```php

    $consumerKey = $_POST['oauth_consumer_key'];

    $nonceStore = new \LTI1\MemoryNonceStore($consumerKey); // You wouldn't actually want to use this, I don't think

    $lti = new \LTI1\ToolProvider($consumerKey, 'shared secret', $_POST, $nonceStore);

    try
    {
        // You probably want a more reliable way to get the requested fully qualified URL, as well
        $lti->validateRequest($_SERVER['REQUEST_METHOD'], 'http'.($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HOSTNAME'] . $_SERVER['REQUEST_URI']);

        error_log($lti->getUserKey() . ' is requesting a new resource ' . $lti->getResourceKey() . ' for course ' . $lti->getCourseKey());

        // Reroute user to whatever you need to do here as part of your LTI Tool

    } catch(\LTI1\RequestValidationException $e)
    {
        echo("Invalid credentials/request: " . $e->message);
    }
```

## Build

Install using composer.

```
    php composer.phar install
```

Run Tests using:

```
    ./vendor/bin/phpunit test
```


## TODO

* Tool consumer class
* Handle OAuth properties passed as HTTP headers
* Deal with 1.1 message (gradebook) returns
* Handle other signature methods than HMAC_SHA1


