<?php

require_once 'BaseService.php';

class OrderService extends BaseService {
    
    public function getAllOrders() {
        try {
            $stmt = $this->db->query("
                SELECT o.*, u.name as customer_name 
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju narudžbi: ' . $e->getMessage()];
        }
    }
    
    public function getOrderById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, u.name as customer_name, u.email as customer_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                return ['error' => 'Narudžba nije pronađena'];
            }
            
            // Dohvati stavke narudžbe
            $stmt = $this->db->prepare("
                SELECT oi.*, p.name as product_name, p.price as unit_price
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$id]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $order;
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju narudžbe: ' . $e->getMessage()];
        }
    }
    
    public function getOrdersByUser($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => 'Greška pri dohvaćanju narudžbi: ' . $e->getMessage()];
        }
    }
    
    public function createOrder($data) {
        $errors = $this->validateRequired($data, ['user_id', 'items']);
        
        if (!empty($errors)) {
            return ['error' => implode(', ', $errors)];
        }
        
        if (!is_array($data['items']) || empty($data['items'])) {
            return ['error' => 'Narudžba mora imati barem jednu stavku'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Provjeri korisnika
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$data['user_id']]);
            if (!$stmt->fetch()) {
                $this->db->rollBack();
                return ['error' => 'Korisnik ne postoji'];
            }
            
            $totalAmount = 0;
            
            // Provjeri proizvode i izračunaj ukupan iznos
            foreach ($data['items'] as $item) {
                if (!isset($item['product_id']) || !isset($item['quantity'])) {
                    $this->db->rollBack();
                    return ['error' => 'Svaka stavka mora imati product_id i quantity'];
                }
                
                $stmt = $this->db->prepare("SELECT price, stock_quantity FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$product) {
                    $this->db->rollBack();
                    return ['error' => 'Proizvod ID ' . $item['product_id'] . ' ne postoji'];
                }
                
                if ($product['stock_quantity'] < $item['quantity']) {
                    $this->db->rollBack();
                    return ['error' => 'Nedovoljna količina na lageru za proizvod ID ' . $item['product_id']];
                }
                
                $totalAmount += $product['price'] * $item['quantity'];
            }
            
            // Kreiraj narudžbu
            $stmt = $this->db->prepare("
                INSERT INTO orders (user_id, total_amount, status, created_at) 
                VALUES (?, ?, 'pending', NOW())
            ");
            $stmt->execute([$data['user_id'], $totalAmount]);
            $orderId = $this->db->lastInsertId();
            
            // Dodaj stavke narudžbe i ažuriraj zalihe
            foreach ($data['items'] as $item) {
                $stmt = $this->db->prepare("SELECT price FROM products WHERE id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $this->db->prepare("
                    INSERT INTO order_items (order_id, product_id, quantity, price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $product['price']
                ]);
                
                // Ažuriraj zalihe
                $stmt = $this->db->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity - ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            $this->db->commit();
            return $this->getOrderById($orderId);
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Greška pri kreiranju narudžbe: ' . $e->getMessage()];
        }
    }
    
    public function updateOrderStatus($id, $status) {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return ['error' => 'Nevalidan status. Dozvoljeno: ' . implode(', ', $validStatuses)];
        }
        
        try {
            $order = $this->getOrderById($id);
            if (isset($order['error'])) {
                return $order;
            }
            
            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            
            return $this->getOrderById($id);
            
        } catch (PDOException $e) {
            return ['error' => 'Greška pri ažuriranju statusa: ' . $e->getMessage()];
        }
    }
    
    public function deleteOrder($id) {
        try {
            $order = $this->getOrderById($id);
            if (isset($order['error'])) {
                return $order;
            }
            
            $this->db->beginTransaction();
            
            // Vrati proizvode na lager
            foreach ($order['items'] as $item) {
                $stmt = $this->db->prepare("
                    UPDATE products 
                    SET stock_quantity = stock_quantity + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Obriši stavke narudžbe
            $stmt = $this->db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$id]);
            
            // Obriši narudžbu
            $stmt = $this->db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Narudžba uspješno obrisana'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['error' => 'Greška pri brisanju narudžbe: ' . $e->getMessage()];
        }
    }
}