<?php
interface Mooduino_Db_Migrations_MigrationProvider_Interface {
	public function generate($name, $env='development');
	public function redo($step=1, $env='development');
	public function current($env='development');
	public function update($to='latest', $env='development');
	public function show($revision='all', $env='development');
}
