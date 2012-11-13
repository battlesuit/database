<?php
namespace mysql;

/**
 * Basic mysql database connection
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
class Connection extends \database\Connection {
  
  /**
   * Query result object
   *
   * @access public
   * @var MySQLi_Result
   */
  public $query_result;
  
  /**
   * Mysql driver object
   *
   * @access protected
   * @var MySQLi
   */
  protected $driver;
  
  /**
   * Constructs a new mysql connection instance
   *
   * @access public
   * @param string $name
   * @param string $host
   * @param string $user
   * @param string $password
   * @param int $port
   */
  function __construct($name, $host, $user, $password, $port = 3306) {
    parent::__construct($name, $host, $user, $password, $port);
  }
  
  /**
   * Establish connection with access data
   *
   * @access public
   * @param string $database
   * @return boolean
   */  
  function establish($database = null) {
    $this->driver = \mysqli_connect($this->host, $this->user, $this->password);

    if(mysqli_connect_error()) {
      throw new \ErrorException("Failed establishing a MySQL connection on $host: (".mysqli_connect_errno().") ".mysqli_connect_error());
    }
    
    if(isset($database)) $this->select_database($database);
    return $this->established = true;
  }
  
  /**
   * Closes the mysql connection
   *
   * @access public
   */
  function close() {
    if($this->established) {
      $this->driver->close();
      $this->established = false;
    }
  }
  
  /**
   * Selecting a given database by name
   *
   * @access public
   * @param string $name
   */
  function select_database($name) {
    $this->driver->select_db($name);
    return parent::select_database($name);
  }
  
  /**
   * Executing a query
   * This method gets called by Connection::query()
   *
   * @access protected
   * @param string $sql
   */
  protected function execute_query($sql, array $options = array()) {  
    extract($options);

    if(isset($multi) and $multi !== false) { 
      $this->query_result = $this->driver->multi_query($sql);
    } else $this->query_result = $this->driver->query($sql);
    
    if($this->query_result === false) {
      throw new \ErrorException("Error in query : (".$this->driver->errno.") ".$this->driver->error);
    }
  }
  
  /**
   * Inserts a row with fielddata
   *
   * @access public
   * @param string $table
   * @param array $fields
   * @return int created_id()
   */   
  function create_table_row($table, array $fields = array()) {  
    $into = $this->into_clause($table, array_keys($fields));
    $values = $this->values_clause(array_values($fields));

    $this->query("INSERT $into $values");
    
    return $this->created_row_id();
  }
  
  /**
   * Updates row-fields for a table
   *
   * @access public
   * @param string $table
   * @param array $fields
   * @param array $options
   * @return Connection
   */  
  function update_table_rows($table, array $fields, array $options = array()) {
    $table = $this->name_to_sql($table);   
    $set = $this->set_clause($fields);   
    $sql = "UPDATE $table $set";
    
    extract($options);
    
    if(!empty($where)) {
      $sql .= " ".$this->where_clause($where);
    }
    
    if(!empty($order_by)) {
      $sql .= " ".$this->order_by_clause($order_by);
    }
    
    return $this->query($sql);
  }
  
  /**
   * Deletes rows from a table
   *
   * @access public
   * @param string $table
   * @param array $options
   * @return Connection
   */
  function delete_table_rows($table, array $options = array()) {
    $from = $this->from_clause($table);    
    $sql = "DELETE $from";
    extract($options);
    
    if(!empty($where)) {
      $sql .= " ".$this->where_clause($where);
    }
    
    if(!empty($order_by)) {
      $sql .= " ".$this->order_by_clause($order_by);
    }
    
    return $this->query($sql);
  }
  
  /**
   * Reads rows from a table
   *
   * @access public
   * @param string $table
   * @param array $options
   * @return Rows
   */
  function read_table_rows($table, array $options = array()) {
    $table = $this->from_clause($table);
    extract($options);
    
    if(!empty($columns) and is_array($columns)) {
      $columns = $this->names_for_sql($columns);
    } else $columns = '*';
    
    $sql = "SELECT $columns $table";
    
    if(!empty($where)) {
      $sql .= " ".$this->where_clause($where);
    }
    
    if(!empty($order_by)) {
      $sql .= " ".$this->order_by_clause($order_by);
    }
    
    if(!empty($limit)) {
      $sql .= " ".$this->limit_clause($limit);
    }
    
    return $this->find_by_sql($sql);
  }
  
  /**
   * Collects the query-result into a result-table
   *
   * @access public
   * @return Rows
   */
  function result_table() {
    return new Rows($this->query_result);
  }
  
  /**
   * Collects a sql-query into a result-table
   *
   * @access public
   * @param string $sql
   * @return Rows
   */
  function find_by_sql($sql) {
    return $this->query($sql)->result_table();
  }
  
  /**
   * Builds a column instance from a row result
   *
   * @access public
   * @param array $row
   * @return database\Column
   */
  function row_to_column(array $row) {
    $name = $row['Field'];
    $type = $row['Type'];
    $options = array(
      'nullable' => !($row['Null'] == 'NO'),
      'primary' => isset($row['Key']) ? $row['Key'] == 'PRI' : false,
      'default' => $row['Default'],
      'auto_incremented' => isset($row['Extra']) ? $row['Extra'] == 'auto_increment' : false
    );
    
    if(strpos($type, '(') !== false) {
      list($type, $length) = explode('(', $type);
      $options['length'] = (int)rtrim($length, ')');
    }
    
    return new \database\Column($name, $type, $options);
  }
  
  /**
   * Collects all table columns
   *
   * @access public
   * @param string $table
   * @param callable $each
   * @return array
   */
  function collect_table_columns($table, $each = null) {
    $from = $this->from_clause($table);
    $rows = $this->find_by_sql("SHOW COLUMNS $from");
    $columns = array();
    
    foreach($rows as $row) {
      $column = $this->row_to_column($row);    
      if(isset($each)) $columns[] = call_user_func($each, $column);
      else $columns[] = $column;
    }
    
    return $columns;
  }
  
  /**
   * Creates a table
   *
   * @access public
   * @param string $table
   * @param array $columns
   * @param array $options
   * @return Connection
   */
  function create_table($table, array $columns = array(), array $options = array()) {    
    $sql = "CREATE TABLE ".$this->table_to_sql($table, $columns, $options);
    return $this->query($sql);
  }
  
  /**
   * Drops the given table
   *
   * @access public
   * @param string $table
   * @return Connection
   */
  function drop_table($table) {
    return $this->query("DROP TABLE `$table`");
  }
  
  /**
   * Renames a table
   *
   * @access public
   * @param string $from
   * @param string $to
   * @return Connection
   */
  function rename_table($from, $to) {
    return $this->query("RENAME TABLE `$from` TO `$to`");
  }
  
  /**
   * Truncates the given table
   *
   * @access public
   * @param string $table
   * @return Connection
   */
  function truncate_table($table) {
    return $this->query("TRUNCATE TABLE `$table`");
  }
  
  /**
   * Counts all rows of a given table
   *
   * @access public
   * @param string $table
   * @return int
   */
  function count_table_rows($table) {
    $result = $this->find_by_sql("SELECT COUNT(*) FROM `$table`");
    if($result->any()) return (int)$result[0]['COUNT(*)'];
    else return 0;
  }
  
  /**
   * Does the given table exists?
   *
   * @access public
   * @param string $table
   * @return boolean
   */
  function table_exists($table) {
    $db = $this->selected_database;
    if(empty($db)) trigger_error("Cannot test table existance of $table. No database selected", E_USER_ERROR);
    return $this->find_by_sql("SHOW TABLES WHERE `Tables_in_$db` = '$table'")->any();
  }

  /**
   * Creates a database
   *
   * @access public
   * @param string $name
   * @param array $options
   * @return Connection
   */
  function create_database($name, array $options = array()) {
    $charset = $this->driver->get_charset();
    
    $character_set = $charset->charset;
    $collation = $charset->collation;
    $sql = "CREATE DATABASE `$name`";
    extract($options);
    

    $sql .= " CHARACTER SET = '$character_set'";
    $sql .= " COLLATE = '$collation'";
    
    return $this->query($sql);
  }
  
  /**
   * Drops the selected or the given databae
   *
   * @access public
   * @param string $name
   * @return Connection
   */
  function drop_database($name = null) {
    if(!isset($name)) $name = $this->selected_database;
    if(empty($name)) trigger_error("Cannot drop database $name. No database given", E_USER_ERROR);
    return $this->query("DROP DATABASE `$name`");
  }
  
  /**
   * Does the given database exists?
   * 
   * @access public
   * @param string $name
   * @return boolean
   */
  function database_exists($name) {
    return $this->find_by_sql("SHOW DATABASES WHERE `Database` = '$name'")->any();
  }
  
  /**
   * Alter table
   *
   * @access public
   * @param string $table
   * @param array $options
   * @return Connection
   */
  function alter_table($table, array $options = array()) {
    $character_set = $this->character_set();
    extract($options);
    return $this->query("ALTER TABLE `$table` DEFAULT CHARACTER SET '$character_set'");
  }
  
  /**
   * Affected rows of the last query
   *
   * @access public
   * @return int
   */
  function affected_rows() {
    $result = $this->find_by_sql("SELECT ROW_COUNT()");
    return count($result);
  }
  
  /**
   * Returns or sets the name of the character set
   *
   * @access public
   * @param string $name
   * @return string
   */
  function character_set($name = null) {   
    if(isset($name)) {
      $this->driver->set_charset($name);
    }

    return $this->driver->character_set_name();
  }
  
  /**
   * Returns the last id generated by create_table_row()
   *
   * @access public
   * @return int
   */
  function created_row_id() {
    return (int)$this->driver->insert_id;
  }
  
  /**
   * Converting table information to valid sql definition
   *
   * @access public
   * @param string $name
   * @param array $columns
   * @param array $options
   * @return string
   */
  function table_to_sql($table, array $columns = array(), array $options = array()) {
    $sql = "`$table`";

    $primary_column = null;
    $create_sql = " ".static::separate_each(', ', $columns, function($column) {
      return $this->column_to_sql($column->name(), $column->type(), $column->options);
    });
    
    foreach($columns as $column) {
      if($column->primary() == true) {
        $primary_column = $column->name();
        break;
      }
    }
    
    if(isset($primary_column)) {
      $create_sql .= ", PRIMARY KEY (`$primary_column`)";
    }

    if(!empty($create_sql)) $sql .= " ($create_sql)";
    
    extract($options);
    
    if(!empty($engine)) {
      $sql .= " ENGINE = $engine";
    }

    if(!empty($auto_increment)) {
      $sql .= "AUTO_INCREMENT = $auto_increment";
    }

    if(!empty($character_set)) {
      $sql .= "CHARACTER SET = $character_set";
    }

    return $sql;
  }
  
  /**
   * Converting column information to valid sql definition
   *
   * @access public
   * @param string $name
   * @param string $type
   * @param array $options
   * @return string
   */
  function column_to_sql($name, $type, array $options = array()) {
    $sql = null;
    $nullable = true;
    $auto_incremented = false;
    
    extract($options);
    
    if(!empty($length)) {
      $sql .= "($length)";
    }

    if($nullable === false) {
      $sql .= " NOT NULL";
    }

    if($auto_incremented === true) {
      $sql .= " AUTO_INCREMENT";
    }

    return "`$name` $type$sql";
  }
  
  /**
   * Generates an LIMIT clause
   *
   * @access public
   * @param string $limit
   * @return string
   */  
  function limit_clause($limit) {
    if(is_numeric($limit) or is_int($limit)) {
      return "LIMIT $limit";
    }
  }
  
  /**
   * Generates an OFFSET clause
   *
   * @access public
   * @param string $offset
   * @return string
   */
  function offset_clause($offset) {
    return "OFFSET $offset";
  }
  
  /**
   * Generate ORDER BY clause
   *
   * @access public
   * @param string $field_name
   * @param string $type
   * @return string
   */
  function order_by_clause($sort) {
    $field_name = $sort;
    $sort_type = 'asc';
    if(preg_match('/([a-z0-9_]+) (asc|desc)/i', $sort, $matches) === 1) {
      list(, $field_name, $sort_type) = $matches;
    }

    $sql = $field_name;   
    if(!empty($sql) and !empty($sort_type)) $sql .= " ".strtoupper($sort_type);

    return "ORDER BY $sql";
  }

  /**
   * Generate INTO clause
   *
   * @access public
   * @param string $table_name
   * @param mixed $fields
   * @return string
   */
  function into_clause($table, $fields) {
    $table = $this->name_to_sql($table);
    $fields = $this->names_to_sql($fields);
    return "INTO $table ($fields)";
  }

  /**
   * Generate VALUES clause
   *
   * @access public
   * @param mixed $values
   * @return string
   */
  function values_clause($values) {
    $values = $this->values_to_sql($values);
    return "VALUES ($values)";
  }

  /**
   * Generate a SET clause
   *
   * @access public
   * @param mixed $fields
   * @return string
   */
  function set_clause($fields) {
    $assignments = $this->assignments_to_sql($fields);
    return "SET $assignments";
  }

  /**
   * Generate a WHERE clause
   *
   * @access public
   * @param mixed $conditions
   * @param string $separator
   * @return string
   */
  function where_clause($conditions, $separator = 'AND') {
    if(is_array($conditions)) {
      $connection = $this;
      $conditions = static::separate_each(" $separator ", $conditions, function($value, $name) use($connection) {
        return $connection->assignment_to_sql($name, $value);
      });
    }

    return "WHERE $conditions";
  }

  /**
   * Generate a FROM clause
   *
   * @access public
   * @param mixed $tables
   * @return string
   */
  function from_clause($tables) {
    if(is_array($tables)) $tables = $this->names_to_sql($tables);
    else $tables = $this->name_to_sql($tables);

    return "FROM $tables";
  }
}
?>