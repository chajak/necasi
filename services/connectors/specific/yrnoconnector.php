<?php
    namespace services\connectors\specific;

    use services\connectors as Connectors;

    class YrNoConnector extends Connectors\BaseConnector {
        public function __construct() {
            //call this first
            $this->usingLocation = array("lat", "lng");
            $this->urlExample = "https://api.met.no/weatherapi/locationforecast/1.9/?lat=:lat;lon=:lng";

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