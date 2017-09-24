<?php
    $config = require_once("./config.php");
    require_once($config["rootDir"]."/services/template.php");

    $today = date("Ymd");
    $tomorrow = date("Ymd", strtotime("tomorrow"));

    $homepage = new Template("homepage.html");
    $homepage->set("ga", $config["ga"]);
    $homepage->set("today", $today);
    $homepage->set("tomorrow", $tomorrow);
    echo $homepage->output();
?>