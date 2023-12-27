<?php
    class Patient {
        // Properties
        public $email;
        public $first_name;
        public $last_name;
        public $age;
        public $gender;
        public $password;
        public $phone;
    
        // Constructor
        public function __construct($data) {
            $this->email = trim($data['email']);
            $this->first_name = trim($data['first_name']);
            $this->last_name = trim($data['last_name']);
            $this->age = intval($data['age']);
            if(trim($data['gender'])=='male')
                $this->gender ='M';
            else
                $this->gender ='F';
            $this->phone = trim($data['phone']);
        }
    
        // Method to display patient information
        
    }
?>