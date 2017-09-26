<?php
    namespace services\connectors;

    class BaseConnector extends \BaseClass {
        private $location = array();
        private $dateTime;

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

        private function formatDateTime() {
            return "Override Me!";
        }

        public function whoAmI() {
            echo get_called_class();
        }
    }
?>