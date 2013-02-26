<?php

class CM_Usertext_Filter_MaxLength extends CM_Usertext_Filter_Abstract {

	/** @var int|null */
	private $_lengthMax = null;

	/**
	 * @param int|null $lengthMax
	 */
	function __construct($lengthMax = null) {
		if (null !== $lengthMax) {
			$this->_lengthMax = (int) $lengthMax;
		}
	}

	public function transform($text) {
		$text = (string) $text;
		if (null === $this->_lengthMax) {
			return $text;
		}
		if (strlen($text) > $this->_lengthMax) {
			$text = substr($text, 0, $this->_lengthMax);
			$lastBlank = strrpos($text, ' ');
			if ($lastBlank > 0) {
				$text = substr($text, 0, $lastBlank+1);
			}
			$text = $text . '…';
		}
		return $text;
	}

}
