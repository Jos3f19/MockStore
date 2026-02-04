<?php
/**
 * PlacetoPay Service Class
 * Handles all communication with PlacetoPay WebCheckout API
 * 
 * Documentation: https://docs.placetopay.dev/en/checkout/
 */
class PlacetoPayService
{
    private string $login;
    private string $secretKey;
    private string $baseUrl;
    private string $locale;
    private string $returnUrl;

    public function __construct(array $config)
    {
        $this->login = $config['placetopay']['login'];
        $this->secretKey = $config['placetopay']['secretKey'];
        $this->baseUrl = $config['placetopay']['webcheckout_url'];
        $this->locale = $config['app']['locale'];
        $this->returnUrl = $config['app']['url'];
    }

    /**
     * Generate authentication object for API requests
     * 
     * According to PlacetoPay documentation:
     * - tranKey: Base64(SHA-256(nonce + seed + secretKey))
     * - nonce: Random value encoded in Base64
     * - seed: Current date in ISO 8601 format
     * 
     * @return array Authentication object
     */
    private function generateAuth(): array
    {
        // Generate seed (current date in ISO 8601 format)
        $seed = date('c');
        
        // Generate random nonce
        $rawNonce = strval(rand());
        
        // Generate tranKey: Base64(SHA-256(nonce + seed + secretKey))
        // Note: hash() with true parameter returns raw binary output
        $tranKey = base64_encode(
            hash('sha256', $rawNonce . $seed . $this->secretKey, true)
        );
        
        // Encode nonce in Base64
        $nonce = base64_encode($rawNonce);
        
        return [
            'login' => $this->login,
            'tranKey' => $tranKey,
            'nonce' => $nonce,
            'seed' => $seed,
        ];
    }

    /**
     * Create a new payment session
     * 
     * This method creates a session in PlacetoPay WebCheckout where 
     * the user will complete their payment.
     * 
     * @param array $orderData Order information
     * @return array Response with requestId and processUrl
     */
    public function createSession(array $orderData): array
    {
        $endpoint = $this->baseUrl . '/api/session';
        
        // Build the request payload
        $payload = [
            'auth' => $this->generateAuth(),
            'locale' => $this->locale,
            'payment' => [
                'reference' => $orderData['reference'],
                'description' => $orderData['description'],
                'amount' => [
                    'currency' => $orderData['currency'] ?? 'USD',
                    'total' => $orderData['total'],
                ],
                'items' => $orderData['items'] ?? [],
            ],
            'buyer' => [
                'name' => $orderData['buyer']['name'] ?? '',
                'surname' => $orderData['buyer']['surname'] ?? '',
                'email' => $orderData['buyer']['email'] ?? '',
                'mobile' => $orderData['buyer']['mobile'] ?? '',
                'document' => $orderData['buyer']['document'] ?? '',
                'documentType' => $orderData['buyer']['documentType'] ?? 'CC',
            ],
            'expiration' => date('c', strtotime('+1 hour')),
            'returnUrl' => $this->returnUrl . '/payment/return?reference=' . $orderData['reference'],
            'cancelUrl' => $this->returnUrl . '/payment/cancel?reference=' . $orderData['reference'],
            'ipAddress' => $orderData['ipAddress'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'userAgent' => $orderData['userAgent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'MockStore',
        ];
        
        // Make the API request
        $response = $this->makeRequest('POST', $endpoint, $payload);
        
        return $response;
    }

    /**
     * Query a session status
     * 
     * This method retrieves the current status of a payment session.
     * 
     * @param int $requestId The session request ID
     * @return array Session information with status
     */
    public function querySession(int $requestId): array
    {
        $endpoint = $this->baseUrl . '/api/session/' . $requestId;
        
        $payload = [
            'auth' => $this->generateAuth(),
        ];
        
        return $this->makeRequest('POST', $endpoint, $payload);
    }

    /**
     * Cancel a pending session
     * 
     * @param int $requestId The session request ID
     * @return array Response with cancellation status
     */
    public function cancelSession(int $requestId): array
    {
        $endpoint = $this->baseUrl . '/api/session/' . $requestId . '/cancel';
        
        $payload = [
            'auth' => $this->generateAuth(),
        ];
        
        return $this->makeRequest('POST', $endpoint, $payload);
    }

    /**
     * Make HTTP request to PlacetoPay API
     * 
     * @param string $method HTTP method
     * @param string $url API endpoint
     * @param array $data Request payload
     * @return array Decoded response
     */
    private function makeRequest(string $method, string $url, array $data): array
    {
        $jsonData = json_encode($data);
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
                
        if ($error) {
            return [
                'status' => [
                    'status' => 'ERROR',
                    'reason' => 'CONNECTION_ERROR',
                    'message' => 'Connection error: ' . $error,
                    'date' => date('c'),
                ],
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => [
                    'status' => 'ERROR',
                    'reason' => 'PARSE_ERROR',
                    'message' => 'Failed to parse response',
                    'date' => date('c'),
                ],
            ];
        }
        
        // Log the request for debugging/evidence
        $this->logRequest($method, $url, $data, $decoded, $httpCode);
        
        return $decoded;
    }

    /**
     * Log API requests for debugging and evidence
     * 
     * @param string $method HTTP method
     * @param string $url API endpoint
     * @param array $request Request payload
     * @param array $response API response
     * @param int $httpCode HTTP status code
     */
    private function logRequest(string $method, string $url, array $request, array $response, int $httpCode): void
    {
        $logDir = BASE_PATH . '/logs';
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = [
            'timestamp' => date('c'),
            'method' => $method,
            'url' => $url,
            'http_code' => $httpCode,
            'request' => $request,
            'response' => $response,
        ];
        
        // Remove sensitive data from log
        unset($logEntry['request']['auth']['tranKey']);
        unset($logEntry['request']['auth']['secretKey']);
        
        $logFile = $logDir . '/api_' . date('Y-m-d') . '.log';
        file_put_contents(
            $logFile,
            json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );
    }

    /**
     * Get status badge class based on payment status
     * 
     * @param string $status Payment status
     * @return string Bootstrap badge class
     */
    public static function getStatusBadgeClass(string $status): string
    {
        return match($status) {
            'APPROVED' => 'bg-success',
            'PENDING' => 'bg-warning text-dark',
            'REJECTED' => 'bg-danger',
            'OK' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Get human-readable status label
     * 
     * @param string $status Payment status
     * @return string Status label
     */
    public static function getStatusLabel(string $status): string
    {
        return match($status) {
            'APPROVED' => 'Approved',
            'PENDING' => 'Pending',
            'REJECTED' => 'Rejected',
            'OK' => 'Session Created',
            'FAILED' => 'Failed',
            default => $status,
        };
    }
}
