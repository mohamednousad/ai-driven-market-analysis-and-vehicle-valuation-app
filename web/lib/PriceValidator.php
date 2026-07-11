<?php
class PriceValidator
{
    private PredictionApi $api;
    private float $bandPercent;

    public function __construct(PredictionApi $api, float $bandPercent)
    {
        $this->api = $api;
        $this->bandPercent = $bandPercent;
    }

    public function evaluate(array $inputs, float $askingPrice): array
    {
        $response = $this->api->predict($inputs);
        if (($response['success'] ?? false) !== true) {
            return [
                'ok' => false,
                'message' => $response['message'] ?? 'Prediction failed.',
            ];
        }

        $predicted = (float)($response['prediction']['predicted_price'] ?? 0);
        if ($predicted <= 0) {
            return ['ok' => false, 'message' => 'The AI model returned an invalid price estimate.'];
        }

        $band = $this->bandPercent / 100;
        $lower = $predicted * (1 - $band);
        $upper = $predicted * (1 + $band);
        $fair = ($askingPrice >= $lower && $askingPrice <= $upper);

        $verdict = 'fair';
        if ($askingPrice > $upper) {
            $verdict = 'overpriced';
        } elseif ($askingPrice < $lower) {
            $verdict = 'underpriced';
        }

        return [
            'ok' => true,
            'fair' => $fair,
            'verdict' => $verdict,
            'predicted_price' => round($predicted, 2),
            'lower_bound' => round($lower, 2),
            'upper_bound' => round($upper, 2),
            'band_percent' => $this->bandPercent,
            'asking_price' => $askingPrice,
        ];
    }
}
