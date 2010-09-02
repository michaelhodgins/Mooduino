<?php
interface Mooduino_Db_Migrations_Migration {
	public function up();
	public function down();
	public function getName();
}
