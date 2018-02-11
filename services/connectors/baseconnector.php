<?php
    namespace services\connectors;

    class BaseConnector extends \BaseClass {
        protected $url;
        protected $urlExample;
        protected $usingLocation = array("lat", "lng", "address");
        protected $location = array(
            "lat" => "",
            "lng" => "",
            "address" => ""
        );

        protected $dateTime;
        protected $rawOutput;
        protected $parser;
        protected $model;

        public function __construct() {
            parent::__construct();

            $this->dateTime = new \DateTime();
        }

        public function setLocation($location) {
            $this->location = $location;
        }

        public function setGps($lat, $lng) {
            $this->location["lat"] = $lat;
            $this->location["lng"] = $lng;
        }

        public function setAddress($address) {
            $this->location["address"] = $address;
        }

        public function setDateTime($datetime) {
            $this->datetime = $datetime;
        }

        public function whoAmI() {
            return get_called_class();
        }

        public function setParser($parser) {
            $this->parser = $parser;
        }

        public function getForecast() {
            die("Override Me!");
        }

        protected function reduceLocation() {
            $reducedLocation = array();
            foreach($this->usingLocation as $key => $val) {
                if(isset($this->location[$val])) {
                    $reducedLocation[$val] = $this->location[$val];
                }
            }

            $this->location = $reducedLocation;
        }

        protected function formatDateTime() {
            die("Override Me!");
        }

        protected function isValid() {
            die("Override Me!");
        }
    }
?>