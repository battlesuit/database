<?php
namespace mysql;

class DatabaseMigration extends \database\TableBuilder {
  static $connection = 'mysql-test';
  
  function up() {
    $users = $this->create_table('users', function($t) {
      $t->write_column('id', 'int', array('length' => 11, 'primary' => true, 'auto_incremented' => true));
      $t->write_column('name', 'varchar', array('length' => 255));
    });
  }
  
  function reset() {
    $this->truncate_table('users');
  }
  
  function down() {
    $this->drop_table('users');
  }
}
?>