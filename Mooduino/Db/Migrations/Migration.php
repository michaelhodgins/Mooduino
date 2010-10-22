<?php
/*  Copyright 2010  Michael Hodgins  (email : michael_hodgins@hotmail.com)

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
 /**
  * Returns true if this migration should execute in the given environment.
  * @param string $env
  * @return boolean
  */
 public function runsInEnvironment($env);
}
