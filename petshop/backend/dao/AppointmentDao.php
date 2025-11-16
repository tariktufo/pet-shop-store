<?php

require_once 'BaseDao.php';

class AppointmentDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('appointments');
    }
    
    /**
     * Dohvati sve termine sa informacijama
     */
    public function getAllWithDetails() {
        try {
            $stmt = $this->db->query("
                SELECT a.*, 
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       p.name as pet_name, p.species as pet_species, p.breed as pet_breed
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati termin sa detaljima
     */
    public function getByIdWithDetails($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                       p.name as pet_name, p.species as pet_species, p.breed as pet_breed
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati termine po korisniku
     */
    public function getByUserId($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, p.name as pet_name, p.species as pet_species
                FROM appointments a
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.user_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati termine po ljubimcu
     */
    public function getByPetId($petId) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name as customer_name
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.pet_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->execute([$petId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati termine po datumu
     */
    public function getByDate($date) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as customer_name, u.phone as customer_phone,
                       p.name as pet_name, p.species as pet_species
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.appointment_date = ?
                ORDER BY a.appointment_time ASC
            ");
            $stmt->execute([$date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati termine po statusu
     */
    public function getByStatus($status) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as customer_name,
                       p.name as pet_name
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.status = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Provjeri da li termin postoji
     */
    public function isTimeSlotAvailable($date, $time, $excludeId = null) {
        try {
            if ($excludeId) {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM appointments 
                    WHERE appointment_date = ? AND appointment_time = ? AND status != 'cancelled' AND id != ?
                ");
                $stmt->execute([$date, $time, $excludeId]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) as count 
                    FROM appointments 
                    WHERE appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
                ");
                $stmt->execute([$date, $time]);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] == 0;
        } catch (PDOException $e) {
            throw new Exception("Greška pri provjeri termina: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati nadolazeće termine
     */
    public function getUpcoming($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, 
                       u.name as customer_name, u.phone as customer_phone,
                       p.name as pet_name, p.species as pet_species
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.appointment_date >= CURDATE() AND a.status = 'scheduled'
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju termina: " . $e->getMessage());
        }
    }
    
    /**
     * Ažuriraj status termina
     */
    public function updateStatus($appointmentId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $appointmentId]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri ažuriranju statusa: " . $e->getMessage());
        }
    }
}