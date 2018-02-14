<?php
    include("config.php");

    $config = \BaseClass::$config;

    $today = date("Y-m-d H:00:00");
    $tomorrow = date("Y-m-d 00:00:00", strtotime("tomorrow"));

    $homepage = new services\Template("homepage.html");
    $homepage->set("jsTimestamp", filemtime($config["rootDir"].$config["resourcesUrl"]."/js/script.js"));
    $homepage->set("ga", $config["ga"]);
    $homepage->set("today", $today);
    $homepage->set("tomorrow", $tomorrow);
    echo $homepage->output();
?>