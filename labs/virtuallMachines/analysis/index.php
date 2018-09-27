<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

<?php
  // function loads the csv file
  function loadCsv($path) {
      $csvFile = file($path);
      $csv = array_map('str_getcsv', $csvFile);
      array_walk($csv, function(&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
      });
      array_shift($csv); # remove column header
      array_shift($csv);
      return $csv;
  }
  
