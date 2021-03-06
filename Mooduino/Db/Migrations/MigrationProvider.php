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

require_once 'Mooduino/Db/Migrations/MigrationProvider/Interface.php';
require_once 'Mooduino/Db/Migrations/Migration.php';
require_once 'Mooduino/Db/Migrations/Migration/Abstract.php';
require_once 'Mooduino/Db/Migrations/MigrationManager.php';

class Mooduino_Db_Migrations_MigrationProvider extends Zend_Tool_Project_Provider_Abstract implements Mooduino_Db_Migrations_MigrationProvider_Interface {

  /**
   * The profile. Lazy initialized.
   * @var Zend_Tool_Project_Profile
   */
  private $profile = null;
  /**
   * The config. Lazy initialized.
   * @var Zend_Config_Ini
   */
  private $config = null;
  /**
   * The database adapter. Lazy initialized.
   * @var Zend_Db_Adapter_Abstract
   */
  private $dbAdapter = null;
  /**
   * @var Mooduino_Db_Migrations_MigrationManager
   */
  private $manager = null;

  /**
   * Returns the Provider's name.
   * @return string
   */
  public function getName() {
    return 'Migration';
  }

  /**
   * Generates a migration with the given name.
   * @param string $name
   * @param string $env
   * @param string $baseClass
   */
  public function generate($name, $env='development', $baseClass = 'default') {
    $this->init($env);
    $this->_registry->getResponse()->appendContent(sprintf('Generating migration %s.', $name));
    try {
      $this->manager->generateMigration($name, $baseClass == 'default' ? null : $baseClass);
    } catch (Exception $e) {
      $this->_registry->getResponse()->appendContent($e->getMessage());
    }
  }

  /**
   * Rolls back and reapplies the number of migrations given by $step.
   * @param int $step
   * @param string $env
   */
  public function redo($step=1, $env='development') {
    $this->init($env);
    $this->manager->redo($step);
    $this->_registry->getResponse()->appendContent($this->manager->getMessage());
  }

  /**
   * Rolls back the number of migrations given by $step.
   * @param int $step
   * @param string $env
   */
  public function undo($step=1, $env='development') {
    $this->init($env);
    $this->manager->undo($step);
    $this->_registry->getResponse()->appendContent($this->manager->getMessage());
  }

  /**
   * Details the current migration.
   * @param string $env
   */
  public function current($env='development') {
    $this->init($env);
    $migration = $this->manager->getCurrentMigration();
    if (!is_null($migration)) {
      $this->_registry->getResponse()->appendContent(
          $this->migrationToString($migration)
      );
    } else {
      $this->_registry->getResponse()->appendContent('No migrations have been executed.');
    }
  }

  /**
   * Applies migrations. If $to is 'latest', all unexecuted migrations are
   * executed. If $to is an integer, migrations are applied or rolled back
   * to leave the database at the given step.
   * @param int|string $to
   * @param string $env
   */
  public function update($to='latest', $env='development') {
//    Zend_Debug::dump($to, '$to');
    $this->init($env);
    if ($to == 'latest') {
    	$this->manager->runTo(Mooduino_Db_Migrations_MigrationManager::TOP);
    } elseif (is_numeric($to)) {
    	$this->manager->runTo($to);
    } else {
      throw new Exception('Update to value should be a number or \'latest\'');
    }
    $this->_registry->getResponse()->appendContent($this->manager->getMessage());
  }

  /**
   * Lists migrations in the project. If $revision = 'list', all migrations are
   * shown. If $revison is an integer, the migration at that step is shown.
   * Lastly, if $revision is a string other than 'list', a revision with that
   * name, if one exists, is shown.
   * @param int|string $revision
   * @param string $env
   */
  public function show($revision='list', $env='development') {
    $this->init($env);
    if ($revision == 'list') {
      $migrations = $this->manager->getMigrations();
    } elseif (is_numeric($revision)) {
      $migrations = array($this->manager->getMigrationByStep($revision));
    } else {
      $migrations = array($this->manager->getMigrationByName($revision));
    }
    if (isset($migrations) && !is_null($migrations) && count($migrations) > 0) {
      $this->_registry->getResponse()->appendContent("Step\tName\t\t\tTimestamp\tProcessed");
      foreach ($migrations as $count => $migration) {
        $this->_registry->getResponse()->appendContent(
            $this->migrationToString($migration)
        );
      }
    }
  }

  /**
   * Rolls back all migrations.
   * @param string $env
   */
  public function clear($env = 'development') {
    $this->init($env);
    $this->manager->runTo(0);
    $this->_registry->getResponse()->appendContent($this->manager->getMessage());
  }

  /**
   * Returns a migration as a string representation. Uses the migration's own
   * __toString() method if it has one, otherwise, Mooduino_Db_Migrations_Migration
   * mthods are used to produce the string.
   * @param Mooduino_Db_Migrations_Migration $migration
   * @return string
   */
  private function migrationToString(Mooduino_Db_Migrations_Migration $migration) {
    if (method_exists($migration, '__toString')) {
      return $migration->__toString();
    } else {
      return Mooduino_Db_Migrations_Migration_Abstract::toString($migration);
    }
  }

  /**
   * Initializes the Provider.
   * @param string $env
   */
  private function init($env) {
    $path = realpath('./scripts/migrations');
    if ($path === false) {
      mkdir('./scripts/migrations', 0777, true);
      $path = realpath('./scripts/migrations');
      if ($path === false) {
        throw new Exception('Couldn\'t create the migration directory');
      }
    }
    $this->manager = new Mooduino_Db_Migrations_MigrationManager($path, $this->getDbAdapter($env));
    $this->manager->setEnvironmentName($env);
  }

  /**
   * Returns the project profile.
   * @return Zend_Tool_Project_Profile
   */
  private function getProfile() {
    if (is_null($this->profile)) {
      $this->profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
    }
    return $this->profile;
  }

  /**
   * Returns the project's config for the given environment.
   * @param string $env
   * @return Zend_Config_Ini
   */
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

  /**
   * Returns the database adapter.
   * @param string $env
   * @return Zend_Db_Adapter_Abstract
   */
  private function getDbAdapter($env) {
    if (is_null($this->dbAdapter)) {
      $dbConfig = $this->getConfig($env)->resources->db;
      $this->dbAdapter = Zend_Db::factory($dbConfig->adapter, $dbConfig->params);
    }
    return $this->dbAdapter;
  }

}
