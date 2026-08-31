USE autovalue;
ALTER TABLE payments ADD COLUMN meta TEXT NULL AFTER transaction_id;
UPDATE settings SET `key` = 'flask_api_endpoint' WHERE `key` = 'ai_service_endpoint';
