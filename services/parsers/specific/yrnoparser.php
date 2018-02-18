<?php
    namespace services\parsers\specific;

    use services\parsers as Parsers;

    class YrNoParser extends Parsers\XmlParser {
        public function __construct() {
            parent::__construct();

            //call this after
            $this->parserVersion = "1.0";
            $this->model->name = $this->whoAmI();
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

            $this->model->created = $this->getFormatedDateTimeString($attributes["created"]);
        }

        private function processMetaAttributes() {
            $metaAttributes = $this->xml->meta[0]->model[0]->attributes();

            $this->model->validTo = $this->getFormatedDateTimeString($metaAttributes["nextrun"]);
            $this->model->from = $this->getFormatedDateTimeString($metaAttributes["from"]);
            $this->model->to = $this->getFormatedDateTimeString($metaAttributes["to"]);
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
                    $foundHour["timestamp"] = strtotime($hourTimestamp);
                    $foundHour["datetime"] = $this->getFormatedDateTimeString($hourTimestamp);
                    $foundHour["formattedDate"] = $this->getFormatedDateTimeString($hourTimestamp, "d.m.Y");
                    $foundHour["formattedShortDate"] = $this->getFormatedDateTimeString($hourTimestamp, "d.m.");
                    $foundHour["formattedTime"] = $this->getFormatedDateTimeString($hourTimestamp, "H:i");

                    $foundHour["temperature"] = round((float)$temperatureObject->value, 1);
                    $foundHour["unit"] = (string)$temperatureObject->unit;

                    $foundHour["windspeed"] = round((float)$windSpeedObject->mps, 1);

                    $foundHour["cloudiness"] = round((float)$cloudinessObject->percent);
                    $foundHour["fog"] = (float)$fogObject->percent;
                    $foundHour["real"] = true;

                    $this->model->hours[$foundHour["datetime"]] = $foundHour;
                }  
            }
        }
    }
?>