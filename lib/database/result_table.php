<?php
namespace database;

/**
 * Database result table
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
abstract class ResultTable extends Object implements \ArrayAccess, \Iterator, \Countable {
  
  /**
   * Stored row-stack
   *
   * @access protected
   * @var array
   */
  protected $stack = array();
  
  /**
   * Internal index pointer
   *
   * @access protected
   * @var int
   */
  protected $index_position;
  
  /**
   * Abstract declarations
   * 
   */
  abstract function fetch_row();
  abstract function seek_row($index);
  abstract function count();
  abstract function to_array();
  
  /**
   * Returns row at an index position
   *
   * @access public
   * @param int $index
   * @return array
   */
  function row_at($index) {
    if(isset($this->stack[$index])) return $this->stack[$index];
    
    $this->seek_row($index);
    return $this->stack[$index] = $this->fetch_row();
  }
  
  /**
   * Test for row existence
   *
   * @access public
   * @param int $index
   * @return boolean
   */
  function row_exists($index) {
    return $index < $this->count();
  }
  
  /**
   * Is there any row?
   *
   * @access public
   * @return boolean
   */
  function any() {
    return count($this) > 0;
  }
  
  /**
   * Returns the current index postion
   *
   * @access public
   * @return int
   */
  function current_index() {
    return $this->index_position;
  }

  /**
   * Iterator::rewind() implementation
   *
   * @access public
   */
  function rewind() {
    $this->seek_row(0);
    $this->index_position = 0;
  }

  /**
   * Iterator::current() implementation
   *
   * @access public
   * @return mixed
   */
  function current() {
    return $this->fetch_row();
  }

  /**
   * Iterator::key() implementation
   *
   * @access public
   * @return int
   */
  function key() {
    return $this->index_position;
  }

  /**
   * Iterator::next() implementation
   *
   * @access public
   */
  function next() {
    $this->index_position++;
  }

  /**
   * Iterator::valid() implementation
   *
   * @access public
   * @return boolean
   */
  function valid() {
    return $this->row_exists($this->index_position);
  }

  
  /**
   * ArrayAccess::offsetSet() implementation
   *
   * @access public
   * @param int $index
   * @param mixed $value
   */
  function offsetSet($index, $value) {
    trigger_error("Offset writing on result tables is not supported", E_USER_NOTICE);
  }

  /**
   * ArrayAccess::offsetExists() implementation
   *
   * @access public
   * @param int $index
   * @return boolean
   */
  function offsetExists($index) {
    return $this->row_exists($index);
  }

  /**
   * ArrayAccess::offsetUnset() implementation
   *
   * @access public
   * @param int $index
   */
  function offsetUnset($index) {
    trigger_error("Offset unsetting on result tables is not supported", E_USER_NOTICE);
  }

  /**
   * ArrayAccess::offsetGet() implementation
   *
   * @access public
   * @param int $index
   * @return array
   */
  function offsetGet($index) {
    return $this->row_at($index);
  }
}
?>