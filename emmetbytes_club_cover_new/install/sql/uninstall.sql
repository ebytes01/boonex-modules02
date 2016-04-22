-- tables
DROP TABLE IF EXISTS `eb_club_cover_main`;

-- sys injections
DELETE FROM `sys_injections` WHERE `name` = 'eb_club_cover';

-- system objects
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=ebClubCover/';

-- admin menu
DELETE FROM `sys_menu_admin` WHERE `name` = 'emmet_bytes_club_cover';

-- settings
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'ClubCover' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;
DELETE FROM `sys_options` WHERE `Name` = 'emmet_bytes_club_cover_permalinks';

-- alert handlers
DELETE FROM `sys_alerts_handlers` WHERE `name` = 'eb_club_cover_add_background';
DELETE FROM `sys_alerts_handlers` WHERE `name` = 'eb_club_cover_change_background';
DELETE FROM `sys_alerts_handlers` WHERE `name` = 'eb_club_cover_remove_background';
DELETE FROM `sys_alerts_handlers` WHERE `name` = 'eb_club_cover_add_thumbnail';
DELETE FROM `sys_alerts_handlers` WHERE `name` = 'eb_club_cover_change_thumbnail';
