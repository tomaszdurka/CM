<?php

class CM_Frontend_GlobalResponse {

    /** @var CM_Frontend_TreeNode|null */
    protected $_treeRoot;

    /** @var CM_Frontend_TreeNode|null */
    protected $_treeCurrent;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadHeaderJs;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadPrepareJs;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadJs;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_onloadReadyJs;

    /** @var CM_Tracking_Abstract */
    private $_tracking;

    public function __construct() {
        $this->_onloadHeaderJs = new CM_Frontend_JavascriptContainer();
        $this->_onloadPrepareJs = new CM_Frontend_JavascriptContainer();
        $this->_onloadJs = new CM_Frontend_JavascriptContainer();
        $this->_onloadReadyJs = new CM_Frontend_JavascriptContainer();
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    public function treeExpand(CM_Frontend_ViewResponse $viewResponse) {
        $node = new CM_Frontend_TreeNode($viewResponse);
        if (null === $this->_treeRoot) {
            $this->_treeRoot = $node;
        } else {
            $this->getTreeCurrent()->addChild($node);
        }
        $this->_treeCurrent = $node;
    }

    public function treeCollapse() {
        if ($this->getTreeCurrent()->isRoot()) {
            $this->_treeCurrent = null;
        } else {
            $this->_treeCurrent = $this->getTreeCurrent()->getParent();
        }
    }

    /**
     * @return \CM_Frontend_TreeNode
     * @throws CM_Exception_Invalid
     */
    public function getTreeCurrent() {
        if (null === $this->_treeCurrent) {
            throw new CM_Exception_Invalid('No current tree node set');
        }
        return $this->_treeCurrent;
    }

    /**
     * @throws CM_Exception_Invalid
     * @return \CM_Frontend_TreeNode
     */
    public function getTreeRoot() {
        if (null === $this->_treeRoot) {
            throw new CM_Exception_Invalid('No root tree set');
        }
        return $this->_treeRoot;
    }

    /**
     * @param string $viewClassName
     * @return CM_Frontend_ViewResponse|null
     */
    public function getClosestViewResponse($viewClassName) {
        $node = $this->getTreeCurrent();
        while (!$node->getValue()->getView() instanceof $viewClassName) {
            if ($node->isRoot()) {
                return null;
            }
            $node = $node->getParent();
        };
        return $node->getValue();
    }

    public function clear() {
        $this->_onloadHeaderJs->clear();
        $this->_onloadPrepareJs->clear();
        $this->_onloadJs->clear();
        $this->_onloadReadyJs->clear();
        $this->_treeCurrent = null;
        $this->_treeRoot = null;
    }

    /**
     * @return CM_Tracking_Abstract
     */
    public function getTracking() {
        if (!$this->_tracking) {
            $this->_tracking = CM_Tracking_Abstract::factory();
        }
        return $this->_tracking;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadHeaderJs() {
        return $this->_onloadHeaderJs;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadPrepareJs() {
        return $this->_onloadPrepareJs;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadJs() {
        return $this->_onloadJs;
    }

    /**
     * @return CM_Frontend_JavascriptContainer
     */
    public function getOnloadReadyJs() {
        return $this->_onloadReadyJs;
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     */
    public function registerViewResponse(CM_Frontend_ViewResponse $viewResponse) {
        $reference = 'cm.views["' . $viewResponse->getAutoId() . '"]';
        $view = $viewResponse->getView();
        $code = $reference . ' = new ' . get_class($view) . '({';
        $code .= 'el:$("#' . $viewResponse->getAutoId() . '").get(0),';
        $code .= 'params:' . CM_Params::encode($view->getParams()->getAllOriginal(), true);

        $parentNode = $this->getTreeCurrent()->getParent();
        if ($parentNode) {
            $code .= ',parent:cm.views["' . $parentNode->getValue()->getAutoId() . '"]';
        }
        $code .= '});' . PHP_EOL;

        $this->getOnloadPrepareJs()->prepend($code);
        $this->getOnloadJs()->append($viewResponse->getJs()->compile($reference));
    }

    /**
     * @return string
     */
    public function getJs() {
        $operations = array(
            $this->_onloadHeaderJs->compile(null),
            $this->_onloadPrepareJs->compile(null),
            $this->_onloadJs->compile(null),
            $this->_onloadReadyJs->compile(null),
            $this->getTracking()->getJs(),
        );
        $operations = array_filter($operations);
        return implode(PHP_EOL, $operations);
    }

    /**
     * @param CM_Frontend_Render $render
     * @return string
     */
    public function getHtml(CM_Frontend_Render $render) {
        $html = '<script type="text/javascript">' . PHP_EOL;
        $html .= '$(function() {' . PHP_EOL;
        $html .= $this->_onloadHeaderJs->compile(null) . PHP_EOL;
        $html .= $this->_onloadPrepareJs->compile(null) . PHP_EOL;
        $html .= $this->_onloadJs->compile(null) . PHP_EOL;
        $html .= $this->_onloadReadyJs->compile(null) . PHP_EOL;
        $html .= '});' . PHP_EOL;
        $html .= '</script>' . PHP_EOL;
        $html .= $this->getTracking()->getHtml($render->getEnvironment()->getSite());
        return $html;
    }
}