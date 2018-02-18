<?php
    namespace services\models;
    
    class DataModel {
        public $name;
        public $created;
        public $validTo;

        public $from;
        public $to;

        public $hours;

        public function __construct() {
            $hours = array();
        }
    }
?>