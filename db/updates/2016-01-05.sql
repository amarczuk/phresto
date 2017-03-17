ALTER TABLE `phpfy_project` ADD `nodelete` INT UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `phpfy`.`phpfy_project` DROP INDEX `d_seen`, ADD INDEX `d_seen` (`nodelete`, `owner`, `d_seen`) COMMENT '';