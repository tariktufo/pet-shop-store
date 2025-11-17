<?php

class BaseService {
    protected $db;
    
    public function __construct() {
        global $pdo;
        $this->db = $pdo;
    }
    
    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "Polje '$field' je obavezno";
            }
        }
        return $errors;
    }
    
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    protected function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    protected function validateDateTime($datetime) {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $d && $d->format('Y-m-d H:i:s') === $datetime;
    }
}