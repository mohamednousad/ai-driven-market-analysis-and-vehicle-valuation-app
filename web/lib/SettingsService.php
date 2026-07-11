<?php
class SettingsService
{
    private PDO $pdo;
    private static ?array $cache = null;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $defaults = unserialize(DEFAULT_SETTINGS);
        $stmt = $this->pdo->query('SELECT setting_key, setting_value FROM settings');
        $rows = $stmt->fetchAll();
        $stored = [];
        foreach ($rows as $row) {
            $stored[$row['setting_key']] = $row['setting_value'];
        }
        self::$cache = array_merge($defaults, $stored);
        return self::$cache;
    }

    public function get(string $key, $default = null)
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function save(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach ($data as $key => $value) {
            $stmt->execute([$key, (string)$value]);
        }
        self::$cache = null;
    }

    public function fairnessBand(): float
    {
        return (float)$this->get('fairness_band_percent', 15);
    }

    public function currencySymbol(): string
    {
        return (string)$this->get('currency_symbol', 'Rs');
    }
}
