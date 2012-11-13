<?php
namespace mysql;

class RowsTest extends TestCase {
  function set_up() {  
    $this->result = new Rows($this->connection()->query('SELECT * FROM employees')->query_result);
  }
  
  function test_counting_ability() {
    $this->assert_equality(count($this->result), 5);
  }
  
  function test_row_at() {
    $row = $this->result->row_at(2);
    $this->assert_key_exists('nick_name', $row);
    $this->assert_equality($row['nick_name'], 'titte');
  }
  
  function test_row_exists() {
    $exists = $this->result->row_exists(3);
    $this->assert_true($exists);
    
    $exists = $this->result->row_exists(5);
    $this->assert_false($exists);
  }
  
  function test_fetch_row() {
    while($row = $this->result->fetch_row()) {
      $this->assert_array($row);
    }
  }
  
  function test_offset_writing() {
    try {
      $this->result[2] = 'asd';
    } catch(\Exception $e) {
      return;
    }
    
    $this->fail_assertion('Offset writing is not allowed');
  }
  
  function test_fetch_column() {
    $fields = array('id', 'first_name', 'last_name', 'nick_name', 'postal_code');
    $i = 0;
    while($column = $this->result->fetch_column()) {
      $this->assert_object($column);
      $this->assert_equality($fields[$i], $column->name());
      $i++;
    }
  }
  
  function test_array_access() {
    $row = $this->result[1];
    $this->assert_key_exists('nick_name', $row);
    $this->assert_equality($row['nick_name'], 'mellypropelly');
  }
  
  function test_any() {
    $this->assert_true($this->result->any());
  }
  
  function test_iteration() {
    foreach($this->result as $row) {
      $this->assert_array($row);
    }
  }
  
  function test_to_array() {
    $array = $this->result->to_array();
    $this->assert_array($array);
    $this->assert_equality(count($array), 5);
    $this->assert_equality($array[0]['first_name'], 'thomas');
  }
}
?>