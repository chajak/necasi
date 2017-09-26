<?php
    namespace services\connectors\specific;

    use services\connectors as Connectors;

    class YrNoConnector extends Connectors\BaseConnector {
        public function __construct() {
            parent::__construct();

            $this->usingLocation = array("lat", "lng");
            $this->urlExample = "https://api.met.no/weatherapi/locationforecast/1.9/?lat=:lat;lon=:lng";
            $this->reduceLocation();
        }

        public function getWeather() {
            if($this->isValid()) {
                $this->rawOutput = file_get_contents($this->url);
            }

            //use parser, this for test only
            return $this->rawOutput;
        }

        protected function isValid() {
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