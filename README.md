## Step 1: Create database

Create a database and add the following users table:

    CREATE TABLE users (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (id)
    );

## Step 2: Setup PHPUnit

Copy file `tests/phpunit.xml.dist` to `tests/phpunit.xml`

Edit PHP environmental values:

* DATABASE_HOSTTYPE
* DATABASE_HOSTNAME
* DATABASE_USERNAME
* DATABASE_PASSWORD
* DATABASE_DATABASE

## Step 3: Run tests

    cd tests/
    phpunit .