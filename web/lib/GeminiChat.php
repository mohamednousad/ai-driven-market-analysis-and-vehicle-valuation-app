<?php
class GeminiChat
{
    private string $apiKey;
    private string $model;

    public function __construct(string $apiKey = GEMINI_API_KEY, string $model = GEMINI_MODEL)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    public function ask(array $history, string $userMessage): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Assistant is not configured. Add GEMINI_API_KEY in web/config/config.php.'];
        }

        $system = 'You are AutoValue Assistant, a helpful vehicle buying guide for the Sri Lankan used car market. '
            . 'Ask short clarification questions about budget (in Rs), preferred brand, model, fuel type, transmission and mileage, '
            . 'then summarise a clear search plan. Be neutral and never give financial or legal guarantees.';

        $contents = [];
        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $turn['text']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($this->model)
            . ':generateContent?key=' . urlencode($this->apiKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => $contents,
        ]));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['success' => false, 'message' => 'Could not reach the assistant. Check your internet connection.'];
        }
        $decoded = json_decode((string)$response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($text === null) {
            $apiMessage = $decoded['error']['message'] ?? 'Assistant returned an unexpected response.';
            return ['success' => false, 'message' => $apiMessage];
        }
        return ['success' => true, 'text' => $text];
    }
}
