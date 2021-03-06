DROP TABLE IF EXISTS `[db_prefix]settings`;
DELETE FROM `sys_permalinks` WHERE `standard` = 'modules/?r=ebBoonexContentsSlider/';
SET @iCategId = (SELECT `ID` FROM `sys_options_cats` WHERE `name` = 'BoonexContentsSlider' LIMIT 1);
DELETE FROM `sys_options` WHERE `kateg` = @iCategId;
DELETE FROM `sys_options_cats` WHERE `ID` = @iCategId;
DELETE FROM `sys_options` WHERE `Name` = 'emmet_bytes_boonex_contents_slider_permalinks';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Photo Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Sites Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Blogs Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Blogs Articles Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Sounds Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Videos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Files Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Groups Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'index' AND `Desc` = 'Homepage Ads Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Joined Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Groups Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Joined Groups Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Sites Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Blogs Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Photos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Videos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Sounds Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Desc` = 'Own Ads Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_main' AND `Desc` = 'Upcoming Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_main' AND `Desc` = 'Past Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_main' AND `Desc` = 'Recent Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_events_my' AND `Desc` = 'My Events Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_main' AND `Desc` = 'Recent Groups Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_groups_my' AND `Desc` = 'My Groups Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sites_main' AND `Desc` = 'Recent Sites Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sites_main' AND `Desc` = 'Feautred Sites Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_blogs_home' AND `Desc` = 'Latest Blog Post Datas Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_photos_home' AND `Desc` = 'Main Public Photos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_photos_home' AND `Desc` = 'Main Featured Photos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_photos_home' AND `Desc` = 'Main Favorite Photos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_videos_home' AND `Desc` = 'Main Public Videos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_videos_home' AND `Desc` = 'Main Featured Videos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_videos_home' AND `Desc` = 'Main Favorite Videos Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_home' AND `Desc` = 'Main Public Sounds Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_home' AND `Desc` = 'Main Featured Sounds Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_sounds_home' AND `Desc` = 'Main Favorite Sounds Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_files_home' AND `Desc` = 'Main Public Files Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_files_home' AND `Desc` = 'Main Top Files Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_files_home' AND `Desc` = 'Main Featured Files Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'bx_files_home' AND `Desc` = 'Main Favorite Files Boonex Contents Slider';
DELETE FROM `sys_page_compose` WHERE `Page` = 'ads_home' AND `Desc` = 'Main Last Ads Boonex Contents Slider';
DELETE FROM `sys_menu_admin` WHERE `name` = 'emmetbytes_contents_slider';

