<?php
    namespace services\parsers;

    use services\parsers as Parsers;
    
    class XmlParser extends Parsers\BaseParser {
        protected $rawXml;
        protected $xml;
        protected $parserClass;

        public function __construct() {
            parent::__construct();
            $this->parserClass = "SimpleXMLElement";
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