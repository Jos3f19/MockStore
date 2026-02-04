<?php
/**
 * Product Model
 * Handles all product-related database operations
 */
class Product
{
    private PDO $db;

    public function __construct(array $config)
    {
        $this->db = Database::getInstance($config);
    }

    /**
     * Get all products
     * 
     * @return array List of all products
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY id");
        return $stmt->fetchAll();
    }

    /**
     * Get a product by ID
     * 
     * @param int $id Product ID
     * @return array|null Product data or null if not found
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();
        
        return $product ?: null;
    }

    /**
     * Get multiple products by IDs
     * 
     * @param array $ids Array of product IDs
     * @return array List of products
     */
    public function findMany(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        
        return $stmt->fetchAll();
    }

    /**
     * Update product stock
     * 
     * @param int $id Product ID
     * @param int $quantity Quantity to decrease
     * @return bool Success status
     */
    public function decreaseStock(int $id, int $quantity): bool
    {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET stock = stock - :quantity 
            WHERE id = :id AND stock >= :quantity
        ");
        
        return $stmt->execute(['id' => $id, 'quantity' => $quantity]);
    }
}
