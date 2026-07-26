<?php
class ReportRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $reporterId, int $adId, string $type, string $reason): void
    {
        $allowed = ['spam', 'fraud', 'misleading_price', 'incorrect_info', 'offensive', 'other'];
        if (!in_array($type, $allowed, true)) {
            $type = 'other';
        }
        $this->pdo->prepare(
            'INSERT INTO reports (reporter_id, ad_id, report_type, reason) VALUES (?, ?, ?, ?)'
        )->execute([$reporterId, $adId, $type, $reason]);
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT r.*, a.title AS ad_title, u.full_name AS reporter_name
             FROM reports r
             JOIN ads a ON a.ad_id = r.ad_id
             JOIN users u ON u.user_id = r.reporter_id
             ORDER BY r.status = "pending" DESC, r.created_at DESC LIMIT 200'
        );
        return $stmt->fetchAll();
    }

    public function review(int $reportId, string $status, int $adminId): void
    {
        $allowed = ['reviewed', 'action_taken', 'dismissed'];
        if (!in_array($status, $allowed, true)) {
            return;
        }
        $this->pdo->prepare(
            'UPDATE reports SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE report_id = ?'
        )->execute([$status, $adminId, $reportId]);
    }

    public function pendingCount(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
    }
}
