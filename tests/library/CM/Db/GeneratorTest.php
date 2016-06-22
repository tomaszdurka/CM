<?php

class CM_Db_GeneratorTest extends CMTest_TestCase {

    public function testGetCreateTableSqlFromSchemaDefinition() {
        $schema = new CM_Model_Schema_Definition([
            'name'         => ['type' => 'string'],
            'abbreviation' => ['type' => 'string'],
            'enabled'      => ['type' => 'boolean'],
            'backupId'     => ['type' => 'CM_Model_Language', 'optional' => true],
        ]);

        $serviceManager = $this->getServiceManager();
        $generator = new CM_Db_Generator();
        $generator->setServiceManager($serviceManager);
        $sqlStatements = $generator->getCreateTableSqlFromSchemaDefinition('foo', $schema);

        $expecedSql = 'CREATE TABLE foo (
            id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            abbreviation VARCHAR(255) NOT NULL, 
            enabled TINYINT(1) NOT NULL, 
            backupId INT UNSIGNED DEFAULT NULL, 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB';
        
        $expecedSql = preg_replace('/\n\s+/', '', $expecedSql);
        $this->assertSame($expecedSql, $sqlStatements[0]);
    }

    public function testCreateTableFromSchemaDefinition() {
        $schema = new CM_Model_Schema_Definition([
            'name'         => ['type' => 'string'],
            'abbreviation' => ['type' => 'string'],
            'enabled'      => ['type' => 'boolean'],
            'backupId'     => ['type' => 'CM_Model_Language', 'optional' => true],
        ]);

        $serviceManager = $this->getServiceManager();
        $generator = new CM_Db_Generator();
        $generator->setServiceManager($serviceManager);
        $generator->createTableFromSchemaDefinition('foo', $schema);

        $this->assertTrue(CM_Db_Db::existsTable('foo'));
        $this->assertTrue(CM_Db_Db::existsColumn('foo', 'id'));
        $this->assertTrue(CM_Db_Db::existsColumn('foo', 'name'));
        $this->assertTrue(CM_Db_Db::existsColumn('foo', 'abbreviation'));
        $this->assertTrue(CM_Db_Db::existsColumn('foo', 'enabled'));
        $this->assertTrue(CM_Db_Db::existsColumn('foo', 'backupId'));
    }
}
