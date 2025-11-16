<?php

class BaseDao {
    protected $db;
    protected $table;
    
    public function __construct($table = null) {
        global $pdo;
        $this->db = $pdo;
        $this->table = $table;
    }
    
    /**
     * Dohvati sve zapise iz tabele
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati zapis po ID-u
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju: " . $e->getMessage());
        }
    }
    
    /**
     * Dodaj novi zapis
     */
    public function insert($data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            
            $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_values($data));
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Greška pri dodavanju: " . $e->getMessage());
        }
    }
    
    /**
     * Ažuriraj zapis
     */
    public function update($id, $data) {
        try {
            $set = [];
            foreach (array_keys($data) as $key) {
                $set[] = "{$key} = ?";
            }
            $setString = implode(', ', $set);
            
            $sql = "UPDATE {$this->table} SET {$setString} WHERE id = ?";
            $values = array_values($data);
            $values[] = $id;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            throw new Exception("Greška pri ažuriranju: " . $e->getMessage());
        }
    }
    
    /**
     * Obriši zapis
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri brisanju: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati zapise sa WHERE uslovom
     */
    public function getWhere($column, $value) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
            $stmt->execute([$value]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju: " . $e->getMessage());
        }
    }
    
    /**
     * Prebroj zapise
     */
    public function count($where = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            
            if ($where) {
                $sql .= " WHERE {$where}";
            }
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            throw new Exception("Greška pri brojanju: " . $e->getMessage());
        }
    }
}