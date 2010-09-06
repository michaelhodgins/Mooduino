<?php

class Mooduino_Db_Migrations_MigrationManager {

  /**
   * @var string
   */
  private $directory;
  /**
   * @var Zend_Db_Adapter_Abstract
   */
  private $dbAdapter;
  
  const TOP = -1;

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

  /**
   * Generates a migration file, using the current timestamp and
   * the given name. The name must be valid.
   * If $baseClass is given, that class name is used as the migration's super
   * class; otherwise Mooduino_Db_Migrations_Migration_Abstract is used. If
   * $baseClass is given, the target class must implement Mooduino_Db_Migrations_Migration
   * @param string $name The name of the migration
   * @param string $baseClass The migration superclass
   * @see Mooduino_Db_Migrations_MigrationManager::validateMigrationName()
   */
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
    } catch (Exception $e) {
      fclose($fpointer);
      throw new Exception('An error occured while generating the migration file.', $e->getCode(), $e);
    }
    fclose($fpointer);
  }

  /**
   * Returns an array of the migrations currently in the project.
   * @return array[int]Mooduino_Db_Migrations_Migration
   */
  public function listMigrations() {//TODO: rename to 'getMigrations'?
    $this->checkSchemaTable();
    return $this->getMigrationsFrom();
  }

  /**
   * Returns the current migration.
   * @return Mooduino_Db_Migrations_Migration
   */
  public function getCurrentMigration() {
    $migration = null;
    $record = $this->getLastRecord();
    $timestamp = $record['version'];
    if ($timestamp > 0) {
      $migrations = $this->getMigrationsFrom($timestamp, 1);
      if (count($migrations) > 0) {
        $migration = $migrations[0];
      }
    }
    return $migration;
  }

  /**
   * Returns the migration with the given step value.
   * @param int $step
   * @return Mooduino_Db_Migrations_Migration
   */
  public function getMigrationByStep($step) {
    $migration = null;
    $files = $this->getMigrationFileNames();
    foreach ($files as $count => $file) {
      if (intval($step) == $count+1) {
        $migration = $this->getMigrationFromFile($file, $count+1);
        break;
      }
    }
    return $migration;
  }

  /**
   * Returns the first migration found with the given name.
   * @param string $name
   * @return Mooduino_Db_Migrations_Migration
   */
  public function getMigrationByName($name) {
    $migration = null;
    $files = $this->getMigrationFileNames();
    foreach ($files as $count => $file) {
      $metadata = $this->parseFilenameMetadata($file);
      if (strval($name) == $metadata['name']) {
        $migration = $this->getMigrationFromFile($file, $count+1);
        break;
      }
    }
    return $migration;
  }

  /**
   * Returns true if the given name is a valid name for a migration.
   * @param string $name
   * @return boolean
   */
  public function validateMigrationName($name) {
    return preg_match('/^[a-zA-Z]+[a-zA-Z0-9]*/', $name) == 1;
  }
  
  public function runTo($step) {
  	
  	if ($step == self::TOP) {
  	
  	}
  }
  
  public function getTopStep() {
  	$step = 0;	
  }

  /**
   * Returns migration objects with a timestamp equal to or greater than the
   * given timestamp. If $limit is greater than 0, no more than that number of
   * migrations will be given.
   * @param int $timestamp
   * @param int $limit
   * @return array[int]Mooduino_Db_Migrations_Migration
   */
  private function getMigrationsFrom($timestamp = 0, $limit = 0) {
    $migrations = array();
    $files = $this->getMigrationFileNames();
    foreach ($files as $count => $file) {
      $metadata = $this->parseFilenameMetadata($file);
      if ($metadata['timestamp'] >= $timestamp) {
        $migrations[] = $this->getMigrationFromFile($file, $count+1);
        if ($limit > 0 && count($migrations) >= $limit) {
          break;
        }
      }
    }
    return $migrations;
  }

  /**
   * Returns an array of filenames for all of the migrations in the application.
   * @return array[int]string
   */
  private function getMigrationFileNames() {
    $fileNames = array();
    $files = scandir($this->directory);
    foreach ($files as $file) {
      $metadata = $this->parseFilenameMetadata($file);
      if (is_file($metadata['realpath']) && $file[strlen($file) - 1] != '~') {
        $fileNames[] = $file;
      }
    }
    return $fileNames;
  }

  /**
   * Returns an instance of Mooduino_Db_Migrations_Migration when passed the
   * name of a file that contains the implementation.
   * @param string $file
   * @param int $step
   * @return Mooduino_Db_Migrations_Migration
   */
  private function getMigrationFromFile($file, $step) {
    $metadata = $this->parseFilenameMetadata($file);
    include_once $metadata['realpath'];
    $klass = $metadata['class'];
    $migration = new $klass();
    $record = $this->getRecord($migration->getTimestamp());
    $migration->setProcessedTimestamp($record['date_added']);
    $migration->setStep($step);
    return $migration;
  }

  /**
   * Returns the migration record from the schema table that has the given
   * version timestamp.
   * @param int $timestamp
   * @return array[sting]string
   */
  private function getRecord($timestamp) {
    $select = $this->dbAdapter->select()
            ->from(array('s' => 'schema_version'), array('id', 'version', 'date_added'))
            ->where('version = ?', intval($timestamp))
            ->limit(1);
    return $this->dbAdapter->fetchRow($select);
  }

  /**
   * Returns the last record in the schema table.
   * @return array[string]string
   */
  private function getLastRecord() {
    $select = $this->dbAdapter->select()
            ->from(array('s' => 'schema_version'), array('id', 'version', 'date_added'))
            ->limit(1)
            ->order('version DESC');
    return $this->dbAdapter->fetchRow($select);
  }

  /**
   * Ensures that the schema table is in place. Return true if the table
   * had to be built.
   * @return boolean
   */
  private function checkSchemaTable() {
    $tables = $this->dbAdapter->query('SHOW TABLES;');
    $tableFound = false;
    foreach ($tables as $table) {
      $name = array_pop($table);
      if ($name == 'schema_version') {
        $tableFound = true;
        break;
      }
    }
    if (!$tableFound) {
      $this->createSchemaTable();
    }
    return!$tableFound;
  }

  /**
   * Creates the schema table.
   */
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

  /**
   *
   * @param <type> $filename
   * @return array
   */
  private function parseFilenameMetadata($filename) {
    $metadata = array();
    $metadata['class'] = 'Migration_' . substr($filename, 0, strlen($filename) - 4);
    $metadata['realpath'] = realpath($this->directory . '/' . $filename);
    $matches = array();
    if (preg_match('/^([0-9]+)_([a-zA-Z]+[a-zA-Z0-9]*)\.php/', $filename, &$matches) == 1) {
      $metadata['timestamp'] = intval($matches[1]);
      $metadata['name'] = $matches[2];
    }
    return $metadata;
  }

}

