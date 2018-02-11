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

    /*
    YR NO CACHING
    [meta] => SimpleXMLElement Object
        (
            [model] => SimpleXMLElement Object
                (
                    [@attributes] => Array
                        (
                            [name] => EC.GEO.0125
                            [termin] => 2017-10-09T00:00:00Z
                            [runended] => 2017-10-09T07:37:11Z
                            [nextrun] => 2017-10-09T20:00:00Z
                            [from] => 2017-10-09T16:00:00Z
                            [to] => 2017-10-19T00:00:00Z
                        )
    */

    echo "<pre>".print_r($weather, true)."</pre>";

    //echo "<textarea>".$weather."</textarea>";
?>