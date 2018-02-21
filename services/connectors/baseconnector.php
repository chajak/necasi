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

            $this->reduceLocation();
            $this->tryAutoSetParser();
        }

        private function tryAutoSetParser() {
            $probableParserName = str_replace("Connector", "Parser", $this->className());
            $probableParserClass = "services\\parsers\\specific\\".$probableParserName;
            $this->parser = new $probableParserClass();
        }

        public function className() {
            return (new \ReflectionClass($this))->getShortName();
        }

        public function whoAmI() {
            return $this->className()."_".$this->connectorVersion;
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

        public function setDateTime($dateTimeString) {
            $timestamp = strtotime($dateTimeString);
            $this->dateTime->setTimestamp($timestamp);
        }

        public function setParser($parser) {
            $this->parser = $parser;
        }

        public function getWeather() {
            if(!$this->isValid()) {
                return false;
            }

            if($this::$config["cacheEnabled"] == true) {
                $cache = $this->cache->searchInCache($this->location, $this->whoAmI());
                if(empty($cache)) {
                    //ERROR HANDLING?
                    $this->rawOutput = file_get_contents($this->url);
                    $this->model = $this->parser->parse($this->rawOutput);

                    //all other is saved in cache from previous request
                    $this->cache->saveIntoCache($this->model);
                }
                else {
                    $this->model = $cache[0];
                }
            }
            else {
                $this->rawOutput = file_get_contents($this->url);
                $this->model = $this->parser->parse($this->rawOutput);
            }

            $this->model->hours = $this->filterHours($this->model->hours);

            return $this->model;
        }

        protected function filterHours($hours) {
            $filteredHours = array();
            $currentHourTimestamp = $this->dateTime->getTimestamp();
            $endOfDay = clone $this->dateTime;
            //with midnight
            $endOfDay->modify('tomorrow');
            $endOfDayTimestamp = $endOfDay->getTimestamp();

            foreach($hours as $hour) {
                if($hour["timestamp"] >= $currentHourTimestamp && $hour["timestamp"] <= $endOfDayTimestamp) {
                    array_push($filteredHours, $hour);
                }
            }

            return $filteredHours;
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

        protected function isValid() {
            die("Override Me!");
        }
    }
?>