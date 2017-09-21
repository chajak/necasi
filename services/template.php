<?php
    class Template extends BaseClass {
        protected $file;
        protected $filePath;
        protected $values = array();
    
        public function __construct($file) {
            $this->file = $file;
            $this->filePath = self::$config["templatesDir"].$this->file;

            $this->set("themeUrl", self::$config["themesUrl"].self::$config["theme"]);
            $this->set("resourcesUrl", self::$config["resourcesUrl"]);
        }
        
        public function set($key, $value) {
            $this->values[$key] = $value;
        }
        
        public function output() {
            if (!file_exists($this->filePath)) {
                return "Error loading template file ($this->file).";
            }
            
            $output = file_get_contents($this->filePath);

            foreach ($this->values as $key => $value) {
                $tagToReplace = "{{".$key."}}";
                $output = str_replace($tagToReplace, $value, $output);
            }
        
            return $output;
        }
    }
?>