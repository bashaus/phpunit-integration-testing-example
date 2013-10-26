<?php

namespace IntegrationTest;

use PHPUnit_Extensions_Database_DataSet_CompositeDataSet;

abstract class IntegrationCase extends \PHPUnit_Extensions_Database_TestCase
{

    const DEFAULT_DATASET_EXTENSION = 'yml';
    const DEFAULT_DATASET_TYPE = 'YamlDataSet';

    static public $pdo = null;
    static public $conn = null;

    static public $dataSetExtension = self::DEFAULT_DATASET_EXTENSION;
    static public $dataSetType = self::DEFAULT_DATASET_TYPE;

    public function setup()
    {
        return parent::setup();
    }

    public function teardown()
    {
        $annotations = $this->getAnnotations();
        $annotations = array_merge_recursive($annotations['class'], $annotations['method']);

        if (array_key_exists('assertFixture', $annotations)) {
            $this->assertFixture();
        }

        return parent::teardown();
    }

    public function getClassName()
    {
        $classNamespaced = explode("\\", get_class($this));
        return end($classNamespaced);
    }

    public function getFixtures()
    {
        $fixtures = array();

        $annotations = $this->getAnnotations();
        $annotations = array_merge_recursive($annotations['class'], $annotations['method']);

        if (array_key_exists('fixture', $annotations))
        {
            $fixtures += $annotations['fixture'];
        }

        return $fixtures;
    }

    final public function getConnection()
    {
        if (is_null(self::$conn))
        {
            self::$conn = $this->createDefaultDBConnection(self::$pdo);
        }

        return self::$conn;
    }

    protected function getDataSet()
    {
        $fixtures = $this->getFixtures();

        $dataSets = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array());

        foreach ($fixtures as $fixture) {
            $fixtureFilename = DATABASE_FIXTURES . '/' . $fixture . '.' . static::$dataSetExtension;
            switch (static::$dataSetType) {
                case 'CsvDataSet':
                    $dataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet;
                    $dataSet->addTable($fixture, $fixtureFilename);
                    break;

                case 'FlatXmlDataSet':
                    $dataSet = $this->createFlatXmlDataSet($fixtureFilename);
                    break;

                case 'MysqlXmlDataSet':
                    $dataSet = $this->createMySQLXMLDataSet($fixtureFilename);
                    break;

                case 'XmlDataSet':
                    $dataSet = $this->createXMLDataSet($fixtureFilename);
                    break;

                case 'YamlDataSet':
                    $dataSet = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($fixtureFilename);
                    break;

                default:
                    throw new \Exception('Unknown Data Set type: ' . static::$dataSetType);
            }

            $dataSets->addDataSet($dataSet);
        }

        return $dataSets;
    }

    protected function assertFixture($fixtureFilename=null, $message='')
    {
        if (is_null($fixtureFilename)) {
            $testClass = $this->getClassName();
            $testMethod = $this->getName();

            $class = new \ReflectionClass($this);
            $fixtureFilename = sprintf(
                '%s/%s/%s.%s',
                dirname($class->getFileName()),
                $testClass,
                $testMethod,
                static::$dataSetExtension
            );
        }

        // Ensure the file exists
        $this->assertFileExists($fixtureFilename, $message);

        // Get the dataSet
        $dataSetType = "\\PHPUnit_Extensions_Database_DataSet_" . static::$dataSetType;
        $expectedDataSet = new $dataSetType($fixtureFilename);

        foreach ($expectedDataSet->getTableNames() as $tableName) {
            $expectedTable = $expectedDataSet->getTable($tableName);
            $columns = implode(', ', $expectedDataSet->getTableMetaData($tableName)->getColumns());

            $actualDataSet = $this->getConnection()->createQueryTable(
                $tableName,
                "SELECT ${columns} FROM ${tableName}"
            );

            $this->assertTablesEqual($expectedTable, $actualDataSet);
        }
    }
}