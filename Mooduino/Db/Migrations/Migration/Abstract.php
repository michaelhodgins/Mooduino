<?php

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
        "%d\t%s\t%s\t%s",
        $migration->getStep(),
        $migration->getName(),
        $migration->getTimestamp(),
        $migration->getProcessedTimestamp()
    );
  }

}
