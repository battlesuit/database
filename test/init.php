<?php
namespace test_bench {
  require_once 'Test/load.php';
  require_once '/../load.php';
  
  require 'database/register_connections.php';
  require_once 'bench.php';
  
  # register default autoload functionality
  spl_autoload_register(function($class) {
    return spl_autoload(preg_replace('/(\p{Ll})(\p{Lu})/', '$1_$2', $class), '.php');
  });
}
?>