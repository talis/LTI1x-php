<?php

namespace LTI1;

class ToolProvider {
    protected $nonceStore;
    protected $customParams = array();
    protected $extParams = array();
    protected $nonSpecParams = array();

    protected $contextParams = array();
    protected $launchPresentationParams = array();
    protected $lisCourseSectionParams = array();
    protected $lisCourseOfferingParams = array();
    protected $lisOutcomeParams = array();
    protected $lisPersonParams = array();
    protected $lisResultParams = array();
    protected $ltiParams = array();
    protected $oauthParams = array();
    protected $resourceLinkParams = array();
    protected $roles = array();
    protected $toolConsumerInfoParams = array();
    protected $toolConsumerInstanceParams = array();
    protected $userParams = array();

    protected $allParams = array();

    public function __construct($consumerKey, $consumerSecret, array $params = array(), $nonceStore = null)
    {
        $this->allParams = $params;
        $this->processParams($params);
        if(!isset($this->oauthParams['consumer_key']))
        {
            throw new \InvalidArgumentException('No consumerKey sent!');
        }
        $this->consumerKey = $consumerKey;
        if(empty($consumerSecret))
        {
            throw new \InvalidArgumentException('No consumerSecret sent!');
        }
        $this->consumerSecret = $consumerSecret;


        if(empty($nonceStore))
        {
            $nonceStore = new MemoryNonceStore($consumerKey);
        }
        /** @var NonceStore nonceStore */
        $this->nonceStore = $nonceStore;
    }

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
     * @return mixed
     */
    public function getOauthParams($key = null)
    {
        if($key)
        {
            if(isset($this->oauthParams[$key]))
            {
                return $this->oauthParams[$key];
            }
        } else {
            return $this->oauthParams;
        }
    }

    /**
     * @return mixed
     */
    public function getContextParams($key = null)
    {
        if($key)
        {
            if(isset($this->contextParams[$key]))
            {
                return $this->contextParams[$key];
            }
        } else {
            return $this->contextParams;
        }
    }

    /**
     * @return mixed
     */
    public function getCustomParams($key = null)
    {
        if($key)
        {
            if(isset($this->customParams[$key]))
            {
                return $this->customParams[$key];
            }
        } else {
            return $this->customParams;
        }
    }

    /**
     * @return mixed
     */
    public function getExtParams($key = null)
    {
        if($key)
        {
            if(isset($this->extParams[$key]))
            {
                return $this->extParams[$key];
            }
        } else {
            return $this->extParams;
        }
    }

    /**
     * @return mixed
     */
    public function getLaunchPresentationParams($key = null)
    {
        if($key)
        {
            if(isset($this->launchPresentationParams[$key]))
            {
                return $this->launchPresentationParams[$key];
            }
        } else {
            return $this->launchPresentationParams;
        }
    }

    /**
     * @return mixed
     */
    public function getListCourseOfferingParams($key = null)
    {
        if($key)
        {
            if(isset($this->lisCourseOfferingParams[$key]))
            {
                return $this->lisCourseOfferingParams[$key];
            }
        } else {
            return $this->lisCourseOfferingParams;
        }
    }

    /**
     * @return mixed
     */
    public function getLisCourseSectionParams($key = null)
    {
        if($key)
        {
            if(isset($this->lisCourseSectionParams[$key]))
            {
                return $this->lisCourseSectionParams[$key];
            }
        } else {
            return $this->lisCourseSectionParams;
        }
    }

    /**
     * @return mixed
     */
    public function getLisOutcomeParams($key = null)
    {
        if($key)
        {
            if(isset($this->lisOutcomeParams[$key]))
            {
                return $this->lisOutcomeParams[$key];
            }
        } else {
            return $this->lisOutcomeParams;
        }
    }

    /**
     * @return mixed
     */
    public function getLisPersonParams($key = null)
    {
        if($key)
        {
            if(isset($this->lisPersonParams[$key]))
            {
                return $this->lisPersonParams[$key];
            }
        } else {
            return $this->lisPersonParams;
        }
    }

    /**
     * @return mixed
     */
    public function getLisResultsParams($key = null)
    {
        if($key)
        {
            if(isset($this->lisResultParams[$key]))
            {
                return $this->lisResultParams[$key];
            }
        } else {
            return $this->lisResultParams;
        }
    }

    /**
     * @return mixed
     */
    public function getLtiParams($key = null)
    {
        if($key)
        {
            if(isset($this->ltiParams[$key]))
            {
                return $this->ltiParams[$key];
            }
        } else {
            return $this->ltiParams;
        }
    }

    /**
     * @return mixed
     */
    public function getNonSpecParams($key = null)
    {
        if($key)
        {
            if(isset($this->nonSpecParams[$key]))
            {
                return $this->nonSpecParams[$key];
            }
        } else {
            return $this->nonSpecParams;
        }
    }

    /**
     * @return mixed
     */
    public function getResourceLinkParams($key = null)
    {
        if($key)
        {
            if(isset($this->resourceLinkParams[$key]))
            {
                return $this->resourceLinkParams[$key];
            }
        } else {
            return $this->resourceLinkParams;
        }
    }

    /**
     * Generates the expected OAuth signature for the request
     * @param $method
     * @param $url
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generateSignature($method, $url)
    {
        if(!in_array(strtoupper($method), array('GET','POST', 'PUT', 'DELETE', 'HEAD', 'PATCH')))
        {
            throw new \InvalidArgumentException('Invalid method sent');
        }
        $params = $this->allParams;
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

        $baseString = $this->generateBaseString($method, $url, $pairs);
        // @todo handle other kinds of signature methods
        return base64_encode(hash_hmac('sha1', $baseString, self::urlencode_rfc3986($this->consumerSecret)."&", true));

    }

    public function generateBaseString($method, $url, $params)
    {
        $baseStringParts = self::urlencode_rfc3986(array(strtoupper($method), $url, implode("&", $params)));
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
     * @return string
     */
    function getUserFullName() {
        if(isset($this->lisPersonParams['name_full']) && !empty($this->lisPersonParams['name_full']))
        {
            return $this->lisPersonParams['name_full'];
        }
        $givenname = (isset($this->lisPersonParams['name_given']) ? $this->lisPersonParams['name_given'] : '');
        $familyname = (isset($this->lisPersonParams['name_family']) ? $this->lisPersonParams['name_family'] : '');
        if ( strlen($familyname) > 0 and strlen($givenname) > 0 ) return $givenname + $familyname;
        if ( strlen($givenname) > 0 ) return $givenname;
        if ( strlen($familyname) > 0 ) return $familyname;
        return $this->getUserEmail();
    }

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
     * @return mixed
     */
    function getConsumerKey() {
        return $this->getOauthParams('consumer_key');
    }

    /**
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
            return $this->contextParams['title'];
        }
        return false;
    }

    /**
     * PHP doesn't properly escape strings, so we have this
     * @param $input
     * @return array|mixed|string
     */
    public static function urlencode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('\classes\lti1\ToolProvider', 'urlencode_rfc3986'), $input);
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
}