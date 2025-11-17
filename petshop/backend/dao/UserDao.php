<?php

require_once 'BaseDao.php';

class UserDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('users');
    }
    
    /**
     * Dohvati korisnika po email-u
     */
    public function getByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju korisnika: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati korisnike po ulozi
     */
    public function getByRole($role) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE role = ? ORDER BY name ASC");
            $stmt->execute([$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju korisnika: " . $e->getMessage());
        }
    }
    
    /**
     * Provjeri da li email postoji
     */
    public function emailExists($email, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $excludeId]);
            } else {
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                $stmt->execute([$email]);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            throw new Exception("Greška pri provjeri email-a: " . $e->getMessage());
        }
    }
    
    /**
     * Ažuriraj lozinku korisnika
     */
    public function updatePassword($userId, $hashedPassword) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashedPassword, $userId]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri ažuriranju lozinke: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati statistiku korisnika
     */
    public function getUserStats($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM pets WHERE owner_id = ?) as pets_count,
                    (SELECT COUNT(*) FROM orders WHERE user_id = ?) as orders_count,
                    (SELECT COUNT(*) FROM appointments WHERE user_id = ?) as appointments_count
            ");
            $stmt->execute([$userId, $userId, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju statistike: " . $e->getMessage());
        }
    }
}