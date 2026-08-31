<?php
class LocationModel extends BaseModel
{
    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM locations ORDER BY district, city');
    }

    public function districts(): array
    {
        return $this->db->fetchAll('SELECT DISTINCT district FROM locations ORDER BY district');
    }
}
