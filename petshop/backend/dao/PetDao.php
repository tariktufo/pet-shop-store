<?php

require_once 'BaseDao.php';

class PetDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('pets');
    }
    
    /**
     * Dohvati ljubimce sa informacijama o vlasniku
     */
    public function getAllWithOwners() {
        try {
            $stmt = $this->db->query("
                SELECT p.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                ORDER BY p.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju ljubimaca: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati jednog ljubimca sa informacijama o vlasniku
     */
    public function getByIdWithOwner($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju ljubimca: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati ljubimce po vlasniku
     */
    public function getByOwnerId($ownerId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pets WHERE owner_id = ? ORDER BY name ASC");
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju ljubimaca: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati ljubimce po vrsti
     */
    public function getBySpecies($species) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.name as owner_name
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.species = ?
                ORDER BY p.name ASC
            ");
            $stmt->execute([$species]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju ljubimaca: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati ljubimce po rasi
     */
    public function getByBreed($breed) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.name as owner_name
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.breed = ?
                ORDER BY p.name ASC
            ");
            $stmt->execute([$breed]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju ljubimaca: " . $e->getMessage());
        }
    }
    
    /**
     * Pretraži ljubimce
     */
    public function search($keyword) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, u.name as owner_name
                FROM pets p
                LEFT JOIN users u ON p.owner_id = u.id
                WHERE p.name LIKE ? OR p.species LIKE ? OR p.breed LIKE ? OR u.name LIKE ?
                ORDER BY p.name ASC
            ");
            $searchTerm = "%{$keyword}%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri pretrazi: " . $e->getMessage());
        }
    }
}