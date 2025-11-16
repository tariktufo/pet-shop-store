<?php

require_once 'BaseDao.php';

class OrderDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('orders');
    }
    
    /**
     * Dohvati sve narudžbe sa informacijama o korisniku
     */
    public function getAllWithCustomers() {
        try {
            $stmt = $this->db->query("
                SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju narudžbi: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati narudžbu sa stavkama
     */
    public function getByIdWithItems($orderId) {
        try {
            // Dohvati narudžbu
            $stmt = $this->db->prepare("
                SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order) {
                return null;
            }
            
            // Dohvati stavke narudžbe
            $stmt = $this->db->prepare("
                SELECT oi.*, p.name as product_name, p.image_url
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$orderId]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $order;
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju narudžbe: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati narudžbe po korisniku
     */
    public function getByUserId($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM orders 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju narudžbi: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati narudžbe po statusu
     */
    public function getByStatus($status) {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, u.name as customer_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.status = ?
                ORDER BY o.created_at DESC
            ");
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dohvaćanju narudžbi: " . $e->getMessage());
        }
    }
    
    /**
     * Ažuriraj status narudžbe
     */
    public function updateStatus($orderId, $status) {
        try {
            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            return $stmt->execute([$status, $orderId]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri ažuriranju statusa: " . $e->getMessage());
        }
    }
    
    /**
     * Dodaj stavku narudžbe
     */
    public function addOrderItem($orderItem) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([
                $orderItem['order_id'],
                $orderItem['product_id'],
                $orderItem['quantity'],
                $orderItem['price']
            ]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri dodavanju stavke: " . $e->getMessage());
        }
    }
    
    /**
     * Obriši sve stavke narudžbe
     */
    public function deleteOrderItems($orderId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM order_items WHERE order_id = ?");
            return $stmt->execute([$orderId]);
        } catch (PDOException $e) {
            throw new Exception("Greška pri brisanju stavki: " . $e->getMessage());
        }
    }
    
    /**
     * Dohvati ukupnu prodaju
     */
    public function getTotalSales($startDate = null, $endDate = null) {
        try {
            $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
            
            if ($startDate && $endDate) {
                $sql .= " AND created_at BETWEEN ? AND ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$startDate, $endDate]);
            } else {
                $stmt = $this->db->query($sql);
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            throw new Exception("Greška pri izračunu prodaje: " . $e->getMessage());
        }
    }
}