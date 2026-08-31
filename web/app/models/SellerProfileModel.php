<?php
class SellerProfileModel extends BaseModel
{
    public function findByUserId(int $userId): ?array
    {
        return $this->db->fetchOne('SELECT * FROM seller_profile WHERE user_id = ?', [$userId]);
    }

    public function createOrUpdate(int $userId, array $data): void
    {
        $existing = $this->findByUserId($userId);
        if ($existing) {
            $this->db->execute(
                'UPDATE seller_profile SET phone_number = ?, date_of_birth = ?, gender = ?, bio = ?, address = ?, city = ?, district = ?, province = ?, postal_code = ? WHERE user_id = ?',
                [$data['phone_number'], $data['date_of_birth'], $data['gender'], $data['bio'], $data['address'], $data['city'], $data['district'], $data['province'], $data['postal_code'], $userId]
            );
        } else {
            $this->db->insert(
                'INSERT INTO seller_profile (user_id, phone_number, date_of_birth, gender, bio, address, city, district, province, postal_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$userId, $data['phone_number'], $data['date_of_birth'], $data['gender'], $data['bio'], $data['address'], $data['city'], $data['district'], $data['province'], $data['postal_code']]
            );
        }
    }

    public function updateSellerType(int $userId, string $type): void
    {
        $this->db->execute('UPDATE seller_profile SET seller_type = ? WHERE user_id = ?', [$type, $userId]);
    }

    public function updateSellerImage(int $userId, string $path): void
    {
        $this->db->execute('UPDATE seller_profile SET profile_image = ? WHERE user_id = ?', [$path, $userId]);
    }

    public function refreshAvgRating(int $userId): void
    {
        $this->db->execute(
            'UPDATE seller_profile SET avg_rating = COALESCE((SELECT ROUND(AVG(rating),1) FROM seller_ratings WHERE seller_id = ?), 0) WHERE user_id = ?',
            [$userId, $userId]
        );
    }
}
