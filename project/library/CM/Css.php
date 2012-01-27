<?php

class CM_Css {

	/**
	 * @var CM_Render
	 */
	private $_render = null;

	/**
	 * @param string		  $css
	 * @param CM_Render	   $render
	 * @param CM_Css|null	 $presets
	 * @param string|null	 $prefix
	 */
	public function __construct($css, CM_Render $render, CM_Css $presets = null, $prefix = null) {
		$this->_adapter = new CM_CssAdapter_CM($css, $render, $presets, $prefix);
	}

	/**
	 * @return array
	 */
	public function getData() {
		$this->_adapter->parse();
		return $this->_adapter->getData();
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return $this->_adapter->parse();
	}
}
