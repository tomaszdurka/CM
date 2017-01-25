<?php

namespace CM\Url;

use CM_Model_Language;
use CM_Frontend_Environment;
use Psr\Http\Message\UriInterface;

interface UrlInterface extends UriInterface {

    /**
     * @return bool
     */
    public function isAbsolute();

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguage();

    /**
     * @return string|null
     */
    public function getPrefix();

    /**
     * @param CM_Model_Language $language
     * @return UrlInterface
     */
    public function withLanguage(CM_Model_Language $language);

    /**
     * @param string $prefix
     * @return UrlInterface
     */
    public function withPrefix($prefix);

    /**
     * @param UrlInterface $baseUrl
     * @return UrlInterface
     */
    public function withBaseUrl(UrlInterface $baseUrl);

    /**
     * @param UrlInterface $url
     * @return UrlInterface
     */
    public function withRelativeComponentsFrom(UrlInterface $url);

    /**
     * @param CM_Frontend_Environment $environment
     * @param array                   $options
     * @return UrlInterface
     */
    public function withEnvironment(CM_Frontend_Environment $environment, array $options = null);

    /**
     * @param string $path
     * @return UrlInterface
     */
    public function withPath($path);

    /**
     * @param string $query
     * @return UrlInterface
     */
    public function withQuery($query);

    /**
     * @param string $fragment
     * @return UrlInterface
     */
    public function withFragment($fragment);
}
