<?php

require_once 'BaseService.php';

class UserService extends BaseService {
    
    public function getAllUsers() {
        try {
            $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju korisnika: ' . $e->getMessage()];
        }
    }
    
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['error' => 'Korisnik nije pronađen'];
            }
            
            return $user;
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju korisnika: ' . $e->getMessage()];
        }
    }
    
    public function createUser($data) {
        // Validacija
        $errors = $this->validateRequired($data, ['name', 'email', 'password', 'role']);
        
        if (!empty($errors)) {
            return ['error' => implode(', ', $errors)];
        }
        
        if (!$this->validateEmail($data['email'])) {
            return ['error' => 'Nevalidan email format'];
        }
        
        if (strlen($data['password']) < 6) {
            return ['error' => 'Lozinka mora imati najmanje 6 karaktera'];
        }
        
        if (!in_array($data['role'], ['admin', 'customer'])) {
            return ['error' => 'Nevalidna uloga. Dozvoljeno: admin, customer'];
        }
        
        try {
            // Provjeri da li email već postoji
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                return ['error' => 'Email već postoji'];
            }
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, phone, address, role, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['name'],
                $data['email'],
                $hashedPassword,
                $data['phone'] ?? null,
                $data['address'] ?? null,
                $data['role']
            ]);
            
            $userId = $this->db->lastInsertId();
            return $this->getUserById($userId);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri kreiranju korisnika: ' . $e->getMessage()];
        }
    }
    
    public function updateUser($id, $data) {
        try {
            // Provjeri da li korisnik postoji
            $user = $this->getUserById($id);
            if (isset($user['error'])) {
                return $user;
            }
            
            $fields = [];
            $values = [];
            
            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $values[] = $data['name'];
            }
            
            if (isset($data['email'])) {
                if (!$this->validateEmail($data['email'])) {
                    return ['error' => 'Nevalidan email format'];
                }
                $fields[] = "email = ?";
                $values[] = $data['email'];
            }
            
            if (isset($data['password'])) {
                if (strlen($data['password']) < 6) {
                    return ['error' => 'Lozinka mora imati najmanje 6 karaktera'];
                }
                $fields[] = "password = ?";
                $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            if (isset($data['phone'])) {
                $fields[] = "phone = ?";
                $values[] = $data['phone'];
            }
            
            if (isset($data['address'])) {
                $fields[] = "address = ?";
                $values[] = $data['address'];
            }
            
            if (isset($data['role'])) {
                if (!in_array($data['role'], ['admin', 'customer'])) {
                    return ['error' => 'Nevalidna uloga'];
                }
                $fields[] = "role = ?";
                $values[] = $data['role'];
            }
            
            if (empty($fields)) {
                return ['error' => 'Nema podataka za ažuriranje'];
            }
            
            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $this->getUserById($id);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri ažuriranju korisnika: ' . $e->getMessage()];
        }
    }
    
    public function deleteUser($id) {
        try {
            $user = $this->getUserById($id);
            if (isset($user['error'])) {
                return $user;
            }
            
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Korisnik uspješno obrisan'];
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri brisanju korisnika: ' . $e->getMessage()];
        }
    }
}