<?php
    namespace services\rest;

    include("../../config.php");

    use services\connectors\specific as Connectors;
    use services\parsers\specific as Parsers;   

    $lat = $_GET['lat'] ?? '';
    $lng = $_GET['lng'] ?? '';
    $dateTime = $_GET['datetime'] ?? '';

    $yrNoParser = new Parsers\YrNoParser;
    $yrNo = new Connectors\YrNoConnector;
    $yrNo->setParser($yrNoParser);
    $yrNo->setGps($lat, $lng);
    $yrNo->setDateTime($dateTime);
    $weather = $yrNo->getWeather();

/*
    $owmParser = new Parsers\OwmParser;
    $owm = new Connectors\OwmConnector;
    $owm->setParser($owmParser);
    $owm->setGps($lat, $lng);
    $owm->setDateTime($dateTime);
    $weather = $owm->getWeather();
*/

    if(\BaseClass::$config["debug"] == true) {
        echo "<pre>".print_r($weather, true)."</pre>";
    }
    else {
        $json = json_encode((array)$weather);
        echo $json;
    }
?>