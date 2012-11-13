#Suitcase.Database

(dev-state)

##Import

    require 'Database/load.php';


##Registering connections

    namespace database {
      register_connection('mysql-test', 'mysql://root:foo@localhost:3306/dbname');
    }
    
##Mixed in connection support

    namespace database {
      class TableConnector {
        use Connections;
        
        static $connection = 'mysql-test';
        
        static function table($name) {
          $con = static::establish_connection(static::$connection);
          return new Table($name, $con);
        }
      }
      
      $users = TableConnector::table('users');
      $users->push(['login' => 'tom', 'password' => 'xxx']);
    }