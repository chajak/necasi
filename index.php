<?php
    $config = require_once("./config.php");
    require_once($config["rootDir"]."/services/template.php");

    $homepage = new Template("homepage.html");
    $homepage->set("ga", $config["ga"]);
    echo $homepage->output();
?>