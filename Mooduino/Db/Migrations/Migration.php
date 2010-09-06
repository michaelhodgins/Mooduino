<?php
interface Mooduino_Db_Migrations_Migration {

 /**
  * Returns the SQL statement(s) for this migration.
  * @return string|array[int]string
  */
	public function up();
 /**
  * Returns the SQL statement(s) to undo this migration.
  * @return string|array[int]string
  */
	public function down();
 /**
  * Returns the name of this migration.
  * @return string
  */
	public function getName();
 /**
  * Returns the timestamp of this migration.
  * @return int
  */
	public function getTimestamp();
 /**
  * Returns the timestamp of when this migration was last processed.
  * @return int
  */
	public function getProcessedTimestamp();
 /**
  * Sets the migration's step position.
  * @param int $step
  */
 public function setStep($step);
 /**
  * Returns the migration's step position.
  * @return int
  */
 public function getStep();
}
