<?php
    namespace services\connectors\specific;

    use services\connectors as Connectors;

    class OwmConnector extends Connectors\BaseConnector {
        public function __construct() {
            //call this first
            $this->usingLocation = array("lat", "lng");
            $this->urlExample = "http://api.openweathermap.org/data/2.5/forecast?lat=:lat&lon=:lng&appid=".self::$config["owmKey"]."&units=metric&lang=cz&type=accurate";

            parent::__construct();

            //call this after
            $this->connectorVersion = "1.0";
        }

        //custom is valid
        protected function isValid() {
            if(!isset($this->parser) || empty($this->parser->parserVersion)) {
                return false;
            }
            
            $this->url = $this->urlExample;

            foreach($this->usingLocation as $key => $val) {
                if(empty($this->location[$val])) {
                    $this->url = null;
                    return false;
                }
                else {
                    $this->url = str_replace(":".$val, $this->location[$val], $this->url);
                }
            }

            return true;
        }
    }
?>