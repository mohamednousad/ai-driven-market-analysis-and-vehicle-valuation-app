<?php
class PredictionApi
{
    private string $baseUrl;

    public function __construct(string $baseUrl = AI_API_BASE_URL)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    private function request(string $endpoint, ?array $payload = null): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['success' => false, 'message' => 'AI valuation service is offline. Start ai_service (python app.py) and try again.'];
        }
        $decoded = json_decode((string)$response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Invalid response from the AI valuation service.'];
        }
        return $decoded;
    }

    public function health(): array
    {
        return $this->request('/health');
    }

    public function predict(array $inputs): array
    {
        return $this->request('/predict', ['inputs' => $inputs]);
    }
}
