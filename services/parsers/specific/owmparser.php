<?php
    namespace services\parsers\specific;

    use services\parsers as Parsers;

    class OwmParser extends Parsers\JsonParser {
        public function __construct() {
            parent::__construct();

            //call this after
            $this->parserVersion = "1.0";
            $this->model->name = $this->whoAmI();
        }

        public function parse($rawJson) {
            parent::parse($rawJson);

            $this->buildDataModel();

            return $this->model;
        }

        private function buildDataModel() {
            if(!empty($this->json)) {
                $this->processList();
            }
        }

        private function processList() {
            $items = $this->json->list;

            foreach($items as $item) {
                $hourTimestamp = $item->dt;

                $temperatureObject = $item->main;
                $windSpeedObject = $item->wind;
                $cloudinessObject = $item->clouds;

                $foundHour = array();
                $foundHour["timestamp"] = $hourTimestamp;
                $foundHour["datetime"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp);
                $foundHour["formattedDate"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp, "d.m.Y");
                $foundHour["formattedShortDate"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp, "d.m.");
                $foundHour["formattedTime"] = $this->getFormatedDateTimeStringFromTimestamp($hourTimestamp, "H:i");

                $foundHour["temperature"] = round((float)$temperatureObject->temp, 1);
                $foundHour["unit"] = "celsius";

                $foundHour["windspeed"] = round((float)$windSpeedObject->speed, 1);

                $foundHour["cloudiness"] = round((float)$cloudinessObject->all);
                $foundHour["fog"] = "";
                $foundHour["real"] = true;

                //first found
                if(empty($this->model->created)) {
                    $this->model->created = $this->getFormatedDateTimeStringFromTimestamp(time());
                    $this->model->from = $this->getFormatedDateTimeStringFromTimestamp($foundHour["datetime"]);
                }
                else {
                    //second found
                    if(empty($this->model->validTo)) {
                        $this->model->validTo = $this->getFormatedDateTimeStringFromTimestamp($foundHour["datetime"]);
                    }
                }

                $this->model->hours[$foundHour["datetime"]] = $foundHour;
            }


            //after last found
            $this->model->to = $this->getFormatedDateTimeStringFromTimestamp($foundHour["datetime"]);
            
            //valid to check
            $validTo = strtotime($this->model->validTo);
            if($validTo < time()) {
                $validTo = strtotime("+1 hour");
            }
            $this->model->validTo = $this->getFormatedDateTimeStringFromTimestamp($validTo);

            //must interpolate datetimes between 3 hour intervals
            $this->interpolateRest();
        }
    }
?>