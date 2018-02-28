<?php
    namespace services\parsers;

    use services\models as Models;

    class BaseParser extends \BaseClass {
        public $parserVersion;
        protected $model;

        public function __construct() {
            $this->model = new Models\DataModel;
            $this->parserVersion = "0.0";
        }

        public function whoAmI() {
            return (new \ReflectionClass($this))->getShortName()."_".$this->parserVersion;
        }

        public function parse($rawData) {
            die("Override Me!");
        }

        protected function interpolateRest() {
            $tempHour = null;
            $allHours = array();

            foreach($this->model->hours as $hour) {
                if(isset($tempHour)) {
                    $interpolatedHours = $this->doInterpolation($tempHour, $hour);
                }

                if(!empty($interpolatedHours)) {
                    foreach($interpolatedHours as $interpolatedHour) {
                        array_push($allHours, $interpolatedHour);
                    }

                    $interpolatedHours = array();
                }
                array_push($allHours, $hour);

                $tempHour = $hour;
            }

            $this->model->hours = $allHours;
        }

        protected function interpolatedValue($fromValue, $toValue, $steps) {
            return ($toValue - $fromValue) / $steps;
        }

        protected function doInterpolation($from, $to) {
            $interpolatedHours = array();
            $interpolationStep = 60 * 60;

            //do interpolation
            //JUST NUMBER OF STEPS
            $interpolationSteps = $this->interpolatedValue($from["timestamp"], $to["timestamp"], $interpolationStep);

            //NOW WE USE STEPS! instead of STEP
            $interpolationTemp = $this->interpolatedValue($from["temperature"], $to["temperature"], $interpolationSteps);
            $interpolationWind = $this->interpolatedValue($from["windspeed"], $to["windspeed"], $interpolationSteps);
            $interpolationCloud = $this->interpolatedValue($from["cloudiness"], $to["cloudiness"], $interpolationSteps);
            $interpolationWindDir = $this->interpolatedValue($from["windDir"], $to["windDir"], $interpolationSteps);

            if($interpolationSteps > 1) {
                for($i = 1; $i < $interpolationSteps; $i++) {
                    $interpolatedTimestamp = $from["timestamp"] + ($i * $interpolationStep);
                    $interpolatedTemp = $from["temperature"] + ($i * $interpolationTemp);
                    $interpolatedWind = $from["windspeed"] + ($i * $interpolationWind);
                    $interpolatedCloud = $from["cloudiness"] + ($i * $interpolationCloud);
                    $interpolatedWindDir = $from["windDir"] + ($i * $interpolationWindDir);

                    $interpolatedHour = array();
                    $interpolatedHour["timestamp"] = $interpolatedTimestamp;
                    $interpolatedHour["datetime"] = $this->getFormatedDateTimeStringFromTimestamp($interpolatedTimestamp);
                    $interpolatedHour["formattedDate"] = $this->getFormatedDateTimeStringFromTimestamp($interpolatedTimestamp, "d.m.Y");
                    $interpolatedHour["formattedShortDate"] = $this->getFormatedDateTimeStringFromTimestamp($interpolatedTimestamp, "d.m.");
                    $interpolatedHour["formattedTime"] = $this->getFormatedDateTimeStringFromTimestamp($interpolatedTimestamp, "H:i");
    
                    $interpolatedHour["temperature"] = round((float)$interpolatedTemp, 1);
                    $interpolatedHour["unit"] = $from["unit"];
    
                    $interpolatedHour["windspeed"] = round((float)$interpolatedWind, 1);
                    $interpolatedHour["windDir"] = round((float)$interpolatedWindDir);
    
                    $interpolatedHour["cloudiness"] = round((float)$interpolatedCloud);
                    $interpolatedHour["fog"] = -1; //NO
                    $interpolatedHour["rain"] = -1; //NO
                    $interpolatedHour["real"] = false;

                    array_push($interpolatedHours, $interpolatedHour);
                }
            }

            return $interpolatedHours;
        }
    }
?>