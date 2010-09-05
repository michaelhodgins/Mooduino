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
    public function __construct($directory, Zend_Db_Adapter_Abstract $dbAdapter) {
        $this->directory = $directory;
        //echo $this->directory;
        $this->dbAdapter = $dbAdapter;
        if (!is_dir($this->directory)) {
        	throw new Exception('migration directory must exist');
        }
        if (!is_readable($this->directory)) {
        	throw new Exception('migration directory must be readable');
        }
        if (!is_writable($this->directory)) {
        	throw new Exception('migration directory must be writable');
        }
    }

    public function generateMigration($name, $baseClass=null) {
    	if (!$this->validateMigrationName($name)) {
    		throw new Exception('Check the migration name');
    	}
    	$timestamp = mktime();
        $fileName = sprintf('%s/%d_%s.php', $this->directory, $timestamp, $name);
        $fpointer = fopen($fileName, 'w');
        try {
        	if (is_null($baseClass)) {
				fwrite(
					$fpointer,
					sprintf(
						"<?php\nclass Migration_%d_%s extends Mooduino_Db_Migrations_Migration_Abstract {\n\n\tpublic function __construct() {\n\t\tparent::__construct('%s', %d);\n\t}\n\n\tpublic function up() {\n\t\t\n\t}\n\n\tpublic function down() {\n\t\t\n\t}\n}\n\n",
						$timestamp,
						$name,
						$name,
						$timestamp
					)
				);
		    } else {
		    	fwrite(
		    		$fpointer,
		    		sprintf(
		    			"<?php\nclass Migration_%d_%s extends %s {\n\n\tpublic function up() {\n\t\t\n\t}\n\n\tpublic function down() {\n\t\t\n\t}\n}\n\n",
		    			$timestamp,
		    			$name,
		    			$baseClass
		    		)
		    	);
		    }
        } catch(Exception $e) {}
        fclose($fpointer);
    }
    
    public function listMigrations() {
    	$this->checkSchemaTable();
    	$migrations = array();
    	$files = scandir($this->directory);
    	foreach($files as $file) {
    		if (is_file($this->migrationFilePath($file)) && $file[strlen($file)-1] != '~') {
    			include_once $this->migrationFilePath($file);
    			$klass = $this->migrationClass($file);
    			$migration = new $klass();
    			$record = $this->getRecord($migration->getTimestamp());
    			$migration->setProcessedTimestamp($record['date_added']);
    			$migrations[] = $migration;
    		}
    	}
    	return $migrations;
    }
    
    public function validateMigrationName($name) {
    	return preg_match('/^[a-zA-Z]+[a-zA-Z0-9]*/', $name) == 1;
	}
	
	private function getRecord($timestamp) {
		$select = $this->dbAdapter->select()
			->from(array('s'=>'schema_version'), array('id', 'version', 'date_added'))
			->where('version = ?', intval($timestamp))
			->limit(1);
		return $this->dbAdapter->fetchRow($select);
	}
	
	private function getLastRecord() {
		$select = $this->dbAdapter->select()
			->from(array('s'=>'schema_version'), array('id', 'version', 'date_added'))
			->limit(1)
			->orderBy('version DESC');
		return $this->dbAdapter->fetchRow($select);
	}
	
	private function checkSchemaTable() {
		$tables = $this->dbAdapter->query('SHOW TABLES;');
		$tableFound = false;
		foreach($tables as $table) {
			$name = array_pop($table);
			if ($name == 'schema_version') {
				$tableFound = true;
				break;
			}
		}
		if (!$tableFound) {
			$this->createSchemaTable();
		}
	}
	
	private function createSchemaTable() {
		$this->dbAdapter->query(
			'CREATE TABLE `schema_version` (
				`id` BIGINT NOT NULL AUTO_INCREMENT,
				`version` BIGINT NOT NULL,
				`date_added` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			)'
		);
	}

	private function migrationFilePath($file) {
		return $this->directory.'/'.$file;
	}
	
	private function migrationClass($file) {
		return 'Migration_'.substr($file, 0, strlen($file)-4);
	}
}

