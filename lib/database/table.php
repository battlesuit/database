<?php
namespace database;

/**
 * This is like an active table attempt
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
class Table extends Object {

  /**
   * Engine name
   *
   * @access public
   * @var string
   */
  private $engine = null;
  
  /**
   * Table name
   *
   * @access private
   * @var string
   */
  private $name;
  
  static $column_types = array(
    'integer' => 'int',
    'string' => 'varchar',
    'text' => 'text',
    'datetime' => 'datetime'
  );
  
  /**
   * Connection name to use
   *
   * @access private
   * @var string
   */
  private $connect_to;
  private $columns = array();
  private $columns_collected = false;
  
  private $auto_increment = 0;
  
  function __construct($name, $connect_to = null) {
    $this->name = $name;
    $this->connect_to = $connect_to;
  }
  
  function __set($name, array $properties) {
    $options = array();
    if(isset($properties[0])) {
      $type = $properties[0];
    }
    
    if(isset($properties[1])) {
      if(is_array($properties[1])) {
        $options = array_merge($options, $properties[1]);
      } else {
        $options['length'] = $properties[1];
      }
    }
    
    if(isset($properties[2]) and is_array($properties[2])) {
      $options = array_merge($options, $properties[2]);
    }
    
    return $this->add_column($name, $type, $options);
  }
  
  function __get($name) {
    return $this->column($name);
  }
  
  function name() {
    return $this->name;
  }
  
  function add_column($name, $type, array $options = array()) {
    return $this->columns[$name] = new Column($name, $type, $options);
  }
  
  function column($name) {
    return $this->columns[$name];
  }
  
  function connection() {
    return Connections::establish_connection($this->connect_to);
  }
  
  function fill($csv_file) {
    if(!file_exists($csv_file)) return;
    
    $connection = $this->connection();
    $handle = fopen($csv_file, 'r');
    $fields = fgetcsv($handle);
    
    if(!empty($fields)) {
      $markers = join(',', array_fill(0,count($fields),'?'));

      while(($values = fgetcsv($handle))) {
        foreach($values as $index => $value) {
          if(empty($value)) {
            unset($fields[$index]);
            unset($values[$index]);
          }
        }
        
        $this->push(array_combine($fields, $values));
      }
    }
    
    fclose($handle);
  }
  
  /**
   * Returns added or pulled table columns
   *
   * @access public
   * @return array
   */
  function columns() {
    if(!$this->columns_collected) $this->collect_columns();
    return $this->columns;
  }
  
  function collect_columns($each = null) {
    $this->columns = $this->connection()->collect_table_columns($this->name(), $each);
    $this->columns_collected = true;
  }
  
  function pull(array $options = array()) {
    return $this->connection()->read_table_rows($this->name(), $options);
  }
  
  function put(array $fields, array $options = array()) {
    return $this->connection()->update_table_rows($this->name(), $fields, $options);
  }
  
  function push(array $fields) {
    return $this->connection()->create_table_row($this->name(), $fields);
  }
  
  function del(array $options = array()) {
    return $this->connection()->delete_table_rows($this->name(), $options);
  }
  
  function exists() {
    return $this->connection()->table_exists($this->name());
  }
  
  function save() { 
    if($this->exists()) {
      # update
      $this->update();
    } else {
      # create
      $this->create();
    }
  }
  
  function update() {
    $this->connection()->alter_table($this->name(), $this->columns);
  }
  
  function create() {
    $this->connection()->create_table($this->name(), $this->columns, array('engine' => $this->engine, 'auto_increment' => $this->auto_increment));
  }
  
  function drop() {
    $this->connection()->drop_table($this->name());
  }
  
  function truncate() {
    $this->connection()->truncate_table($this->name());
  }
  
  function count_rows() {
    return $this->connection()->count_table_rows($this->name());
  }
  
  function to_string() {
    return $this->name();
  }
  
  function to_sql() {
    return $this->connection()->table_to_sql($this->name(), $this->columns, array('engine' => $this->engine, 'auto_increment' => $this->auto_increment));
  }
}
?>