<?php

require_once 'TestBase.php';

class ToolProviderTest extends TestBase
{
    public function testConstructor()
    {
        $key = 'fooBar';
        $secret = 'My secret';
        $provider = $this->getMockBuilder('\LTI1\ToolProvider')
            ->setMethods(array('processParams', 'createMemoryNonceStore'))
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())->method('processParams');
        $provider->expects($this->once())->method('createMemoryNonceStore');

        $provider->__construct($key, $secret);

    }

    public function testConstructorBadConsumerKey()
    {
        $this->setExpectedException('\InvalidArgumentException', 'No consumerKey sent!');
        $provider = new \LTI1\ToolProvider('', 'Foo Bar');
    }

    public function testConstructorBadSharedSecret()
    {
        $this->setExpectedException('\InvalidArgumentException', 'No consumerSecret sent!');
        $provider = new \LTI1\ToolProvider('fooBar', '');
    }

    public function testParseGetOAuthParams()
    {
        $params = array(
            'oauth_consumer_key'=>'fooBar',
            'oauth_nonce'=>uniqid(),
            'oauth_timestamp'=>time(),
            'oauth_signature_method'=>'HMAC-SHA1'
        );

        $provider = new \LTI1\ToolProvider($params['oauth_consumer_key'], uniqid(), $params);
        $this->assertEquals('fooBar', $provider->getOauthParams('consumer_key'));
        $this->assertEquals($params['oauth_nonce'], $provider->getOauthParams('nonce'));
        $this->assertEquals($params['oauth_timestamp'], $provider->getOauthParams('timestamp'));
        $this->assertEquals('HMAC-SHA1', $provider->getOauthParams('signature_method'));
        $oauthParams = $provider->getOauthParams();
        $this->assertTrue(is_array($oauthParams));
        $this->assertArrayHasKey('consumer_key', $oauthParams);
        $this->assertArrayHasKey('nonce', $oauthParams);
        $this->assertArrayHasKey('timestamp', $oauthParams);
        $this->assertArrayHasKey('signature_method', $oauthParams);
        $this->assertEquals('fooBar', $oauthParams['consumer_key']);
        $this->assertEquals($params['oauth_nonce'], $oauthParams['nonce']);
        $this->assertEquals($params['oauth_timestamp'], $oauthParams['timestamp']);
        $this->assertEquals('HMAC-SHA1', $oauthParams['signature_method']);

        $this->assertEquals('fooBar', $provider->getParams('oauth_consumer_key'));
        $this->assertEquals($params['oauth_nonce'], $provider->getParams('oauth_nonce'));
        $this->assertEquals($params, $provider->getParams());

        $this->assertEquals('fooBar', $provider->getConsumerKey());
    }

    public function testParseGetContextParams()
    {
        $params = array(
            'context_id'=>'foo_1',
            'context_title'=>'A title',
            'context_label'=>'A label',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);        
        $this->assertEquals('foo_1', $provider->getContextParams('id'));
        $this->assertEquals('A title', $provider->getContextParams('title'));
        $this->assertEquals('A label', $provider->getContextParams('label'));        
        $contextParams = $provider->getContextParams();
        $this->assertTrue(is_array($contextParams));
        $this->assertArrayHasKey('id', $contextParams);
        $this->assertArrayHasKey('title', $contextParams);
        $this->assertArrayHasKey('label', $contextParams);
        $this->assertEquals('foo_1', $contextParams['id']);
        $this->assertEquals('A title', $contextParams['title']);
        $this->assertEquals('A label', $contextParams['label']);

        $this->assertEquals('foo_1', $provider->getParams('context_id'));
        $this->assertEquals('A title', $provider->getParams('context_title'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetCustomParams()
    {
        $params = array(
            'custom_review_chapter'=>'1.2.56',
            'custom_xstart'=>'$CourseSection.timeFrame.begin',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('1.2.56', $provider->getCustomParams('review_chapter'));
        $this->assertEquals('$CourseSection.timeFrame.begin', $provider->getCustomParams('xstart'));
        $customParams = $provider->getCustomParams();
        $this->assertTrue(is_array($customParams));
        $this->assertArrayHasKey('review_chapter', $customParams);
        $this->assertArrayHasKey('xstart', $customParams);
        $this->assertEquals('1.2.56', $customParams['review_chapter']);
        $this->assertEquals('$CourseSection.timeFrame.begin', $customParams['xstart']);

        $this->assertEquals('1.2.56', $provider->getParams('custom_review_chapter'));
        $this->assertEquals('$CourseSection.timeFrame.begin', $provider->getParams('custom_xstart'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetExtParams()
    {
        $params = array(
            'ext_content_return_types'=>'iframe,oembed',
            'ext_lms'=>'moodle-1',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('iframe,oembed', $provider->getExtParams('content_return_types'));
        $this->assertEquals('moodle-1', $provider->getExtParams('lms'));
        $extParams = $provider->getExtParams();
        $this->assertTrue(is_array($extParams));
        $this->assertArrayHasKey('content_return_types', $extParams);
        $this->assertArrayHasKey('lms', $extParams);
        $this->assertEquals('iframe,oembed', $extParams['content_return_types']);
        $this->assertEquals('moodle-1', $extParams['lms']);

        $this->assertEquals('iframe,oembed', $provider->getParams('ext_content_return_types'));
        $this->assertEquals('moodle-1', $provider->getParams('ext_lms'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetLaunchPresentationParams()
    {
        $params = array(
            'launch_presentation_locale'=>'en-US',
            'launch_presentation_width'=>'320',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('en-US', $provider->getLaunchPresentationParams('locale'));
        $this->assertEquals('320', $provider->getLaunchPresentationParams('width'));
        $launchPresentationParams = $provider->getLaunchPresentationParams();
        $this->assertTrue(is_array($launchPresentationParams));
        $this->assertArrayHasKey('locale', $launchPresentationParams);
        $this->assertArrayHasKey('width', $launchPresentationParams);
        $this->assertEquals('en-US', $launchPresentationParams['locale']);
        $this->assertEquals('320', $launchPresentationParams['width']);

        $this->assertEquals('en-US', $provider->getParams('launch_presentation_locale'));
        $this->assertEquals('320', $provider->getParams('launch_presentation_width'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetLisCourseOfferingParams()
    {
        $params = array(
            'lis_course_offering_sourceid'=>'school.edu:SI182-F08',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('school.edu:SI182-F08', $provider->getLisCourseOfferingParams('sourceid'));
        $lisCourseOfferingParams = $provider->getLisCourseOfferingParams();
        $this->assertTrue(is_array($lisCourseOfferingParams));
        $this->assertArrayHasKey('sourceid', $lisCourseOfferingParams);
        $this->assertEquals('school.edu:SI182-F08', $lisCourseOfferingParams['sourceid']);

        $this->assertEquals('school.edu:SI182-F08', $provider->getParams('lis_course_offering_sourceid'));
        $this->assertEquals($params, $provider->getParams());
    }


    public function testParseGetLisCourseSectionParams()
    {
        $params = array(
            'lis_course_section_sourceid'=>'school.edu:SI182-001-F08',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('school.edu:SI182-001-F08', $provider->getLisCourseSectionParams('sourceid'));
        $lisCourseSectionParams = $provider->getLisCourseSectionParams();
        $this->assertTrue(is_array($lisCourseSectionParams));
        $this->assertArrayHasKey('sourceid', $lisCourseSectionParams);
        $this->assertEquals('school.edu:SI182-001-F08', $lisCourseSectionParams['sourceid']);

        $this->assertEquals('school.edu:SI182-001-F08', $provider->getParams('lis_course_section_sourceid'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetLisOutcomeParams()
    {
        $params = array(
            'lis_outcome_service_url'=>'http://example.com/outcome',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('http://example.com/outcome', $provider->getLisOutcomeParams('service_url'));
        $lisOutcomeParams = $provider->getLisOutcomeParams();
        $this->assertTrue(is_array($lisOutcomeParams));
        $this->assertArrayHasKey('service_url', $lisOutcomeParams);
        $this->assertEquals('http://example.com/outcome', $lisOutcomeParams['service_url']);

        $this->assertEquals('http://example.com/outcome', $provider->getParams('lis_outcome_service_url'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetLisPersonParams()
    {
        $params = array(
            'lis_person_name_full'=>'Jane Q. Public',
            'lis_person_name_given'=>'Jane',
            'lis_person_name_family'=>'Public',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('Jane Q. Public', $provider->getLisPersonParams('name_full'));
        $this->assertEquals('Jane', $provider->getLisPersonParams('name_given'));
        $this->assertEquals('Public', $provider->getLisPersonParams('name_family'));
        $lisPersonParams = $provider->getLisPersonParams();
        $this->assertTrue(is_array($lisPersonParams));
        $this->assertArrayHasKey('name_full', $lisPersonParams);
        $this->assertArrayHasKey('name_given', $lisPersonParams);
        $this->assertArrayHasKey('name_family', $lisPersonParams);
        $this->assertEquals('Jane Q. Public', $lisPersonParams['name_full']);
        $this->assertEquals('Jane', $lisPersonParams['name_given']);
        $this->assertEquals('Public', $lisPersonParams['name_family']);

        $this->assertEquals('Jane Q. Public', $provider->getParams('lis_person_name_full'));
        $this->assertEquals('Jane', $provider->getParams('lis_person_name_given'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetLisResultParams()
    {
        $params = array(
            'lis_result_sourceid'=>'83873872987329873264783687634',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('83873872987329873264783687634', $provider->getLisResultParams('sourceid'));
        $lisResultParams = $provider->getLisResultParams();
        $this->assertTrue(is_array($lisResultParams));
        $this->assertArrayHasKey('sourceid', $lisResultParams);
        $this->assertEquals('83873872987329873264783687634', $lisResultParams['sourceid']);

        $this->assertEquals('83873872987329873264783687634', $provider->getParams('lis_result_sourceid'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetLtiParams()
    {
        $params = array(
            'lti_message_type'=>'basic-lti-launch-request',
            'lti_version'=>'LTI-1p0'
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('basic-lti-launch-request', $provider->getLtiParams('message_type'));
        $this->assertEquals('LTI-1p0', $provider->getLtiParams('version'));
        $ltiParams = $provider->getLtiParams();
        $this->assertTrue(is_array($ltiParams));
        $this->assertArrayHasKey('message_type', $ltiParams);
        $this->assertArrayHasKey('version', $ltiParams);
        $this->assertEquals('basic-lti-launch-request', $ltiParams['message_type']);
        $this->assertEquals('LTI-1p0', $ltiParams['version']);

        $this->assertEquals('basic-lti-launch-request', $provider->getParams('lti_message_type'));
        $this->assertEquals('LTI-1p0', $provider->getParams('lti_version'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testParseGetResourceLinkParams()
    {
        $params = array(
            'resource_link_id'=>'foo_1',
            'resource_link_title'=>'A title',
            'resource_link_description'=>'A description of thing',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('foo_1', $provider->getResourceLinkParams('id'));
        $this->assertEquals('A title', $provider->getResourceLinkParams('title'));
        $this->assertEquals('A description of thing', $provider->getResourceLinkParams('description'));
        $resourceLinkParams = $provider->getResourceLinkParams();
        $this->assertTrue(is_array($resourceLinkParams));
        $this->assertArrayHasKey('id', $resourceLinkParams);
        $this->assertArrayHasKey('title', $resourceLinkParams);
        $this->assertArrayHasKey('description', $resourceLinkParams);
        $this->assertEquals('foo_1', $resourceLinkParams['id']);
        $this->assertEquals('A title', $resourceLinkParams['title']);
        $this->assertEquals('A description of thing', $resourceLinkParams['description']);

        $this->assertEquals('foo_1', $provider->getParams('resource_link_id'));
        $this->assertEquals('A title', $provider->getParams('resource_link_title'));
        $this->assertEquals($params, $provider->getParams());
    }

    public function testRoles()
    {
        $roles = 'urn:lti:role:ims/lis/Instructor/Lecturer,urn:lti:role:ims/lis/Mentor/Advisor';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));

        $this->assertEquals(explode(',', strtolower($roles)), $provider->getRoles());
        $this->assertTrue($provider->isInstructor());

        $roles = 'urn:lti:role:ims/lis/Administrator/Developer';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));

        $this->assertEquals(array(strtolower($roles)), $provider->getRoles());
        $this->assertTrue($provider->isInstructor());

        $roles = 'urn:lti:role:ims/lis/Learner,urn:lti:role:ims/lis/Member/Member,urn:lti:role:ims/lis/Mentor/Tutor';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));
        $this->assertEquals(explode(',', strtolower($roles)), $provider->getRoles());
        $this->assertFalse($provider->isInstructor());

        $roles = 'urn:lti:role:ims/lis/TeachingAssistant';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));
        $this->assertEquals(array(strtolower($roles)), $provider->getRoles());
        $this->assertTrue($provider->isTeachingAssistant());

        $roles = 'urn:lti:role:ims/lis/CourseDesigner';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));
        $this->assertEquals(array(strtolower($roles)), $provider->getRoles());
        $this->assertTrue($provider->isCourseDesigner());

        $roles = 'urn:lti:role:ims/lis/Manager';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));
        $this->assertEquals(array(strtolower($roles)), $provider->getRoles());
        $this->assertTrue($provider->isManager());

        $roles = 'urn:lti:role:ims/lis/Mentor';
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('roles'=>$roles));
        $this->assertEquals(array(strtolower($roles)), $provider->getRoles());
        $this->assertTrue($provider->isMentor());


    }

    public function testUserDataGetters()
    {
        $user = array(
            'lis_person_name_full'=>'Jane Q. Public',
            'lis_person_name_given'=>'Jane',
            'lis_person_name_family'=>'Public',
            'lis_person_contact_email_primary'=>'jqpublic@school.edu'
        );
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $user);

        $this->assertEquals('jqpublic@school.edu', $provider->getUserEmail());
        $this->assertEquals('Jane', $provider->getUserShortName());
        $this->assertEquals('Jane Q. Public', $provider->getUserFullName());

        unset($user['lis_person_name_full']);
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $user);

        $this->assertEquals('Jane', $provider->getUserShortName());
        $this->assertEquals('Jane Public', $provider->getUserFullName());

        unset($user['lis_person_name_given']);
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $user);

        $this->assertEquals('Public', $provider->getUserShortName());
        $this->assertEquals('Public', $provider->getUserFullName());

        unset($user['lis_person_name_family']);
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $user);

        $this->assertEquals('jqpublic@school.edu', $provider->getUserShortName());
        $this->assertEquals('jqpublic@school.edu', $provider->getUserFullName());
    }

    public function testGetUniqueKeys()
    {
        $toolGuid = uniqid();
        $consumerKey = uniqid();
        $contextId = uniqid();
        $resourceLinkId = uniqid();
        $userId = uniqid();
        $params = array(
            'tool_consumer_instance_guid'=>$toolGuid,
            'oauth_consumer_key'=>$consumerKey,
            'context_id'=>$contextId,
            'resource_link_id'=>$resourceLinkId,
            'user_id'=>$userId

        );
        $provider = new \LTI1\ToolProvider($consumerKey, uniqid(), $params);

        $key = $toolGuid . ':' . $consumerKey . ':';
        $this->assertEquals($key . 'context_id:' . $contextId, $provider->getCourseKey());
        $this->assertEquals($key . 'resource_link_id:' . $resourceLinkId, $provider->getResourceKey());
        $this->assertEquals($key . 'user_id:' . $userId, $provider->getUserKey());

        unset($params['tool_consumer_instance_guid']);
        $provider = new \LTI1\ToolProvider($consumerKey, uniqid(), $params);

        $key = $consumerKey . ':';
        $this->assertEquals($key . 'context_id:' . $contextId, $provider->getCourseKey());
        $this->assertEquals($key . 'resource_link_id:' . $resourceLinkId, $provider->getResourceKey());
        $this->assertEquals($key . 'user_id:' . $userId, $provider->getUserKey());
        unset($params['oauth_consumer_key']);
        $provider = new \LTI1\ToolProvider($consumerKey, uniqid(), $params);
        $this->assertFalse($provider->getCourseKey());
        $this->assertFalse($provider->getResourceKey());
        $this->assertFalse($provider->getUserKey());

        $params['tool_consumer_instance_guid'] = $toolGuid;
        $key = $toolGuid . ':';
        $provider = new \LTI1\ToolProvider($consumerKey, uniqid(), $params);
        $this->assertEquals($key . 'context_id:' . $contextId, $provider->getCourseKey());
        $this->assertEquals($key . 'resource_link_id:' . $resourceLinkId, $provider->getResourceKey());
        $this->assertEquals($key . 'user_id:' . $userId, $provider->getUserKey());
    }

    public function testGetUserImage()
    {
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('user_image'=>'http://example.org/1234'));
        $this->assertEquals('http://example.org/1234', $provider->getUserImage());

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('lis_person_contact_email_primary'=>'jqpublic@school.edu'));
        $this->assertEquals("//www.gravatar.com/avatar.php?gravatar_id=".md5( strtolower('jqpublic@school.edu') )."&size=40", $provider->getUserImage());

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid());
        $this->assertFalse($provider->getUserImage());
    }

    public function testGetResourceTitle()
    {
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid());
        $this->assertFalse($provider->getResourceTitle());

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), array('resource_link_title'=>'A title of a resource'));
        $this->assertEquals('A title of a resource', $provider->getResourceTitle());
    }

    public function testGetCourseName()
    {
        $params = array(
            'context_id'=>'foo_1',
            'context_title'=>'A title',
            'context_label'=>'A label',
        );

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('A title', $provider->getCourseName());

        unset($params['context_title']);
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('A label', $provider->getCourseName());

        unset($params['context_label']);
        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(), $params);
        $this->assertEquals('foo_1', $provider->getCourseName());

        $provider = new \LTI1\ToolProvider(uniqid(), uniqid(),array());
        $this->assertFalse($provider->getCourseName());
    }
}