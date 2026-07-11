<?php
class PredictionApi
{
    private string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    private function request(string $endpoint, string $method = 'GET', ?array $payload = null): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'The AI valuation service is not available. Please start the Flask service.'];
        }
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Invalid response from the AI valuation service.'];
        }
        return $decoded;
    }

    public function health(): array
    {
        return $this->request('/health');
    }

    public function schema(): array
    {
        return $this->request('/schema');
    }

    public function predict(array $inputs): array
    {
        return $this->request('/predict', 'POST', ['inputs' => $inputs]);
    }
}
