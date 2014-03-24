# IMS LTI 1.x for PHP

Intended as a simple, generic LTI 1.x library for PHP 5.3+.

Usage:

    $consumerKey = $_POST['oauth_consumer_key'];

    $nonceStore = new \LTI1\MemoryNonceStore($consumerKey); // You wouldn't actually want to use this, I don't think

    $lti = new \LTI1\ToolProvider($consumerKey, 'shared secret', $_POST, $nonceStore);

    try
    {
        // You probably want a more reliable way to get the requested fully qualified URL, as well
        $lti->validateRequest($_SERVER['REQUEST_METHOD'], 'http'.($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HOSTNAME'] . $_SERVER['REQUEST_URI']);

        error_log($lti->getUserKey() . ' is requesting a new resource ' . $lti->getResourceKey() . ' for course ' . $lti->getCourseKey());

        // Reroute user to whatever you need to do here
    } catch(\LTI1\RequestValidationException $e)
    {
        echo("Invalid credentials/request: " . $e->message);
    }

## TODO

* Tool consumer
* Handle OAuth properties passed as HTTP headers
* Deal with 1.1 message (gradebook) returns



