<?php
final class AiClient
{
    public static function analyze(array $vehicle, float $priceLkr): array
    {
        $payload = json_encode([
            'make' => $vehicle['make'],
            'model' => $vehicle['model'],
            'manufacture_year' => (int)$vehicle['manufacture_year'],
            'engine_cc' => (int)$vehicle['engine_cc'],
            'transmission' => $vehicle['transmission'],
            'fuel_type' => $vehicle['fuel_type'],
            'mileage' => (int)$vehicle['mileage'],
            'price' => $priceLkr,
        ]);
        $ch = curl_init(Config::get('AI_ENDPOINT'));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => (int)Config::get('AI_TIMEOUT'),
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false || $httpCode !== 200) {
            return ['ok' => false, 'error' => 'AI service is not reachable. Make sure the Flask service is running on port 5000.'];
        }
        $data = json_decode($response, true);
        if (!is_array($data) || !isset($data['fair_price_status'])) {
            return ['ok' => false, 'error' => 'AI service returned an unexpected response.'];
        }
        return ['ok' => true, 'data' => $data];
    }
}
