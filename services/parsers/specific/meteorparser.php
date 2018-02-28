<?php
    namespace services\parsers\specific;

    use services\parsers as Parsers;

    class MeteorParser extends Parsers\JsonParser {
        private $apiRunData;

        public function __construct() {
            parent::__construct();

            //call this after
            $this->parserVersion = "1.0";
            $this->model->name = $this->whoAmI();
        }

        public function setApiRunData($apiRunData) {
            $this->apiRunData = $apiRunData;
        }

        public function parse($rawJson) {
            parent::parse($rawJson);

            $this->buildDataModel();

            return $this->model;
        }

        private function buildDataModel() {
            if(!empty($this->json)) {
                $this->processData();
                $this->prepareModel();
                $this->processLists();
            }
        }

        private function processData() {
            $this->model->created = $this->getFormatedDateTimeStringFromTimestamp(time());
            $this->model->validTo = $this->getFormatedDateTimeString($this->apiRunData["validTo"]);
            $this->model->from = $this->getFormatedDateTimeString($this->apiRunData["from"]);
            $this->model->to = $this->getFormatedDateTimeString($this->apiRunData["to"]);
        }

        private function prepareModel() {
            for($hourTimestamp = $this->json->start; $hourTimestamp <= $this->json->end; $hourTimestamp = $hourTimestamp + $this->json->step) {
                $foundHour = array();
                
                $foundHour["timestamp"] = $hourTimestamp;
                $foundHour["datetime"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp);
                $foundHour["formattedDate"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp, "d.m.Y");
                $foundHour["formattedShortDate"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp, "d.m.");
                $foundHour["formattedTime"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp, "H:i");

                $foundHour["temperature"] = -1;
                $foundHour["unit"] = "celsius";

                $foundHour["windspeed"] = -1;
                $foundHour["windDir"] = -1;

                $foundHour["cloudiness"] = -1;
                $foundHour["fog"] = -1; //NO
                $foundHour["rain"] = -1;
                $foundHour["real"] = true;

                $this->model->hours[$foundHour["datetime"]] = $foundHour;
            }
        }

        private function processLists() {
            $i = 0;
            foreach($this->model->hours as $key => $hour) {
                $this->model->hours[$key]["temperature"] = round((float)$this->json->data->t2c->values[$i], 1);
                $this->model->hours[$key]["windspeed"] = round((float)$this->json->data->wspd->values[$i], 1);
                $this->model->hours[$key]["windDir"] = round((float)$this->json->data->wdir->values[$i] + 180); //-180 - 180 - so add 180
                
                $cloudsAvg = ((float)$this->json->data->cloud_low->values[$i] + (float)$this->json->data->cloud_mid->values[$i] + (float)$this->json->data->cloud_high->values[$i]) / 3; //AVG
                $this->model->hours[$key]["cloudiness"] = round($cloudsAvg * 100);
                
                if(isset($this->json->data->rain_dif->values[$i])) {
                    $this->model->hours[$key]["rain"] = round((float)$this->json->data->rain_dif->values[$i], 1);
                }
                else {
                    $this->model->hours[$key]["rain"] = round((float)$this->json->data->rain_dif->values[$i - 1], 1);
                }

                $i++;
            }
        }
    }
?>