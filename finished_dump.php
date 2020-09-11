<?php

//Variables to control the amount of times to run before the script ends
$total_times = 10;
$current_run = 1;

// Setting the start and end date variables in the MySQL format
$start_date = date('Y-m-d H:i:s', strtotime('2020-06-30 16:15:00'));
$end_date = date('Y-m-d H:i:s', strtotime('2020-06-30 16:30:00'));

// Time to wait variable. 45 minutes = 2700 seconds.   15 minutes = 900 seconds
$time_to_wait = 10;
$curl_api = "curl_api()";

//Function used to run the API using cURL
function curl_api($start_date, $end_date) {
    //Replace the ' ' with a '+' in the query string so that the API call will work.
    $url = str_replace(' ', '+', "https://example.com/apibroker/sql-data-dump-customer?&start_date=$start_date&end_date=$end_date");
    //runs the API
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "$url",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Cookie: Cookie"
      ),
    ));
    echo "Running...";
    $response = curl_exec($curl);
    $response = json_decode($response);

    // handle error; error output
    if(curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {

      if (!empty($response)) {
        var_dump($response);
      }
    }
    echo curl_error($curl);
    curl_close($curl);
}

//Function to set the current time
function current_time() {
  date_default_timezone_set('America/Denver');
  $current_time = getdate();
  echo $current_time['hours'] . ":" . $current_time['minutes'] . " - ";
}

//Function used to check DB for row entries saying the dump was successful
function finished_dump($start_date, $end_date) {
    include 'connection.php';

    $no_errors = "Export finished for $start_date to $end_date";
    $errors = "Export finished - error(s) occurred $start_date to $end_date";

    $sql = "SELECT message FROM datadump_log
            WHERE message = ?
            OR message = ?";
    try {
        $results = $db->prepare($sql);
        $results->bindValue(1, $no_errors, PDO::PARAM_STR);
        $results->bindValue(2, $errors, PDO::PARAM_STR);
        echo "\nChecking Database...\n";
        $results->execute();
    } catch (Exception $e) {
        echo "Error!\n";
        echo $e->getMessage();
    }
    return $results->fetch();
}

//Start of program
$time_to_run = 0;

while ($current_run < $total_times) {
    //Check for the rows in the DB saying the export ran
    if (finished_dump($start_date, $end_date) == true) {
        //Increases the intervals by 15 minutes
        $start_date = date('Y-m-d H:i:s', strtotime("+15 minutes", strtotime($start_date)));
        $end_date = date('Y-m-d H:i:s', strtotime("+15 minutes", strtotime($end_date)));
        echo "\n$curl_api\n";
        echo "\nRan for the following time range: \n$start_date\n$end_date\n";
        $current_run++;
        $time_to_run = 0;
        //Sets the time between each iteration.
        current_time();
        echo "Waiting " . $time_to_wait/60 . " min...\n\n";
        sleep($time_to_wait);
    //If the datadump hasn't ran yet, then it will wait $time_to_wait longer
    } elseif ($time_to_run < 6) {
        echo "\nStill Processing...\n$start_date\n$end_date\n";
        sleep($time_to_wait);
        $time_to_run += 1;
    //If after a certain amount of time, there is no success message, subtract 15 minutes from start and end time variables and run that same timeframe again.
    //This can be calculated by using the $time_to_wait variable, and dividing by the second parameter in the if statement below. For example:
    //$time_to_run set to 10 minutes, and is >= 6, meaning this will run for 60 minutes before trying again
    } elseif ($time_to_run >= 6) {
        $start_date = date('Y-m-d H:i:s', strtotime("-15 minutes", strtotime($start_date)));
        $end_date = date('Y-m-d H:i:s', strtotime("-15 minutes", strtotime($end_date)));
    }
}