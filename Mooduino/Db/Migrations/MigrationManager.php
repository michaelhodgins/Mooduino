<?php

class Mooduino_Db_Migrations_MigrationManager {

    private $directory;
    private $dbAdapter;
    /**
     * Constructs the MigrationManager. This is private
     * as MigrationManager is a singleton.
     * @param string $directory the directory that migration files are stored in
     * @param Zend_Db_Adapter_Abstract $dbAdapter the database connection
     */
    public function __constructor($directory, Zend_Db_Adapter_Abstract $dbAdapter) {
        $this->directory = $directory;
        $this->dbAdapter = $dbAdapter;
    }

    public function generateMigration($name) {
        
    }

}

