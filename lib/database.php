<?php
/**
 * Initializes the database suit
 * This core package can be used as an isolate unit from Suitcase. Just apply
 * your own autoloading functionality.
 *
 * PHP Version 5.3+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
namespace {
  
  # register default autoload functionality
  spl_autoload_register(function($class) {
    return spl_autoload(preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', $class), '.php');
  });
  
  # import global functions
  require_once 'database/functions.php';
}
?>