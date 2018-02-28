<?php
    namespace services\connectors\specific;

    use services\connectors as Connectors;

    class MeteorConnector extends Connectors\BaseConnector {
        private $apiRunUrl;
        private $apiRunData;

        public function __construct() {
             //call this first
            $this->usingLocation = array("lat", "lng");

            parent::__construct();

            //get correct apiRun
            $this->apiRunData = array();
            $this->apiRunUrl = "http://medard-online.cz/apirun";
            $this->getCorrectApiRun();

            //call this after
            $this->urlExample = "http://medard-online.cz/apimeteogram?run=".$this->apiRunData["apiRun"]."&lat=:lat&long=:lng";

            $this->connectorVersion = "1.0";
        }

        private function getCorrectApiRun() {
            $this->apiRunData = $this->cache->searchApiRunInCache();
            if(empty($this->apiRunData)) {
                //ERROR HANDLING?
                $this->getApiRunFromServer();
                $this->cache->saveApiRunIntoCache($this->apiRunData);
            }
        }

        private function getApiRunFromServer() {
            $rawApiRunResponse = file_get_contents($this->apiRunUrl);
            $apiRunResponse = json_decode($rawApiRunResponse);
            $this->apiRunData["from"] = date('Y-m-d G:i:s', $apiRunResponse->start);
            $this->apiRunData["to"] = date('Y-m-d G:i:s', $apiRunResponse->end);

            //valid to
            $validTo = $apiRunResponse->expires;
            if($validTo <= time()) {
                $validTo = strtotime("+1 hour");
            }

            $this->apiRunData["validTo"] = date('Y-m-d G:i:s', $validTo);
            $this->apiRunData["apiRun"] = $apiRunResponse->id;
        }

        //custom is valid
        protected function isValid() {
            if(!isset($this->parser) || empty($this->parser->parserVersion) || empty($this->apiRunData)) {
                return false;
            }
            
            $this->url = $this->urlExample;

            foreach($this->usingLocation as $key => $val) {
                if(empty($this->location[$val])) {
                    $this->url = null;
                    return false;
                }
                else {
                    $this->url = str_replace(":".$val, $this->location[$val], $this->url);
                }
            }

            return true;
        }

        //overriden
        public function setParser($parser) {
            $this->parser = $parser;
            $this->parser->setApiRunData($this->apiRunData);
        }
    }
?>