<?php
/*  Copyright 2010  Michael Hodgins  (email : michael_hodgins@hotmail.)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Mooduino_Db_Migrations_MigrationManager {

  /**
   * The directory where the migration files are located.
   * @var string
   */
  private $directory;
  /**
   * The database adapter.
   * @var Zend_Db_Adapter_Abstract
   */
  private $dbAdapter;

  /**
   * A constant meaning the last step value.
   * @var int
   */
  const TOP = -1;

  /**
   * Migrate up.
   * @var int
   */
  private static $UP = 1;
  /**
   * Migrate down.
   * @var int
   */
  private static $DOWN = -1;
  /**
   * Set to true when we know that the table is in place.
   * @var boolean
   */
  private $tableInPlace = false;
  /**
   * A message set after migrations are executed.
   * @var string
   */
  private $message = '';

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
   * Returns the last message from the migration process.
   * @return string
   */
  public function getMessage() {
    return $this->message;
  }

  /**
   * Sets the migration process message.
   * @param string $message
   */
  private function setMessage($message) {
    $this->message = strval($message);
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
//    if (function_exists('microtime')) {
//     $timestamp = preg_replace('/\./', '', microtime(true));
//    } else {
      $timestamp = time();
//    }
    $fileName = sprintf('%s/%s_%s.php', $this->directory, $timestamp, $name);
    $fpointer = fopen($fileName, 'w');
    try {
      if (is_null($baseClass)) {
        fwrite(
            $fpointer,
            sprintf(
                "<?php\nclass Migration_%s_%s extends Mooduino_Db_Migrations_Migration_Abstract {\n\n\tpublic function __construct() {\n\t\tparent::__construct('%s', %s);\n\t}\n\n\tpublic function up() {\n\t\t\n\t}\n\n\tpublic function down() {\n\t\t\n\t}\n}\n\n",
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
                "<?php\nclass Migration_%s_%s extends %s {\n\n\tpublic function up() {\n\t\t\n\t}\n\n\tpublic function down() {\n\t\t\n\t}\n}\n\n",
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
    $this->setMessage('Migration saved to ' . $fileName);
  }

  /**
   * Returns an array of the migrations currently in the project.
   * @return array[int]Mooduino_Db_Migrations_Migration
   */
  public function getMigrations() {
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
      if (intval($step) == $count + 1) {
        $migration = $this->getMigrationFromFile($file, $count + 1);
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
        $migration = $this->getMigrationFromFile($file, $count + 1);
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

  /**
   * Runs migrations to the given step. If the current step is after the given
   * target step, the migrations after the target are rolled back.
   * @see Mooduino_Db_Migrations_MigrationManager::TOP
   * @param int $step Either the actual step value or Mooduino_Db_Migrations_MigrationManager::TOP
   */
  public function runTo($step) {
    if ($step == self::TOP) {
      $step = $this->getTopStep();
    } elseif (is_numeric($step)) {
      $step = intval($step);
    } else {
      throw new Exception('Step should be a number.');
    }
    $current = $this->getCurrentMigration();
    $undo = (!is_null($current)) && ($step < $current->getStep());
    if ($undo) {
      $migrations = array_reverse(
              $this->getMigrationsTo(
                  $current->getTimestamp(),
                  $current->getStep() - $step
              )
      );
    } else {
      $migrations = $this->getMigrationsFrom(
              is_null($current) ? 0 : $current->getTimestamp() + 1
      );
    }
    $count = 0;
    foreach ($migrations as $migration) {
      if ($undo) {
        if ($migration->getStep() > $step) {
          $this->undoMigration($migration);
          $count++;
        }
      } else {
        if ($migration->getStep() <= $step) {
          $this->runMigration($migration);
          $count++;
        }
      }
    }
    $this->setMessage(
        sprintf(
            '%d migration%s %s.',
            $count,
            $count == 1 ? '' : 's',
            $undo ? 'rolled back' : 'applied'
        )
    );
  }

  public function undo($step) {
    if (is_numeric($step)) {
      $step = intval($step);
    } else {
      throw new Exception('Step should be a number.');
    }
    $current = $this->getCurrentMigration();
    $this->runTo($current->getStep() - $step);
    $this->setMessage(
        sprintf(
            '%d migration%s rolled back.',
            $step,
            $step == 1 ? '' : 's'
        )
    );
  }

  public function redo($step) {
    if (is_numeric($step)) {
      $step = intval($step);
    } else {
      throw new Exception('Step should be a number.');
    }
    $current = $this->getCurrentMigration();
    $this->runTo($current->getStep() - $step);
    $this->runTo($current->getStep());
    $this->setMessage(
        sprintf(
            '%d migration%s rolled back and reapplied.',
            $step,
            $step == 1 ? '' : 's'
        )
    );
  }

  /**
   * Returns the step value of the last migration file.
   * @return int
   */
  private function getTopStep() {
    return count($this->getMigrationFileNames());
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
        $migrations[] = $this->getMigrationFromFile($file, $count + 1);
        if ($limit > 0 && count($migrations) >= $limit) {
          break;
        }
      }
    }
    return $migrations;
  }

  /**
   * Returns migration objects with the timestamp equal or less than the given
   * timestamp. If $limit is greater than 0, no more than that number of
   * migrations will be given.
   * @param int $timestamp
   * @param int $limit
   * @return array[int]Mooduino_Db_Migrations_Migration
   */
  private function getMigrationsTo($timestamp = -1, $limit = 0) {
    $migrations = array();
    $files = array_reverse($this->getMigrationFileNames());
    foreach ($files as $count => $file) {
      $metadata = $this->parseFilenameMetadata($file);
      if ($timestamp < 0 || $metadata['timestamp'] <= $timestamp) {
        $migrations[] = $this->getMigrationFromFile($file, count($files) - $count);
        if ($limit > 0 && count($migrations) >= $limit) {
          break;
        }
      }
    }
    return array_reverse($migrations);
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
    $this->checkSchemaTable();
    $select = $this->dbAdapter->select()
            ->from(array('s' => 'schema_version'), array('id', 'version', 'date_added' => 'UNIX_TIMESTAMP(date_added)'))
            ->where('version = ?', intval($timestamp))
            ->limit(1);
    return $this->dbAdapter->fetchRow($select);
  }

  /**
   * Returns the last record in the schema table.
   * @return array[string]string
   */
  private function getLastRecord() {
    $this->checkSchemaTable();
    $this->checkSchemaTable();
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
    $tableFound = $this->tableInPlace;
    if (!$tableFound) {
      $tables = $this->dbAdapter->query('SHOW TABLES;');
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
    $this->tableInPlace = true;
  }

  /**
   * Returns metadata about a migration file, implied from the file name alone.
   * @param string $filename
   * @return array[string]mixed
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

  /**
   * Executes the SQL statements returned by the given migration's up()
   * function.
   * @param Mooduino_Db_Migrations_Migration $migration
   */
  private function runMigration(Mooduino_Db_Migrations_Migration $migration) {
    $this->executeMigration($migration, self::$UP);
  }

  /**
   * Executes the SQL statements returned by the given migrations' up()
   * functions.
   * @param array[int]Mooduino_Db_Migrations_Migration $migrations
   */
  private function runMigrations($migrations) {
    foreach ($migrations as $migration) {
      $this->runMigration($migration);
    }
  }

  /**
   * Executes the SQL statements returned by the given migration's down()
   * function.
   * @param Mooduino_Db_Migrations_Migration $migration
   */
  private function undoMigration(Mooduino_Db_Migrations_Migration $migration) {
    $this->executeMigration($migration, self::$DOWN);
  }

  /**
   * Executes the SQL statements returned by the given migrations' down()
   * functions.
   * @param array[int]Mooduino_Db_Migrations_Migration $migrations
   */
  private function undoMigrations($migrations) {
    foreach ($migrations as $migrations) {
      $this->undoMigration($migration);
    }
  }

  /**
   * Executes a single migration against the database within a database
   * transaction. Whether the migration is executed up or down is determined by
   * the value of $direction.
   * @param Mooduino_Db_Migrations_Migration $migration
   * @param int $direction
   */
  private function executeMigration(Mooduino_Db_Migrations_Migration $migration, $direction) {
    if (!in_array($direction, array(self::$UP, self::$DOWN))) {
      throw new Exception('direction unknown');
    }
    $this->checkSchemaTable();
    $this->dbAdapter->beginTransaction();
    try {
      if ($direction == self::$UP) {
        $query = $migration->up();
        $this->dbAdapter->insert('schema_version', array('version' => $migration->getTimestamp()));
      } elseif ($direction == self::$DOWN) {
        $query = $migration->down();
        $this->dbAdapter->delete('schema_version', 'version = ' . $migration->getTimestamp());
      }
      $this->execute($query);
    } catch (Exception $e) {
      echo $e->getMessage();
      $this->dbAdapter->rollBack();
      throw new Exception('An error occured while executing the migration(s).', $e->getCode(), $e);
    }
    $this->dbAdapter->commit();
  }

  /**
   * Executes the given SQL statment or statements. The parameter can be either
   * a single string or an array of strings.
   * @param string|array[int]string $query
   */
  private function execute($query) {
    if (is_array($query)) {
      foreach ($query as $subquery) {
        $this->execute($subquery);
      }
    } elseif (is_string($query)) {
//      echo $query."\n";
      $this->dbAdapter->query($query);
    }
  }

}

