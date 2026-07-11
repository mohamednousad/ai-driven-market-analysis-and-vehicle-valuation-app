<?php
class AdRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vehicle_ads
                (seller_id, title, brand, vehicle_model, model_year, mileage, engine_capacity,
                 fuel_type, transmission, condition_grade, asking_price, predicted_price,
                 lower_bound, upper_bound, description, location, image_path, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['seller_id'],
            $data['title'],
            $data['brand'],
            $data['vehicle_model'],
            $data['model_year'],
            $data['mileage'],
            $data['engine_capacity'],
            $data['fuel_type'],
            $data['transmission'],
            $data['condition_grade'],
            $data['asking_price'],
            $data['predicted_price'],
            $data['lower_bound'],
            $data['upper_bound'],
            $data['description'],
            $data['location'],
            $data['image_path'],
            $data['status'],
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, u.full_name AS seller_name, u.phone AS seller_phone, u.email AS seller_email
             FROM vehicle_ads a
             JOIN users u ON u.id = a.seller_id
             WHERE a.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $ad = $stmt->fetch();
        return $ad ?: null;
    }

    public function search(array $filters, int $page = 1, int $perPage = 9): array
    {
        $where = ["a.status = " . $this->pdo->quote(AD_STATUS_APPROVED)];
        $params = [];

        if (!empty($filters['brand'])) {
            $where[] = 'a.brand = ?';
            $params[] = $filters['brand'];
        }
        if (!empty($filters['fuel_type'])) {
            $where[] = 'a.fuel_type = ?';
            $params[] = $filters['fuel_type'];
        }
        if (!empty($filters['transmission'])) {
            $where[] = 'a.transmission = ?';
            $params[] = $filters['transmission'];
        }
        if (!empty($filters['keyword'])) {
            $where[] = '(a.title LIKE ? OR a.vehicle_model LIKE ? OR a.brand LIKE ?)';
            $kw = '%' . $filters['keyword'] . '%';
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }
        if (!empty($filters['min_price'])) {
            $where[] = 'a.asking_price >= ?';
            $params[] = (float)$filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = 'a.asking_price <= ?';
            $params[] = (float)$filters['max_price'];
        }
        if (!empty($filters['min_year'])) {
            $where[] = 'a.model_year >= ?';
            $params[] = (int)$filters['min_year'];
        }
        if (!empty($filters['min_rating'])) {
            $where[] = 'COALESCE(r.avg_rating, 0) >= ?';
            $params[] = (float)$filters['min_rating'];
        }

        $orderBy = 'a.created_at DESC';
        if (($filters['sort'] ?? '') === 'price_low') {
            $orderBy = 'a.asking_price ASC';
        } elseif (($filters['sort'] ?? '') === 'price_high') {
            $orderBy = 'a.asking_price DESC';
        } elseif (($filters['sort'] ?? '') === 'rating') {
            $orderBy = 'COALESCE(r.avg_rating, 0) DESC';
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT a.*, u.full_name AS seller_name,
                       COALESCE(r.avg_rating, 0) AS seller_rating,
                       COALESCE(r.rating_count, 0) AS seller_rating_count
                FROM vehicle_ads a
                JOIN users u ON u.id = a.seller_id
                LEFT JOIN (
                    SELECT seller_id, AVG(rating) AS avg_rating, COUNT(*) AS rating_count
                    FROM seller_ratings GROUP BY seller_id
                ) r ON r.seller_id = a.seller_id
                WHERE $whereSql
                ORDER BY $orderBy
                LIMIT $perPage OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) AS total
                     FROM vehicle_ads a
                     JOIN users u ON u.id = a.seller_id
                     LEFT JOIN (
                        SELECT seller_id, AVG(rating) AS avg_rating, COUNT(*) AS rating_count
                        FROM seller_ratings GROUP BY seller_id
                     ) r ON r.seller_id = a.seller_id
                     WHERE $whereSql";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int)ceil($total / $perPage),
        ];
    }

    public function bySeller(int $sellerId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM vehicle_ads WHERE seller_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function allForAdmin(string $status = ''): array
    {
        $sql = 'SELECT a.*, u.full_name AS seller_name
                FROM vehicle_ads a JOIN users u ON u.id = a.seller_id';
        $params = [];
        if ($status) {
            $sql .= ' WHERE a.status = ?';
            $params[] = $status;
        }
        $sql .= ' ORDER BY a.created_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE vehicle_ads SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function delete(int $id, ?int $sellerId = null): void
    {
        if ($sellerId !== null) {
            $stmt = $this->pdo->prepare('DELETE FROM vehicle_ads WHERE id = ? AND seller_id = ?');
            $stmt->execute([$id, $sellerId]);
        } else {
            $stmt = $this->pdo->prepare('DELETE FROM vehicle_ads WHERE id = ?');
            $stmt->execute([$id]);
        }
    }

    public function countBySellerStatus(int $sellerId, string $status): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total FROM vehicle_ads WHERE seller_id = ? AND status = ?'
        );
        $stmt->execute([$sellerId, $status]);
        return (int)$stmt->fetch()['total'];
    }

    public function featured(int $limit = 6): array
    {
        $limit = (int)$limit;
        $stmt = $this->pdo->query(
            "SELECT a.*, u.full_name AS seller_name,
                    COALESCE(r.avg_rating, 0) AS seller_rating
             FROM vehicle_ads a
             JOIN users u ON u.id = a.seller_id
             LEFT JOIN (
                SELECT seller_id, AVG(rating) AS avg_rating FROM seller_ratings GROUP BY seller_id
             ) r ON r.seller_id = a.seller_id
             WHERE a.status = " . $this->pdo->quote(AD_STATUS_APPROVED) . "
             ORDER BY seller_rating DESC, a.created_at DESC
             LIMIT $limit"
        );
        return $stmt->fetchAll();
    }
}
