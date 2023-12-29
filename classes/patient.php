<?php
    include 'user.php';
    class Patient extends user {
        // Constructor
        public function __construct($data) {
           parent::__construct($data);
        }        
    }
?>