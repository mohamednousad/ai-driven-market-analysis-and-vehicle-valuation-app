<?php
class AdRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createFull(array $ad, array $vehicle, array $spec, array $imagePaths): int
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "INSERT INTO ads (seller_id, location_id, title, description, price, is_negotiable, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending_review')"
            )->execute([
                $ad['seller_id'], $ad['location_id'], $ad['title'],
                $ad['description'], $ad['price'], $ad['is_negotiable'],
            ]);
            $adId = (int)$this->pdo->lastInsertId();

            $this->pdo->prepare(
                'INSERT INTO vehicles (vehicle_id, manufacture_year, register_year, chassis_no, engine_cc, fuel_type)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([
                $adId, $vehicle['manufacture_year'], $vehicle['register_year'],
                $vehicle['chassis_no'], $vehicle['engine_cc'], $vehicle['fuel_type'],
            ]);

            $this->pdo->prepare(
                'INSERT INTO vehicle_specifications (vehicle_id, make, model, body_type, transmission, mileage_km, features)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $adId, $spec['make'], $spec['model'], $spec['body_type'],
                $spec['transmission'], $spec['mileage_km'], $spec['features'],
            ]);

            $imgStmt = $this->pdo->prepare(
                'INSERT INTO vehicle_images (vehicle_id, file_path, is_primary, sort_order) VALUES (?, ?, ?, ?)'
            );
            foreach ($imagePaths as $i => $path) {
                $imgStmt->execute([$adId, $path, $i === 0 ? 1 : 0, $i]);
            }

            $this->pdo->commit();
            return $adId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function baseSelect(): string
    {
        return "SELECT a.ad_id, a.title, a.price, a.is_negotiable, a.status, a.views_count,
                       a.published_at, a.created_at,
                       l.name AS district,
                       u.full_name AS seller_name, u.poster_type,
                       v.manufacture_year, v.fuel_type,
                       s.make, s.model, s.body_type, s.transmission, s.mileage_km,
                       (SELECT file_path FROM vehicle_images vi
                         WHERE vi.vehicle_id = a.ad_id ORDER BY vi.is_primary DESC, vi.sort_order ASC LIMIT 1) AS thumb,
                       (SELECT p.promotion_type FROM ad_promotions p
                         WHERE p.ad_id = a.ad_id AND p.payment_status = 'paid'
                           AND (p.starts_at IS NULL OR p.starts_at <= NOW())
                           AND (p.ends_at IS NULL OR p.ends_at >= NOW())
                         ORDER BY p.created_at DESC LIMIT 1) AS promo_type
                FROM ads a
                JOIN users u ON u.user_id = a.seller_id
                JOIN locations l ON l.location_id = a.location_id
                JOIN vehicles v ON v.vehicle_id = a.ad_id
                JOIN vehicle_specifications s ON s.vehicle_id = a.ad_id";
    }

    public function search(array $f, int $page, int $perPage): array
    {
        $where = ["a.status = 'approved'"];
        $params = [];

        if (!empty($f['q'])) {
            $where[] = '(a.title LIKE ? OR s.make LIKE ? OR s.model LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($f['location_id'])) {
            $where[] = 'a.location_id = ?';
            $params[] = (int)$f['location_id'];
        }
        if (!empty($f['poster_type'])) {
            $where[] = 'u.poster_type = ?';
            $params[] = $f['poster_type'];
        }
        if (!empty($f['min_price'])) {
            $where[] = 'a.price >= ?';
            $params[] = (float)$f['min_price'];
        }
        if (!empty($f['max_price'])) {
            $where[] = 'a.price <= ?';
            $params[] = (float)$f['max_price'];
        }
        if (!empty($f['promoted_only'])) {
            $where[] = "EXISTS (SELECT 1 FROM ad_promotions p WHERE p.ad_id = a.ad_id AND p.payment_status = 'paid'
                        AND (p.starts_at IS NULL OR p.starts_at <= NOW()) AND (p.ends_at IS NULL OR p.ends_at >= NOW()))";
        }

        $order = 'promo_type IS NULL, a.published_at DESC';
        if (($f['sort'] ?? '') === 'price_asc') $order = 'promo_type IS NULL, a.price ASC';
        if (($f['sort'] ?? '') === 'price_desc') $order = 'promo_type IS NULL, a.price DESC';

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM ads a
             JOIN users u ON u.user_id = a.seller_id
             JOIN vehicle_specifications s ON s.vehicle_id = a.ad_id
             WHERE {$whereSql}"
        );
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = $this->baseSelect() . " WHERE {$whereSql} ORDER BY {$order} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['rows' => $stmt->fetchAll(), 'total' => $total];
    }

    public function findDetail(int $adId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT a.*, l.name AS district,
                    u.full_name AS seller_name, u.poster_type, u.created_at AS seller_since, u.user_id AS seller_id,
                    r.phone AS seller_phone,
                    v.manufacture_year, v.register_year, v.chassis_no, v.engine_cc, v.fuel_type,
                    s.make, s.model, s.body_type, s.transmission, s.mileage_km, s.features
             FROM ads a
             JOIN users u ON u.user_id = a.seller_id
             LEFT JOIN user_registration r ON r.user_id = u.user_id
             JOIN locations l ON l.location_id = a.location_id
             JOIN vehicles v ON v.vehicle_id = a.ad_id
             JOIN vehicle_specifications s ON s.vehicle_id = a.ad_id
             WHERE a.ad_id = ? LIMIT 1"
        );
        $stmt->execute([$adId]);
        $ad = $stmt->fetch();
        if (!$ad) {
            return null;
        }
        $imgStmt = $this->pdo->prepare(
            'SELECT file_path FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, sort_order ASC'
        );
        $imgStmt->execute([$adId]);
        $ad['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

        $promoStmt = $this->pdo->prepare(
            "SELECT promotion_type FROM ad_promotions
             WHERE ad_id = ? AND payment_status = 'paid'
               AND (starts_at IS NULL OR starts_at <= NOW())
               AND (ends_at IS NULL OR ends_at >= NOW())
             ORDER BY created_at DESC LIMIT 1"
        );
        $promoStmt->execute([$adId]);
        $ad['promo_type'] = $promoStmt->fetchColumn() ?: null;

        return $ad;
    }

    public function incrementViews(int $adId): void
    {
        $this->pdo->prepare('UPDATE ads SET views_count = views_count + 1 WHERE ad_id = ?')->execute([$adId]);
    }

    public function bySeller(int $sellerId): array
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' WHERE a.seller_id = ? ORDER BY a.created_at DESC');
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function pendingForAdmin(): array
    {
        $stmt = $this->pdo->prepare(
            $this->baseSelect() . " WHERE a.status = 'pending_review' ORDER BY a.created_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function allForAdmin(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelect() . ' ORDER BY a.created_at DESC LIMIT 200');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function setStatus(int $adId, string $status): void
    {
        if ($status === 'approved') {
            $this->pdo->prepare(
                "UPDATE ads SET status = 'approved', published_at = NOW(),
                        expires_at = DATE_ADD(NOW(), INTERVAL " . (int)AD_LIFETIME_DAYS . " DAY)
                 WHERE ad_id = ?"
            )->execute([$adId]);
            return;
        }
        $stmt = $this->pdo->prepare('UPDATE ads SET status = ? WHERE ad_id = ?');
        $stmt->execute([$status, $adId]);
    }

    public function markSold(int $adId, int $sellerId): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE ads SET status = 'sold' WHERE ad_id = ? AND seller_id = ? AND status = 'approved'"
        );
        $stmt->execute([$adId, $sellerId]);
        return $stmt->rowCount() > 0;
    }

    public function ownerOf(int $adId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT seller_id FROM ads WHERE ad_id = ?');
        $stmt->execute([$adId]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    public function counts(): array
    {
        $rows = $this->pdo->query('SELECT status, COUNT(*) AS c FROM ads GROUP BY status')->fetchAll();
        $out = ['pending_review' => 0, 'approved' => 0, 'rejected' => 0, 'sold' => 0];
        foreach ($rows as $r) {
            $out[$r['status']] = (int)$r['c'];
        }
        return $out;
    }
}
