<?php
namespace database;

/**
 * Table builder class
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
class TableBuilder extends Object {
  use Connections;
  
  static function connection() {
    return static::establish_connection();
  }
  
  static function table($name) {
    return static::connection()->table($name);
  }
  
  function create_table($name, $block) {  
    $table = static::table($name);
    call_user_func($block, $table);
    $table->create();
    return $table;
  }
  
  function truncate_table($name) {
    if(static::connection()->table_exists($name)) {
      $table = static::table($name);
      $table->truncate();
    }
  }
  
  function drop_table($name) {
    if(static::connection()->table_exists($name)) {
      $table = static::table($name);
      $table->drop();
    }
  }
}
?>