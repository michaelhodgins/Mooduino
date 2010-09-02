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
		    fwrite(
		    	$fpointer,
		    	sprintf(
		    		"<?php\nclass Migration_%d_%s extends Mooduino_Db_Migrations_Migration_Abstract {\n\n\tpublic function __construct() {\n\t\t\$this->name = '%s';\n\t}\n\n\tpublic function up() {\n\t\t\n\t}\n\n\tpublic function down() {\n\t\t\n\t}\n}\n\n",
		    		$timestamp,
		    		$name,
		    		$name
		    	)
		    );
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
    			$migrations[] = new $klass();
    		}
    	}
    	return $migrations;
    }
    
    public function validateMigrationName($name) {
    	return preg_match('/^[a-zA-Z]+[a-zA-Z0-9]*/', $name) == 1;
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

