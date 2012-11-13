<?php
namespace database;

/**
 * Column class
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
class Column extends Object {
  private $name;
  private $type;
  public $options = array(
    'length' => 0,
    'default' => null,
    'nullable' => true,
    'primary' => false,
    'auto_incremented' => false
  );
  
  function __construct($name, $type = 'varchar', array $options = array()) {
    $this->name = $name;
    $this->type = $type;
    $this->options = array_merge($this->options, $options);
  }
  
  function name() {
    return $this->name;
  }
  
  function type() {
    return $this->type;
  }
  
  function primary() {
    return $this->options['primary'];
  }
  
  function to_string() {
    return $this->name();
  }
}
?>