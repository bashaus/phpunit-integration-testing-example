<?php

namespace IntegrationTest;

use PDO;

ini_set('date.timezone', 'UTC');

require __DIR__ . '/../vendor/autoload.php';

IntegrationCase::$dataSetExtension = 'xml';
IntegrationCase::$dataSetType = 'XmlDataSet';
IntegrationCase::$pdo = new PDO(
    sprintf(
        '%s:host=%s;dbname=%s', 
        getenv('DATABASE_HOSTTYPE'), 
        getenv('DATABASE_HOSTNAME'), 
        getenv('DATABASE_DATABASE')
    ),
    getenv('DATABASE_USERNAME'),
    getenv('DATABASE_PASSWORD'),
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
);

define('DATABASE_FIXTURES', __DIR__ . '/IntegrationTest/fixtures');