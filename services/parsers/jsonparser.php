<?php
    namespace services\parsers;

    use services\models as Models;

    class JsonParser extends \BaseClass {
        protected $rawJson;
        protected $json;
        public $parserVersion;
        protected $model;

        public function __construct() {
            $this->model = new Models\DataModel;
            $this->parserVersion = "0.0";
        }

        public function whoAmI() {
            return (new \ReflectionClass($this))->getShortName()."_".$this->parserVersion;
        }

        public function parse($rawJson) {
            if(!empty($rawJson)) {
                $this->rawJson = $rawJson;
            }

            if(!empty($this->rawJson)) {
                $this->json = json_decode($this->rawJson);
            }
        }
    }
?>