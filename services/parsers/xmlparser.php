<?php
    namespace services\parsers;

    class XmlParser extends \BaseClass {
        protected $rawXml;
        protected $xml;
        protected $parserClass;
        protected $parserName;
        public $parserVersion;

        public function __construct() {
            $this->parserClass = "SimpleXMLElement";
            $this->parserName = $this->whoAmI();
            $this->parserVersion = "1.0";
        }

        public function whoAmI() {
            return get_called_class();
        }

        public function setRawXml($rawXml) {
            $this->rawXml = $rawXml;
        }

        public function parse($rawXml) {
            if(!empty($rawXml)) {
                $this->rawXml = $rawXml;
            }

            if(!empty($this->rawXml)) {
                $this->xml = simplexml_load_string($this->rawXml, $this->parserClass, LIBXML_NOCDATA | LIBXML_NOBLANKS);
            }

            //temporary
            return $this->xml;
        }
    }
?>