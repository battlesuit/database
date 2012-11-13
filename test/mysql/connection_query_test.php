<?php
namespace mysql;

class ConnectionQueryTest extends ConnectionTest {  
  function set_up() {
    $this->connection = $this->new_connection();
    $this->connection->establish($this->use_database);
  }
}
?>