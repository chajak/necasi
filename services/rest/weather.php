<?php
    namespace services\rest;

    include("../../config.php");

    use services\connectors\specific as Connectors;
    use services\parsers\specific as Parsers;   

    $lat = $_GET['lat'] ?? '';
    $lng = $_GET['lng'] ?? '';
    $dateTime = $_GET['datetime'] ?? '';
    $parser = $_GET['parser'] ?? '';

    if($parser == "owm") {
        $owmParser = new Parsers\OwmParser;
        $owm = new Connectors\OwmConnector;
        $owm->setParser($owmParser);
        $owm->setGps($lat, $lng);
        $owm->setDateTime($dateTime);
        $weather = $owm->getWeather();
    }
    else if($parser == "meteor") {
        $meteorParser = new Parsers\MeteorParser;
        $meteor = new Connectors\MeteorConnector;
        $meteor->setParser($meteorParser);
        $meteor->setGps($lat, $lng);
        $meteor->setDateTime($dateTime);
        $weather = $meteor->getWeather();
    }
    else {
        $yrNoParser = new Parsers\YrNoParser;
        $yrNo = new Connectors\YrNoConnector;
        $yrNo->setParser($yrNoParser);
        $yrNo->setGps($lat, $lng);
        $yrNo->setDateTime($dateTime);
        $weather = $yrNo->getWeather();
    }

    if(isset($weather)) {
        if(\BaseClass::$config["debug"] == true) {
            echo "<pre>".print_r($weather, true)."</pre>";
        }
        else {
            $json = json_encode((array)$weather);
            echo $json;
        }
    }
?>