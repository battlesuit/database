<?php
namespace test_bench;

class DatabaseTestBench extends Base {
  function initialize() {
    $this->add_test(new \mysql\RowsTest());
    $this->add_test(new \mysql\ConnectionTest());
    $this->add_test(new \mysql\TableTest());
  }
}
?>