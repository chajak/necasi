<?php
    namespace services\parsers;

    use services\models as Models;

    class XmlParser extends \BaseClass {
        protected $rawXml;
        protected $xml;
        protected $parserClass;
        protected $parserName;
        public $parserVersion;
        protected $model;

        public function __construct() {
            $this->model = new Models\DataModel;
            $this->parserClass = "SimpleXMLElement";
            $this->parserName = $this->whoAmI();
            $this->parserVersion = "1.0";
        }

        public function whoAmI() {
            return get_called_class();
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