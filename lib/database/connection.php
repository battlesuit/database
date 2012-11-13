<?php
namespace database;

/**
 * Main abstract connection class
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
abstract class Connection extends Object {
  
  /**
   * Last query string written by query() method
   *
   * @access protected
   * @var string
   */
  protected $query_string;
  
  /**
   * Establishment status of this connection
   *
   * @access protected
   * @var boolean
   */
  protected $established = false;
  
  /**
   * Connection identifier
   *
   * @access protected
   * @var string
   */
  protected $name;
  
  /**
   * Connection host
   * 
   * @access protected
   * @var string
   */
  protected $host;
  
  /**
   * Connection user
   * 
   * @access protected
   * @var string
   */  
  protected $user;
  
  /**
   * Connection password
   * 
   * @access protected
   * @var string
   */
  protected $password;
  
  /**
   * Connection port
   * 
   * @access protected
   * @var int
   */
  protected $port;
  
  /**
   * Selected database
   * 
   * @access protected
   * @var string
   */
  protected $selected_database;
  
  /**
   * Table storage by name filled by table() method
   *
   * @access private
   * @var array
   */
  private $tables = array();
  
  /**
   * Quote for sql names used in queries
   *
   * @access public
   * @var string
   */
  protected static $name_quotation = '`';
  
  /**
   * Constructs a connection instance
   *
   * @access public
   * @param string $name
   * @param string $host
   * @param string $user
   * @param string $password
   * @param int $port
   */
  function __construct($name, $host, $user, $password, $port = 0) {
    $this->name = $name;
    $this->host = $host;
    $this->user = $user;
    $this->password = $password;
    $this->port = $port;
  }
  
  /**
   * Load is called by Connections trait
   *
   * @static
   * @access public
   * @param string $name
   * @param string $host
   * @param string $user
   * @param string $password
   * @return Connection
   */
  static function load($name, $host, $user, $password, $port = 0) {
    return new static($name, $host, $user, $password, $port);
  }
  
  /**
   * Reads the connection name
   *
   * @access public
   * @return string
   */  
  function name() {
    return $this->name;
  }
  
  /**
   * Reads the connection host
   *
   * @access public
   * @return string
   */  
  function host() {
    return $this->host;
  }
  
  /**
   * Reads the connection user
   *
   * @access public
   * @return string
   */  
  function user() {
    return $this->user;
  }
  
  /**
   * Reads the connection password
   *
   * @access public
   * @return string
   */  
  function password() {
    return $this->password;
  }
  
  /**
   * Reads the connection port
   *
   * @access public
   * @return int
   */  
  function port() {
    return $this->port;
  }
  
  /**
   * Returns the connection type from namespace
   * 
   * @access public
   * @return string
   */
  function type() {
    $class = get_called_class();
    return substr($class, 0, strrpos($class, '\\'));
  }
  
  /**
   * Main query caller method
   * Runs abstract execute_query() method which must be defined in childclasses
   *
   * @access public
   * @param string $sql
   * @param array $options
   * @return Connection (self)
   */
  function query($sql, array $options = array()) {
    $this->query_string = $sql;
    $this->execute_query($sql, $options);
    return $this;
  }
  
  /**
   * Reads the last query string
   *
   * @access public
   * @return string
   */
  function query_string() {
    return $this->query_string;
  }
  
  /**
   * Selecting a given database
   *
   * @access public
   * @param string $name
   */
  function select_database($name) {
    $this->selected_database = $name;
  }
  
  /**
   * Returns the selected database for this connection
   *
   * @access public
   * @return string
   */
  function selected_database() {
    return $this->selected_database;
  }
  
  /**
   * Returns a database table instance
   *
   * @access public
   * @return Table
   */
  function table($name) {
    $db = $this->selected_database;
    if(empty($db)) trigger_error("Cannot build table $name. No database selected for $this connection");
    if(isset($this->tables[$db][$name])) return $this->tables[$db][$name];

    return $this->tables[$db][$name] = new Table($name, $this->name());
  }
  
  /**
   * Check if connection was established
   *
   * @access public
   * @return boolean
   */
  function established() {
    return $this->established;
  }

  /**
   * Prepares names for sql statements
   *
   * @access public
   * @param string $name
   * @return string
   */
  function name_to_sql($name) {
    return $this->quote_name($name);
  }

  /**
   * Prepares values for sql statements
   *
   * @access public
   * @param mixed $value
   * @return string
   */
  function value_to_sql($value) {
    return $this->quote_value($value);
  }

  /**
   * Generate a comma-separated list of names for a sql statement
   *
   * @access public
   * @param mixed $names
   * @return string
   */
  function names_to_sql($names) {
    if(is_array($names)) {
      return static::separate_each_by_comma($names, function($name) {
        return $this->name_to_sql($name);
      });
    }

    return $names;
  }

  /**
   * Generate a comma-separated list of values for a sql statement
   *
   * @access public
   * @param mixed $values
   * @return string
   */
  function values_to_sql($values) {
    if(is_array($values)) {
      return static::separate_each_by_comma($values, function($value) {
        return $this->value_to_sql($value);
      });
    }

    return $values;
  }

  /**
   * Generate a comma-separated list of assignments for a sql statement
   *
   * @access public
   * @param mixed $values
   * @return string
   */
  function assignments_to_sql($assignments) {
    if(is_array($assignments)) {
      return static::separate_each_by_comma($assignments, function($value, $name) {
        return $this->assignment_to_sql($name, $value);
      });
    }

    return $assignments;
  }

  /**
   * Generates a sql name => value assignment
   *
   * @access public
   * @param string $name
   * @param mixed $value
   * @return string
   */
  function assignment_to_sql($name, $value) {
    return $this->name_to_sql($name)." = ".$this->value_to_sql($value);
  }

  /**
   * Quotes a name for sql statements
   *
   * @access public
   * @param mixed $name
   * @return string
   */
  function quote_name($name) {
    $name = (string)$name;
    if(empty($name)) return null;
    $q = static::$name_quotation;
    return ($name[0] === $q or $name[strlen($name)-1] === $q) ? $name : $q.$name.$q;
  }

  /**
   * Quotes a values for sql statements
   *
   * @access public
   * @param mixed $value
   * @return string
   */
  function quote_value($value) {
    if(is_int($value) or is_numeric($value)) {
      return $value;
    }

    return "'$value'";
  }
  
  /**
   * Array to separated string helper method
   *
   * @access protected
   * @param string $by
   * @param array $haystack
   * @param callable $each
   * @return string
   */
  protected static function separate_each($by, array $haystack, $each) {
    $string = $sep = null;
    
    foreach($haystack as $key => $value) {
      $string .= $sep.call_user_func($each, $value, $key);
      $sep = $by;
    }
    
    return $string;
  }
  
  /**
   * Internal helper method
   * Generates a comma-separated string via callback on each iteration
   *
   * @static
   * @access protected
   * @param array $haystack
   * @param callable $each
   * @return string
   */
  protected static function separate_each_by_comma(array $haystack, $each) {
    return static::separate_each(', ', $haystack, $each);
  }
  
  /**
   * To-string conversion returns the connection name
   *
   * @access public
   * @return string
   */
  function to_string() {
    return $this->name();
  }
  
  /**
   * Should execute and sql query
   *
   * @access protected
   * @param string $sql
   */
  abstract protected function execute_query($sql);
}
?>