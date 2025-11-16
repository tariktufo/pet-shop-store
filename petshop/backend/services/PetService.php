<?php

require_once 'BaseService.php';

class PetService extends BaseService {
    
    public function getAllPets() {
        try {
            $stmt = $this->db->query("
                SELECT p.*, u.name as owner_name 
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                ORDER BY p.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju ljubimaca: ' . $e->getMessage()];
        }
    }
    
    public function getPetById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.name as owner_name 
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $pet = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pet) {
                return ['error' => 'Ljubimac nije pronađen'];
            }
            
            return $pet;
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju ljubimca: ' . $e->getMessage()];
        }
    }
    
    public function getPetsByOwner($ownerId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pets WHERE owner_id = ?");
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju ljubimaca: ' . $e->getMessage()];
        }
    }
    
    public function createPet($data) {
        $errors = $this->validateRequired($data, ['name', 'species', 'owner_id']);
        
        if (!empty($errors)) {
            return ['error' => implode(', ', $errors)];
        }
        
        if (isset($data['date_of_birth']) && !$this->validateDate($data['date_of_birth'])) {
            return ['error' => 'Nevalidan format datuma. Koristi: YYYY-MM-DD'];
        }
        
        try {
            // Provjeri da li vlasnik postoji
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$data['owner_id']]);
            if (!$stmt->fetch()) {
                return ['error' => 'Vlasnik ne postoji'];
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO pets (name, species, breed, date_of_birth, owner_id, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['name'],
                $data['species'],
                $data['breed'] ?? null,
                $data['date_of_birth'] ?? null,
                $data['owner_id']
            ]);
            
            $petId = $this->db->lastInsertId();
            return $this->getPetById($petId);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri kreiranju ljubimca: ' . $e->getMessage()];
        }
    }
    
    public function updatePet($id, $data) {
        try {
            $pet = $this->getPetById($id);
            if (isset($pet['error'])) {
                return $pet;
            }
            
            $fields = [];
            $values = [];
            
            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $values[] = $data['name'];
            }
            
            if (isset($data['species'])) {
                $fields[] = "species = ?";
                $values[] = $data['species'];
            }
            
            if (isset($data['breed'])) {
                $fields[] = "breed = ?";
                $values[] = $data['breed'];
            }
            
            if (isset($data['date_of_birth'])) {
                if (!$this->validateDate($data['date_of_birth'])) {
                    return ['error' => 'Nevalidan format datuma'];
                }
                $fields[] = "date_of_birth = ?";
                $values[] = $data['date_of_birth'];
            }
            
            if (isset($data['owner_id'])) {
                $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$data['owner_id']]);
                if (!$stmt->fetch()) {
                    return ['error' => 'Vlasnik ne postoji'];
                }
                $fields[] = "owner_id = ?";
                $values[] = $data['owner_id'];
            }
            
            if (empty($fields)) {
                return ['error' => 'Nema podataka za ažuriranje'];
            }
            
            $values[] = $id;
            $sql = "UPDATE pets SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $this->getPetById($id);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri ažuriranju ljubimca: ' . $e->getMessage()];
        }
    }
    
    public function deletePet($id) {
        try {
            $pet = $this->getPetById($id);
            if (isset($pet['error'])) {
                return $pet;
            }
            
            $stmt = $this->db->prepare("DELETE FROM pets WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Ljubimac uspješno obrisan'];
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri brisanju ljubimca: ' . $e->getMessage()];
        }
    }
}