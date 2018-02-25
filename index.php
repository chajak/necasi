<?php
    include("config.php");

    $config = \BaseClass::$config;
    $shortDays = $config["daysShort"];

    $today = date("Y-m-d H:00:00");
    $tomorrow = date("Y-m-d 00:00:00", strtotime("tomorrow"));

    $homepage = new services\Template("homepage.html");
    $homepage->set("jsTimestamp", filemtime($config["rootDir"].$config["resourcesUrl"]."/js/script.js"));
    $homepage->set("ga", $config["ga"]);
    $homepage->set("today", $today);
    $homepage->set("tomorrow", $tomorrow);

    $day3timestamp = strtotime("+2 days");
    $day4timestamp = strtotime("+3 days");
    $day5timestamp = strtotime("+4 days");

    $homepage->set("day3text", $shortDays[date("N", $day3timestamp)]." ".date("d.m.", $day3timestamp));
    $homepage->set("day3value", date("Y-m-d 00:00:00", $day3timestamp));
    $homepage->set("day4text", $shortDays[date("N", $day4timestamp)]." ".date("d.m.", $day4timestamp));
    $homepage->set("day4value", date("Y-m-d 00:00:00", $day4timestamp));
    $homepage->set("day5text", $shortDays[date("N", $day5timestamp)]." ".date("d.m.", $day5timestamp));
    $homepage->set("day5value", date("Y-m-d 00:00:00", $day5timestamp));

    echo $homepage->output();
?>