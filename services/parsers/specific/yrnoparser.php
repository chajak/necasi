<?php
    namespace services\parsers\specific;

    use services\parsers as Parsers;

    class YrNoParser extends Parsers\XmlParser {
        public function __construct() {
            parent::__construct();

            //$this->parserVersion = "1.0";
        }
    }
?>