<?php

/**
 * This class is intended to provide the functionality necessary for an LTI "tool provider".  From an LTI launch request,
 * it will parse the passed parameters and validate the OAuth credentials.  Convenience methods are provided to get at
 * specific aspects of the LTI launch request.
 */
namespace LTI1;

class ToolProvider {
    /**
     * This property should implement the iNonceStore interface, will default to MemoryNonceStore
     * @var iNonceStore
     */
    protected $nonceStore;

    /**
     * Custom LTI parameters
     * @var array
     */
    protected $customParams = array();

    /**
     * LTI 'ext_' parameters
     * @var array
     */
    protected $extParams = array();

    /**
     * LTI 'context_' (e.g. 'course') parameters
     * @var array
     */
    protected $contextParams = array();

    /**
     * LTI 'launch_presentation_' parameters
     * @var array
     */
    protected $launchPresentationParams = array();

    /**
     * LTI LIS course section parameters
     * @var array
     */
    protected $lisCourseSectionParams = array();

    /**
     * LTI LIS course parameters
     * @var array
     */
    protected $lisCourseOfferingParams = array();

    /**
     * LTI LIS outcome parameters
     * @var array
     */
    protected $lisOutcomeParams = array();

    /**
     * LTI LIS person parameters
     * @var array
     */
    protected $lisPersonParams = array();

    /**
     * LTI lis_result_ (e.g. sourceid) parameters
     * @var array
     */
    protected $lisResultParams = array();

    /**
     * LTI basic (e.g. "message_type", "version") parameters
     * @var array
     */
    protected $ltiParams = array();

    /**
     * OAuth parameters
     * @var array
     */
    protected $oauthParams = array();

    /**
     * LTI resource_link_ (e.g. the actual LIS item) parameters
     * @var array
     */
    protected $resourceLinkParams = array();

    /**
     * The user's roles in context of this specific request
     * @var array
     */
    protected $roles = array();

    /**
     * LTI tool_consumer_info_ parameters
     * @var array
     */
    protected $toolConsumerInfoParams = array();

    /**
     * LTI tool_consumer_instance_ parameters
     * @var array
     */
    protected $toolConsumerInstanceParams = array();

    /**
     * LTI user_ parameters
     * @var array
     */
    protected $userParams = array();

    /**
     * All of the parameters passed in the request (LTI and otherwise)
     * @var array
     */
    protected $allParams = array();

    /**
     * Create a new LTI ToolProvider object
     *
     * @param mixed $consumerKey The identifier the tool consumer uses to access the tool provider's resources
     * @param mixed $consumerSecret The shared secret between the consumer and provider
     * @param array $params The request parameters
     * @param null|iNonceStore $nonceStore The object which handles OAuth nonce management, will default to MemoryNonceStore
     * @throws \InvalidArgumentException
     */
    public function __construct($consumerKey, $consumerSecret, array $params = array(), $nonceStore = null)
    {
        if(empty($consumerKey))
        {
            throw new \InvalidArgumentException('No consumerKey sent!');
        }
        $this->consumerKey = $consumerKey;

        if(empty($consumerSecret))
        {
            throw new \InvalidArgumentException('No consumerSecret sent!');
        }
        $this->consumerSecret = $consumerSecret;

        // Set the (very simple) MemoryNonceStore to handle nonce management if nothing is sent
        if(empty($nonceStore))
        {
            $nonceStore = $this->createMemoryNonceStore();
        } elseif(!$nonceStore instanceof iNonceStore)
        {
            throw new \InvalidArgumentException('Nonce store object must implement iNonceStore!');
        }
        /** @var iNonceStore nonceStore */
        $this->nonceStore = $nonceStore;

        // Save our params in their original form
        $this->allParams = $params;

        // Parse and sort the parameters into their respective attributes
        $this->processParams($params);
    }

    /**
     * Validate the OAuth signature and nonce; throws a RequestValidationException if invalid or returns true
     * @param string $method HTTP request method
     * @param string $requestUrl The scheme://host/path of the request
     * @return bool
     * @throws RequestValidationException
     */
    public function validateRequest($method, $requestUrl)
    {
        if($this->generateSignature($method, $requestUrl) !== $this->oauthParams['signature'])
        {
            throw new RequestValidationException("Invalid signature sent");
        }
        // This will throw an error if the nonce is invalid.
        $this->nonceStore->checkNonce($this->oauthParams['nonce'], $this->oauthParams['timestamp']);
        return true;
    }

    /**
     * Parse the request params/headers and add them to the object
     *
     * @param array $params
     */
    protected function processParams(array $params)
    {
        foreach($params as $key=>$value)
        {
            if(strpos($key, 'oauth_') === 0)
            {
                $this->oauthParams[str_replace('oauth_', '', $key)] = $value;
            } elseif(strpos($key, 'lti_') === 0)
            {
                $this->ltiParams[str_replace('lti_', '', $key)] = $value;
            } elseif(strpos($key, 'context_') === 0)
            {
                $this->contextParams[str_replace('context_', '', $key)] = $value;
            } elseif(strpos($key, 'launch_presentation_') === 0)
            {
                $this->launchPresentationParams[str_replace('launch_presentation_', '', $key)] = $value;
            }
            elseif(strpos($key, 'lis_course_section_') === 0)
            {
                $this->lisCourseSectionParams[str_replace('lis_course_section_', '', $key)] = $value;
            }elseif(strpos($key, 'lis_course_offering_') === 0)
            {
                $this->lisCourseOfferingParams[str_replace('lis_course_offering_', '', $key)] = $value;
            } elseif(strpos($key, 'lis_outcome_') === 0)
            {
                $this->lisOutcomeParams[str_replace('lis_outcome_', '', $key)] = $value;
            } elseif(strpos($key, 'lis_person_') === 0)
            {
                $this->lisPersonParams[str_replace('lis_person_', '', $key)] = $value;
            } elseif(strpos($key, 'lis_result_') === 0)
            {
                $this->lisResultParams[str_replace('lis_result_', '', $key)] = $value;
            } elseif(strpos($key, 'resource_link_') === 0)
            {
                $this->resourceLinkParams[str_replace('resource_link_', '', $key)] = $value;
            } elseif(strpos($key, 'tool_consumer_info_') === 0)
            {
                $this->toolConsumerInfoParams[str_replace('tool_consumer_info_', '', $key)] = $value;
            } elseif(strpos($key, 'tool_consumer_instance_') === 0)
            {
                $this->toolConsumerInstanceParams[str_replace('tool_consumer_instance_', '', $key)] = $value;
            } elseif(strpos($key, 'user_') === 0)
            {
                $this->userParams[str_replace('user_', '', $key)] = $value;
            } elseif(strpos($key, 'ext_') === 0)
            {
                $this->extParams[str_replace('ext_', '', $key)] = $value;
            } elseif(strpos($key, 'custom_') === 0)
            {
                $this->customParams[str_replace('custom_', '', $key)] = $value;
            } elseif($key === 'roles')
            {
                if(is_array($value))
                {
                    $this->roles = array_map('strtolower', $value);
                } else {
                    $this->roles = explode(",", strtolower($value));
                }
            }
        }
    }

    /**
     * Generalized getter for the various param attributes
     * @param string $prefix
     * @param null $key
     * @return mixed
     */
    private function getParsedParams($prefix, $key = null)
    {
        $attributeName = $prefix . 'Params';
        if($key)
        {
            if(isset($this->$prefix[$key]))
            {
                return $this->$prefix[$key];
            }
        } else {
            return $this->$prefix;
        }
    }

    /**
     * Getter for the allParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key
     * @return array
     */
    public function getParams($key = null)
    {
        if($key)
        {
            if(isset($this->allParams[$key]))
            {
                return $this->allParams[$key];
            }
        } else {
            return $this->allParams;
        }
    }

    /**
     * Getter for the oauthParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getOauthParams($key = null)
    {
        return $this->getParsedParams('oauth');
    }

    /**
     * Getter for the contextParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getContextParams($key = null)
    {
        return $this->getParsedParams('context');
    }

    /**
     * Getter for the customParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getCustomParams($key = null)
    {
        return $this->getParsedParams('custom');
    }

    /**
     * Getter for the extParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getExtParams($key = null)
    {
        return $this->getParsedParams('ext');
    }

    /**
     * Getter for the launchPresentationParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getLaunchPresentationParams($key = null)
    {
        return $this->getParsedParams('launchPresentation');
    }

    /**
     * Getter for the lisCourseOfferingParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getLisCourseOfferingParams($key = null)
    {
        return $this->getParsedParams('lisCourseOffering');
    }

    /**
     * Getter for the lisCourseSectionParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getLisCourseSectionParams($key = null)
    {
        return $this->getParsedParams('lisCourseSection');
    }

    /**
     * Getter for the lisOutcomeParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getLisOutcomeParams($key = null)
    {
        return $this->getParsedParams('lisOutcome');
    }

    /**
     * @return mixed
     */
    public function getLisPersonParams($key = null)
    {
        return $this->getParsedParams('lisPerson');
    }

    /**
     * Getter for the lisResultsParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getLisResultsParams($key = null)
    {
        return $this->getParsedParams('lisResult');
    }

    /**
     * Getter for the ltiParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getLtiParams($key = null)
    {
        return $this->getParsedParams('lti');
    }

    /**
     * Getter for the resourceLinkParams attribute: returns a specific value (or null) if a key is passed, or an associate array of all key/values
     * @param string|null $key An optional key to return a specific parameter value
     * @return mixed
     */
    public function getResourceLinkParams($key = null)
    {
        return $this->getParsedParams('resourceLink');
    }

    /**
     * Generates the expected OAuth signature for the request
     * @param string $method The HTTP method of the request
     * @param string $url The fully qualified request URL
     * @return string The signed string
     * @throws \InvalidArgumentException
     */
    public function generateSignature($method, $url)
    {
        if(!in_array(strtoupper($method), array('GET','POST', 'PUT', 'DELETE', 'HEAD', 'PATCH')))
        {
            throw new \InvalidArgumentException('Invalid method sent');
        }

        $baseString = $this->generateBaseString($method, $url, $this->allParams);
        // @todo handle other kinds of signature methods
        return base64_encode(hash_hmac('sha1', $baseString, self::urlencode_rfc3986($this->consumerSecret)."&", true));

    }

    /**
     * Generates the base string to be signed
     *
     * @param string $method The HTTP method of the request
     * @param string $url The fully qualified request URL
     * @param array $params An array of the request parameters
     * @return string
     */
    public function generateBaseString($method, $url, array $params)
    {
        unset($params['oauth_signature']);
        $keys = self::urlencode_rfc3986(array_keys($params));
        $values = self::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                natsort($value);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        $baseStringParts = self::urlencode_rfc3986(array(strtoupper($method), $url, implode("&", $pairs)));
        return implode('&', $baseStringParts);
    }

    /**
     * Returns whether or not the requester is an instructor
     *
     * @return bool
     */
    function isInstructor() {
        foreach($this->roles as $role)
        {
            if ( ! ( strpos($role,"instructor") === false ) ) return true;
            if ( ! ( strpos($role,"administrator") === false ) ) return true;
        }
        return false;
    }

    /**
     * Returns the user's email address from the request params (if present)
     *
     * @return string|bool
     */
    function getUserEmail() {
        if(isset($this->lisPersonParams['contact_email_primary']) && !empty($this->lisPersonParams['contact_email_primary']))
        {
            return $this->lisPersonParams['contact_email_primary'];
        }

        # Sakai Hack
        if(isset($this->lisPersonParams['contact_emailprimary']) && !empty($this->lisPersonParams['contact_emailprimary']))
        {
            return $this->lisPersonParams['contact_emailprimary'];
        }

        return false;
    }

    /**
     * Returns (in the following order, until it finds a value) lis_person_name_given, lis_person_name_family, lis_person_name_full, or lis_person_contact_email_primary
     * @return string
     */
    function getUserShortName() {
        $email = $this->getUserEmail();
        if(!empty($email))
        {
            return $email;
        }
        if(isset($this->lisPersonParams['name_given']) && !empty($this->lisPersonParams['name_given']))
        {
            return $this->lisPersonParams['name_given'];
        }
        if(isset($this->lisPersonParams['name_family']) && !empty($this->lisPersonParams['name_family']))
        {
            return $this->lisPersonParams['name_family'];
        }
        return $this->getUserFullName();
    }

    /**
     * Returns (in the following order, until it finds a value) lis_person_name_full, lis_person_name_given + lis_person_name_family, lis_person_name_given, lis_person_name_family, or lis_person_contact_email_primary
     * @return string
     */
    function getUserFullName() {
        if(isset($this->lisPersonParams['name_full']) && !empty($this->lisPersonParams['name_full']))
        {
            return $this->lisPersonParams['name_full'];
        }
        $givenname = (isset($this->lisPersonParams['name_given']) ? $this->lisPersonParams['name_given'] : '');
        $familyname = (isset($this->lisPersonParams['name_family']) ? $this->lisPersonParams['name_family'] : '');
        if ( strlen($familyname) > 0 and strlen($givenname) > 0 ) return $givenname . ' ' . $familyname;
        if ( strlen($givenname) > 0 ) return $givenname;
        if ( strlen($familyname) > 0 ) return $familyname;
        return $this->getUserEmail();
    }

    /**
     * Returns an opaque ID representing the user.
     * Generated using [{tool_consumer_instance_guid}:][{oauth_consumer_key}:]user_id:{user_id}
     * The user_id and either tool_consumer_instance_guid or oauth_consumer must exist, or it returns false
     *
     * @return bool|string
     */
    function getUserKey() {
        $key = array();
        if(isset($this->userParams['id']) && !empty($this->userParams['id']))
        {
            if(isset($this->toolConsumerInstanceParams['guid']) && !empty($this->toolConsumerInstanceParams['guid']))
            {
                $key[] = $this->toolConsumerInstanceParams['guid'];
            }
            if(isset($this->oauthParams['consumer_key']) && !empty($this->oauthParams['consumer_key']))
            {
                $key[] = $this->oauthParams['consumer_key'];
            }
            $key[] = 'user_id:' . $this->userParams['id'];
            if(count($key) > 1)
            {
                return implode(":", $key);
            }
        }
        return false;
    }

    /**
     * Constructs a gravatar URL for the user email, if user_image param isn't sent
     * @return bool|string
     */
    function getUserImage() {
        if(isset($this->userParams['image']) && !empty($this->userParams['image']))
        {
            return $this->userParams['image'];
        }

        $email = $this->getUserEmail();
        if ( $email === false ) return false;
        $size = 40;
        $grav_url = "://www.gravatar.com/avatar.php?gravatar_id=".md5( strtolower($email) )."&size=".$size;
        return $grav_url;
    }

    /**
     * Returns an opaque ID representing the resource where the launch request originated.
     * Generated using [{tool_consumer_instance_guid}:][{oauth_consumer_key}:]resource_link_id:{resource_link_id}
     * The resource_link_id and either tool_consumer_instance_guid or oauth_consumer must exist, or it returns false
     *
     * @return bool|string
     */
    function getResourceKey() {
        $key = array();
        if(isset($this->resourceLinkParams['id']) && !empty($this->resourceLinkParams['id']))
        {
            if(isset($this->toolConsumerInstanceParams['guid']) && !empty($this->toolConsumerInstanceParams['guid']))
            {
                $key[] = $this->toolConsumerInstanceParams['guid'];
            }
            if(isset($this->oauthParams['consumer_key']) && !empty($this->oauthParams['consumer_key']))
            {
                $key[] = $this->oauthParams['consumer_key'];
            }
            $key[] = 'resource_link_id:' . $this->resourceLinkParams['id'];
            if(count($key) > 1)
            {
                return implode(":", $key);
            }
        }
        return false;
    }

    /**
     * Convenience method to get at the resource_link_title property
     *
     * @return bool|string
     */
    function getResourceTitle() {
        if(isset($this->resourceLinkParams['title']) && !empty($this->resourceLinkParams['title']))
        {
            return $this->resourceLinkParams['title'];
        }
        return false;
    }

    /**
     * Convenience method to get at the oauth_consumer_key property
     * @return mixed
     */
    function getConsumerKey() {
        return $this->getOauthParams('consumer_key');
    }

    /**
     * Returns an opaque ID representing the context (generally 'course').
     * Generated using [{tool_consumer_instance_guid}:][{oauth_consumer_key}:]context_id:{context_id}
     * The context_id and either tool_consumer_instance_guid or oauth_consumer must exist, or it returns false*
     * @return bool|string
     */
    function getCourseKey() {
        $key = array();
        if(isset($this->contextParams['id']) && !empty($this->contextParams['id']))
        {
            if(isset($this->toolConsumerInstanceParams['guid']) && !empty($this->toolConsumerInstanceParams['guid']))
            {
                $key[] = $this->toolConsumerInstanceParams['guid'];
            }
            if(isset($this->oauthParams['consumer_key']) && !empty($this->oauthParams['consumer_key']))
            {
                $key[] = $this->oauthParams['consumer_key'];
            }
            $key[] = 'context_id:' . $this->contextParams['id'];
            if(count($key) > 1)
            {
                return implode(":", $key);
            }
        }
        return false;
    }

    /**
     * A convenience method to return a 'course' (context) name
     * Returns (in the following order, until it finds a value) context_title, context_label, context_id, or false
     * @return bool|string
     */
    function getCourseName() {
        if(isset($this->contextParams['title']) && !empty($this->contextParams['title']))
        {
            return $this->contextParams['title'];
        }
        if(isset($this->contextParams['label']) && !empty($this->contextParams['label']))
        {
            return $this->contextParams['label'];
        }
        if(isset($this->contextParams['id']) && !empty($this->contextParams['id']))
        {
            return $this->contextParams['id'];
        }
        return false;
    }

    /**
     * PHP doesn't properly escape strings, so we have this
     * @param string|array $input Input can be a string or an array of strings
     * @return array|mixed|string
     */
    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('\LTI1\ToolProvider', 'urlencode_rfc3986'), $input);
        } else if (is_scalar($input)) {
            return str_replace(
                '+',
                ' ',
                str_replace('%7E', '~', rawurlencode($input))
            );
        } else {
            return '';
        }
    }

    /**
     * For mocking
     * @return MemoryNonceStore
     */
    protected function createMemoryNonceStore()
    {
        return new \LTI1\MemoryNonceStore($this->consumerKey);
    }

}