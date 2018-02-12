<?php
    namespace services\connectors\specific;

    use services\connectors as Connectors;

    class YrNoConnector extends Connectors\BaseConnector {
        public function __construct() {
            parent::__construct();

            $this->connectorVersion = "1.0";

            $this->usingLocation = array("lat", "lng");
            $this->urlExample = "https://api.met.no/weatherapi/locationforecast/1.9/?lat=:lat;lon=:lng";
            $this->reduceLocation();
        }

        public function getWeather() {
            if(!$this->isValid()) {
                return false;
            }

            $cache = $this->cache->searchInCache($this->location, $this->whoAmI());

            //cache?
            //print_r($this->db);

            $this->rawOutput = file_get_contents($this->url);
            $this->model = $this->parser->parse($this->rawOutput);

            //save cache?
            
            return $this->model;
        }

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