<?php

class CM_Model_Schema_Definition {

    /** @var array */
    private $_schema;

    /**
     * @param array $schema
     */
    public function __construct(array $schema) {
        $this->_schema = $schema;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->_schema;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @throws CM_Model_Exception_Validation
     */
    public function encodeField($key, $value) {
        $key = (string) $key;
        if ($this->hasField($key)) {
            $schemaField = $this->_schema[$key];

            if (null !== $value) {
                $type = isset($schemaField['type']) ? $schemaField['type'] : null;
                if (null !== $type) {
                    switch ($type) {
                        case 'integer':
                        case 'int':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                        case 'string':
                            $value = (string) $value;
                            break;
                        case 'boolean':
                        case 'bool':
                            $value = (bool) $value;
                            break;
                        case 'array':
                            break;
                        case 'DateTime':
                            /** @var DateTime $value */
                            $value = $value->getTimestamp();
                            break;
                        default:
                            if (!class_exists($type)) {
                                throw new CM_Model_Exception_Validation('Field type `' . $type . '` is not a valid class');
                            }
                            $className = $type;
                            if (!$value instanceof $className) {
                                throw new CM_Model_Exception_Validation(
                                    'Value `' . CM_Util::var_line($value) . '` is not an instance of `' . $className . '`');
                            }

                            if (is_a($className, 'CM_Model_Abstract', true)) {
                                /** @var CM_Model_Abstract $value */
                                $id = $value->getIdRaw();
                                if (count($id) == 1) {
                                    $value = $value->getId();
                                } else {
                                    $value = CM_Util::jsonEncode($id);
                                }
                            } elseif (is_subclass_of($className, 'CM_ArrayConvertible', true)) {
                                /** @var CM_ArrayConvertible $value */
                                $value = $value->toArray();
                                $value = CM_Util::jsonEncode($value);
                            } else {
                                throw new CM_Model_Exception_Validation(
                                    'Class `' . $className . '` is neither CM_Model_Abstract nor CM_ArrayConvertible');
                            }
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @throws CM_Model_Exception_Validation
     */
    public function decodeField($key, $value) {
        $key = (string) $key;
        if ($this->hasField($key)) {
            $schemaField = $this->_schema[$key];

            if (null !== $value) {
                $type = isset($schemaField['type']) ? $schemaField['type'] : null;
                if (null !== $type) {
                    switch ($type) {
                        case 'integer':
                        case 'int':
                            $value = (int) $value;
                            break;
                        case 'float':
                            $value = (float) $value;
                            break;
                        case 'string':
                            $value = (string) $value;
                            break;
                        case 'boolean':
                        case 'bool':
                            $value = (bool) $value;
                            break;
                        case 'array':
                            break;
                        case 'DateTime':
                            $value = DateTime::createFromFormat('U', $value);
                            break;
                        default:
                            if (!class_exists($type)) {
                                throw new CM_Model_Exception_Validation('Field type `' . $type . '` is not a valid class/interface');
                            }
                            $className = $type;
                            if (is_a($className, 'CM_Model_Abstract', true)) {
                                /** @var CM_Model_Abstract $type */
                                if ($this->_isJson($value)) {
                                    $value = CM_Util::jsonDecode($value);
                                }
                                $id = $value;

                                if (!is_array($id)) {
                                    $id = ['id' => $id];
                                }
                                $value = CM_Model_Abstract::factoryGeneric($type::getTypeStatic(), $id);
                            } elseif (is_subclass_of($className, 'CM_ArrayConvertible', true)) {
                                /** @var CM_ArrayConvertible $className */
                                $value = CM_Util::jsonDecode($value);
                                $value = $className::fromArray($value);
                            } else {
                                throw new CM_Model_Exception_Validation(
                                    'Class `' . $className . '` is neither CM_Model_Abstract nor CM_ArrayConvertible');
                            }

                            if (!$value instanceof $className) {
                                throw new CM_Model_Exception_Validation(
                                    'Value `' . CM_Util::var_line($value) . '` is not an instance of `' . $className . '`');
                            }
                    }
                }
            }
        }
        return $value;
    }

    /**
     * @return string[]
     */
    public function getFieldNames() {
        return array_keys($this->_schema);
    }

    /**
     * @param string|string[] $key
     * @return bool
     */
    public function hasField($key) {
        if (is_array($key)) {
            return count(array_intersect($key, array_keys($this->_schema))) > 0;
        }
        return isset($this->_schema[$key]);
    }

    /**
     * @return bool
     */
    public function isEmpty() {
        return empty($this->_schema);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return mixed
     * @throws CM_Exception_Invalid
     * @throws CM_Model_Exception_Validation
     */
    public function validateField($key, $value) {
        $key = (string) $key;
        if ($this->hasField($key)) {
            $schemaField = $this->_schema[$key];

            $optional = !empty($schemaField['optional']);

            if (!$optional && null === $value) {
                throw new CM_Model_Exception_Validation('Field `' . $key . '` is mandatory');
            }

            if (null !== $value) {
                $type = isset($schemaField['type']) ? $schemaField['type'] : null;
                if (null !== $type) {
                    switch ($type) {
                        case 'integer':
                        case 'int':
                            if (!$this->_isInt($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not an integer');
                            }
                            break;
                        case 'float':
                            if (!$this->_isFloat($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a float');
                            }
                            break;
                        case 'string':
                            if (!$this->_isString($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a string');
                            }
                            break;
                        case 'boolean':
                        case 'bool':
                            if (!$this->_isBoolean($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a boolean');
                            }
                            break;
                        case 'array':
                            if (!$this->_isArray($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not an array');
                            }
                            break;
                        case 'DateTime':
                            if (!$this->_isInt($value)) {
                                throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid timestamp');
                            }
                            break;
                        default:
                            if (is_subclass_of($type, 'CM_Model_Abstract')) {
                                if (!$this->_isModel($value)) {
                                    throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid model');
                                }
                                break;
                            }
                            if (is_subclass_of($type, 'CM_ArrayConvertible')) {
                                if (!$this->_isJson($value)) {
                                    throw new CM_Model_Exception_Validation('Field `' . $key . '` is not a valid json');
                                }
                                break;
                            }
                            throw new CM_Exception_Invalid('Invalid type `' . $type . '`');
                    }
                }
            }
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isArray($value) {
        return is_array($value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isBoolean($value) {
        return is_bool($value) || (is_string($value) && ('0' === $value || '1' === $value));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isFloat($value) {
        return is_numeric($value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isInt($value) {
        return is_int($value) || (is_string($value) && $value === (string) (int) $value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isModel($value) {
        if ($this->_isJson($value)) {
            $value = CM_Params::jsonDecode($value);
        }
        if (is_array($value)) {
            if (!array_key_exists('id', $value)) {
                return false;
            }
            $value = $value['id'];
        }
        return $this->_isInt($value) || $this->_isString($value);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function _isString($value) {
        return is_string($value);
    }

    /**
     * @param string $value
     * @return bool
     */
    protected function _isJson($value) {
        // Hack proposed by Reto Kaiser in order to support multi-ids
        return substr($value, 0, 1) === '{';
    }
}
