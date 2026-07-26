<?php
class AnalysisRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(int $adId, float $submitted, array $prediction, string $result): void
    {
        $this->pdo->prepare(
            'INSERT INTO analysis
                (ad_id, submitted_price, predicted_price, lower_bound, upper_bound, confidence_score, result)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $adId,
            $submitted,
            $prediction['predicted_price'],
            $prediction['lower_bound'],
            $prediction['upper_bound'],
            $prediction['confidence_score'],
            $result,
        ]);
    }

    public function latestForAd(int $adId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM analysis WHERE ad_id = ? ORDER BY analyzed_at DESC LIMIT 1'
        );
        $stmt->execute([$adId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
