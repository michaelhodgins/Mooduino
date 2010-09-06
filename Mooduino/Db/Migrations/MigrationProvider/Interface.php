<?php
interface Mooduino_Db_Migrations_MigrationProvider_Interface {
  
  /**
   * Generates a migration with the given name.
   * @param string $name
   * @param string $env
   * @param string $baseClass
   */
	public function generate($name, $env='development', $baseClass = 'default');

  /**
   * Rolls back and reapplies the number of migrations given by $step.
   * @param int $step
   * @param string $env
   */
	public function redo($step=1, $env='development');

  /**
   * Rolls back the number of migrations given by $step.
   * @param int $step
   * @param string $env
   */
 public function undo($step=1, $env='development');

  /**
   * Details the current migration.
   * @param string $env
   */
	public function current($env='development');

  /**
   * Applies migrations. If $to is 'latest', all unexecuted migrations are
   * executed. If $to is an integer, migrations are applied or rolled back
   * to leave the database at the given step.
   * @param int|string $to
   * @param string $env
   */
	public function update($to='latest', $env='development');
  /**
   * Lists migrations in the project. If $revision = 'list', all migrations are
   * shown. If $revison is an integer, the migration at that step is shown.
   * Lastly, if $revision is a string other than 'list', a revision with that
   * name, if one exists, is shown.
   * @param int|string $revision
   * @param string $env
   */
	public function show($revision='all', $env='development');
 /**
  * Rolls back all migrations.
  * @param strint $env
  */
 public function clear($env='development');
}
