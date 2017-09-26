<?php
    namespace services\rest;

    include("../../config.php");

    use services\connectors\specific as SpecificConnectors;

    $lat = $_GET['lat'] ?? '';
    $lng = $_GET['lng'] ?? '';
    $dateTime = $_GET['datetime'] ?? '';

    $yrNo = new SpecificConnectors\YrNoConnector;
    $yrNo->setGps($lat, $lng);
    $weather = $yrNo->getWeather();
    echo '<textarea>'.$weather.'</textarea>';
?>