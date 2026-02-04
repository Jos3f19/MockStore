<?php
/**
 * Order Controller
 * Handles order history and details
 */
class OrderController extends Controller
{
    /**
     * Display all orders
     */
    public function index(): void
    {
        $orderModel = new Order($this->config);
        $orders = $orderModel->getAll();
        
        $this->render('orders/index', [
            'orders' => $orders,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Display a single order
     * 
     * @param string $id Order ID
     */
    public function show(string $id): void
    {
        $orderModel = new Order($this->config);
        $order = $orderModel->find((int) $id);
        
        if (!$order) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('/orders');
            return;
        }
        
        // Query PlacetoPay for latest status if we have a request ID
        if ($order['request_id'] && in_array($order['status'], ['PENDING', 'OK'])) {
            $placetoPay = new PlacetoPayService($this->config);
            $response = $placetoPay->querySession((int) $order['request_id']);
            
            if (isset($response['status']['status'])) {
                $status = $response['status']['status'];
                $message = $response['status']['message'] ?? null;
                
                // Update order status if changed
                if ($status !== $order['status']) {
                    $orderModel->updateStatus($order['id'], $status, $message);
                    $order['status'] = $status;
                    $order['status_message'] = $message;
                }
            }
        }
        
        // Get order items
        $orderItems = $orderModel->getItems($order['id']);
        
        $this->render('orders/show', [
            'order' => $order,
            'orderItems' => $orderItems,
            'flash' => $this->getFlash(),
        ]);
    }
}
