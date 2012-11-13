<?php
namespace database;

/**
 * Manage database connections
 *
 * PHP Version 5.4+
 * @author Thomas Monzel <tm@apparat-hamburg.de>
 * @version $Revision$
 * @package Suitcase
 * @subpackage Database
 */
trait Connections {
  
  /**
   * All usable adapters
   * currently only mysql
   *
   * @static
   * @access public
   * @var array
   */
  static $connection_adapters = [
    'mysql' => 'mysql\Connection'
  ];
  
  /**
   * Connect to default "dev"
   *
   * @static
   * @access public
   * @var string
   */
  static $__default_connection = 'dev';
  
  /**
   * All registered connections by name as attributes
   *
   * @static
   * @access public
   * @var array
   */
  static $registered_connections = [];
  
  /**
   * All established connection instances by name
   *
   * @static
   * @access public
   * @var array
   */
  static $established_connections = [];
  
  static function default_connection($name = null) {
    if(isset($name)) return Connections::$__default_connection = $name;      
    return Connections::$__default_connection;
  }
  
  /**
   * 
   * 
   */
  static function connection_name() {    
    return isset(static::$connect_to) ? static::$connect_to : Connections::default_connection();
  }
    
  /**
   * Establishes aconnection by name
   *
   * @static
   * @access public
   * @param string $name
   * @return Connection Established connection instance
   */
  static function establish_connection($name = null) {
    $name = isset($name) ? $name : static::connection_name();
    if(Connections::connection_established($name)) return Connections::$established_connections[$name];
    if(empty(Connections::$registered_connections)) trigger_error("Cannot connect to $name connection. No connections registered", E_USER_ERROR);
    
    if(Connections::connection_registered($name)) {
      $attributes = Connections::$registered_connections[$name];
      
      # defaults
      $type = 'mysql';
      $port = 3306;
      $database = null;
      $options = array();
      
      if(is_array($attributes)) extract($attributes);
      else {
        
        $url_info = parse_url($attributes);
        if($url_info !== false) {
          
          extract($url_info);
          if(!empty($scheme)) $type = $scheme;
          if(!empty($pass)) $password = $pass;
          if(!empty($path)) $database = substr($path, 1);
          if(!empty($query)) {
            /*parse_str($query, $query_options);
            if(isset($query_options['default']) and $query_options['default'] == 'true') {
              $query_options['default'] = true;
            }
            $options = array_merge($options, $query_options);*/
          }
          
        } else trigger_error("Connection access url contains errors");
      }
    } else trigger_error("Connection $name is not registered");
    
    $adapter = static::load_connection($type, $name, $host, $user, $password, $port);
    $adapter->establish($database);
    
    /*extract($options);
    if(isset($default) and $default == true) {
      Connections::default_connection($name);
    }*/
    
    return Connections::$established_connections[$adapter->name()] = $adapter;
  }
  
  /**
   * Loads a connection class by attributes
   *
   * @static
   * @access public
   * @param string $type
   * @param string $name
   * @param string $host
   * @param string $user
   * @param string $password
   * @param int $port
   * @return Connection
   */
  static function load_connection($type, $name, $host, $user, $password, $port) {
    if(!isset(Connections::$connection_adapters[$type])) trigger_error("Connection type $type is not allowed");
    
    
    $adapter_class = Connections::$connection_adapters[$type];
    if(!class_exists($adapter_class, true)) trigger_error("Connection adapter class $adapter_class could not be loaded");
    return $adapter_class::load($name, $host, $user, $password, $port);
  }
  
  /**
   * Registers a connection
   *
   * @static
   * @access public
   * @return boolean
   */  
  static function register_connection($name, $attributes, array $options = array()) {
    if(isset($options['default']) and $options['default'] === true) {
      Connections::default_connection($name);  
    }
    
    Connections::$registered_connections[$name] = $attributes;
  }
  
  /**
   * Test if the given connection was registered
   *
   * @static
   * @access public
   * @return boolean
   */  
  static function connection_registered($name) {
    return array_key_exists($name, Connections::$registered_connections);
  }
  
  /**
   * Test if the given connection was established
   *
   * @static
   * @access public
   * @return boolean
   */
  static function connection_established($name) {
    return array_key_exists($name, Connections::$established_connections);
  }
}
?>