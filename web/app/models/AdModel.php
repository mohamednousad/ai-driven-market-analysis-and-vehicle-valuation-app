<?php
class AdModel extends BaseModel
{
    private const CARD_SELECT = "SELECT a.*, v.make, v.model, v.manufacture_year, v.transmission, v.fuel_type, v.mileage, v.engine_cc,
        l.district, l.city, u.username AS seller_name, u.profile_image AS seller_avatar,
        sp.seller_type, sp.avg_rating,
        (SELECT image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY vi.is_primary DESC, vi.id ASC LIMIT 1) AS primary_image,
        an.fair_price_status,
        (SELECT COUNT(*) FROM ad_promotions p WHERE p.ad_id = a.id AND NOW() BETWEEN p.starts_at AND p.ends_at) AS is_promoted
        FROM ads a
        JOIN vehicle_info v ON v.ad_id = a.id
        JOIN locations l ON l.id = a.location_id
        JOIN users u ON u.id = a.seller_id
        LEFT JOIN seller_profile sp ON sp.user_id = a.seller_id
        LEFT JOIN ad_system_analysis an ON an.id = (SELECT MAX(x.id) FROM ad_system_analysis x WHERE x.ad_id = a.id)";

    public function approvedList(array $filters, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildFilters($filters);
        $sql = self::CARD_SELECT . " WHERE a.status = 'approved' $where ORDER BY is_promoted DESC, a.published_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        return $this->db->fetchAll($sql, $params);
    }

    public function approvedCount(array $filters): int
    {
        [$where, $params] = $this->buildFilters($filters);
        $sql = "SELECT COUNT(*) AS c FROM ads a JOIN vehicle_info v ON v.ad_id = a.id JOIN locations l ON l.id = a.location_id WHERE a.status = 'approved' $where";
        return (int)($this->db->fetchOne($sql, $params)['c'] ?? 0);
    }

    private function buildFilters(array $f): array
    {
        $where = '';
        $params = [];
        if (!empty($f['make'])) { $where .= ' AND v.make = ?'; $params[] = $f['make']; }
        if (!empty($f['district'])) { $where .= ' AND l.district = ?'; $params[] = $f['district']; }
        if (!empty($f['transmission'])) { $where .= ' AND v.transmission = ?'; $params[] = $f['transmission']; }
        if (!empty($f['fuel_type'])) { $where .= ' AND v.fuel_type = ?'; $params[] = $f['fuel_type']; }
        if (!empty($f['min_price'])) { $where .= ' AND a.price >= ?'; $params[] = (float)$f['min_price']; }
        if (!empty($f['max_price'])) { $where .= ' AND a.price <= ?'; $params[] = (float)$f['max_price']; }
        if (!empty($f['q'])) { $where .= ' AND (a.title LIKE ? OR v.make LIKE ? OR v.model LIKE ?)'; $like = '%' . $f['q'] . '%'; array_push($params, $like, $like, $like); }
        if (!empty($f['fair_only'])) { $where .= " AND EXISTS (SELECT 1 FROM ad_system_analysis x WHERE x.ad_id = a.id AND x.fair_price_status = 'fair')"; }
        if (!empty($f['authorized_only'])) { $where .= " AND EXISTS (SELECT 1 FROM seller_profile s WHERE s.user_id = a.seller_id AND s.seller_type = 'authorized')"; }
        return [$where, $params];
    }

    public function featured(int $limit): array
    {
        return $this->db->fetchAll(self::CARD_SELECT . " WHERE a.status = 'approved' ORDER BY is_promoted DESC, a.views DESC LIMIT ?", [$limit]);
    }

    public function findFull(int $id): ?array
    {
        return $this->db->fetchOne(self::CARD_SELECT . ' WHERE a.id = ?', [$id]);
    }

    public function vehicleByAd(int $adId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM vehicle_info WHERE ad_id = ?', [$adId]);
    }

    public function imagesByVehicle(int $vehicleId): array
    {
        return $this->db->fetchAll('SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, id ASC', [$vehicleId]);
    }

    public function analysisByAd(int $adId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM ad_system_analysis WHERE ad_id = ? ORDER BY id DESC LIMIT 1', [$adId]);
    }

    public function bySeller(int $sellerId): array
    {
        return $this->db->fetchAll(self::CARD_SELECT . ' WHERE a.seller_id = ? ORDER BY a.created_at DESC', [$sellerId]);
    }

    public function pendingForAdmin(): array
    {
        return $this->db->fetchAll(self::CARD_SELECT . " WHERE a.status IN ('pending','rejected') ORDER BY a.created_at DESC");
    }

    public function createWithVehicle(int $sellerId, array $ad, array $vehicle): int
    {
        $this->db->begin();
        try {
            $adId = $this->db->insert(
                "INSERT INTO ads (seller_id, location_id, title, description, price, is_negotiable, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')",
                [$sellerId, (int)$ad['location_id'], $ad['title'], $ad['description'], (float)$ad['price'], (int)$ad['is_negotiable']]
            );
            $this->db->insert(
                'INSERT INTO vehicle_info (ad_id, make, model, manufacture_year, registration_year, body_type, transmission, fuel_type, engine_cc, mileage, colour, `condition`, number_of_owners, finance_status, finance_company, accident_history, service_history, features) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$adId, $vehicle['make'], $vehicle['model'], (int)$vehicle['manufacture_year'], (int)$vehicle['registration_year'], $vehicle['body_type'], $vehicle['transmission'], $vehicle['fuel_type'], (int)$vehicle['engine_cc'], (int)$vehicle['mileage'], $vehicle['colour'], $vehicle['condition'], (int)$vehicle['number_of_owners'], $vehicle['finance_status'], $vehicle['finance_company'], (int)$vehicle['accident_history'], (int)$vehicle['service_history'], $vehicle['features']]
            );
            $this->db->commit();
            return $adId;
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function addImage(int $vehicleId, string $path, bool $primary, int $size, string $format): int
    {
        return $this->db->insert(
            'INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, file_size, file_format) VALUES (?, ?, ?, ?, ?)',
            [$vehicleId, $path, (int)$primary, $size, $format]
        );
    }

    public function updateImage(int $imageId, string $path, string $format): void
    {
        $this->db->execute('UPDATE vehicle_images SET image_path = ?, file_format = ? WHERE id = ?', [$path, $format, $imageId]);
    }

    public function deleteImage(int $imageId): void
    {
        $this->db->execute('DELETE FROM vehicle_images WHERE id = ?', [$imageId]);
    }

    public function saveAnalysis(int $adId, float $price, string $status, string $result): void
    {
        $this->db->insert(
            'INSERT INTO ad_system_analysis (ad_id, submitted_price, fair_price_status, result, analyzed_at) VALUES (?, ?, ?, ?, NOW())',
            [$adId, $price, $status, $result]
        );
    }

    public function setStatus(int $adId, string $status): void
    {
        if ($status === 'approved') {
            $this->db->execute("UPDATE ads SET status = 'approved', published_at = NOW() WHERE id = ?", [$adId]);
        } else {
            $this->db->execute('UPDATE ads SET status = ? WHERE id = ?', [$status, $adId]);
        }
    }

    /**
     * Build the Observer subject with the default observers attached.
     * Kept as a factory method so call sites can reuse it or add their own observers.
     */
    public function statusSubject(): AdStatusSubject
    {
        $subject = new AdStatusSubject();
        $subject->attach(new NotificationObserver());
        return $subject;
    }

    /**
     * Change an ad's status AND broadcast the change through the Observer pattern.
     * The model no longer needs to know how notifications are delivered.
     */
    public function changeStatus(int $adId, string $status, int $sellerId, string $title, string $detail = ''): void
    {
        $this->setStatus($adId, $status);
        $this->statusSubject()->notify($adId, $sellerId, $status, $title, $detail);
    }

    public function updatePrice(int $adId, float $price): void
    {
        $this->db->execute("UPDATE ads SET price = ?, status = 'pending' WHERE id = ?", [$price, $adId]);
    }

    public function incrementViews(int $adId): void
    {
        $this->db->execute('UPDATE ads SET views = views + 1 WHERE id = ?', [$adId]);
    }

    public function delete(int $adId, int $sellerId): void
    {
        $this->db->execute('DELETE FROM ads WHERE id = ? AND seller_id = ?', [$adId, $sellerId]);
    }

    public function distinctMakes(): array
    {
        return $this->db->fetchAll("SELECT DISTINCT make FROM vehicle_info WHERE make IS NOT NULL AND make != '' ORDER BY make");
    }

    public function statsForSeller(int $sellerId): array
    {
        return [
            'active' => (int)($this->db->fetchOne("SELECT COUNT(*) AS c FROM ads WHERE seller_id = ? AND status = 'approved'", [$sellerId])['c'] ?? 0),
            'views' => (int)($this->db->fetchOne('SELECT COALESCE(SUM(views),0) AS c FROM ads WHERE seller_id = ?', [$sellerId])['c'] ?? 0),
            'chats' => (int)($this->db->fetchOne('SELECT COUNT(*) AS c FROM chats WHERE seller_id = ?', [$sellerId])['c'] ?? 0),
            'rating' => (float)($this->db->fetchOne('SELECT COALESCE(avg_rating,0) AS r FROM seller_profile WHERE user_id = ?', [$sellerId])['r'] ?? 0),
        ];
    }
}
