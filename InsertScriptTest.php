<?php

include 'connection.php';

// date/time variables
$start_date = date('Y-m-d H:i:s', strtotime('2020-06-30 16:45:00'));
$end_date = date('Y-m-d H:i:s', strtotime('2020-06-30 17:00:00'));

// Run variables
$current_run = 1;
$total_runs = 5;

function add_rows($start_date,$end_date) {
    include 'connection.php';
    // Sql array for randomly entering values into the DB
    //Export finished - error(s) occurred 2020-09-09 23:00:25 to 2020-09-10 23:00:12
    $sql = array ();
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Starting export')";
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Exporting after: $start_date')";
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Running 269 Cases')";
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Done Cases')";
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Running 291 Tasks')";
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Done Tasks')";
    //$sql[] = "INSERT INTO datadump_log (status, message)
      //  VALUES ('MESSAGE', 'Export finished for $start_date to $end_date')";
    $sql[] = "INSERT INTO datadump_log (status, message)
        VALUES ('MESSAGE', 'Export finished - error(s) occurred $start_date to $end_date')";
    // Parse through the $sql variable and add a row into the DB
    foreach ($sql as $item) {
        try {
            $db->query($item);
            echo "\nSuccess";
        } catch (Exception $e) {
            echo "Error!\n";
            echo $e->getMessage();
        }
        sleep(1);
    }
}

while ($current_run <= $total_runs) {
    echo "\n$start_date\n$end_date\n";
    add_rows($start_date,$end_date);
    $start_date = date('Y-m-d H:i:s', strtotime("+15 minutes", strtotime($start_date)));
    $end_date = date('Y-m-d H:i:s', strtotime("+15 minutes", strtotime($end_date)));
    echo "\n$start_date\n$end_date\n";
    $current_run++;
}
