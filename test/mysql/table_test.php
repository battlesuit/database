<?php
namespace mysql;
use database\Table;

class TableTest extends TestCase {
  function set_up() {
    $this->table = new Table('people', 'mysql-test');
  }
  
  function test_name() {
    $this->assert_equality($this->table->name(), 'people');
  }
  
  function test_columns() {
    $columns = $this->table->columns();
  }
  
  function test_push() {
    $this->table->push(['first_name' => 'Robert', 'age' => 67]);
  }
  
  function test_put() {
    $this->table->put(['first_name' => 'Otto'], ['where' => ['id' => 2]]);
  }
  
  function test_pull() {
    $this->table->pull();
  }
  
  function test_del() {
    $this->table->del(['where' => ['id' => 4]]);
  }
  
  function test_drop() {
    $this->table->drop();
    $this->create_tables();
  }
  
  function test_truncate() {
    $this->table->truncate();
    $this->create_tables();
  }
  
  function test_count_rows() {
    $this->assert_equality($this->table->count_rows(), 5);
  }
  
  function test_to_string() {
    $this->assert_equality("$this->table", 'people');
  }
  
  function test_exists() {
    $this->assert_true($this->table->exists());
  }
}
?>