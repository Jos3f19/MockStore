<?php
/**
 * Payment Controller
 * Handles payment callbacks and status updates from PlacetoPay
 */
class PaymentController extends Controller
{
    /**
     * Handle return from PlacetoPay after payment
     */
    public function return(): void
    {
        // Rate limiting: Max 10 payment status checks per minute per IP
        $clientIp = RateLimiter::getClientIdentifier();
        RateLimiter::enforce($clientIp, 10, 60, 'payment_return', 'Too many payment status requests. Please wait.');
        
        $reference = $_GET['reference'] ?? null;
        
        if (!$reference) {
            $this->setFlash('error', 'Invalid payment reference');
            $this->redirect('/');
            return;
        }
        
        // Find the order
        $orderModel = new Order($this->config);
        $order = $orderModel->findByReference($reference);
        
        if (!$order) {
            $this->setFlash('error', 'Order not found');
            $this->redirect('/');
            return;
        }
        
        // Query PlacetoPay for the current status
        if ($order['request_id']) {
            $placetoPay = new PlacetoPayService($this->config);
            $response = $placetoPay->querySession((int) $order['request_id']);
            
            if (isset($response['status']['status'])) {
                $status = $response['status']['status'];
                $message = $response['status']['message'] ?? null;
                
                // Update order status
                $orderModel->updateStatus($order['id'], $status, $message);
                
                // Refresh order data
                $order = $orderModel->findByReference($reference);
            }
        }
        
        // Get order items
        $orderItems = $orderModel->getItems($order['id']);
        
        $this->render('payment/result', [
            'order' => $order,
            'orderItems' => $orderItems,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Handle cancellation from PlacetoPay
     */
    public function cancel(): void
    {
        $reference = $_GET['reference'] ?? null;
        
        if ($reference) {
            $orderModel = new Order($this->config);
            $order = $orderModel->findByReference($reference);
            
            if ($order) {
                $orderModel->updateStatus($order['id'], 'CANCELLED', 'Payment cancelled by user');
            }
        }
        
        $this->setFlash('info', 'Payment was cancelled');
        $this->redirect('/orders');
    }
}
