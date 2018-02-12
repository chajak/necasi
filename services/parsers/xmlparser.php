<?php
    namespace services\parsers;

    use services\models as Models;

    class XmlParser extends \BaseClass {
        protected $rawXml;
        protected $xml;
        protected $parserClass;
        public $parserVersion;
        protected $model;

        public function __construct() {
            $this->model = new Models\DataModel;
            $this->parserClass = "SimpleXMLElement";
            $this->parserVersion = "0.0";
        }

        public function whoAmI() {
            return (new \ReflectionClass($this))->getShortName()."_".$this->parserVersion;
        }

        public function parse($rawXml) {
            if(!empty($rawXml)) {
                $this->rawXml = $rawXml;
            }

            if(!empty($this->rawXml)) {
                $this->xml = simplexml_load_string($this->rawXml, $this->parserClass, LIBXML_NOCDATA | LIBXML_NOBLANKS);
            }
        }
    }
?>