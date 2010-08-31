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
        echo $this->directory;
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

    public function generateMigration($name) {
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
    
    public function validateMigrationName($name) {
    	return true;
	}

}

