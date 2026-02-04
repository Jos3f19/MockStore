<?php
/**
 * Database Class
 * Handles SQLite database connection and operations
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Get database instance (Singleton)
     */
    public static function getInstance(array $config): PDO
    {
        if (self::$instance === null) {
            try {
                $dbPath = $config['database']['path'];
                
                // Create database directory if it doesn't exist
                $dbDir = dirname($dbPath);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0755, true);
                }
                
                self::$instance = new PDO("sqlite:$dbPath");
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Initialize tables
                self::initializeTables();
            } catch (PDOException $e) {
                ErrorHandler::handleDatabaseException($e, 'database connection');
            }
        }
        
        return self::$instance;
    }

    /**
     * Initialize database tables
     */
    private static function initializeTables(): void
    {
        try {
            $db = self::$instance;
            
            // Create products table
            $db->exec("
                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    description TEXT,
                    price DECIMAL(10, 2) NOT NULL,
                    image VARCHAR(255),
                    stock INTEGER DEFAULT 100,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Create orders table
            $db->exec("
                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    reference VARCHAR(50) UNIQUE NOT NULL,
                    request_id INTEGER,
                    process_url TEXT,
                    status VARCHAR(50) DEFAULT 'PENDING',
                    status_message TEXT,
                    total DECIMAL(10, 2) NOT NULL,
                    currency VARCHAR(3) DEFAULT 'USD',
                    customer_name VARCHAR(255),
                    customer_email VARCHAR(255),
                    customer_phone VARCHAR(50),
                    customer_document VARCHAR(50),
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Create order_items table
            $db->exec("
                CREATE TABLE IF NOT EXISTS order_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                quantity INTEGER NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id),
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ");
        
        // Seed products if table is empty
        $count = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($count == 0) {
            self::seedProducts();
        }
        } catch (PDOException $e) {
            ErrorHandler::handleDatabaseException($e, 'table initialization');
        }
    }

    /**
     * Seed initial products
     */
    private static function seedProducts(): void
    {
        try {
            $db = self::$instance;
            
            $products = [
                [
                    'name' => 'Wireless Headphones Pro',
                    'description' => 'Premium wireless headphones with noise cancellation, 30-hour battery life, and crystal-clear audio quality.',
                    'price' => 149.99,
                    'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
                ],
                [
                    'name' => 'Smart Watch Series X',
                    'description' => 'Advanced smartwatch with health monitoring, GPS, water resistance, and customizable watch faces.',
                    'price' => 299.99,
                    'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400',
                ],
                [
                    'name' => 'Portable Bluetooth Speaker',
                    'description' => 'Compact and powerful speaker with 360° sound, waterproof design, and 12-hour playtime.',
                    'price' => 79.99,
                    'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400',
                ],
                [
                    'name' => 'Mechanical Gaming Keyboard',
                    'description' => 'RGB backlit mechanical keyboard with Cherry MX switches, programmable keys, and aluminum frame.',
                    'price' => 129.99,
                    'image' => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=400',
                ],
                [
                    'name' => 'Ergonomic Office Chair',
                    'description' => 'Premium ergonomic chair with lumbar support, adjustable armrests, and breathable mesh back.',
                    'price' => 399.99,
                    'image' => 'https://images.unsplash.com/photo-1580480055273-228ff5388ef8?w=400',
                ],
                [
                    'name' => 'USB-C Hub Multiport Adapter',
                    'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, SD card reader, and power delivery support.',
                    'price' => 49.99,
                    'image' => 'https://images.unsplash.com/photo-1625842268584-8f3296236761?w=400',
                ],
            ];
            
            $stmt = $db->prepare("
                INSERT INTO products (name, description, price, image) 
                VALUES (:name, :description, :price, :image)
            ");
            
            foreach ($products as $product) {
                $stmt->execute($product);
            }
        } catch (PDOException $e) {
            ErrorHandler::handleDatabaseException($e, 'product seeding');
        }
    }
}
