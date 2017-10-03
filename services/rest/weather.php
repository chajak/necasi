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
    $weather = $yrNo->getWeather();

    echo "<pre>".print_r($weather, true)."</pre>";

    //echo "<textarea>".$weather."</textarea>";
?>