<?php
    namespace services\connectors;

    use services\caches as Caches;

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
        protected $cache;

        public $connectorVersion;

        public function __construct() {
            parent::__construct();

            $this->connectorVersion = "0.0";

            $this->dateTime = new \DateTime();
            $this->cache = new Caches\DbCache($this->db, $this::$config);
        }

        public function whoAmI() {
            return (new \ReflectionClass($this))->getShortName()."_".$this->connectorVersion;
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