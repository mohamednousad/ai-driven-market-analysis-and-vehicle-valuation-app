<?php
class PriceValidator
{
    public function classify(float $submitted, array $prediction): string
    {
        if ($submitted > (float)$prediction['upper_bound']) {
            return 'overpriced';
        }
        if ($submitted < (float)$prediction['lower_bound']) {
            return 'underpriced';
        }
        return 'fair';
    }

    public function message(string $result, array $prediction): string
    {
        $range = money((float)$prediction['lower_bound']) . ' - ' . money((float)$prediction['upper_bound']);
        if ($result === 'overpriced') {
            return 'Your asking price is above the AI fair range (' . $range . '). Lower the price to publish.';
        }
        if ($result === 'underpriced') {
            return 'Your asking price is below the AI fair range (' . $range . '). Raise the price to publish.';
        }
        return 'Your price is within the AI fair range (' . $range . ').';
    }
}
