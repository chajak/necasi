<?php
    namespace services\parsers\specific;

    use services\parsers as Parsers;

    class YrNoParser extends Parsers\XmlParser {
        public function __construct() {
            parent::__construct();

            //$this->parserVersion = "1.0";
        }

        public function parse($rawXml) {
            parent::parse($rawXml);

            $this->buildDataModel();

            return $this->model;
        }

        private function buildDataModel() {
            if(!empty($this->xml)) {
                $this->processAttributes();
                $this->processMetaAttributes();
                $this->processHours();
            }
        }

        private function processAttributes() {
            $attributes = $this->xml->attributes();

            $this->model->created = (string)($attributes["created"]);
        }

        private function processMetaAttributes() {
            $metaAttributes = $this->xml->meta[0]->model[0]->attributes();

            $this->model->validTo = (string)($metaAttributes["nextrun"]);
            $this->model->from = (string)($metaAttributes["from"]);
            $this->model->to = (string)($metaAttributes["to"]);
        }

        private function processHours() {
            $times = $this->xml->product[0]->time;

            foreach($times as $time) {
                $timeAttributes = $time->attributes();
                if((string)$timeAttributes["from"] == (string)$timeAttributes["to"]) {
                    $hourTimestamp = (string)$timeAttributes["from"];

                    $location = $time->location[0];
                    $temperatureObject = $location->temperature[0]->attributes();
                    $windSpeedObject = $location->windSpeed[0]->attributes();
                    $cloudinessObject = $location->cloudiness[0]->attributes();
                    $fogObject = $location->fog[0]->attributes();

                    $foundHour = array();
                    $foundHour["datetime"] = $hourTimestamp;
                    $foundHour["temperature"] = (string)$temperatureObject->value;
                    $foundHour["unit"] = (string)$temperatureObject->unit;

                    $foundHour["windspeed"] = (string)$windSpeedObject->mps;

                    $foundHour["cloudiness"] = (string)$cloudinessObject->percent;
                    $foundHour["fog"] = (string)$fogObject->percent;

                    $this->model->hours[$hourTimestamp] = $foundHour;
                }  
            }
        }
    }
?>