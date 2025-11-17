<?php

require_once 'BaseService.php';

class AppointmentService extends BaseService {
    
    public function getAllAppointments() {
        try {
            $stmt = $this->db->query("
                SELECT a.*, u.name as customer_name, p.name as pet_name
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju termina: ' . $e->getMessage()];
        }
    }
    
    public function getAppointmentById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name as customer_name, u.email as customer_email,
                       p.name as pet_name, p.species as pet_species
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$appointment) {
                return ['error' => 'Termin nije pronađen'];
            }
            
            return $appointment;
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju termina: ' . $e->getMessage()];
        }
    }
    
    public function getAppointmentsByUser($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, p.name as pet_name
                FROM appointments a
                LEFT JOIN pets p ON a.pet_id = p.id
                WHERE a.user_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju termina: ' . $e->getMessage()];
        }
    }
    
    public function getAppointmentsByPet($petId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM appointments WHERE pet_id = ? ORDER BY appointment_date DESC");
            $stmt->execute([$petId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju termina: ' . $e->getMessage()];
        }
    }
    
    public function createAppointment($data) {
        $errors = $this->validateRequired($data, ['user_id', 'pet_id', 'appointment_date', 'appointment_time', 'service_type']);
        
        if (!empty($errors)) {
            return ['error' => implode(', ', $errors)];
        }
        
        if (!$this->validateDate($data['appointment_date'])) {
            return ['error' => 'Nevalidan format datuma. Koristi: YYYY-MM-DD'];
        }
        
        // Validacija vremena (HH:MM:SS format)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $data['appointment_time'])) {
            return ['error' => 'Nevalidan format vremena. Koristi: HH:MM ili HH:MM:SS'];
        }
        
        try {
            // Provjeri korisnika
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$data['user_id']]);
            if (!$stmt->fetch()) {
                return ['error' => 'Korisnik ne postoji'];
            }
            
            // Provjeri ljubimca
            $stmt = $this->db->prepare("SELECT id, owner_id FROM pets WHERE id = ?");
            $stmt->execute([$data['pet_id']]);
            $pet = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$pet) {
                return ['error' => 'Ljubimac ne postoji'];
            }
            
            // Provjeri da li ljubimac pripada korisniku
            if ($pet['owner_id'] != $data['user_id']) {
                return ['error' => 'Ljubimac ne pripada ovom korisniku'];
            }
            
            // Provjeri da li termin već postoji
            $stmt = $this->db->prepare("
                SELECT id FROM appointments 
                WHERE appointment_date = ? AND appointment_time = ? AND status != 'cancelled'
            ");
            $stmt->execute([$data['appointment_date'], $data['appointment_time']]);
            if ($stmt->fetch()) {
                return ['error' => 'Termin za ovaj datum i vrijeme je već zauzet'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO appointments (user_id, pet_id, appointment_date, appointment_time, service_type, notes, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'scheduled', NOW())
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['pet_id'],
                $data['appointment_date'],
                $data['appointment_time'],
                $data['service_type'],
                $data['notes'] ?? null
            ]);
            
            $appointmentId = $this->db->lastInsertId();
            return $this->getAppointmentById($appointmentId);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri kreiranju termina: ' . $e->getMessage()];
        }
    }
    
    public function updateAppointment($id, $data) {
        try {
            $appointment = $this->getAppointmentById($id);
            if (isset($appointment['error'])) {
                return $appointment;
            }
            
            $fields = [];
            $values = [];
            
            if (isset($data['appointment_date'])) {
                if (!$this->validateDate($data['appointment_date'])) {
                    return ['error' => 'Nevalidan format datuma'];
                }
                $fields[] = "appointment_date = ?";
                $values[] = $data['appointment_date'];
            }
            
            if (isset($data['appointment_time'])) {
                if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $data['appointment_time'])) {
                    return ['error' => 'Nevalidan format vremena'];
                }
                $fields[] = "appointment_time = ?";
                $values[] = $data['appointment_time'];
            }
            
            if (isset($data['service_type'])) {
                $fields[] = "service_type = ?";
                $values[] = $data['service_type'];
            }
            
            if (isset($data['notes'])) {
                $fields[] = "notes = ?";
                $values[] = $data['notes'];
            }
            
            if (isset($data['status'])) {
                $validStatuses = ['scheduled', 'completed', 'cancelled'];
                if (!in_array($data['status'], $validStatuses)) {
                    return ['error' => 'Nevalidan status. Dozvoljeno: ' . implode(', ', $validStatuses)];
                }
                $fields[] = "status = ?";
                $values[] = $data['status'];
            }
            
            if (empty($fields)) {
                return ['error' => 'Nema podataka za ažuriranje'];
            }
            
            // Provjeri da li novi termin postoji (ako se mijenja datum/vrijeme)
            if (isset($data['appointment_date']) || isset($data['appointment_time'])) {
                $checkDate = $data['appointment_date'] ?? $appointment['appointment_date'];
                $checkTime = $data['appointment_time'] ?? $appointment['appointment_time'];
                
                $stmt = $this->db->prepare("
                    SELECT id FROM appointments 
                    WHERE appointment_date = ? AND appointment_time = ? 
                    AND id != ? AND status != 'cancelled'
                ");
                $stmt->execute([$checkDate, $checkTime, $id]);
                if ($stmt->fetch()) {
                    return ['error' => 'Termin za ovaj datum i vrijeme je već zauzet'];
                }
            }
            
            $values[] = $id;
            $sql = "UPDATE appointments SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $this->getAppointmentById($id);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri ažuriranju termina: ' . $e->getMessage()];
        }
    }
    
    public function deleteAppointment($id) {
        try {
            $appointment = $this->getAppointmentById($id);
            if (isset($appointment['error'])) {
                return $appointment;
            }
            
            $stmt = $this->db->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Termin uspješno obrisan'];
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri brisanju termina: ' . $e->getMessage()];
        }
    }
    
    public function cancelAppointment($id) {
        return $this->updateAppointment($id, ['status' => 'cancelled']);
    }
}