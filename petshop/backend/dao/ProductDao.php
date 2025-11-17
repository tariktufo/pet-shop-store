<?php

require_once 'BaseDao.php';

class ProductDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('products');
    }
    
    /**
     * Dohvati proizvode po kategoriji
     */
    public function getByCategory($category) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE category = ? ORDER BY name ASC");
            $stmt->execute([$category]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju proizvoda: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati sve kategorije
     */
    public function getAllCategories() {
        try {
            $stmt = $this->db->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju kategorija: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati proizvode sa niskim zalihama
     */
    public function getLowStock($threshold = 10) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE stock_quantity <= ? ORDER BY stock_quantity ASC");
            $stmt->execute([$threshold]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju proizvoda: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati proizvode u određenom cjenovnom rangu
     */
    public function getByPriceRange($minPrice, $maxPrice) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM products 
                WHERE price BETWEEN ? AND ? 
                ORDER BY price ASC
            ");
            $stmt->execute([$minPrice, $maxPrice]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju proizvoda: " . $e->getMessage());
        }
    }
    
    /**
     * Ažuriraj zalihe proizvoda
     */
    public function updateStock($productId, $quantity) {
        try {
            $stmt = $this->db->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity + ? 
                WHERE id = ?
            ");
            return $stmt->execute([$quantity, $productId]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri ažuriranju zaliha: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati proizvod sa provjerom zaliha
     */
    public function getWithStockCheck($productId, $requiredQuantity) {
        try {
            $stmt = $this->db->prepare("
                SELECT *, 
                       (stock_quantity >= ?) as has_sufficient_stock 
                FROM products 
                WHERE id = ?
            ");
            $stmt->execute([$requiredQuantity, $productId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri provjeri zaliha: " . $e->getMessage());
        }
    }
    
    /**
     * Pretraži proizvode
     */
    public function search($keyword) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM products 
                WHERE name LIKE ? OR description LIKE ? OR category LIKE ?
                ORDER BY name ASC
            ");
            $searchTerm = "%{$keyword}%";
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri pretrazi: " . $e->getMessage());
        }
    }
}