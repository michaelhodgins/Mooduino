<?php

require_once 'Mooduino/Db/Migrations/MigrationProvider/Interface.php';
require_once 'Mooduino/Db/Migrations/Migration.php';
require_once 'Mooduino/Db/Migrations/Migration/Abstract.php';
require_once 'Mooduino/Db/Migrations/MigrationManager.php';

class Mooduino_Db_Migrations_MigrationProvider extends Zend_Tool_Project_Provider_Abstract implements Mooduino_Db_Migrations_MigrationProvider_Interface {

    private $profile = null;
    private $config = null;
    private $dbAdapter = null;
    private $manager = null;

    public function getName() {
        return 'Migration';
    }

    public function generate($name, $env='development') {
        $this->init($env);
        $this->_registry->getResponse()->appendContent(sprintf('Generating migration %s.', $name));
        try {
	        $this->manager->generateMigration($name);
        } catch(Exception $e) {
        	$this->_registry->getResponse()->appendContent($e->getMessage());
        }
    }

    public function redo($step=1, $env='development') {
        $this->init($env);
        
    }

    public function current($env='development') {
        $this->init($env);
        
    }

    public function update($to='latest', $env='development') {
        $this->init($env);
        
    }

    public function show($revision='all', $env='development') {
        $this->init($env);
        if ($revision == 'all') {
        	$migrations = $this->manager->listMigrations();
        	foreach($migrations as $migration) {
        		$this->_registry->getResponse()->appendContent($migration->getName());
        	}
        } else {
        
        }
    }

    private function init($env) {
    	$path = realpath('./scripts/migrations');
    	if ($path === false) {
    		mkdir('./scripts/migrations', 0777, true);
	    	$path = realpath('./scripts/migrations');
	    	if ($path === false) {
	    		throw new Exception('Couldn\'t create the migration directory');
	    	}
    	}
//    	print_r($path);
        $this->manager = new Mooduino_Db_Migrations_MigrationManager($path, $this->getDbAdapter($env));
//		print_r($this->manager);
    }

    private function getProfile() {
        if (is_null($this->profile)) {
            $this->profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        }
        return $this->profile;
    }

    private function getConfig($env) {
        if (is_null($this->config)) {
            $configFile = $this->getProfile()->search('applicationConfigFile');
            if ($configFile === false) {
                throw new Zend_Tool_Project_Exception('An application configuration file is required.');
            }
            $this->config = new Zend_Config_Ini($configFile->getPath(), $env);
        }
        return $this->config;
    }

    private function getDbAdapter($env) {
        if (is_null($this->dbAdapter)) {
            $dbConfig = $this->getConfig($env)->resources->db;
            $this->dbAdapter = Zend_Db::factory($dbConfig->adapter, $dbConfig->params);
        }
        return $this->dbAdapter;
    }

}
