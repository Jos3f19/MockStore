<?php
/**
 * Order Model
 * Handles all order-related database operations
 */
class Order
{
    private PDO $db;

    public function __construct(array $config)
    {
        $this->db = Database::getInstance($config);
    }

    /**
     * Create a new order
     * 
     * @param array $data Order data
     * @return int|false Order ID or false on failure
     */
    public function create(array $data): int|false
    {
        $stmt = $this->db->prepare("
            INSERT INTO orders (
                reference, request_id, process_url, status, status_message,
                total, currency, customer_name, customer_email, customer_phone,
                customer_document, ip_address, user_agent
            ) VALUES (
                :reference, :request_id, :process_url, :status, :status_message,
                :total, :currency, :customer_name, :customer_email, :customer_phone,
                :customer_document, :ip_address, :user_agent
            )
        ");
        
        $success = $stmt->execute([
            'reference' => $data['reference'],
            'request_id' => $data['request_id'] ?? null,
            'process_url' => $data['process_url'] ?? null,
            'status' => $data['status'] ?? 'PENDING',
            'status_message' => $data['status_message'] ?? null,
            'total' => $data['total'],
            'currency' => $data['currency'] ?? 'USD',
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_document' => $data['customer_document'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
        
        return $success ? (int) $this->db->lastInsertId() : false;
    }

    /**
     * Add items to an order
     * 
     * @param int $orderId Order ID
     * @param array $items Array of items
     * @return bool Success status
     */
    public function addItems(int $orderId, array $items): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
            VALUES (:order_id, :product_id, :product_name, :quantity, :price)
        ");
        
        foreach ($items as $item) {
            $stmt->execute([
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
        
        return true;
    }

    /**
     * Get all orders
     * 
     * @return array List of all orders
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find order by ID
     * 
     * @param int $id Order ID
     * @return array|null Order data or null
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        
        return $order ?: null;
    }

    /**
     * Find order by reference
     * 
     * @param string $reference Order reference
     * @return array|null Order data or null
     */
    public function findByReference(string $reference): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE reference = :reference");
        $stmt->execute(['reference' => $reference]);
        $order = $stmt->fetch();
        
        return $order ?: null;
    }

    /**
     * Get order items
     * 
     * @param int $orderId Order ID
     * @return array List of order items
     */
    public function getItems(int $orderId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Update order status
     * 
     * @param int $id Order ID
     * @param string $status New status
     * @param string|null $message Status message
     * @return bool Success status
     */
    public function updateStatus(int $id, string $status, ?string $message = null): bool
    {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET status = :status, 
                status_message = :message, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $id,
            'status' => $status,
            'message' => $message,
        ]);
    }

    /**
     * Update PlacetoPay request info
     * 
     * @param int $id Order ID
     * @param int $requestId PlacetoPay request ID
     * @param string $processUrl PlacetoPay process URL
     * @return bool Success status
     */
    public function updatePlacetoPayInfo(int $id, int $requestId, string $processUrl): bool
    {
        $stmt = $this->db->prepare("
            UPDATE orders 
            SET request_id = :request_id, 
                process_url = :process_url,
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'id' => $id,
            'request_id' => $requestId,
            'process_url' => $processUrl,
        ]);
    }

    /**
     * Generate unique order reference
     * 
     * @return string Unique reference
     */
    public function generateReference(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
