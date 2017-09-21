<?php
    class BaseClass {
        public static $config = array (
            "ga" => "UA-106812970-1",
            "rootDir" => __DIR__,
            "templatesDir" => __DIR__."/templates/",
            "resourcesUrl" => "/resources",
            "themesUrl" => "/resources/themes/",         
            "theme" => "default"
        );
    }

    return BaseClass::$config;
?>
