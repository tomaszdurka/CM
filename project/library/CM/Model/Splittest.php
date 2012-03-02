<?php

class CM_Model_Splittest extends CM_Model_Abstract {
	CONST TYPE = 16;

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->_construct(array('name' => (string) $name));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_getId('name');
	}

	/**
	 * @return int
	 */
	public function getId() {
		return (int) $this->_get('id');
	}

	/**
	 * @return int
	 */
	public function getCreated() {
		return (int) $this->_get('createStamp');
	}

	/**
	 * @return string[]
	 */
	public function getVariations() {
		return $this->_get('variations');
	}

	/**
	 * @param CM_Model_User $user
	 * @return string|null
	 */
	public function getVariation(CM_Model_User $user) {
		$cacheKey = CM_CacheConst::Splittest_VariationFixtures . '_userId:' . $user->getId();
		if (($variationFixtures = CM_Cache::get($cacheKey)) === false) {
			$variationFixtures = CM_Mysql::select(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId',
				'variationId'), array('userId' => $user->getId()))->fetchAllTree();

			CM_Cache::set($cacheKey, $variationFixtures);
		}

		$variations = $this->getVariations();
		if (array_key_exists($this->getId(), $variationFixtures)) {
			$variationId = $variationFixtures[$this->getId()];
		} else {
			$variationIds = array_keys($variations);
			$variationIds[] = null;
			$variationId = $variationIds[array_rand($variationIds)];
			CM_Mysql::replace(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId' => $this->getId(), 'userId' => $user->getId(),
				'variationId' => $variationId));
			CM_Cache::delete($cacheKey);
		}

		if (null === $variationId) {
			return null;
		}
		if (!array_key_exists($variationId, $variations)) {
			throw new CM_Exception_Invalid('Unknown variation `' . $variationId . '` for splittest `' . $this->getId() . '`.');
		}
		return $variations[$variationId];
	}

	protected function _loadData() {
		$data = CM_Mysql::select(TBL_CM_SPLITTEST, '*', array('name' => $this->getName()))->fetchAssoc();
		if ($data) {
			$data['variations'] = CM_Mysql::select(TBL_CM_SPLITTESTVARIATION, array('id',
				'name'), array('splittestId' => $data['id']))->fetchAllTree();
		}
		return $data;
	}

	protected static function _create(array $data) {
		$name = (string) $data['name'];
		$variations = array_unique($data['variations']);
		try {
			$id = CM_Mysql::insert(TBL_CM_SPLITTEST, array('name' => $name, 'createStamp' => time()));
			foreach ($variations as $variation) {
				CM_Mysql::insert(TBL_CM_SPLITTESTVARIATION, array('splittestId' => $id, 'name' => $variation));
			}
		} catch (CM_Exception $e) {
			CM_Mysql::delete(TBL_CM_SPLITTEST, array('id' => $id));
			CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION, array('splittestId' => $id));
			throw $e;
		}
		return new static($name);
	}

	protected function _onDelete() {
		CM_Mysql::delete(TBL_CM_SPLITTEST, array('id' => $this->getId()));
		CM_Mysql::delete(TBL_CM_SPLITTESTVARIATION, array('splittestId' => $this->getId()));
	}
}
