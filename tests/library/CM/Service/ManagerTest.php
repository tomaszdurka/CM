<?php

class CM_Service_ManagerTest extends PHPUnit_Framework_TestCase {
    use CM_ExceptionHandling_CatcherTrait;

    public function testHas() {
        $serviceManager = new CM_Service_Manager();
        $this->assertFalse($serviceManager->has('foo'));
        $this->assertTrue($serviceManager->has(CM_Service_Manager::class));
        
        $serviceManager->register('foo', 'DummyService', array('foo' => 'bar'));
        $serviceManager->registerInstance('bar', 'my-service');
  
        $this->assertTrue($serviceManager->has('foo'));
        $this->assertTrue($serviceManager->has('bar'));
    }

    public function testGet() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertInstanceOf('DummyService', $service);
    }

    public function testGetAssertInstanceOf() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService', 'DummyService');
        $this->assertInstanceOf('DummyService', $service);
    }

    public function testGetWithMethod() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'), 'getArray', array('key' => 'foo', 'value' => 1234));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertSame(array('foo' => 1234), $serviceManager->get('DummyService'));
    }

    public function testGetAssertInstanceOfInvalid() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'));

        $exception = $this->catchException(function () use ($serviceManager) {
            $serviceManager->get('DummyService', 'SomethingElse');
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Service has an invalid class.', $exception->getMessage());
        $this->assertSame(
            [
                'service'           => 'DummyService',
                'actualClassName'   => 'DummyService',
                'expectedClassName' => 'SomethingElse',
            ],
            $exception->getMetaInfo()
        );
    }

    public function testServiceMethod() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'));

        /** @var DummyService $service */
        $service = $serviceManager->get('DummyService');
        $this->assertSame('bar', $service->getFoo());
    }

    public function testInstanceCaching() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'));

        $service1 = $serviceManager->get('DummyService');
        $service2 = $serviceManager->get('DummyService');
        $this->assertSame($service1, $service2);
    }

    public function testInvalidService() {
        $serviceManager = new CM_Service_Manager();

        $exception = $this->catchException(function () use ($serviceManager) {
            $serviceManager->get('InvalidService');
        });

        $this->assertInstanceOf(\DI\NotFoundException::class, $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame("No entry or class found for 'InvalidService'", $exception->getMessage());
    }

    public function testMagicGet() {
        $serviceManager = new CM_Service_Manager();
        $serviceManager->register('DummyService', 'DummyService', array('foo' => 'bar'));

        $service1 = $serviceManager->getDummyService();
        $service2 = $serviceManager->get('DummyService');
        $this->assertSame($service1, $service2);
    }

    public function testRegisterInstance() {
        $serviceManager = new CM_Service_Manager();

        $serviceFoo = 12.3;
        $serviceManager->registerInstance('foo', $serviceFoo);
        $this->assertSame($serviceFoo, $serviceManager->get('foo'));

        $serviceBar = new DummyService('hello');
        $serviceManager->registerInstance('bar', $serviceBar);
        $this->assertSame($serviceBar, $serviceManager->get('bar'));
        $this->assertSame($serviceManager, $serviceBar->getServiceManager());
    }

    public function testReplaceInstance() {
        $serviceManager = new CM_Service_Manager();
        $this->assertSame(false, $serviceManager->has('foo'));

        $serviceManager->replaceInstance('foo', 12.3);
        $this->assertSame(12.3, $serviceManager->get('foo'));

        $serviceManager->replaceInstance('foo', 12.4);
        $this->assertSame(12.4, $serviceManager->get('foo'));
    }
}

class DummyService implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    private $_foo;

    public function __construct($foo) {
        $this->_foo = $foo;
    }

    public function getFoo() {
        return $this->_foo;
    }

    public function getArray($key, $value) {
        return array($key => $value);
    }
}
