<?php

use \Doctrine\DBAL\Types\Type as DoctrineType;

class CM_Db_Generator implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string                     $tableName
     * @param CM_Model_Schema_Definition $schema
     * @throws CM_Db_Exception
     * @throws CM_Exception_Invalid
     */
    public function createTableFromSchemaDefinition($tableName, CM_Model_Schema_Definition $schema) {
        $sqlStatements = $this->getCreateTableSqlFromSchemaDefinition($tableName, $schema);

        $client = $this->getServiceManager()->getDatabases()->getMaster();
        foreach ($sqlStatements as $sqlStatement) {
            $client->createStatement($sqlStatement)->execute();
        }
    }

    /**
     * @param string                     $tableName
     * @param CM_Model_Schema_Definition $schema
     * @return array
     * @throws CM_Exception_Invalid
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getCreateTableSqlFromSchemaDefinition($tableName, CM_Model_Schema_Definition $schema) {
        $columns = [];

        $type = DoctrineType::getType(DoctrineType::INTEGER);
        $column = new \Doctrine\DBAL\Schema\Column('id', $type);
        $column->setAutoincrement(true);
        $column->setUnsigned(true);
        $column->setNotnull(true);
        $columns[] = $column;

        foreach ($schema->getFields() as $fieldName => $fieldDefinition) {
            $type = $this->_mapPhpTypeToDoctrineType($fieldDefinition);
            $column = new \Doctrine\DBAL\Schema\Column($fieldName, $type);

            if ($type instanceof \Doctrine\DBAL\Types\IntegerType) {
                $column->setUnsigned(true);
            }
            $column->setNotnull(empty($fieldDefinition['optional']));
            $columns[] = $column;
        }
        $table = new \Doctrine\DBAL\Schema\Table($tableName, $columns);
        $table->setPrimaryKey(['id']);

        $platform = new \Doctrine\DBAL\Platforms\MySqlPlatform();
        return $platform->getCreateTableSQL($table);
    }

    /**
     * @param array $field
     * @return DoctrineType
     * @throws CM_Exception_Invalid
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function _mapPhpTypeToDoctrineType(array $field) {
        if (isset($field['sqlType'])) {
            return $field['sqlType'];
        }
        $type = $field['type'];
        switch ($type) {
            case 'integer':
            case 'int':
                return DoctrineType::getType(DoctrineType::INTEGER);
            case 'float':
                return DoctrineType::getType(DoctrineType::FLOAT);
            case 'string':
                return DoctrineType::getType(DoctrineType::STRING);
            case 'boolean':
            case 'bool':
                return DoctrineType::getType(DoctrineType::BOOLEAN);
            case 'array':
                return DoctrineType::getType(DoctrineType::STRING);
            case 'DateTime':
                return DoctrineType::getType(DoctrineType::INTEGER);
            default:
                if (!class_exists($type)) {
                    throw new CM_Exception_Invalid('Field type `' . $type . '` is not a valid class');
                }
                if (is_a($type, 'CM_Model_Abstract', true)) {
                    return DoctrineType::getType(DoctrineType::INTEGER);
                } elseif (is_subclass_of($type, 'CM_ArrayConvertible', true)) {
                    return DoctrineType::getType(DoctrineType::STRING);
                } else {
                    throw new CM_Exception_Invalid(
                        'Class `' . $type . '` is neither CM_Model_Abstract nor CM_ArrayConvertible');
                }
        }
    }
}
