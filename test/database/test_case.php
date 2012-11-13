<?php
namespace database;

class TestCase extends \test_case\Unit {
  static $selected_database = 'test';
  static $connect_to;
  
  function boot() {
    $this->recreate_database();
  }
  
  function shut() {
    $this->connection()->close();
  }
  
  function recreate_database() {
    $connection = static::connection();
    $connection->establish();
    
    $exists = $connection->database_exists(static::$selected_database);
    if(!$exists) $connection->create_database(static::$selected_database);
    else {
      $connection->drop_database(static::$selected_database);
      $connection->create_database(static::$selected_database);
    }
    
    $connection->select_database(static::$selected_database);
    
    $this->create_tables();
  }
  
  function read_file($file) {
    if(!file_exists($file)) trigger_error("File not found: $file");
    return file_get_contents($file);
  }
  
  function sql_dir() {
    $type = static::connection()->type();
    return __DIR__."/../$type/tables";
  }
  
  function sql_for_table($name) {
    $file = $this->sql_dir()."/$name.sql";
    if(!file_exists($file)) trigger_error("SQL-File not found: $file");
    return file_get_contents($file);
  }
  
  function load_table($name) {
    $connection = static::connection();
    if($connection->table_exists($name)) $connection->drop_table($name);
    
    $connection->query($this->sql_for_table($name));
    $this->fill_table($name);
  }
  
  function create_tables() {
    $connection = static::connection();
    $type = $connection->type();
    $table_files = glob(__DIR__."/../$type/tables/*.sql");
    
    foreach($table_files as $file) {
      $this->load_table(pathinfo($file)['filename']);
    }
  }
  
  function fill_table($table) {
    $csv_file = __DIR__."/../fixtures/table_data/$table.csv";
    if(!file_exists($csv_file)) return;
    
    $connection = static::connection();
    $handle = fopen($csv_file, 'r');
    $fields = fgetcsv($handle);
    
    if(!empty($fields)) {
      $markers = join(',', array_fill(0,count($fields),'?'));
      $table = $connection->quote_name($table);
      
      foreach($fields as &$name) {
        $name = $connection->quote_name($name);
      }
      
      
      while(($values = fgetcsv($handle))) {
        foreach($values as $index => $value) {
          if(empty($value)) {
            unset($fields[$index]);
            unset($values[$index]);
          }
        }
        
        $fields_sql = join(',', $fields);
        $values = "'".implode("', '", $values)."'";

        $connection->query("INSERT INTO $table($fields_sql) VALUES($values)");
      }
    }
    
    fclose($handle);
  }
  
  static function connection() { 
    return \database\Connections::establish_connection(static::$connect_to);
  }
}
?>