-- Groups tablosuna telegram alanları ekleme
ALTER TABLE groups ADD COLUMN telegram_enabled TINYINT(1) DEFAULT 0 AFTER telegram_chat_id;
ALTER TABLE groups ADD COLUMN telegram_language ENUM('TR','EN','RU') DEFAULT 'TR' AFTER telegram_enabled;