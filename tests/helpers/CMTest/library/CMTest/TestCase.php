<?php

abstract class CMTest_TestCase extends PHPUnit_Framework_TestCase {

    public function runBare() {
        if (!isset(CM_Config::get()->CM_Site_Abstract->class)) {
            $siteDefault = $this->getMockSite(null, null, null, 'http://www.default.dev', 'http://cdn.default.dev', 'Default', 'default@default.dev');
            CM_Config::get()->CM_Site_Abstract->class = get_class($siteDefault);
        }
        parent::runBare();
    }

    public static function tearDownAfterClass() {
        CMTest_TH::clearEnv();
    }

    /**
     * @return CM_Form_Abstract
     */
    public function getMockForm() {
        $formMock = $this->getMockForAbstractClass('CM_Form_Abstract');
        $formMock->expects($this->any())->method('getName')->will($this->returnValue('formName'));
        return $formMock;
    }

    /**
     * @param string|null $classname
     * @param int|null    $type
     * @param array|null  $methods
     * @param string|null $url
     * @param string|null $urlCdn
     * @param string|null $name
     * @param string|null $emailAddress
     * @throws CM_Exception_Invalid
     * @return CM_Site_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockSite($classname = null, $type = null, array $methods = null, $url = null, $urlCdn = null, $name = null, $emailAddress = null) {
        if (null === $classname) {
            $classname = 'CM_Site_Abstract';
        }
        $classname = (string) $classname;
        $config = CM_Config::get();

        if (null === $type) {
            $type = $config->CM_Class_Abstract->typesMaxValue + 1;
        }
        $type = (int) $type;
        $types = $config->CM_Site_Abstract->types;
        if (isset($types[$type])) {
            throw new CM_Exception_Invalid('Site type ' . $type . ' already used');
        }
        $methods = (array) $methods;
        $url = is_null($url) ? null : (string) $url;
        $urlCdn = is_null($urlCdn) ? null : (string) $urlCdn;
        $name = is_null($name) ? null : (string) $name;
        $emailAddress = is_null($emailAddress) ? null : (string) $emailAddress;

        $site = $this->getMockForAbstractClass($classname, array(), $classname . '_Mock' . $type, true, true, true, $methods);

        $siteClassName = get_class($site);
        $config->CM_Site_Abstract->types[$type] = $siteClassName;
        $config->$siteClassName = new stdClass;
        $config->$siteClassName->type = $type;
        $config->$siteClassName->name = $name;
        $config->$siteClassName->url = $url;
        $config->$siteClassName->urlCdn = $urlCdn;
        $config->$siteClassName->emailAddress = $emailAddress;
        $config->$siteClassName->emailAddressSupport = $emailAddress;
        $config->$siteClassName->emailAddressComplaints = $emailAddress;
        $config->CM_Class_Abstract->typesMaxValue = $type;

        return $site;
    }

    /**
     * @param string             $methodName
     * @param string             $viewClassName
     * @param array|null         $params
     * @param CM_Model_User|null $viewer
     * @param array|null         $viewParams
     * @param int|null           $siteId
     * @return CM_Response_View_Ajax
     */
    public function getResponseAjax($methodName, $viewClassName, array $params = null, CM_Model_User $viewer = null, array $viewParams = null, $siteId = null) {
        if (null === $viewParams) {
            $viewParams = array();
        }
        if (null === $params) {
            $params = array();
        }
        if (null === $siteId) {
            $siteId = 'null';
        }
        $session = new CM_Session();
        if ($viewer) {
            $session->setUser($viewer);
        }
        $headers = array('Cookie' => 'sessionId=' . $session->getId());
        unset($session); // Make sure session is stored persistently

        $viewArray = array('className' => $viewClassName, 'id' => 'mockViewId', 'params' => $viewParams);
        $body = CM_Params::encode(array('view' => $viewArray, 'method' => $methodName, 'params' => $params), true);
        $request = new CM_Request_Post('/ajax/' . $siteId, $headers, null, $body);

        $response = new CM_Response_View_Ajax($request);
        $response->process();
        return $response;
    }

    /**
     * @param string               $formClassName
     * @param string               $actionName
     * @param array                $data
     * @param string|null          $componentClassName Component that uses that form
     * @param CM_Model_User|null   $viewer
     * @param array|null           $componentParams
     * @param CM_Request_Post|null $request
     * @param int|null             $siteId
     * @param string|null          $languageAbbreviation
     * @return CM_Response_View_Form
     */
    public function getResponseForm($formClassName, $actionName, array $data, $componentClassName = null, CM_Model_User $viewer = null, array $componentParams = null, &$request = null, $siteId = null, $languageAbbreviation = null) {
        if (null === $componentParams) {
            $componentParams = array();
        }
        if (null === $siteId) {
            $siteId = 'null';
        }
        if (null !== $languageAbbreviation) {
            $languageAbbreviation .= '/';
        }
        $session = new CM_Session();
        if ($viewer) {
            $session->setUser($viewer);
        }
        $headers = array('Cookie' => 'sessionId=' . $session->getId());
        $server = array('remote_addr' => '1.2.3.4');
        unset($session); // Make sure session is stored persistently

        $formArray = array('className' => $formClassName, 'params' => array(), 'id' => 'mockFormId');
        $viewArray = array('className' => $componentClassName, 'params' => $componentParams, 'id' => 'mockFormComponentId');
        $body = CM_Params::encode(array('view' => $viewArray, 'form' => $formArray, 'actionName' => $actionName, 'data' => $data), true);
        $request = new CM_Request_Post('/form/' . $languageAbbreviation . $siteId, $headers, $server, $body);

        $response = new CM_Response_View_Form($request);
        $response->process();
        return $response;
    }

    /**
     * @param string             $pageClass
     * @param CM_Model_User|null $viewer OPTIONAL
     * @param array              $params OPTIONAL
     * @return CM_Page_Abstract
     */
    protected function _createPage($pageClass, CM_Model_User $viewer = null, $params = array()) {
        return new $pageClass(CM_Params::factory($params), $viewer);
    }

    /**
     * @param CM_Component_Abstract $component
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CMTest_TH_Html
     */
    protected function _renderComponent(CM_Component_Abstract $component, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        $render = new CM_Render($site, $viewer);
        $component->checkAccessible($render);
        $component->prepare();
        $componentHtml = $render->render($component);
        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $componentHtml . '</body></html>';
        return new CMTest_TH_Html($html);
    }

    /**
     * @param CM_Form_Abstract      $form
     * @param CM_FormField_Abstract $formField
     * @param string                $fieldName
     * @param array|null            $params
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CMTest_TH_Html
     */
    protected function _renderFormField(CM_Form_Abstract $form, CM_FormField_Abstract $formField, $fieldName, array $params = null, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        if (null === $params) {
            $params = array();
        }
        $formField->prepare($params);
        $render = new CM_Render($site, $viewer);
        $html = $render->render($formField, array('form' => $form, 'fieldName' => $fieldName));
        return new CMTest_TH_Html($html);
    }

    /**
     * @param CM_Page_Abstract      $page
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CMTest_TH_Html
     */
    protected function _renderPage(CM_Page_Abstract $page, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        if (null === $site) {
            $site = CM_Site_Abstract::factory();
        }
        $host = parse_url($site->getUrl(), PHP_URL_HOST);
        $request = new CM_Request_Get('?' . http_build_query($page->getParams()->getAllOriginal()), array('host' => $host), null, $viewer);
        $response = new CM_Response_Page($request);
        $render = new CM_Render($site, $viewer);
        $page->prepareResponse($response);
        $page->checkAccessible($render);
        $page->prepare();
        $html = $render->render($page);
        return new CMTest_TH_Html($html);
    }

    /**
     * @param CM_Response_View_Ajax $response
     * @param array|null            $data
     */
    public static function assertAjaxResponseSuccess(CM_Response_View_Ajax $response, array $data = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertArrayHasKey('success', $responseContent, 'AjaxCall not successful');
        if (null !== $data) {
            self::assertSame($data, $responseContent['success']['data']);
        }
    }

    /**
     *
     * @param array $needles
     * @param array $haystacks
     */
    public static function assertArrayContains(array $needles, array $haystacks) {
        if (count($haystacks) < count($needles)) {
            self::fail('not enough elements to compare each');
        }
        for ($i = 0; $i < count($needles); $i++) {
            self::assertContains($needles[$i], $haystacks[$i]);
        }
    }

    /**
     * @param CM_Component_Abstract $cmp
     * @param CM_Render|null        $render
     */
    public static function assertComponentAccessible(CM_Component_Abstract $cmp, CM_Render $render = null) {
        if (null === $render) {
            $render = new CM_Render();
        }
        try {
            $cmp->checkAccessible($render);
            self::assertTrue(true);
        } catch (CM_Exception_AuthRequired $e) {
            self::fail('should be accessible');
        } catch (CM_Exception_Nonexistent $e) {
            self::fail('should be accessible');
        }
    }

    /**
     * @param CM_Component_Abstract $cmp
     * @param CM_Render|null        $render
     */
    public static function assertComponentNotAccessible(CM_Component_Abstract $cmp, CM_Render $render = null) {
        if (null === $render) {
            $render = new CM_Render();
        }
        try {
            $cmp->checkAccessible($render);
            self::fail('checkAccessible should throw exception');
        } catch (CM_Exception_AuthRequired $e) {
            self::assertTrue(true);
        } catch (CM_Exception_Nonexistent $e) {
            self::assertTrue(true);
        }
    }

    /**
     * @param CM_Component_Abstract $component
     * @param string|null           $expectedExceptionClass
     * @param CM_Model_User|null    $viewer
     */
    public function assertComponentNotRenderable(CM_Component_Abstract $component, $expectedExceptionClass = null, CM_Model_User $viewer = null) {
        if (null === $expectedExceptionClass) {
            $expectedExceptionClass = 'CM_Exception';
        }
        try {
            $this->_renderComponent($component, $viewer);
            $this->fail('Rendering page `' . get_class($component) . '` did not throw an exception');
        } catch (Exception $e) {
            $this->assertInstanceOf($expectedExceptionClass, $e);
        }
    }

    /**
     * @param mixed|CM_Comparable $needle
     * @param Traversable         $haystack
     * @param string              $message
     * @param boolean             $ignoreCase
     * @param boolean             $checkForObjectIdentity
     * @throws CM_Exception_Invalid
     */
    public static function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true) {
        if ($needle instanceof CM_Comparable) {
            if (!(is_array($haystack) || $haystack instanceof Traversable)) {
                throw new CM_Exception_Invalid('Haystack is not traversable.');
            }
            $match = false;
            foreach ($haystack as $hay) {
                if ($needle->equals($hay)) {
                    $match = true;
                    break;
                }
            }
            self::assertTrue($match, 'Needle not contained.');
        } else {
            parent::assertContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
        }
    }

    /**
     * @param mixed|CM_Comparable $needle
     * @param mixed|Traversable   $haystack
     * @param string              $message
     * @param boolean             $ignoreCase
     * @param boolean             $checkForObjectIdentity
     * @throws CM_Exception_Invalid
     */
    public static function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true) {
        if ($needle instanceof CM_Comparable) {
            if (!(is_array($haystack) || $haystack instanceof Traversable)) {
                throw new CM_Exception_Invalid('Haystack is not traversable.');
            }
            $match = false;
            foreach ($haystack as $hay) {
                if ($needle->equals($hay)) {
                    $match = true;
                    break;
                }
            }
            self::assertFalse($match, 'Needle contained.');
        } else {
            parent::assertNotContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity);
        }
    }

    /**
     * @param array $needles
     * @param mixed $haystack
     */
    public static function assertContainsAll(array $needles, $haystack) {
        foreach ($needles as $needle) {
            self::assertContains($needle, $haystack);
        }
    }

    /**
     * @param array $needles
     * @param mixed $haystack
     */
    public static function assertNotContainsAll(array $needles, $haystack) {
        foreach ($needles as $needle) {
            self::assertNotContains($needle, $haystack);
        }
    }

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = true) {
        if ($expected instanceof CM_Paging_Abstract) {
            $expected = $expected->getItems();
        }
        if ($actual instanceof CM_Paging_Abstract) {
            $actual = $actual->getItems();
        }
        if (is_array($expected) && is_array($actual)) {
            self::assertSame(array_keys($expected), array_keys($actual), $message);
            foreach ($expected as $expectedKey => $expectedValue) {
                self::assertEquals($expectedValue, $actual[$expectedKey], $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
            }
            return;
        }
        if ($expected instanceof CM_Comparable) {
            self::assertTrue($expected->equals($actual), 'Comparables differ');
        } else {
            parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
        }
    }

    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        try {
            self::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
        } catch (PHPUnit_Framework_AssertionFailedError $exception) {
            return;
        }
        self::fail($message);
    }

    /**
     * @param CM_Response_View_Form $response
     * @param string|null           $msg
     */
    public static function assertFormResponseSuccess(CM_Response_View_Form $response, $msg = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertFalse($response->hasErrors(), 'Response has errors.');
        if (null !== $msg) {
            $msg = (string) $msg;
            self::assertContains($msg, $responseContent['success']['messages'], 'Response has no message `' . $msg . '`.');
        }
    }

    /**
     * @param CM_Response_View_Form $response
     * @param string|null           $errorMsg
     * @param string|null           $formFieldName
     */
    public static function assertFormResponseError(CM_Response_View_Form $response, $errorMsg = null, $formFieldName = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertTrue($response->hasErrors());
        if (null !== $errorMsg) {
            $errorMsg = (string) $errorMsg;
            $error = $errorMsg;
            if (null !== $formFieldName) {
                $formFieldName = (string) $formFieldName;
                $error = array($errorMsg, $formFieldName);
            }
            self::assertContains($error, $responseContent['success']['errors']);
        }
    }

    /**
     * @param CMTest_TH_Html $html
     * @param string         $css
     */
    public static function assertHtmlExists(CMTest_TH_Html $html, $css) {
        self::assertTrue($html->exists($css), 'HTML does not contain `' . $css . '`.');
    }

    /**
     * @param CM_Page_Abstract $page
     */
    public static function assertPageViewable(CM_Page_Abstract $page) {
        self::assertTrue($page->isViewable());
    }

    /**
     * @param CM_Page_Abstract $page
     */
    public static function assertPageNotViewable(CM_Page_Abstract $page) {
        self::assertFalse($page->isViewable());
    }

    /**
     * @param CM_Page_Abstract   $page
     * @param string|null        $expectedExceptionClass
     * @param CM_Model_User|null $viewer
     */
    public function assertPageNotRenderable(CM_Page_Abstract $page, $expectedExceptionClass = null, CM_Model_User $viewer = null) {
        if (null === $expectedExceptionClass) {
            $expectedExceptionClass = 'CM_Exception';
        }
        try {
            $this->_renderPage($page, $viewer);
            $this->fail('Rendering page `' . get_class($page) . '` did not throw an exception');
        } catch (Exception $e) {
            $this->assertInstanceOf($expectedExceptionClass, $e);
        }
    }

    /**
     * @param string $table
     * @param array  $where WHERE conditions: ('attr' => 'value', 'attr2' => 'value')
     * @param int    $rowCount
     */
    public static function assertRow($table, $where = null, $rowCount = 1) {
        $result = CM_Db_Db::select($table, '*', $where);
        $rowCountActual = count($result->fetchAll());
        self::assertEquals($rowCount, $rowCountActual);
    }

    public static function assertNotRow($table, $columns) {
        self::assertRow($table, $columns, 0);
    }

    /**
     * @param number $expected
     * @param number $actual
     * @param number|null
     */
    public static function assertSameTime($expected, $actual, $delta = null) {
        if (null === $delta) {
            $delta = 1;
        }
        self::assertEquals($expected, $actual, '', $delta);
    }

    /**
     * @param CMTest_TH_Html $page
     * @param bool           $warnings
     */
    public static function assertTidy(CMTest_TH_Html $page, $warnings = true) {
        if (!extension_loaded('tidy')) {
            self::markTestSkipped('The tidy extension is not available.');
        }

        $html = $page->getHtml();
        $tidy = new tidy();

        $tidyConfig = array('show-errors' => 1, 'show-warnings' => $warnings);
        $tidy->parseString($html, $tidyConfig, 'UTF8');

        //$tidy->cleanRepair();
        $tidy->diagnose();
        $lines = array_reverse(explode("\n", $tidy->errorBuffer));
        $content = '';

        foreach ($lines as $line) {
            if (empty($line) || $line == 'No warnings or errors were found.' || strpos($line, 'Info:') === 0 ||
                strpos($line, 'errors were found!') > 0 || strpos($line, 'proprietary attribute') != false
            ) {
                // ignore
            } else {
                $content .= $line . PHP_EOL;
            }
        }

        self::assertEmpty($content, $content);
    }
}
