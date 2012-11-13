<?php
namespace test_bench {
  require_once 'init.php';

  (new DatabaseTestBench())->run_and_present_as_text();
}
?>