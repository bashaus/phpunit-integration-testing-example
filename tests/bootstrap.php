<?php

ini_set('date.timezone', 'UTC');

$environmentConstants = array(
    'DATABASE_HOSTTYPE',
    'DATABASE_HOSTNAME',
    'DATABASE_USERNAME',
    'DATABASE_PASSWORD',
    'DATABASE_DATABASE'
);

foreach ($environmentConstants as $environmentConstant) {
    $environmentValue = getenv($environmentConstant);

    if ($environmentValue === false) {
        throw new \Exception('Environmental value does not exists: ' . $environmentConstant);
    }

    define($environmentConstant, $environmentValue);
}

if (!@include __DIR__ . '/../vendor/autoload.php') {
    die(<<<'EOT'
You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install

EOT
    );
}

define('DATABASE_FIXTURES', __DIR__ . '/Acme/fixtures');

require __DIR__ . '/Acme/units/AcmeTest.php';