<?php
    namespace services\caches;

    class DbCache {
        private $db;
        private $config;

        public function __construct($db, $config) {
            $this->db = $db;
            $this->config = $config;
        }

        public function searchInCache($lat, $lng, $connectorName = null) {
            $toReturnArray = array();
            if(isset($connectorName)) {
                //search just for one connector
            }
            else {
                //search for all connectors
            }

            return $toReturnArray;
        }
    }
?>