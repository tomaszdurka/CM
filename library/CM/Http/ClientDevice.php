<?php

class CM_Http_ClientDevice {

    /** @var \Jenssegers\Agent\Agent */
    protected $_parser;

    /** @var array */
    protected $_headerList;

    /**
     * @param CM_Http_Request_Abstract $request
     */
    public function __construct(CM_Http_Request_Abstract $request) {
        $this->_request = $request;

        $headerList = [];
        foreach ($request->getHeaders() as $key => $header) {
            if (substr($key, 0, 8) !== 'content-') {
                $headerList['HTTP_' . str_replace('-', '_', strtoupper($key))] = $header;
            }
        }
        $this->_parser = new Jenssegers\Agent\Agent($headerList);
        $this->_headerList = $headerList;
    }

    /**
     * @return bool
     */
    public function isMobile() {
        $cache = CM_Cache_Local::getInstance();

        return $cache->get($cache->key(__METHOD__, $this->_headerList), function () {
            return $this->_parser->isMobile();
        });
    }

    /**
     * @param string $property
     * @return string|false
     */
    public function getVersion($property) {
        $property = (string) $property;
        $cache = CM_Cache_Local::getInstance();

        return $cache->get($cache->key(__METHOD__, $this->_headerList, $property), function () use ($property) {
            return $this->_parser->version($property);
        });
    }
}
