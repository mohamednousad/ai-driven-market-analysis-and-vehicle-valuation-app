<?php
class ReportModel extends BaseModel
{
    public function add(int $reporterId, int $reportedUserId, ?int $adId, string $type, string $reason): void
    {
        $this->db->insert(
            'INSERT INTO reports (reporter_id, reported_user_id, ad_id, type, reason) VALUES (?, ?, ?, ?, ?)',
            [$reporterId, $reportedUserId, $adId, $type, $reason]
        );
    }

    public function all(): array
    {
        return $this->db->fetchAll(
            'SELECT r.*, ru.username AS reported_name, rp.username AS reporter_name, a.title AS ad_title
             FROM reports r
             JOIN users ru ON ru.id = r.reported_user_id
             JOIN users rp ON rp.id = r.reporter_id
             LEFT JOIN ads a ON a.id = r.ad_id
             ORDER BY r.created_at DESC'
        );
    }

    public function review(int $id, int $adminId, string $status): void
    {
        $this->db->execute('UPDATE reports SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?', [$status, $adminId, $id]);
    }

    public function pendingCount(): int
    {
        return (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM reports WHERE status = 'pending'")['c'] ?? 0);
    }
}
