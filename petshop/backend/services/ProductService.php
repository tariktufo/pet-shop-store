<?php

require_once 'BaseService.php';

class ProductService extends BaseService {
    
    public function getAllProducts() {
        try {
            $stmt = $this->db->query("SELECT * FROM products ORDER BY name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju proizvoda: ' . $e->getMessage()];
        }
    }
    
    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                return ['error' => 'Proizvod nije pronađen'];
            }
            
            return $product;
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju proizvoda: ' . $e->getMessage()];
        }
    }
    
    public function getProductsByCategory($category) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE category = ? ORDER BY name ASC");
            $stmt->execute([$category]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju proizvoda: ' . $e->getMessage()];
        }
    }
    
    public function createProduct($data) {
        $errors = $this->validateRequired($data, ['name', 'category', 'price', 'stock_quantity']);
        
        if (!empty($errors)) {
            return ['error' => implode(', ', $errors)];
        }
        
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['error' => 'Cijena mora biti pozitivan broj'];
        }
        
        if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
            return ['error' => 'Količina na lageru mora biti pozitivan broj'];
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO products (name, description, category, price, stock_quantity, image_url, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['category'],
                $data['price'],
                $data['stock_quantity'],
                $data['image_url'] ?? null
            ]);
            
            $productId = $this->db->lastInsertId();
            return $this->getProductById($productId);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri kreiranju proizvoda: ' . $e->getMessage()];
        }
    }
    
    public function updateProduct($id, $data) {
        try {
            $product = $this->getProductById($id);
            if (isset($product['error'])) {
                return $product;
            }
            
            $fields = [];
            $values = [];
            
            if (isset($data['name'])) {
                $fields[] = "name = ?";
                $values[] = $data['name'];
            }
            
            if (isset($data['description'])) {
                $fields[] = "description = ?";
                $values[] = $data['description'];
            }
            
            if (isset($data['category'])) {
                $fields[] = "category = ?";
                $values[] = $data['category'];
            }
            
            if (isset($data['price'])) {
                if (!is_numeric($data['price']) || $data['price'] < 0) {
                    return ['error' => 'Cijena mora biti pozitivan broj'];
                }
                $fields[] = "price = ?";
                $values[] = $data['price'];
            }
            
            if (isset($data['stock_quantity'])) {
                if (!is_numeric($data['stock_quantity']) || $data['stock_quantity'] < 0) {
                    return ['error' => 'Količina mora biti pozitivan broj'];
                }
                $fields[] = "stock_quantity = ?";
                $values[] = $data['stock_quantity'];
            }
            
            if (isset($data['image_url'])) {
                $fields[] = "image_url = ?";
                $values[] = $data['image_url'];
            }
            
            if (empty($fields)) {
                return ['error' => 'Nema podataka za ažuriranje'];
            }
            
            $values[] = $id;
            $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return $this->getProductById($id);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri ažuriranju proizvoda: ' . $e->getMessage()];
        }
    }
    
    public function deleteProduct($id) {
        try {
            $product = $this->getProductById($id);
            if (isset($product['error'])) {
                return $product;
            }
            
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Proizvod uspješno obrisan'];
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri brisanju proizvoda: ' . $e->getMessage()];
        }
    }
    
    public function updateStock($id, $quantity) {
        try {
            $product = $this->getProductById($id);
            if (isset($product['error'])) {
                return $product;
            }
            
            $newQuantity = $product['stock_quantity'] + $quantity;
            
            if ($newQuantity < 0) {
                return ['error' => 'Nedovoljna količina na lageru'];
            }
            
            $stmt = $this->db->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $id]);
            
            return $this->getProductById($id);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri ažuriranju zaliha: ' . $e->getMessage()];
        }
    }
}