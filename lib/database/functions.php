<?php
/**
 * Database helper functions
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
namespace database {

  /**
   * Shortcut for Connections::register_connection()
   *
   * @param string $name
   * @param mixed $attributes_or_url
   * @param array $options
   */
  function register_connection($name, $attributes_or_url, array $options = array()) {
    Connections::register_connection($name, $attributes_or_url, $options);
  }
}
?>