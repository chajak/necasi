<?php
    include("config.php");

    $config = \BaseClass::$config;

    $today = date("Ymd");
    $tomorrow = date("Ymd", strtotime("tomorrow"));

    $homepage = new services\Template("homepage.html");
    $homepage->set("jsTimestamp", filemtime($config["rootDir"].$config["resourcesUrl"]."/js/script.js"));
    $homepage->set("ga", $config["ga"]);
    $homepage->set("today", $today);
    $homepage->set("tomorrow", $tomorrow);
    echo $homepage->output();
?>