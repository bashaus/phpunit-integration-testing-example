<?php

namespace Acme;

use PHPUnit_Extensions_Database_DataSet_YamlDataSet;
use PHPUnit_Extensions_Database_TestCase;
use PDO;
use ReflectionClass;

abstract class AcmeTest extends PHPUnit_Extensions_Database_TestCase
{

    static private $pdo = null;
    static private $conn = null;

    final public function getConnection()
    {
        if (is_null(self::$conn))
        {
            if (is_null(self::$pdo))
            {
                self::$pdo = new PDO(
                    sprintf(
                        '%s:host=%s;dbname=%s', 
                        DATABASE_HOSTTYPE, 
                        DATABASE_HOSTNAME, 
                        DATABASE_DATABASE
                    ),
                    DATABASE_USERNAME,
                    DATABASE_PASSWORD
                );
            }

            self::$conn = $this->createDefaultDBConnection(self::$pdo, DATABASE_DATABASE);
        }

        return self::$conn;
    }

    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            DATABASE_FIXTURES . '/default.yml'
        );
    }

    protected function assertFixture($fixtureFilename=null, $message='')
    {
        $class = new ReflectionClass($this);

        if (is_null($fixtureFilename)) {
            $testClass = end(explode("\\", get_class($this)));
            $testMethod = $this->getName();

            $fixtureFilename = dirname($class->getFileName()) . '/' . $testMethod . '.yml';
        }

        // Ensure the file exists
        self::assertFileExists($fixtureFilename, $message);

        // Get the dataset
        $expectedDataset = new PHPUnit_Extensions_Database_DataSet_YamlDataSet($fixtureFilename);

        foreach ($expectedDataset->getTableNames() as $tableName) {
            $expectedTable = $expectedDataset->getTable($tableName);
            $columns = implode(', ', $expectedDataset->getTableMetaData($tableName)->getColumns());

            $actualDataset = $this->getConnection()->createQueryTable(
                $tableName,
                "SELECT ${columns} FROM ${tableName}"
            );

            $this->assertTablesEqual($expectedTable, $actualDataset);
        }
    }
}