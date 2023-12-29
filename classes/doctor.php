<?php
    include 'user.php';
    class doctor extends user{
        // Properties
        public $specialization;
    
        // Constructor
        public function __construct($data) {
            parent::__construct($data);
            $this->specialization = $data['specialization'];
        }        
    }
?>