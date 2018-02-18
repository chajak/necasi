<?php
    namespace services\caches;

    class DbCache {
        private $db;
        private $config;

        private $location;

        private $minLat;
        private $maxLat;
        private $minLng;
        private $maxLng;

        private $connectorName;

        public function __construct($db, $config) {
            $this->db = $db;
            $this->config = $config;
        }

        public function searchInCache($location, $connectorName = null) {
            $this->location = $location;
            $this->connectorName = $connectorName;
            $lat = $this->location["lat"];
            $lng = $this->location["lng"];

            $this->calculateEdges();

            $toReturnArray = array();
            $result = $this->db->query("SELECT `model` FROM `cache` WHERE ".(isset($this->connectorName) ? "`connector` = '".$this->connectorName."' AND " : "")."`validTo` > NOW() AND `minLat` <= ".$lat." AND `maxLat` >= ".$lat." AND `minLng` <= ".$lng." AND `maxLng` >= ".$lng." ORDER BY `id` DESC LIMIT 1");
            if($result->num_rows == 1) {
                $row = mysqli_fetch_assoc($result);
                $deserializedMode = unserialize(base64_decode($row["model"]));
                array_push($toReturnArray, $deserializedMode);
            }

            return $toReturnArray;
        }

        public function saveIntoCache($model) {
            $serializedModel = base64_encode(serialize($model));
            $localizedValidTo = date('Y-m-d G:i:s', strtotime($model->validTo));

            $this->db->query("INSERT INTO `cache` (`validTo`, `connector`, `minLat`, `maxLat`, `minLng`, `maxLng`, `model`) VALUES ('".$localizedValidTo."', '".$this->connectorName."', '".$this->minLat."', '".$this->maxLat."', '".$this->minLng."', '".$this->maxLng."', '".$serializedModel."')");
        }

        private function calculateEdges() {
            $this->minLat = $this->location["lat"] - $this->config["cacheLatPrecision"];
            $this->maxLat = $this->location["lat"] + $this->config["cacheLatPrecision"];

            $this->minLng = $this->location["lng"] - $this->config["cacheLngPrecision"];
            $this->maxLng = $this->location["lng"] + $this->config["cacheLngPrecision"];
        }

        //custom functions for Meteor only
        public function searchApiRunInCache() {
            $apiRunData = array();
            $result = $this->db->query("SELECT `from`, `to`, `validTo`, `apiRun` FROM `meteor_apirun` WHERE `validTo` > NOW() ORDER BY `id` DESC LIMIT 1");
            if($result->num_rows == 1) {
                $row = mysqli_fetch_assoc($result);
                $apiRunData["from"] = $row["from"];
                $apiRunData["to"] = $row["to"];
                $apiRunData["validTo"] = $row["validTo"];
                $apiRunData["apiRun"] = $row["apiRun"];
            }

            return $apiRunData;
        }

        public function saveApiRunIntoCache($apiRunData) {
            $from = $apiRunData["from"];
            $to = $apiRunData["to"];
            $validTo = $apiRunData["validTo"];
            $apiRun = $apiRunData["apiRun"];

            $this->db->query("INSERT INTO `meteor_apirun` (`from`, `to`, `validTo`, `apiRun`) VALUES ('".$from."', '".$to."', '".$validTo."', '".$apiRun."')");
        }
    }
?>