<?php
class GeminiChat
{
    private string $apiKey;
    private string $model;

    public function __construct(string $apiKey, string $model = 'gemini-1.5-flash')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function isConfigured(): bool
    {
        return trim($this->apiKey) !== '';
    }

    public function ask(string $message, array $history = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'The AI assistant is not configured yet. An administrator can add a Gemini API key in Settings.',
            ];
        }

        $systemPrompt = 'You are a helpful vehicle-buying assistant for a Sri Lankan used-vehicle marketplace. '
            . 'Help buyers by asking about their budget, preferred brand, body type, mileage and fuel type, then '
            . 'summarise a clear vehicle search plan. Keep answers short, friendly and practical. Prices are in LKR. '
            . 'Give data-backed guidance only. Never give legal or financial guarantees, and remind buyers that '
            . 'final decisions need a physical inspection.';

        $contents = [];
        foreach ($history as $turn) {
            $role = ($turn['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => (string)($turn['text'] ?? '')]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $payload = [
            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents,
            'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 512],
        ];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/'
            . rawurlencode($this->model) . ':generateContent?key=' . rawurlencode($this->apiKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'Unable to reach the AI assistant right now.'];
        }
        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            $apiMessage = $decoded['error']['message'] ?? 'The AI assistant returned an empty response.';
            return ['success' => false, 'message' => $apiMessage];
        }
        return ['success' => true, 'reply' => trim($text)];
    }
}
