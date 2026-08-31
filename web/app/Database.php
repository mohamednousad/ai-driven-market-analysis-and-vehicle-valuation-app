<?php
final class Database
{
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $this->conn = new mysqli(
                Config::get('DB_HOST'),
                Config::get('DB_USER'),
                Config::get('DB_PASS'),
                Config::get('DB_NAME')
            );
            $this->conn->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            http_response_code(500);
            exit('Database connection failed. Check config and that MySQL is running.');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connection(): mysqli
    {
        return $this->conn;
    }

    public function query(string $sql, array $params = []): mysqli_stmt
    {
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $types = '';
            foreach ($params as $p) {
                $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
            }
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $result = $this->query($sql, $params)->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function fetchOne(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return $row ?: null;
    }

    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->affected_rows;
    }

    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return (int)$this->conn->insert_id;
    }

    public function begin(): void { $this->conn->begin_transaction(); }
    public function commit(): void { $this->conn->commit(); }
    public function rollback(): void { $this->conn->rollback(); }
}
