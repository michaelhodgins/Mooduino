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


abstract class Mooduino_Db_Migrations_Migration_Abstract implements Mooduino_Db_Migrations_Migration {

  /**
   * The migration name.
   * @var string
   */
  private $name = '';
  /**
   * The migration timestamp.
   * @var int
   */
  private $timestamp = 0;
  /**
   * When the migration was last processed.
   * @var int
   */
  private $processed = null;
  /**
   * The step position of this migration.
   * @var int
   */
  private $step;

  /**
   * Instantiates the migration using the given name and timestamp. The step
   * position must be set after the constructor is called, if it is known.
   * The exact value of the timestamp is platform dependent; if the platform
   * supports the gettimeofday() system call, then it will be the number of
   * milliseconds since unix epoch; otherwise it will be the more standard
   * number of seconds since that time.
   * @param string $name
   * @param int $timestamp
   */
  public function __construct($name, $timestamp) {
    $this->name = $name;
    $this->timestamp = $timestamp;
  }

  /**
   * Returns the migration's name.
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Returns the migration's timestamp.
   * @return int
   */
  public function getTimestamp() {
    return $this->timestamp;
  }

  /**
   * Returns the timestamp of when the migration was last processed.
   * @return int
   */
  public function getProcessedTimestamp() {
    return $this->processed;
  }

  /**
   * Sets the timestamp of when the migration was last processed.
   * @param int $timestamp
   */
  public function setProcessedTimestamp($timestamp) {
    $this->processed = intval($timestamp);
  }

  /**
   * Sets the step position of the migration.
   * @param int $step
   */
  public function setStep($step) {
    $this->step = intval($step);
  }

  /**
   * Returns the step position of the migration.
   * @return int
   */
  public function getStep() {
    return $this->step;
  }

  /**
   * Returns a string representation of the migration.
   * @return string
   */
  public function __toString() {
    return self::toString($this);
  }

  /**
   * Returns a string representation of the given migration.
   * @param Mooduino_Db_Migrations_Migration $migration
   * @return string
   */
  public static function toString(Mooduino_Db_Migrations_Migration $migration) {
    return sprintf(
        "%d\t%-20s\t%s\t%s",
        $migration->getStep(),
        $migration->getName(),
        $migration->getTimestamp(),
        $migration->getProcessedTimestamp() == 0 ? 'Never' : new Zend_Date($migration->getProcessedTimestamp())
    );
  }

  /**
   * This method always returns true, meaning that this migration should run
   * in all environments. The method should be overridden if another value
   * is needed (which is to say, if the migration should only be executed in
   * a certain environment).
   * @param string $env
   * @return boolean
   */
  public function runsInEnvironment($env) {
    return true;
  }

}
