<?php

use DI\ContainerBuilder;
use DI\Definition\Helper as DefinitionHelper;

class CM_Service_Manager extends CM_Class_Abstract {

    /** @var array */
    private $_serviceInstanceList = [];

    /** @var \DI\Container */
    public $_container;

    /** @var CM_Service_Manager */
    protected static $instance;

    public function __construct() {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAnnotations(false);
        $this->_container = $builder->build();
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName) {
        return $this->_container->has($serviceName);
    }

    /**
     * @param string     $name
     * @param array|null $parameters
     * @return mixed
     */
    public function make($name, array $parameters = null) {
        return $this->_container->make($name, (array) $parameters);
    }

    /**
     * @param callable   $callable
     * @param array|null $parameters
     * @return mixed
     */
    public function call(callable $callable, array $parameters = null) {
        $parameters = (array) $parameters;
        var_dump($parameters);
        return $this->_container->call($callable, $parameters);
    }

    /**
     * @param $name
     * @param mixed|DefinitionHelper|Definition|\Closure
     */
    public function set($name, $value) {
        $this->_container->set($name, $value);
    }

    /**
     * @param string      $serviceName
     * @param string|null $assertInstanceOf
     * @throws CM_Exception_Invalid
     * @return mixed
     */
    public function get($serviceName, $assertInstanceOf = null) {
        $service = $this->_container->get($serviceName);
        if (null !== $assertInstanceOf && !is_a($service, $assertInstanceOf, true)) {
            throw new CM_Exception_Invalid('Service has an invalid class.', null, [
                'service'           => $serviceName,
                'actualClassName'   => get_class($service),
                'expectedClassName' => $assertInstanceOf,
            ]);
        }
        return $service;
    }

    /**
     * @param string      $serviceName
     * @param string      $className
     * @param array|null  $arguments
     * @param string|null $methodName
     * @param array|null  $methodArguments
     * @throws CM_Exception_Invalid
     */
    public function register($serviceName, $className, array $arguments = null, $methodName = null, array $methodArguments = null) {
        $config = [
            'class'     => $className,
            'arguments' => $arguments,
        ];
        if (null !== $methodName) {
            $config['method'] = [
                'name'      => $methodName,
                'arguments' => $methodArguments,
            ];
        }
        $this->registerWithArray($serviceName, $config);
    }

    /**
     * @param string $serviceName
     * @param array  $config
     * @throws CM_Exception_Invalid
     */
    public function registerWithArray($serviceName, array $config) {
        $this->_container->set($serviceName, function () use ($serviceName, $config) {
            $class = (string) $config['class'];
            $arguments = [];
            if (isset($config['arguments'])) {
                $arguments = (array) $config['arguments'];
            }
            $method = null;
            if (isset($config['method'])) {
                $methodName = (string) $config['method']['name'];
                $methodArguments = [];
                if (isset($config['method']['arguments'])) {
                    $methodArguments = (array) $config['method']['arguments'];
                }
                $method = ['name' => $methodName, 'arguments' => $methodArguments];
            }

            $instanceConfig = [
                'class'     => $class,
                'arguments' => $arguments,
                'method'    => $method,
            ];
            return $this->_instantiateService($serviceName, $instanceConfig);
        });
    }

    /**
     * @param string $serviceName
     * @param mixed  $instance
     * @throws CM_Exception_Invalid
     */
    public function registerInstance($serviceName, $instance) {
        $serviceName = (string) $serviceName;
        if ($instance instanceof CM_Service_ManagerAwareInterface) {
            $instance->setServiceManager($this);
        }
        $this->_container->set($serviceName, $instance);
    }

    public function resetServiceInstances() {
        $this->_serviceInstanceList = [];
    }

    /**
     * @param string $serviceName
     * @param mixed  $instance
     */
    public function replaceInstance($serviceName, $instance) {
        $this->registerInstance($serviceName, $instance);
    }

    /**
     * @param string $serviceName
     * @throws CM_Exception_NotImplemented
     * @deprecated
     */
    public function unregister($serviceName) {
        throw new CM_Exception_NotImplemented();
    }

    /**
     * Methods in format get[serviceName] returns a instance of a service with given name.
     *
     * @param string $name
     * @param mixed  $parameters
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function __call($name, $parameters) {
        if (preg_match('/get(.+)/', $name, $matches)) {
            $serviceName = $matches[1];
            return $this->get($serviceName);
        }
        throw new CM_Exception_Invalid('Cannot extract service name.', null, ['name' => $name]);
    }

    /**
     * @return CM_Service_Databases
     */
    public function getDatabases() {
        return $this->get('databases', 'CM_Service_Databases');
    }

    /**
     * @return CM_Jobdistribution_DelayedQueue
     */
    public function getDelayedJobQueue() {
        return $this->get('delayedJobQueue', 'CM_Jobdistribution_DelayedQueue');
    }

    /**
     * @param string|null $serviceName
     * @return CM_MongoDb_Client
     */
    public function getMongoDb($serviceName = null) {
        if (null === $serviceName) {
            $serviceName = 'MongoDb';
        }
        return $this->get($serviceName, 'CM_MongoDb_Client');
    }

    /**
     * @return CM_Options
     * @throws CM_Exception_Invalid
     */
    public function getOptions() {
        return $this->get('options', 'CM_Options');
    }

    /**
     * @return CM_Service_Filesystems
     */
    public function getFilesystems() {
        return $this->get('filesystems', 'CM_Service_Filesystems');
    }

    /**
     * @return CM_Debug
     */
    public function getDebug() {
        return $this->get('debug', 'CM_Debug');
    }

    /**
     * @return CM_Service_Trackings
     */
    public function getTrackings() {
        return $this->get('trackings', 'CM_Service_Trackings');
    }

    /**
     * @return CM_Service_UserContent
     */
    public function getUserContent() {
        return $this->get('usercontent', 'CM_Service_UserContent');
    }

    /**
     * @param string $serviceName
     * @return CM_Janus_Service
     * @throws CM_Exception_Invalid
     */
    public function getJanus($serviceName) {
        return $this->get($serviceName, 'CM_Janus_Service');
    }

    /**
     * @return CM_Memcache_Client
     */
    public function getMemcache() {
        return $this->get('memcache', 'CM_Memcache_Client');
    }

    /**
     * @return CM_MessageStream_Service
     */
    public function getStreamMessage() {
        return $this->get('stream-message', 'CM_MessageStream_Service');
    }

    /**
     * @return CM_Redis_Client
     */
    public function getRedis() {
        return $this->get('redis', 'CM_Redis_Client');
    }

    /**
     * @return CM_Elasticsearch_Cluster
     */
    public function getElasticsearch() {
        return $this->get('elasticsearch', 'CM_Elasticsearch_Cluster');
    }

    /**
     * @return CMService_Newrelic
     */
    public function getNewrelic() {
        return $this->get('newrelic', 'CMService_Newrelic');
    }

    /**
     * @return CM_Log_Logger
     */
    public function getLogger() {
        return $this->get('logger', 'CM_Log_Logger');
    }

    /**
     * @return CM_Mail_Mailer
     */
    public function getMailer() {
        return $this->get('mailer', 'CM_Mail_Mailer');
    }

    /**
     * @param string $serviceName
     * @param array  $config
     * @return mixed|object
     */
    protected function _instantiateService($serviceName, array $config) {
        $reflection = new ReflectionClass($config['class']);

        $arguments = $config['arguments'];
        if ($constructor = $reflection->getConstructor()) {
            $arguments = $this->_matchNamedArgs($serviceName, $constructor, $arguments);
        }
        $instance = $reflection->newInstanceArgs($arguments);

        if ($instance instanceof CM_Service_ManagerAwareInterface) {
            $instance->setServiceManager($this);
        }

        if (null !== $config['method']) {
            $method = $reflection->getMethod($config['method']['name']);
            $methodArguments = $this->_matchNamedArgs($serviceName, $method, $config['method']['arguments']);
            $instance = $method->invokeArgs($instance, $methodArguments);
        }
        return $instance;
    }

    /**
     * @param string           $serviceName
     * @param ReflectionMethod $method
     * @param array            $arguments
     * @throws CM_Exception_Invalid
     * @return array
     */
    protected function _matchNamedArgs($serviceName, ReflectionMethod $method, array $arguments) {
        $namedArgs = new CM_Util_NamedArgs();
        try {
            return $namedArgs->matchNamedArgs($method, $arguments);
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_Invalid('Cannot match arguments for service', null, [
                'serviceName'              => $serviceName,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @deprecated Instead make your class manager-aware (`CM_Service_ManagerAwareInterface`) and pass the manager.
     *
     * @return CM_Service_Manager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::setInstance(new self());
        }
        return self::$instance;
    }

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public static function setInstance(CM_Service_Manager $serviceManager) {
        self::$instance = $serviceManager;
    }

    function __clone() {
        foreach ($this->_serviceInstanceList as &$instance) {
            $instance = clone $instance;
        }
    }

}
