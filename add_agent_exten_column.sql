-- user_agents tablosuna agent_exten sütunu ekle
ALTER TABLE user_agents ADD COLUMN agent_exten VARCHAR(20) DEFAULT NULL AFTER agent_number;

-- Varolan kayıtları güncellemek için (isteğe bağlı)
-- Eğer mevcut user_agents kayıtları varsa, bunları agent'larla eşleştirmek için:
-- UPDATE user_agents ua 
-- JOIN agents a ON ua.user_id IN (
--     SELECT u.id FROM users u 
--     WHERE u.login = a.user_login OR u.exten = a.exten
-- ) 
-- SET ua.agent_exten = a.exten 
-- WHERE ua.agent_exten IS NULL;

-- Index ekle (performans için)
CREATE INDEX idx_user_agents_agent_exten ON user_agents(agent_exten);