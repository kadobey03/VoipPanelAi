-- Performance indexes — run once on the production DB
-- calls tablosu: start kolonu üzerinde index (dashboard sorguları için kritik)
ALTER TABLE `calls` ADD INDEX IF NOT EXISTS `idx_calls_start` (`start`);
ALTER TABLE `calls` ADD INDEX IF NOT EXISTS `idx_calls_group_start` (`group_id`, `start`);

-- agents tablosu: group_name üzerinde index
ALTER TABLE `agents` ADD INDEX IF NOT EXISTS `idx_agents_group_name` (`group_name`);
ALTER TABLE `agents` ADD INDEX IF NOT EXISTS `idx_agents_active` (`active`);