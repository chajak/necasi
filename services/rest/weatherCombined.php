<?php
    namespace services\rest;

    include("../../config.php");

    use services\connectors\specific as Connectors;
    use services\parsers\specific as Parsers;
    use services\models as Models;

    $lat = $_GET['lat'] ?? '';
    $lng = $_GET['lng'] ?? '';
    $dateTime = $_GET['datetime'] ?? '';
    $ifFromCombined = true;
    
    function weightHour($hour, $weight) {
        echo "<pre>".$weight."<br>".print_r($hour, true)."</pre>";
        $hour->temperature = round((float)($hour->temperature * $weight), 1);
        $hour->windspeed = round((float)($hour->windspeed * $weight), 1);
        $hour->windDir = round((float)($hour->windDir * $weight));
        $hour->cloudiness = round((float)($hour->cloudiness * $weight));
        if(isset($hour->fog) && !empty($hour->fog) && $hour->fog >= 0) {
            $hour->fog = (float)($hour->fog * $weight);
        }
        
        if(isset($hour->rain) && !empty($hour->rain) && $hour->rain >= 0) {
            $hour->rain = round((float)($hour->rain * $weight));
        }

        return $hour;
    }

    function combineHour($hour, $hour1) {
        $hour->temperature = round((float)($hour->temperature + $hour1->temperature), 1);
        $hour->windspeed = round((float)($hour->windspeed + $hour1->windspeed), 1);
        $hour->windDir = round((float)($hour->windDir + $hour1->windDir));
        $hour->cloudiness = round((float)($hour->cloudiness + $hour1->cloudiness));
        if(isset($hour->fog) && !empty($hour->fog) && $hour->fog >= 0 && isset($hour1->fog) && !empty($hour1->fog) && $hour1->fog >= 0) {
            $hour->fog = (float)($hour->fog + $hour1->fog);
        }
        if(isset($hour->rain) && !empty($hour->rain) && $hour->rain >= 0 && isset($hour1->rain) && !empty($hour1->rain) && $hour1->rain >= 0) {
            $hour->rain = round((float)($hour->rain + $hour1->rain));
        }

        return $hour;
    }

    //get all available parsers
    $allParsers = \BaseClass::$config["weatherParsers"];

    //get all weathers
    $weathers = array();
    foreach($allParsers as $parser) {
        ob_start();
        include "weather.php";
        array_push($weathers, json_decode(ob_get_clean()));
    }

    //combine here
    //load rules from DB
    //for now here
    //weight - 1 2 3 - owm, meteor, yr - from worst to best
    //hour bonus 10 if real (not interpolated)
    
    //now take yrNo - index 2 as base
    $weatherCombined = new Models\DataModel;

    foreach($weathers[2]->hours as $hour) {
        $neededTimestamp = $hour->timestamp;

        $neededHour0Array = array_filter(
            $weathers[0]->hours,
            function ($e) use (&$neededTimestamp) {
                return $e->timestamp == $neededTimestamp;
            }
        );

        $neededHour1Array = array_filter(
            $weathers[1]->hours,
            function ($e) use (&$neededTimestamp) {
                return $e->timestamp == $neededTimestamp;
            }
        );

        $totalWeight = 0;

        $realModificator = 1;
        if($hour->real == true) {
            $realModificator = 10;
        }
        $thisWeight = (3 * $realModificator);

        $totalWeight += $thisWeight;
        $weightedHour = weightHour($hour, $thisWeight);

        $weatherCombined = $weightedHour;

        if(!empty($neededHour0Array)) {
            $realModificator = 1;
            $neededHour0 = array_pop($neededHour0Array);
            if($neededHour0->real == true) {
                $realModificator = 10;
            }
            $thisWeight = (1 * $realModificator);

            $weightedHour = weightHour($neededHour0, $thisWeight);
            $weatherCombined = combineHour($weatherCombined, $weightedHour);

            $totalWeight += $thisWeight;
        }

        if(!empty($neededHour1Array)) {
            $neededHour1 = array_pop($neededHour1Array);
            if($neededHour1->real == true) {
                $realModificator = 10;
            }
            $thisWeight = (2 * $realModificator);

            $weightedHour = weightHour($neededHour1, $thisWeight);
            $weatherCombined = combineHour($weatherCombined, $weightedHour);

            $totalWeight += $thisWeight;
        }

        //divide by $totalWeight
        $weatherCombined = weightHour($weatherCombined, (1 / $totalWeight));

        echo "<pre>".print_r($weatherCombined, true)."</pre><hr>";
    }

    if(isset($weatherCombined)) {
        if(\BaseClass::$config["debug"] == true) {
            echo "<pre>".print_r($weather, true)."</pre>";
        }
        else {
            $json = json_encode((array)$weatherCombined);
            echo $json;
        }
    }
?>