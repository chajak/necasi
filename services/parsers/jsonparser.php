<?php
    namespace services\parsers;

    use services\parsers as Parsers;

    class JsonParser extends Parsers\BaseParser {
        protected $rawJson;
        protected $json;

        public function __construct() {
            parent::__construct();
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