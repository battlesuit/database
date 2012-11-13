<?php
namespace mysql;

/**
 * Mysql row result collection
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
class Rows extends \database\ResultTable {
  static $column_type_map = array(
    1 => array('tinyint', 'bool'),
    2 => 'smallint',
    3 => 'integer',
    4 => 'float',
    5 => 'double',
    16 => 'bit',
    9 => 'mediumint',
    8 => array('bigint', 'serial'),
    246 => array('decimal', 'numeric', 'fixed'),
    10 => 'date',
    12 => 'datetime',
    7 => 'timestamp',
    11 => 'time',
    13 => 'year',
    254 => array('char', 'enum', 'set', 'binary'),
    253 => array('varchar', 'varbinary'),
    252 => array('text', 'mediumtext', 'tinytext', 'longtext', 'blob', 'tinyblob')
  );
  
  /**
   * Mysql result object
   *
   * @access private
   * @var MySQLi_Result
   */
  private $result;
  
  /**
   * Constructs a new result table instance
   *
   * @access public
   * @param MySQLi_Result $result
   */
  function __construct(\MySQLi_Result $result) {
    $this->result = $result;
  }
  
  /**
   * Fetches a mysql row
   *
   * @access public
   * @return array
   */
  function fetch_row() {
    return $this->result->fetch_assoc();
  }
  
  /**
   * Fetches a mysql column
   *
   * @access public
   * @return database\Column
   */
  function fetch_column() {
    $field = $this->result->fetch_field();
    if(!$field) return false;
    
    $options = array();
    if(!empty($field->length)) {
      $options['length'] = (int)$field->length;
    }
    
    if(!empty($field->def)) {
      $options['default'] = $field->def;
    }
    
    if($field->flags & \MYSQLI_PRI_KEY_FLAG) {
      $options['primary'] = true;
    }
    
    if($field->flags & \MYSQLI_AUTO_INCREMENT_FLAG) {
      $options['auto_incremented'] = true;
    }
    
    $types = (array)static::$column_type_map[$field->type];
    return new \database\Column($field->name, $types[0], $options);
  }
  
  /**
   * Seeks a column
   *
   * @access public
   * @param int $num
   */
  function seek_column($num) {
    $this->result->field_seek($num);
  }
  
  /**
   * Seeks a row
   *
   * @access public
   * @param int $num
   */
  function seek_row($num) {
    $this->result->data_seek($num);
  }
  
  /**
   * Count rows (used by Countable)
   *
   * @access public
   * @return int
   */
  function count() {
    return $this->result->num_rows;
  }
  
  /**
   * Convert to array
   *
   * @access public
   * @return array
   */
  function to_array() {
    return $this->result->fetch_all(MYSQLI_ASSOC);
  }
}
?>