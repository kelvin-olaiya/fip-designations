<?php
    if (!isset($_POST['message'])) {
        header('Content-Type: application/json');
        print json_encode(array(
            "success" => false,
            "error" => "No message provided",
        ));
        exit();
    }

    $message = $_POST['message'];
    
    $details_rgxp = "/(?'championship'U[0-9][0-9][SG]?\/[FGELMS]|DR[1-4])"
              ."|(?'ref'[123]:[^ ]*)"
              ."|(?'date'[[:digit:]]{2}\/[[:digit:]]{2}\/[[:digit:]]{4})"
              ."|(?'time'[[:digit:]]{2}:[[:digit:]]{2})"
              ."|(?'city'[[:alpha:][:punct:]]* \([[:alpha:]]*\))$/m";

    $teams_rgxp = "/(?'ref'[123]:[[:upper:]]\.[A-Z]* )\g'ref'{0,2}(?'teams'.*\/.*) [[:digit:]]{2}\/[[:digit:]]{2}\/[[:digit:]]{4}/m";
    $address_rgxp = "/[[:digit:]]{2}:[[:digit:]]{2} (?'place'.*) [[:alpha:][:punct:]]* \([[:alpha:]]*\)$/m";

    preg_match_all($details_rgxp, $message, $details);
    preg_match_all($teams_rgxp, $message, $teams);
    preg_match_all($address_rgxp, $message, $address);
    
    $details = $details[0];

    header('Content-Type: application/json');

    $championship = $details[0];
    $referees = array();
    $offset = count($details) - 6;
    for ($i = 1; $i < 3 + $offset; $i++) {
        $referees[] = $details[$i];
    }
    $date = $details[3 + $offset];
    $time = $details[4 + $offset];
    $city = $details[5 + $offset];

    $teams = array_map(fn($s): string =>  ucwords(strtolower($s)), explode("/", $teams['teams'][0]));
    $homeTeam = $teams[0];
    $awayTeam = $teams[1];
    $address = $address['place'][0].", ".$city;

    $date = explode("/", $date);
    $date = "$date[2]-$date[1]-$date[0]";
    $matchTime = "$date $time";
    $endTime = date("Y-m-d H:i", strtotime($matchTime) + 7200);
    
    print json_encode(array(
        "success" => true,
        "title" => "$championship - $homeTeam vs. $awayTeam",
        "start" => $matchTime,
        "end" => $endTime,
        "location" => $address,
        "notes" => "Arbitri: \n".implode("\n", $referees),
    ));

?>