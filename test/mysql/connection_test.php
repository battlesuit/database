<?php
namespace mysql;

class ConnectionTest extends TestCase {
  function set_up() {
    $this->connection = static::connection();
    $this->create_tables();
    
    $exists = $this->connection->table_exists('_new_table_');
    if($exists) $this->drop_table('_new_table_');
  }
  
  function test_simple_query() {
    $result = $this->connection->query("SELECT * FROM users")->query_result;
    $this->assert_equality($result->num_rows, 4);
  }
  
  function test_create() {
    $id = $this->connection->create_table_row('products', array('name' => 'shower gel', 'amount' => 200));
    $this->assert_int($id);
    $this->assert_equality($id, 1);
    $this->assert_equality($this->connection->created_row_id(), 1);
  }
  
  function test_type() {
    $this->assert_equality($this->connection->type(), 'mysql');
  }
  
  function test_delete_all() {
    $this->connection->delete_table_rows('users');
  }
  
  function test_delete_where() {
    $this->connection->delete_table_rows('users', ['where' => ['id' => 2]]);
  }
  
  function test_delete_order_by() {
    $this->connection->delete_table_rows('users', ['order_by' => 'login']);
  }
  
  function test_update_all() {
    $this->connection->update_table_rows('employees', ['postal_code' => '22000']);
  }
  
  function test_update_where() {
    $this->connection->update_table_rows('employees', ['nick_name' => 'mel'], ['where' => ['id' => 2]]);
  }
  
  function test_update_order_by() {
    $this->connection->update_table_rows('employees', ['postal_code' => '22000'], ['order_by' => 'last_name']);
  }
  
  function test_read_all() {
    $this->connection->read_table_rows('employees');
  }
  
  function test_read_where() {
    $this->connection->read_table_rows('employees', ['where' => ['id' => 5]]);
  }
  
  function test_read_order_by() {
    $this->connection->read_table_rows('employees', ['order_by' => 'last_name']);
  }
  
  function test_read_limit() {
    $this->connection->read_table_rows('employees', ['limit' => 2]);
  }
  
  function test_collect_result() {
    $this->connection->query('SELECT * FROM employees');
    $result = $this->connection->result_table();
    $this->assert_instanceof($result, 'mysql\Rows');
  }
  
  function test_find_by_sql() {
    $result = $this->connection->find_by_sql("SELECT * FROM users");
    $this->assert_equality($result[1]['login'], 'mel');
  }
  
  function test_collect_table_columns() {
    $columns = $this->connection->collect_table_columns('employees');
    $this->assert_equality(count($columns), 5);
  }
  
  function test_count_table_rows() {
    $count = $this->connection->count_table_rows('users');
    $this->assert_int($count);
    $this->assert_equality($count, 4);
  }
  
  function test_create_table() {
    //$this->connection->create_table('_new_table_');
  }
  
  function test_alter_table() {
    
  }
  
  function test_affected_rows() {
    
  }
  
  function test_read_character_set() {
    $charset = $this->connection->character_set();
  }
}
?>