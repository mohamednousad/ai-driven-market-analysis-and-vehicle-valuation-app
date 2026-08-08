<?php
abstract class BaseModel
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
