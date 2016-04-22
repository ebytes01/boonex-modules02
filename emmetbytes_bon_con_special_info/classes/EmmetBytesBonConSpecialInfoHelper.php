<?php
/**********************************************************************************************
 * Created By : EmmetBytes Software Solutions
 * Created Date : June 10, 2012
 * Email : emmetbytes@gmail.com
 *
 * Copyright : (c) EmmetBytes Software Solutions 2012
 * Product Name : Bon Con Special Info
 * Product Version : 1.0
 * 
 * Important : This is a commercial product by EmmetBytes Software Solutions and 
 *   cannot be modified, redistributed or resold without any written permission 
 *   from EmmetBytes Software Solutions
 **********************************************************************************************/

class EmmetBytesBonConSpecialInfoHelper{
    var $boonexVersion;
    var $helperObj;
    
    // CONSTRUCTOR
    function EmmetBytesBonConSpecialInfoHelper($oMain){
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        if($this->boonexVersion >= '7.1.0'){
            $this->helperObj = new EmmetBytesBonConSpecialInfoD710UpHelper($oMain);
        } else if ($this->boonexVersion >= '7.0.7' && $this->boonexVersion < '7.1.0'){
            $this->helperObj = new EmmetBytesBonConSpecialInfoD707UpHelper($oMain);
        }else{
            $this->helperObj = new EmmetBytesBonConSpecialInfoDefaultHelper($oMain);
        }
    }

}


class EmmetBytesBonConSpecialInfoDefaultHelper{
    // ATTRIBUTES
    var $oMain, $oDb, $oTemplate, $oConfig, $boonexVersion; 
    
    // CONSTRUCTOR
    function EmmetBytesBonConSpecialInfoDefaultHelper($oMain){
        $this->profileId = $oMain->_iProfileId;
        $this->oDb = $oMain->_oDb;
        $this->oTemplate = $GLOBALS['ebBonConSpecialInfoSysTemplate'] = (isset($GLOBALS['ebBonConSpecialInfoSysTemplate'])) ? $GLOBALS['ebBonConSpecialInfoSysTemplate'] : $oMain->_oTemplate;
        $this->oConfig = $GLOBALS['ebBonConSpecialInfoSysConfig'] = (isset($GLOBALS['ebBonConSpecialInfoSysConfig'])) ? $GLOBALS['ebBonConSpecialInfoSysConfig'] : $oMain->_oConfig;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        $this->addAllCss();
        $this->addAllJs();
    }
    
    // ACTION RESPONSE
    // gets the information container
    function getInformationContainerResponse(){
        if($this->isAjaxRequest()){
            $entryID = $_POST['entryID'];
            $moduleVars = json_decode($_POST['moduleVars'], true);
            $modVars = $moduleVars['mod_vars'];
            $informationParams = $modVars['information_params'];
            $sysModules = $moduleVars['sys_modules'];
            $system = $modVars['system'];
            list($searchObj, $className) = $this->getSearchResultObject($moduleVars);
            $columnsMap = $modVars['columns_map'];
            $settings = $modVars['settings'];
            $iBlockID = $modVars['block_id'];
            $templateVars = $this->getMainTemplateVars($iBlockID);
            return $this->getCommonInfoBlockContainer($entryID, $informationParams, $modVars, $sysModules, $searchObj, $system, $settings, $templateVars);        
        }
    }

    // gets the image
    function getImage($imageID, $width, $height){
        if($width == 0 || $height == 0){ return false; }
        $params = $imageID;
        $sponsorImageDatas = $this->getPrimPhoto($params, 'file');
        $sponsorImage = $sponsorImageDatas['path'];
        return $this->getResizedImage($sponsorImage, $width, $height);
    }

    // get blog image 
    function getBlogImage($imageName, $width, $height){
        $sponsorImage = BX_DIRECTORY_PATH_ROOT . 'media/images/blog/orig_' . $imageName;
        return $this->getResizedImage($sponsorImage, $width, $height);
    }

    // gets the file image
    function getFileImage($imagePath, $width, $height){
        $imagePath = base64_decode($imagePath);
        header('Cache-Control: private, max-age=10800, pre-check=10800');
        header('Content-type: image/jpeg');
        readfile($imagePath);
    }

    // gets the resized image
    protected function getResizedImage($sponsorImage, $width, $height, $quality=4){
        $filePath =BX_DIRECTORY_PATH_MODULES . 'EmmetBytes/emmetbytes_bon_con_special_info/files/images/' . md5(basename($sponsorImage) . $width . $height) . '.jpg';
        if(!file_exists($filePath)){
            list($iwidth, $iheight, $imageType) = getimagesize($sponsorImage);
            switch($imageType){
                case IMAGETYPE_JPEG : 
                    $image = imagecreatefromjpeg($sponsorImage);
                    break;
                case IMAGETYPE_GIF : 
                    $image = imagecreatefromgif($sponsorImage);
                    break;
                case IMAGETYPE_PNG :
                    $image = imagecreatefrompng($sponsorImage);
                    break;
                default: 
                    return;
            }
            $newWidth = $iwidth;
            $newHeight = $iheight;
            if($iwidth > $width && $iheight > $height){
                $wratio = $width/$iwidth;
                $hratio = $height/$iheight;
                $newWidth = $hratio * $iwidth;
                $newHeight = $height;
                if($wratio < $hratio){
                    $newWidth = $width;
                    $newHeight = $wratio * $iheight;
                } 
            }
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            if ($quality < 5 && (($newWidth * $quality) < $iwidth || ($newHeight * $quality) < $iheight)) {
                $temp = imagecreatetruecolor ($newWidth * $quality + 1, $newHeight * $quality + 1);
                imagecopyresized ($temp, $image, 0, 0, 0, 0, $newWidth * $quality + 1, $newHeight * $quality + 1, $iwidth * $quality, $iheight * $quality);
                imagecopyresampled($newImage, $temp, 0, 0, 0, 0, $newWidth, $newHeight, $iwidth * $quality, $iheight * $quality);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth * $quality, $newHeight * $quality);
                imagedestroy ($temp);
            }else{
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $iwidth, $iheight);
            }
            imagejpeg($newImage, $filePath);
            imagedestroy($image);
            imagedestroy($newImage);
        }
        if(function_exists('ob_gzhandler')){ ob_start('ob_gzhandler'); }else{ ob_start(); }
        header('Cache-Control: private, max-age=10800, pre-check=10800');
        header('Content-Type: image/jpeg');
        readfile($filePath);
        ob_end_flush();
    }

    // getting more entries
    function getMoreEntriesResponse(){
        $moduleVars = json_decode($_POST['moduleVars'], true);
        $modVars = $moduleVars['mod_vars'];
        $sysModules = $moduleVars['sys_modules'];
        $system = $modVars['system'];
        $thumbnailsColumnsMap = $modVars['thumbnails_params']['columns_map'];
        $settings = $modVars['settings'];
        $iBlockID = $modVars['block_id'];
        $searchResultDatas = $this->getThumnailSearchResults($moduleVars, true);
        $searchObj = $searchResultDatas['search_obj'];
        $searchResults = $searchResultDatas['search_results'];
        $numOfDisplayedEntries = sizeof($searchResults) + $moduleVars['num_of_displayed_entries'];
        $templateVars = $this->getMainTemplateVars($iBlockID);
        $thumbnailContainers = '';
        foreach($searchResults  as $key=>$searchResult){
            $thumbnailContainers .= $this->getCommonThumbContainer($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars);
        }
        $entriesDatas = array( 'content' => $thumbnailContainers, 'num_of_displayed_entries' => $numOfDisplayedEntries, );
        return json_encode($entriesDatas);
    }
    // EOF THE ACTION RESPONSE

    // HOMEPAGE SERVICE BLOCKS HELPERS
    // homepage events block helper
    function homepageEventsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_event_block_settings');
        $eventParams = array(
            'browse_mode' => $settings['default_tab'],
            'is_public' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_filter'])){
            switch ($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_filter']){
                case 'featured':
                case 'recent':
                case 'top':
                case 'popular':
                case 'upcoming':
                    $eventParams['browse_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_filter'];
                    break;
            }
        }
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        return array(
            $eventDatas,
            array(
                _t('_bx_events_tab_upcoming') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=upcoming', 'active' => 'upcoming' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_featured') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=featured', 'active' => 'featured' == $eventParams['browse_mode'], 'dynamic' => true),                
                _t('_bx_events_tab_recent') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=recent', 'active' => 'recent' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=top', 'active' => 'top' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=popular', 'active' => 'popular' == $eventParams['browse_mode'], 'dynamic' => true),                
            )
        );
    }

    // homepage groups block helper
    function homepageGroupsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_group_block_settings');
        $groupsParams = array(
            'browse_mode' => $settings['default_tab'],
            'is_public' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_filter'])){
            switch ($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_filter']) {
                case 'featured':
                case 'recent':
                case 'top':
                case 'popular':
                    $groupsParams['browse_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_filter'];
                    break;
            }
        }
        $groupsDatas = $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
        if(!$groupsDatas){ return false; }
        return array(
            $groupsDatas,
            array(
                _t('_bx_groups_tab_featured') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=featured', 'active' => 'featured' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_recent') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=recent', 'active' => 'recent' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=top', 'active' => 'top' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_filter=popular', 'active' => 'popular' == $groupsParams['browse_mode'], 'dynamic' => true),
            )
        );
    }

    // homepage sites block helper
    function homepageSitesBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_site_block_settings');
        $siteParams = array( 'browse_mode' => 'index',);
        return $this->getSiteDatas($iBlockID,$siteParams,$settings);
    }

    // homepage blogs block helper
    function homepageBlogsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_blogs_block_settings');
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'], 
            'restriction' => array(
                'allow_view' => array(
                    'value' => '',
                ),
            ),
            'allow_view_checker' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){
            return false;
        }
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    // homepage photos block helper
    function homepagePhotosBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_photos_block_settings');
        $photoParams = array(
            'browse_mode' => '', 
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $photoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $photoParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        return array(
            $photosDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $photoParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $photoParams['sort_mode'], 'dynamic' => true), 
            )
        );
    }
    
    // homepage videos block helper
    function homepageVideosBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_videos_block_settings');
        $videosParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $videosParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        return array(
            $this->getVideosDatas($iBlockID, $videosParams, $settings),
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $videosParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $videosParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // homepage sounds block helper
    function homepageSoundsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_sounds_block_settings');
        $soundsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL,),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $soundsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        return array(
            $soundsDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $soundsParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $soundsParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // homepage files block helper
    function homepageFilesBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_files_block_settings');
        $filesParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=popular', 'active' => 'popular' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }
    // EOF THE HOMEPAGE SERVICE BLOCKS HELPERS        
    
    // PROFILE SERVICE BLOCKS HELPERS
    // bof events
    // profile own events block helper
    function profileOwnEventsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_event_block_settings');
        $aProfile = getProfileInfo($profileID);
        $eventParams = array(
            'browse_mode' => 'user',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
            'allow_view_checker' => true,
        );
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }

    // profile joined events block helper
    function profileJoinedEventsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_joined_event_block_settings');
        $aProfile = getProfileInfo($profileID);
        $eventParams = array(
            'browse_mode' => 'joined',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
            'allow_view_checker' => true,
        );
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }
    // eof the events

    // bof groups
    // profile own groups
    function profileOwnGroupsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_group_block_settings');
        $aProfile = getProfileInfo($profileID);
        $groupsParams = array(
            'browse_mode' => 'user',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
            'allow_view_checker' => true,
        );
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }

    // profile joined groups
    function profileJoinedGroupsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_joined_group_block_settings');
        $aProfile = getProfileInfo($profileID);
        $groupsParams = array(
            'browse_mode' => 'joined',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
            'allow_view_checker' => true,
        );
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }
    // eof the groups

    // bof sites
    // profile own sites
    function profileOwnSitesBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_site_block_settings');
        $aProfile = getProfileInfo($profileID);
        $groupsParams = array(
            'browse_mode' => 'profile',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        return $this->getSiteDatas($iBlockID, $groupsParams, $settings);
    }
    // eof the sites

    // bof blogs
    // profile own blogs
    function profileOwnBlogsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_blogs_block_settings');
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'last',
            'restriction' => array('owner' => array('value' => $profileID)),
            'allow_view_checker' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        return $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
    }
    // eof the blogs

    // bof photos
    // profile own photos
    function profileOwnPhotosBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_photos_block_settings');
        $photosParams = array(
            'browse_mode' => '',
            'sort_mode' => 'album_order', 
            'restriction' => array(
                'owner' => array('value' => $profileID),
            ),
            'extra_params' => array(
                'restriction' => array(
                    'album' => '',
                ),
            ),
            'has_block_addon_checker' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $photosParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        return $this->getPhotoDatas($iBlockID, $photosParams, $settings);
    }
    // eof the photos

    // bof videos
    // profile own videos
    function profileOwnVideosBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_videos_block_settings');
        $videosParams = array(
            'browse_mode' => '',
            'sort_mode' => 'album_order',
            'restriction' => array(
                'owner' => array('value' => $profileID),
            ),
            'extra_params' => array(
                'restriction' => array(
                    'album' => '',
                ),
            ),
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        return $this->getVideosDatas($iBlockID, $videosParams, $settings);
    }
    // eof the videos

    // sounds
    function profileOwnSoundsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_sounds_block_settings');
        $soundsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'album_order',
            'restriction' => array(
                'owner' => array('value' => $profileID),
            ),
            'extra_params' => array(
                'restriction' => array(
                    'album' => '',
                ),
            ),
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        return $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
    }
    // EOF PROFILE SERVICE BLOCKS HELPERS

    // PAGE SERVICE BLOCKS HELPER
    // BOF EVENTS SERVICE BLOCKS
    // BOF the main events page
    // upcoming events
    function mainEventsUpcoming($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_upcoming_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'upcoming',
            'is_public' => true,
        );
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }

    // past events
    function mainEventsPast($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_past_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'past',
            'is_public' => true,
        );
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }

    // recent
    function mainEventsRecent($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'recent',
            'is_public' => true,
        );
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }
    // EOF the main events page

    // BOF the my events page
    // users own events
    function myEvents($iBlockID, $profileID){
        $settings = $this->getBlockSettings('module_blocks_users_event_block_settings');
        $aProfile = getProfileInfo($profileID);
        $eventParams = array(
            'browse_mode' => 'user',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }
    // EOF the my events page
    // EOF THE EVENTS SERVICE BLOCKS

    // BOF GROUPS SERVICE BLOCKS
    // BOF the main groups page
    // main recent groups
    function mainRecentGroups($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_group_block_settings');
        $groupsParams = array(
            'browse_mode' => 'recent',
            'is_public' => true,
        );
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }
    // EOF the main groups page

    // BOF the my groups page
    // users own groups
    function myGroups($iBlockID, $profileID){
        $settings = $this->getBlockSettings('module_blocks_users_group_block_settings');
        $aProfile = getProfileInfo($profileID);
        $groupsParams = array(
            'browse_mode' => 'user',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }
    // EOF the my groups page
    // EOF THE GROUPS SERVICE BLOCKS

    // BOF SITES SERVICE BLOCKS
    // bof main page sites
    // main featured sites
    function mainFeaturedSites($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_site_block_settings');
        $siteParams = array( 'browse_mode' => 'featuredshort',);
        return $this->getSiteDatas($iBlockID, $siteParams, $settings);
    }

    // main recent sites
    function mainRecentSites($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_site_block_settings');
        $siteParams = array( 'browse_mode' => 'home',);
        return $this->getSiteDatas($iBlockID, $siteParams, $settings);
    }
    // eof the main page sites

    // my page sites
    function usersOwnSites($iBlockID, $profileID){
        $settings = $this->getBlockSettings('module_blocks_users_site_block_settings');
        $aProfile = getProfileInfo($profileID);
        $groupsParams = array(
            'browse_mode' => 'user',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        return $this->getSiteDatas($iBlockID, $groupsParams, $settings);
    }
    // EOF THE SITES SERVICE BLOCKS

    // BOF BLOGS SERVICE BLOCKS
    // gets the latest blog post
    function latestBlogPost($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_latest_blogs_block_settings');
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );

        $blogsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS; 
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        return array(
            $blogsDatas,
            array(
            ),
        );
    }
    // EOF THE BLOGS SERVICE BLOCKS

    // BOF PHOTOS SERVICE BLOCKS
    // bof the photos main page service blocks
    // main public photos
    function mainPublicPhotos($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_public_photos_block_settings');
        $photoParams = array(
            'browse_mode' => '', 
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $photoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $photoParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        return array(
            $photosDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/photos/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $photoParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/photos/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $photoParams['sort_mode'], 'dynamic' => true), 
            )
        );
    }
    
    // main favorite photos
    function mainFavoritePhotos($iBlockID, $profileId){
        if($profileId <= 0){
            return array();
        }
        $settings = $this->getBlockSettings('module_blocks_main_favorite_photos_block_settings');
        $photoParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
            'extra_search_params' => array(
                'joins' => array(
                    'favorite' => array(
                        'variable' => array(
                            'content' => 'aAddPartsConfig',
                            'content_key' => 'favorite',
                        ),
                    ),
                ),
                'restrictions' => array(
                    'fav' => array(
                        'content' => array(
                            'variable_name' => 'aAddPartsConfig',
                            'value' => $profileId,
                            'operator' => '=',
                            'field' => array(
                                'keys' => array(
                                    'favorite', 'userField',
                                ),
                            ),
                            'table' => array(
                                'keys' => array(
                                    'favorite', 'table',
                                ),
                            ),
                        ),
                    )
                ),
            ),
        );
        if(getLoggedId() > 0){
            $photoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getPhotoDatas($iBlockID, $photoParams, $settings);
    }

    // main featured photos
    function mainFeaturedPhotos($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_photos_block_settings');
        $photoParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'featured' => array(
                    'field' => 'Featured',
                    'value' => '1',
                    'operator' => '=',
                    'param' => 'featured'
                ),
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $photoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getPhotoDatas($iBlockID, $photoParams, $settings);
    }
    // eof the photos main page service blocks
    // EOF THE PHOTOS SERVICE BLOCKS

    // BOF VIDEOS SERVICE BLOCKS
    // bof the videos main page service blocks
    // main public videos
    function mainPublicVideos($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_public_videos_block_settings');
        $videosParams = array(
            'browse_mode' => '', 
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $videosParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        return array(
            $videosDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/videos/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $videosParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/videos/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $videosParams['sort_mode'], 'dynamic' => true), 
            ),
        );
        // return $this->getVideosDatas($iBlockID, $videosParams, $settings);
    }
    
    // main favorite videos
    function mainFavoriteVideos($iBlockID, $profileId){
        if($profileId <= 0){
            return array();
        }
        $settings = $this->getBlockSettings('module_blocks_main_favorite_videos_block_settings');
        $videoParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
            'extra_search_params' => array(
                'joins' => array(
                    'favorite' => array(
                        'variable' => array(
                            'content' => 'aAddPartsConfig',
                            'content_key' => 'favorite',
                        ),
                    ),
                ),
                'restrictions' => array(
                    'fav' => array(
                        'content' => array(
                            'variable_name' => 'aAddPartsConfig',
                            'value' => $profileId,
                            'operator' => '=',
                            'field' => array(
                                'keys' => array(
                                    'favorite', 'userField',
                                ),
                            ),
                            'table' => array(
                                'keys' => array(
                                    'favorite', 'table',
                                ),
                            ),
                        ),
                    )
                ),
            ),
        );
        if(getLoggedId() > 0){
            $videoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getVideosDatas($iBlockID, $videoParams, $settings);
    }

    // main featured videos
    function mainFeaturedVideos($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_videos_block_settings');
        $vidoesParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'featured' => array(
                    'field' => 'Featured',
                    'value' => '1',
                    'operator' => '=',
                    'param' => 'featured'
                ),
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $vidoesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getVideosDatas($iBlockID, $vidoesParams, $settings);
    }
    // eof the videos main page service blocks
    // EOF THE VIDEOS SERVICE PAGE BLOCKS

    // BOF SOUNDS SERVICE BLOCKS
    // bof the sounds main page service blocks
    // main public sounds
    function mainPublicSounds($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_public_sounds_block_settings');
        $soundsParams = array(
            'browse_mode' => '', 
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $soundsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        return array(
            $soundsDatas, 
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/sounds/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $soundsParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/sounds/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $soundsParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // main favorite sounds 
    function mainFavoriteSounds($iBlockID, $profileId){
        if($profileId <= 0){ return ''; }
        $settings = $this->getBlockSettings('module_blocks_main_favorite_sounds_block_settings');
        $videoParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
            'extra_search_params' => array(
                'joins' => array(
                    'favorite' => array(
                        'variable' => array(
                            'content' => 'aAddPartsConfig',
                            'content_key' => 'favorite',
                        ),
                    ),
                ),
                'restrictions' => array(
                    'fav' => array(
                        'content' => array(
                            'variable_name' => 'aAddPartsConfig',
                            'value' => $profileId,
                            'operator' => '=',
                            'field' => array(
                                'keys' => array(
                                    'favorite', 'userField',
                                ),
                            ),
                            'table' => array(
                                'keys' => array(
                                    'favorite', 'table',
                                ),
                            ),
                        ),
                    )
                ),
            ),
        );
        if(getLoggedId() > 0){
            $videoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getSoundsDatas($iBlockID, $videoParams, $settings);
    }

    // main featured sounds
    function mainFeaturedSounds($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_sounds_block_settings');
        $soundsParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'featured' => array(
                    'field' => 'Featured',
                    'value' => '1',
                    'operator' => '=',
                    'param' => 'featured'
                ),
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $soundsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
    }
    // eof the sounds main page service blocks
    // EOF THE SOUNDS SERVICE BLOCKS

    // BOF FILES SERVICE BLOCKS
    // bof the files main page service blocks
    // main public files
    function mainPublicFiles($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_public_files_block_settings');
        $filesParams = array(
            'browse_mode' => '', 
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Popular') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=popular', 'active' => 'popular' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }
    
    // main top files
    function mainTopFiles($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_top_files_block_settings');
        $filesParams = array(
            'browse_mode' => '', 
            'sort_mode' => 'top',
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getFilesDatas($iBlockID, $filesParams, $settings);
    }
    
    // main favorite files
    function mainFavoriteFiles($iBlockID, $profileId){
        if($profileId <= 0){ return ''; }
        $settings = $this->getBlockSettings('module_blocks_main_favorite_files_block_settings');
        $filesParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
            'extra_search_params' => array(
                'joins' => array(
                    'favorite' => array(
                        'variable' => array(
                            'content' => 'aAddPartsConfig',
                            'content_key' => 'favorite',
                        ),
                    ),
                ),
                'restrictions' => array(
                    'fav' => array(
                        'content' => array(
                            'variable_name' => 'aAddPartsConfig',
                            'value' => $profileId,
                            'operator' => '=',
                            'field' => array(
                                'keys' => array(
                                    'favorite', 'userField',
                                ),
                            ),
                            'table' => array(
                                'keys' => array(
                                    'favorite', 'table',
                                ),
                            ),
                        ),
                    )
                ),
            ),
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getFilesDatas($iBlockID, $filesParams, $settings);
    }

    // main featured files
    function mainFeaturedFiles($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_files_block_settings');
        $filesParams = array(
            'browse_mode' => '', 
            'restriction' => array(
                'featured' => array(
                    'field' => 'Featured',
                    'value' => '1',
                    'operator' => '=',
                    'param' => 'featured'
                ),
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        return $this->getFilesDatas($iBlockID, $filesParams, $settings);
    }
    // eof the files main page service blocks
    // EOF THE FILES SERVICE BLOCKS
    // EOF THE PAGE SERVICE BLOCKS HELPER

    // GETTERS
    // BOF the system methods
    // custom import
    protected function customImport($sClassName, $aModule = array()){
        if (class_exists($aModule['class_prefix'] . $sClassName)){ return; }
        require(BX_DIRECTORY_PATH_MODULES . $aModule['path'] . 'classes/' . $aModule['class_prefix'] . $sClassName . '.php');
    }

    // gets the module from the database
    protected function getModuleArray($modVars = array()){
        if(sizeof($modVars) > 0){
            $aModule = db_assoc_arr ('SELECT `id`, `title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix` FROM `sys_modules` WHERE `title` = "'.$modVars['title'].'" AND `class_prefix` = "'.$modVars['class_prefix'].'" LIMIT 1');
            return array( 'sys_modules' => $aModule, 'mod_vars' => $modVars,);
        } else {
            return false;
        }
    }

    // gets the module config object
    protected function getModuleConfig($aModule){
        $mod = new BxDolModule($aModule);
        $config = $mod->_oConfig;
        return $config;
    }

    // gets the search result object
    protected function getSearchResultObject($aModule){
        $sysModules = $aModule['sys_modules'];
        $modVars = $aModule['mod_vars'];
        $this->customImport($modVars['class_suffix'], $sysModules);
        $sClassName = $modVars['class_name']; 
        $searchObj=(isset($_GLOBALS['emmetbytes_'.$modVars['title'].'SearchObj'])) ? $_GLOBALS['emmetbytes_'.$modVars['title'].'SearchObj'] : new $sClassName(); 
        return array(
            $searchObj, 
            $sClassName,
        );
    }

    // gets the modules base url
    protected function getModuleBaseUrl($moduleUri, $custom=false){
        if(!$custom){
            return $this->getPermalinksObj()->permalink('modules/?r=' . $moduleUri . '/');
        }else{
            return $this->getPermalinksObj()->permalink('modules/' . $moduleUri);
        }
    }

    // gets the system permalink obj
    protected function getPermalinksObj(){
        if(!isset($GLOBALS['oSysPermalinks'])){
            bx_import('BxDolPermalinks');
            $GLOBALS['oSysPermalinks'] = new BxDolPermalinks();
        }
        return $GLOBALS['oSysPermalinks'];
    }

    // gets the template voting view obj
    protected function getVotes($sSystem, $iId){
        bx_import('BxTemplVotingView'); 
        $votingObj = new BxTemplVotingView($sSystem, $iId);
        return $votingObj->getJustVotingElement(false);
    }

    // allow view checker
    protected function isAllowedToView($modVars, $searchObj, $searchResult, $height, $width){
        if(!isset($modVars['allow_view_checker']) || empty($modVars['allow_view_checker'])){
            return false;
        }
        $viewCheckerParams = $modVars['allow_view_checker'];
        if($viewCheckerParams['obj'] == 'default'){
            $modMain = $searchObj->$viewCheckerParams['mod_main']();
            $infoData = $searchResult;
            if(!$modMain->$viewCheckerParams['allow_view_checker']($infoData)){
                // default boonex modules restricted access
                return array(
                    'restricted' => true,
                    'height' => $height,
                    'width' => $width,
                    'icon' => $viewCheckerParams['private_container_params']['icon_image'],
                );
            }
        }elseif($viewCheckerParams['obj'] == 'blogs'){
            $modMain = $searchObj->$viewCheckerParams['mod_main']();
            $modMain = $modMain->$viewCheckerParams['privacy_method'];
            $viewCheckerParam = $searchResult;
            if(!$modMain->$viewCheckerParams['allow_view_checker']('view', $viewCheckerParam['id'], getLoggedId())){
                // boonex blogs module restricted access
                return array(
                    'restricted' => true,
                    'height' => $height,
                    'width' => $width,
                    'icon' => $viewCheckerParams['private_container_params']['icon_image'],
                );
            }
        }else{
            return false;
        }
    }

    // parse the tags
    protected function parseTags($commonData){
        return $this->oTemplate->_parseAnything ($commonData['tags'], ',', $commonData['module_base_uri'] . $commonData['tags_suffix']);
    }

    // parse the categories
    protected function parseCategories($commonData){
        return $this->oTemplate->_parseAnything ($commonData['categories'], ',', $commonData['module_base_uri'] . $commonData['categories_suffix']);
    }

    // bof the custom photo datas fetch, based on the photo module
    // gets the custom photo datas, based on the photo module
    protected function getCustomPhotoDatas($iId, $sType = 'thumb'){
        $aImageInfo = $this->_getImageDbInfo($iId);
        $photosConfig = $this->_getPhotosConfig();
        $imageUrl = $this->_getImageFullUrl($aImageInfo, $photosConfig, $sType);
        return array(
            'file' => $imageUrl,
            'path' => $photosConfig['filesPath'] . $aImageInfo['id'] . $photosConfig['picPostfix'][$sType],
            'height' => $photosConfig['sizes'][$sType],
            'width' => $photosConfig['sizes'][$sType],
        );
    }

    // copied from the photos config page
    protected function _getPhotosConfig(){
        return array(
            'picPostfix' => array(
                'thumb' => '_rt.jpg',
                'browse' => '_t.jpg',
                'icon' => '_ri.jpg',
                'file' => '_m.jpg',
                'original' => '.{ext}'
            ),
            'filesPath' => BX_DIRECTORY_PATH_MODULES . 'boonex/photos/data/files/',
            'baseUri' => BX_DOL_URL_ROOT . 'm/photos/',
            'sizes' => array(
                'thumb' => 64, 'icon' => 32, 'file' => 600, 'browse' => 140, 'original' => 0
            ),
        );
    }

    // copied from the photos search method
    function _getImageDbInfo ($iId) {
        $iId = (int)$iId;
        $sqlQuery = "SELECT a.`ID` as `id`,
        					a.`Ext` as `ext`,
        					a.`Uri` as `uri`,
        					a.`Owner` as `owner`,
        					a.`Hash`
							FROM `bx_photos_main` as a
							WHERE a.`ID`='" . $iId . "' AND a.`Status`<>'disapproved'";
        $aImageInfo = ($iId) ? db_arr($sqlQuery) : null;
        return $aImageInfo;
    }
    
    // get image source url, copied from the photos search method
    protected function _getImageFullUrl ($aImageInfo, $photosConfig, $sType = 'thumb') {
        $sName = $aImageInfo['id'] . $photosConfig['picPostfix'][$sType];
        $sName = str_replace('{ext}', $aImageInfo['ext'], $sName);
        $sImageUrl = !empty($aImageInfo['id']) && extFileExists($photosConfig['filesPath'] . $sName) ? $this->getImgUrl($photosConfig, $aImageInfo['Hash'], $sType) : '';
        return $sImageUrl; 
    }

    // gets the image url, copied from the photos module
    function getImgUrl ($photosConfig, $sHash, $sImgType = 'browse') {
	    return $photosConfig['baseUri'] . 'get_image/' . $sImgType .'/' . $sHash . '.jpg';
	}
    // eof the custom photo datas fetch, based on the photo module
    // EOF the system methods

    // BOF the data getters
    // getting the thumbnail search results
    protected function getThumnailSearchResults($aModule, $getMoreEntries = false){
        $modVars = $aModule['mod_vars'];
        $thumbnailsParams = $modVars['thumbnails_params'];
        $searchParams = $thumbnailsParams['search_params'];
        list($o, $className) = $this->getSearchResultObject($aModule);
        if(isset($thumbnailsParams['block_addon_checker']) && sizeof($thumbnailsParams['block_addon_checker']) > 0){
            $blockAddonChecker = $thumbnailsParams['block_addon_checker'];
            if($blockAddonChecker['type'] == 'file_album'){
                $albumUri = $thumbnailsParams['a_current']['inner_replace']['restriction']['album']['value'];
                $ownerId = $thumbnailsParams['a_current']['inner_replace']['restriction']['owner']['value'];
                // create the album object
                $oAlbums = new BxDolAlbums($modVars['system']);
                // get the album info
                $albumInfo = $oAlbums->getAlbumInfo(array('fileUri' => $albumUri, 'owner' => $ownerId), array('ID')); 
                $albumId = $albumInfo['ID'];
                if(isset($blockAddonChecker['checker']) && sizeof($blockAddonChecker['checker']) > 0){
                    $blockAddonCheckerParams = $blockAddonChecker['checker'];
                    // getting the main module
                    $mainMod = $o->$blockAddonCheckerParams['main_module'];
                    $modPrivacy = $mainMod->$blockAddonCheckerParams['privacy_method'];
                    if(!$modPrivacy->check('album_view', $albumId, getLoggedId())){
                        $allowSearch = false;
                        return array();
                    }
                }
            }
        }
        // added the browse modes
        $browsePage = (isset($searchParams['browse_mode'])) ? $searchParams['browse_mode'] : false; 
        if($browsePage){
            $param1 = $searchParams['param1'];
            $o->$className($browsePage, $param1);
        }
        // change the current aCurrent values
        $replaceValues = $thumbnailsParams['a_current']; 
        foreach($replaceValues as $key=>$aCurrentValues){
            switch($key){
                case 'inner_replace':
                    foreach($aCurrentValues as $key1=>$aCurrentValue){
                        $o->aCurrent[$key1] = array_replace_recursive($o->aCurrent[$key1],$aCurrentValue);
                    }
                    break;
                case 'replace_all':
                    foreach($aCurrentValues as $key1=>$aCurrentValue){
                        $o->aCurrent[$key1] = $aCurrentValue;
                    }
                    break;
            }
        }
        if(isset($modVars['thumbnails_params']['extra_search_params']) && !empty($modVars['thumbnails_params']['extra_search_params'])){
            // join
            if(isset($modVars['thumbnails_params']['extra_search_params']['joins'])){
                $joinDatas = $modVars['thumbnails_params']['extra_search_params']['joins'];
                foreach($joinDatas as $key=>$joinData){
                    if(isset($joinData['variable'])){
                        $var = $o->$joinData['variable']['content'];
                        $o->aCurrent['join'][$key] = $var[$joinData['variable']['content_key']];
                    }
                }
            }
            // restrictions
            if(isset($modVars['thumbnails_params']['extra_search_params']['restrictions'])){
                $restrictionDatas = $modVars['thumbnails_params']['extra_search_params']['restrictions'];
                foreach($restrictionDatas as $key=>$restrictionData){
                    $varName = $restrictionData['content']['variable_name'];
                    if(is_array($restrictionData['content']['field'])){
                        $keys = $restrictionData['content']['field']['keys'];
                        $restrictionData['content']['field'] = $o->$varName;
                        foreach($keys as $key){
                            $restrictionData['content']['field'] = $restrictionData['content']['field'][$key];
                        }
                    }
                    if(is_array($restrictionData['content']['table'])){
                        $keys = $restrictionData['content']['table']['keys'];
                        $restrictionData['content']['table'] = $o->$varName;
                        foreach($keys as $key){
                            $restrictionData['content']['table'] = $restrictionData['content']['table'][$key];
                        }
                    }
                    unset($restrictionData['content']['variable_name']);
                    $o->aCurrent['restriction'][$key] = $restrictionData['content'];
                }
            }
        }
        if(isset($modVars['is_public']) && !empty($modVars['is_public'])){ $o->setPublicUnitsOnly($modVars['is_public']); }
        if($getMoreEntries){
            $totalNum = $o->getCount();
            $o->aCurrent['paginate']['forcePage'] = $aModule['page'];
            $o->aCurrent['paginate']['perPage'] = $totalNum - $aModule['total_entries'] + $modVars['thumbnails_params']['a_current']['inner_replace']['paginate']['perPage'];
        }
        $searchDatas = $o->getSearchData();
        if(sizeof($searchDatas) <= 0){ return false; }
        return array(
            'search_results' => $searchDatas, 
            'total_entries' => $o->aCurrent['paginate']['totalNum'],
            'search_obj' => $o,
        );
    }

    // getting the information search results
    protected function getInformationSearchResult($resultId, $informationParams, $searchObj){
        $replaceValues = $informationParams['a_current']; 
        foreach($replaceValues as $key=>$aCurrentValues){
            switch($key){
                case 'inner_replace':
                    foreach($aCurrentValues as $key1=>$aCurrentValue){
                        $searchObj->aCurrent[$key1] = array_replace_recursive($searchObj->aCurrent[$key1],$aCurrentValue);
                    }
                    break;
                case 'replace_all':
                    foreach($aCurrentValues as $key1=>$aCurrentValue){
                        $searchObj->aCurrent[$key1] = $aCurrentValue;
                    }
                    break;
            }
        }
        $searchObj->aCurrent['restriction']['id'] = array('value' => $resultId, 'field' => $informationParams['prim_key'], 'operator' => '=');
        return $searchObj->getSearchData();
    }

    // gets the remapped columns
    protected function getRemappedColumns($searchDatas, $columnsMap){
        foreach($columnsMap as $key1=>$columnMap){
            if(isset($searchDatas[$columnMap])){
                $remappedSearchDatas[$key1] = $searchDatas[$columnMap];
            }
        }
        return $remappedSearchDatas;
    }

    // getting the thumbnails common data
    protected function getThumbnailCommonData($searchObj, $resultData, $modVars, $sysModules, $thumbnailsColumnsMap){
        $searchResult = $resultData;
        $resultData = $commonData = $this->getRemappedColumns($resultData, $thumbnailsColumnsMap, $sysModules);
        // getting the files data
        if(isset($searchObj->aConstants) && isset($searchObj->aConstants) && !empty($searchObj->aConstants['filesDir'])){
            $filesDir = $searchObj->aConstants['filesDir'];
            $filesPostfix = (isset($searchObj->aConstants['picPostfix'])) ? ((array_key_exists('browse', (array)$searchObj->aConstants['picPostfix'])) ? $searchObj->aConstants['picPostfix']['browse'] : $searchObj->aConstants['picPostfix']) : '';
        }
        // getting the thumbnails photo
        if(isset($resultData['thumb_photo']) && !empty($resultData['thumb_photo'])){
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_photo'], 'thumb');
        }elseif(isset($resultData['thumb_blog_photo']) && !empty($resultData['thumb_blog_photo'])){
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_blog_photo']);
        }elseif(isset($resultData['thumb_file_photo']) && !empty($resultData['thumb_file_photo'])){
            $imageFile = (file_exists($filesDir . $resultData['thumb_file_photo'] . $filesPostfix)) ? $filesDir . $resultData['thumb_file_photo'] . $filesPostfix : $filesDir . $modVars['prim_photo_default_name'];
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_file_photo'], $searchObj); // gets the file images
        }elseif(isset($resultData['thumb_file_photo_two']) && !empty($resultData['thumb_file_photo_two'])){
            $imageFile = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['path'], true) . $modVars['prim_photo_defaults'];
            $primPhoto = $this->$modVars['prim_photo_function']($imageFile); // gets the file images
        }else{
            $primPhoto=array('file'=> $this->oTemplate->getIconUrl($modVars['empty_image_icon']),'width'=>64,'height'=>64);
        }
        $commonData['thumb_photos'] = $primPhoto;
        // check for the restricted data
        if($allowViewData = $this->isAllowedToView($modVars, $searchObj, $searchResult, $primPhoto['height'], $primPhoto['width'])){
            return array_merge($commonData, $allowViewData);
        }
        if(isset($modVars['custom_uri']) && !empty($modVars['custom_uri'])){
            $commonData['module_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true) . $modVars['view_uri'] . $resultData['entry_uri'];
            $commonData['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true);
        }elseif(isset($sysModules['uri']) && !empty($sysModules['uri'])){
            $commonData['module_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']) . $modVars['view_uri'] . $resultData['entry_uri'];
            $commonData['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']);
        }
        return $commonData;
    }

    // getting the informations common data
    protected function getInformationsCommonData($resultData, $modVars, $sysModules, $searchObj){

        if(isset($searchObj->aConstants) && isset($searchObj->aConstants) && !empty($searchObj->aConstants['filesDir'])){
            $filesDir = $searchObj->aConstants['filesDir'];
            $filesPostfix = (isset($searchObj->aConstants['picPostfix'])) ? ((array_key_exists('browse', (array)$searchObj->aConstants['picPostfix'])) ? $searchObj->aConstants['picPostfix']['browse'] : $searchObj->aConstants['picPostfix']) : '';
        }

        $commonData = $resultData;
        // getting the module uri
        if(isset($modVars['custom_uri']) && !empty($modVars['custom_uri'])){
            $commonData['module_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true) . $modVars['view_uri'] . $resultData['entry_uri'];
            $commonData['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true);
        }elseif(isset($sysModules['uri']) && !empty($sysModules['uri'])){
            $commonData['module_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']) . $modVars['view_uri'] . $resultData['entry_uri'];
            $commonData['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']);
        }
        if(isset($resultData['prim_file_photo']) && !empty($resultData['prim_file_photo'])){
            $imageFile = (file_exists($filesDir . $resultData['prim_file_photo'] . $filesPostfix)) ? $filesDir . $resultData['prim_file_photo'] . $filesPostfix : $filesDir . $modVars['prim_photo_default_name'];
            $commonData['prim_file_photo'] = base64_encode($imageFile);
        }elseif(isset($resultData['prim_file_photo_two']) && !empty($resultData['prim_file_photo_two'])){
            $imageFile = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['path'], true) . $modVars['prim_photo_defaults'];
            $commonData['prim_file_photo'] = base64_encode($imageFile);
        }
        $commonData['place'] = (isset($resultData['place']) && !empty($resultData['place'])) ? $resultData['place'] . ', ' : '';
        $commonData['city'] = (isset($resultData['city']) && !empty($resultData['city'])) ? $resultData['city'] . ', ' : '';
        $commonData['categories_suffix'] = str_replace('{nickname}', getNickName($resultData['author_id']), $modVars['categories_link']);
        $commonData['tags_suffix'] = str_replace('{nickname}', getNickName($resultData['author_id']), $modVars['tags_link']);
        $commonData['description'] = strip_tags($resultData['description']);
        // getting the album uri
        if(isset($modVars['album_uri']) && !empty($modVars['album_uri'])){
            $profileInfo = getProfileInfo($resultData['author_id']);
            $albumUri = str_replace('{owner_name}', $profileInfo['NickName'], $modVars['album_uri']);
            $commonData['album_uri'] = $commonData['module_base_uri'] . str_replace('{album_uri}', $resultData['album_uri'] , $albumUri);
        }
        return $commonData;
    }

    // getting the primary photo, based on the photo object
    protected function getPrimPhoto($primPhotoId, $type='file'){
        return $this->getCustomPhotoDatas($primPhotoId, $type);
    }

    // getting the blog images
    protected function getPrimBlogPhoto($photoName){
        return array(
            'file' => BX_DOL_URL_ROOT . 'media/images/blog/big_' . $photoName,
            'width' => 64,
            'height' => 64,
        );
    }

    // getting the file images
    protected function getPrimFilePhoto($primPhotoId, $searchObj){
        return array(
            'file' => $searchObj->getImgUrl($primPhotoId), 
            'width' => 64,
            'height' => 64
        );
    }

    // getting the file images part two
    protected function getPrimFilePhotoTwo($path){
        return array(
            'file' => $path, 
            'width' => 64,
            'height' => 64
        );
    }

    // getting the information data
    protected function getInformationData($resultId, $informationParams, $modVars, $sysModules, $searchObj, $system, $settings){
        $searchResult = $this->getInformationSearchResult($resultId, $informationParams, $searchObj);
        $oldSearchResult = $searchResult[0];
        $height = $settings['info_image_height']; 
        $width = $settings['info_image_width'];
        // check for the restricted data
        if($allowViewData = $this->isAllowedToView($modVars, $searchObj, $searchResult[0], $height, $width)){
            return $allowViewData;
        }
        $searchResult = $this->getRemappedColumns($searchResult[0], $informationParams['columns_map']);
        $commonData = $this->getInformationsCommonData($searchResult, $modVars, $sysModules, $searchObj);
        $image_url = '';
        $profileInfo = getProfileInfo($commonData['author_id']);
        $infoDataCaptions = $this->getInfoDataCaptions();
        // getting the primary photo
        if(isset($commonData['prim_photo']) && !empty($commonData['prim_photo'])){
            $image_url = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_info_image/' . $commonData['prim_photo'] . '/' . $width.'/'.$height;
        }else if(isset($commonData['prim_blog_photo']) && !empty($commonData['prim_blog_photo'])){
            $image_url = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_info_blog_image/' . $commonData['prim_blog_photo'] . '/' . $width.'/'.$height;
        }else if(isset($commonData['prim_file_photo']) && !empty($commonData['prim_file_photo'])){
            $image_url = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_info_file_image/' . $commonData['prim_file_photo'] . '/' . $width.'/'.$height;
        }else if(isset($commonData['prim_file_photo_two']) && !empty($commonData['prim_file_photo_two'])){
            $image_url = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_info_file_image_two/' . $commonData['prim_file_photo'] . '/' . $width.'/'.$height;
        }else{
            $image_url = $this->oTemplate->getIconUrl($modVars['empty_image_icon']);
        }
        $description = $commonData['description'];
        if(strlen($description) > $settings['max_description_chars']){
            $description = process_text_withlinks_output(mb_substr($description, 0, $settings['max_description_chars'])) . '...';
        }
        $commonData = array(
            'nickname' => $profileInfo['NickName'],
            'image_url' => $image_url,
            'width' => $width,
            'height' => $height,
            'module_uri' => $commonData['module_uri'],
            'spacer' => getTemplateIcon('spacer.gif'),
            'bx_if:display_title' => array(
                'condition' => (isset($commonData['title']) && !empty($commonData['title'])) ? true : false,
                'content' => array(
                    'title_caption' => $infoDataCaptions['title_caption'],
                    'title' => $commonData['title'],
                    'module_uri' => $commonData['module_uri'],
                ),
            ),
            'bx_if:display_sites_url' => array(
                'condition' => ($settings['display_sites_url'] && isset($commonData['sites_url']) && !empty($commonData['sites_url'])) ? true : false,
                'content' => array(
                    'sites_url_caption' => $infoDataCaptions['sites_url_caption'],
                    'sites_url_title' => (isset($commonData['sites_url'])) ? $commonData['sites_url'] : '',
                    'sites_url_link' => (isset($commonData['sites_url'])) ? ((!preg_match('/http:\//', $commonData['sites_url'])) ? 'http://'. $commonData['sites_url'] : $commonData['sites_url']) : '',
                ),
            ),
            'bx_if:display_author' => array(
                'condition' => ($settings['display_author'] && isset($profileInfo['NickName']) && !empty($profileInfo['NickName'])) ? true : false,
                'content' => array(
                    'author_caption' => $infoDataCaptions['author_caption'],
                    'author_username' => getNickName($profileInfo['ID']),
                    'author_url' => getProfileLink($profileInfo['ID']),
                ),
            ),
            'bx_if:display_album' => array(
                'condition' => ($settings['display_album'] && isset($commonData['album_uri']) && !empty($commonData['album_uri'])) ? true : false,
                'content' => array(
                    'album_caption' => $infoDataCaptions['album_caption'],
                    'album_title' => (isset($commonData['album_title'])) ? $commonData['album_title'] : '',
                    'album_url' => (isset($commonData['album_uri'])) ? $commonData['album_uri'] : '',
                ),
            ),
            'bx_if:display_rating' => array(
                'condition' => ($settings['display_rating'] && isset($commonData['entry_rate']) && $commonData['entry_rate'] != '') ? true : false,
                'content' => array(
                    'rating_caption' => $infoDataCaptions['rating_caption'],
                    'rating' => $this->getVotes($system, $commonData['id']),
                ),
            ),
            'bx_if:display_location' => array(
                'condition' => ($settings['display_location'] && isset($commonData['country']) && !empty($commonData['country'])) ? true : false,
                'content' => array(
                    'location_caption' => $infoDataCaptions['location_caption'],
                    'location' => (isset($commonData['country'])) ? (genFlag($commonData['country']) . ' ' . $commonData['place'] . $commonData['city'] . _t($GLOBALS['aPreValues']['Country'][$commonData['country']]['LKey'])) : '',
                ),
            ),
            'bx_if:display_tags' => array(
                'condition' => ($settings['display_tags'] && isset($commonData['tags']) && !empty($commonData['tags'])) ? true : false,
                'content' => array(
                    'tags_caption' => $infoDataCaptions['tags_caption'],
                    'tags' => $this->parseTags($commonData),                
                ),
            ),
            'bx_if:display_categories' => array(
                'condition' => ($settings['display_categories'] && isset($commonData['categories']) && !empty($commonData['categories'])) ? true : false,
                'content' => array(
                    'categories_caption' => $infoDataCaptions['categories_caption'],
                    'categories' => $this->parseCategories($commonData),                
                ),
            ),
            'bx_if:display_date_start' => array(
                'condition' => ($settings['display_date_start'] && isset($commonData['entry_start']) && !empty($commonData['entry_start'])) ? true : false,
                'content' => array(
                    'date_start_caption' => $infoDataCaptions['date_start_caption'],
                    'entry_start_formatted' => (isset($commonData['entry_start'])) ? getLocaleDate($commonData['entry_start'], BX_DOL_LOCALE_DATE_SHORT) : '',
                    'entry_start_date_ago' => (isset($commonData['entry_start'])) ? defineTimeInterval($commonData['entry_start']) : '',
                ),
            ),
            'bx_if:display_date_end' => array(
                'condition' => ($settings['display_date_end'] && isset($commonData['entry_end']) && !empty($commonData['entry_end'])) ? true : false,
                'content' => array(
                    'date_end_caption' => $infoDataCaptions['date_end_caption'],
                    'entry_end_formatted' => (isset($commonData['entry_end'])) ? getLocaleDate($commonData['entry_end'], BX_DOL_LOCALE_DATE_SHORT) : '',
                    'entry_end_date_ago' => (isset($commonData['entry_end'])) ? defineTimeInterval($commonData['entry_end']) : '',
                ),
            ),
            'bx_if:display_description' => array(
                'condition' => ($settings['display_description'] && isset($commonData['description']) && !empty($commonData['description'])) ? true : false,
                'content' => array(
                    'desc_caption' => $infoDataCaptions['desc_caption'],
                    'description' => $description,
                ),
            ),
        );
        return $commonData;
    }

    // getting the information data captions/languages
    protected function getInfoDataCaptions(){
        return array(
           'title_caption' => _t('emmetbytes_bon_con_special_info_common_info_title_caption'),
           'sites_url_caption' => _t('emmetbytes_bon_con_special_info_common_info_sites_url_caption'),
           'author_caption' => _t('emmetbytes_bon_con_special_info_common_info_author_caption'),
           'album_caption' => _t('emmetbytes_bon_con_special_info_common_info_album_caption'),
           'rating_caption' => _t('emmetbytes_bon_con_special_info_common_info_rating_caption'),
           'location_caption' => _t('emmetbytes_bon_con_special_info_common_info_location_caption'),
           'tags_caption' => _t('emmetbytes_bon_con_special_info_common_info_tags_caption'),
           'categories_caption' => _t('emmetbytes_bon_con_special_info_common_info_categories_caption'),
           'date_start_caption' => _t('emmetbytes_bon_con_special_info_common_info_date_start_caption'),
           'date_end_caption' => _t('emmetbytes_bon_con_special_info_common_info_date_end_caption'),
           'desc_caption' => _t('emmetbytes_bon_con_special_info_common_info_description_caption'),
        );
    }

    // gets the settings
    protected function getBlockSettings($name){
        return $this->oDb->getBonConSpecialInfoSettings($name);
    }
    // EOF the data getters

    // BOF the content getters
    // gets the modules common container
    protected function getModuleCommonContainer($modVars, $extraParams = false){
        $aModule = $this->getModuleArray($modVars);
        if(sizeof($aModule['sys_modules']) <= 0){
            return false;
        }
        if($extraParams){
            if(isset($extraParams['restriction'])){
                $ownerId = $modVars['thumbnails_params']['a_current']['inner_replace']['restriction']['owner']['value'];
                $moduleConfig = $this->getModuleConfig($aModule['sys_modules']);
                $profileInfo = getProfileInfo($ownerId);
                $sCaption = str_replace('{nickname}', $profileInfo['NickName'], $moduleConfig->getGlParam('profile_album_name'));
                $sUri = uriFilter($sCaption);
                $extraRestrictions = array('album' => array('value'=>$sUri, 'field'=>'Uri', 'operator'=>'=', 'paramName'=>'albumUri', 'table'=>'sys_albums'));
                $aModule['mod_vars']['thumbnails_params']['a_current']['inner_replace']['restriction'] = array_merge_recursive($aModule['mod_vars']['thumbnails_params']['a_current']['inner_replace']['restriction'], $extraRestrictions);
            }
        }
        $searchResultDatas = $this->getThumnailSearchResults($aModule);
        if(!$searchResultDatas){
            $commonContainer = $this->getEmptyContainer();
        }else{
            $aModule['total_entries'] = $searchResultDatas['total_entries'];
            $aModule['num_of_displayed_entries'] = sizeof($searchResultDatas['search_results']);
            $moduleDatas = array( 'mod_datas' => $aModule, 'result_datas' => $searchResultDatas);
            $commonContainer = $this->getCommonMainContainer($moduleDatas);
        }
        return $commonContainer;
    }

    // gets the empty container
    protected function getEmptyContainer(){
        return MsgBox(_t('_Empty'));
    }

    // getting the common main container
    protected function getCommonMainContainer($moduleDatas){
        $moduleVars = $moduleDatas['mod_datas'];
        $modVars = $moduleVars['mod_vars'];
        $sysModules = $moduleVars['sys_modules'];
        $resultDatas = $moduleDatas['result_datas'];
        $searchResults = $resultDatas['search_results'];
        $searchObj = $resultDatas['search_obj'];
        $iBlockID = $modVars['block_id'];
        $system = $modVars['system'];
        $thumbnailsParams = $modVars['thumbnails_params'];
        $thumbnailsColumnsMap = $thumbnailsParams['columns_map'];
        $informationParams = $modVars['information_params'];
        $settings = $modVars['settings'];
        $thumbnailDatas = array();
        $infoContainer = $thumbActiveClass = '';
        $templateVars = $this->getMainTemplateVars($iBlockID);
        $counter=0;
        foreach($searchResults as $key=>$searchResult){
            if($counter==0){
                $thumbActiveClass = 'active';
                $moduleVars['current_entry'] = $searchResult['id'];
                $infoContainer = $this->getCommonInfoBlockContainer($searchResult['id'], $informationParams, $modVars, $sysModules, $searchObj, $system, $settings, $templateVars);        
            }
            $thumbnailDatas[$key]['container'] = $this->getCommonThumbContainer($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars, $thumbActiveClass);
            $thumbActiveClass = '';
            ++$counter;
        }
        $mcVars = array(
            'block_id' => $iBlockID,
            'info_container' => $infoContainer,
            'bx_repeat:thumbnail_datas' => $thumbnailDatas, 
            'script' => $this->getMainJavascript($templateVars, $moduleVars),
        );
        $mcVars = array_merge($mcVars, $templateVars);
        return $this->oTemplate->parseHTMLByName('common_main_container', $mcVars);
    }

    // getting of the template variables
    protected function getMainTemplateVars($iBlockID){
        return array(
            'main_container_class' => 'emmetbytes_bon_con_special_info_common_main_container',
            'main_container_id' => 'emmetbytes_bon_con_special_info_common_main_container_' . $iBlockID,
            'loader_container_class' => 'emmetbytes_bon_con_special_info_loader_container',
            'info_container_class' => 'emmetbytes_bon_con_special_info_common_info_container',
            'thumbnails_container_class' => 'emmetbytes_bon_con_special_info_common_thumbnails_container',
            'thumbnails_nav_container' => 'emmetbytes_bon_con_special_info_common_thumb_nav',
            'thumbnails_nav_container_prev' => 'emmetbytes_bon_con_special_info_common_thumb_nav_prev',
            'thumbnails_nav_container_next' => 'emmetbytes_bon_con_special_info_common_thumb_nav_next',
            'thumbnails_container_inner_class' => 'emmetbytes_bon_con_special_info_common_thumbnails_inner_container',
            'thumbnail_container_class' => 'emmetbytes_bon_con_special_info_common_thumbnail_container',
            'thumbnail_container_inner_class' => 'emmetbytes_bon_con_special_info_common_inner_thumbnail_container',
            'thumbnail_inner_container_class' => 'emmetbytes_bon_con_special_info_common_thumbnail_inner_container',
        );
    }

    // gets the common information block container
    protected function getCommonInfoBlockContainer($resultId, $informationParams, $modVars, $sysModules, $searchObj, $system, $settings, $templateVars){
        $infoDatas = $this->getInformationData($resultId, $informationParams, $modVars, $sysModules, $searchObj, $system, $settings);
        if(isset($infoDatas['restricted']) && $infoDatas['restricted']){
            return $this->getRestrictedInfoDataContainer($infoDatas);
        }else{
            $infoDatas = array_merge($infoDatas, $templateVars);
            return $this->oTemplate->parseHTMLByName('common_info_container', $infoDatas);
       }
    }

    // getting the restricted data container
    protected function getRestrictedInfoDataContainer($infoDatas){
        $infoDatas['icon'] = $this->oTemplate->getIconUrl($infoDatas['icon']);
        $infoDatas['spacer'] = getTemplateIcon('spacer.gif');
        $infoDatas['caption'] = _t('_emmetbytes_bon_con_special_info_restricted_access_caption');
        return $this->oTemplate->parseHTMLByName('restricted_info_container', $infoDatas);
    }

    // gets the private container part two
    protected function getPrivateContainerTwo(){
        return $this->getEmptyContainer();
    }

    // getting the common thumbnail container
    protected function getCommonThumbContainer($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars, $isActive=''){
        $commonData = $this->getThumbnailCommonData($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap);
        $tData = $commonData['thumb_photos'];
        if(isset($commonData['restricted']) && $commonData['restricted']){
            return $this->getRestrictedThumbnailContainer($commonData, $templateVars, $modVars);
        }else{
            $title = $this->subWords($commonData['title'], 35);
            $thumbnailDatas = array(
                'id' => $commonData['id'],
                'title' => $title,
                'is_active' => $isActive,
                'spacer' => getTemplateIcon('spacer.gif'),
                'thumbnail_image' => $tData['file'],
                'thumbnail_width' => $tData['width'],
                'thumbnail_height' => $tData['height'],
                'module_uri' => $commonData['module_uri'],
            );
            $thumbnailDatas = array_merge($thumbnailDatas, $templateVars);
            return $this->oTemplate->parseHTMLByName('common_thumbnail_container', $thumbnailDatas);
        }
    }

    // getting the restricted thumbnail container
    protected function getRestrictedThumbnailContainer($commonData, $templateVars, $modVars){
        $thumbnailDatas = array(
            'id' => $commonData['id'],
            'caption' => _t('_emmetbytes_bon_con_special_info_restricted_access_caption'),
            'is_active' => $isActive,
            'spacer' => getTemplateIcon('spacer.gif'),
            'thumbnail_image' => $this->oTemplate->getIconUrl($modVars['allow_view_checker']['private_container_params']['icon_image']),
            'thumbnail_width' => $commonData['width'],
            'thumbnail_height' => $commonData['height'],
        );
        $thumbnailDatas = array_merge($thumbnailDatas, $templateVars);
        return $this->oTemplate->parseHTMLByName('restricted_thumbnail_container', $thumbnailDatas);
    }

    // getting the main javascript code
    protected function getMainJavascript($templateVars, $moduleVars){
        $iBlockID = $moduleVars['mod_vars']['block_id'];
        $jsVars = array(
            'get_entries_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_more_entries',
            'get_info_container_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_information_container',
            'block_id' => $iBlockID,
            'module_vars' => (!is_null($moduleVars)) ? json_encode($moduleVars) : '',
        );
        $jsVars = array_merge($jsVars, $templateVars);
        return $this->oTemplate->parseHTMLByName('main_js', $jsVars);
    }
    // EOF the content getters

    // BOF the module getters
    // bof events module
    // getting the event datas
    protected function getEventDatas($iBlockID, $eventParams, $settings = array()){
        $modVars = $this->getEventModuleVars($iBlockID, $eventParams, $settings);
        $commonContainer = $this->getModuleCommonContainer($modVars);
        unset($modVars);
        return $commonContainer;
    }

    // event module variables
    protected function getEventModuleVars($iBlockID, $eventParams, $settings = array()){
        $eventModuleVars = array(
            'title' => 'Events',
            'class_prefix' => 'BxEvents' ,
            'class_suffix' => 'SearchResult',
            'class_name' => 'BxEventsSearchResult',
            'view_uri' => 'view/',
            'empty_image_icon' => 'no-photo.png',
            'system' => 'bx_events',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'ID',
                    'title' => 'title',
                    'entry_uri' => 'EntryUri',
                    'thumb_photo' => 'PrimPhoto',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($eventParams['browse_mode'])) ? $eventParams['browse_mode']: '',
                    'param1' => (isset($eventParams['param1'])) ? $eventParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID', 
                            'title',
                            'PrimPhoto',
                            'EntryUri',
                        ),
                    ),
                    'primary_key' => 'ID',
                ),
            ),
            'information_params' => array(
                'prim_key' => 'ID', 
                'columns_map' => array(
                    'id' => 'ID',
                    'title' => 'Title',
                    'description' => 'Description',
                    'entry_uri' => 'EntryUri',
                    'country' => 'Country',
                    'city' => 'City',
                    'place' => 'Place',
                    'entry_start' => 'EventStart',
                    'entry_end' => 'EventEnd',
                    'author_id' => 'ResponsibleID',
                    'prim_photo' => 'PrimPhoto',
                    'fans_count' => 'FansCount',
                    'entry_rate' => 'Rate',
                    'categories' => 'Categories',
                    'tags' => 'Tags',
                ),
                'a_current' => array(
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID',
                            'Title',
                            'Description',
                            'EntryUri',
                            'Country',
                            'City',
                            'Place',
                            'EventStart',
                            'EventEnd',
                            'ResponsibleID',
                            'PrimPhoto',
                            'FansCount',
                            'Rate',
                            'Categories',
                            'Tags',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimPhoto',
            'block_id' => $iBlockID,
            'is_public' => $eventParams['is_public'],
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($eventParams['settings_name']),
        );
        if(isset($eventParams['allow_view_checker']) && !empty($eventParams['allow_view_checker'])){
            $eventModuleVars['allow_view_checker'] = array(
                'obj' => 'default',
                'mod_main' => 'getMain',
                'allow_view_checker' => 'isAllowedView',
                'param' => 'searchResult',
                'private_container_params' => array(
                    'icon_image' => 'lock.png',
                ),
            );
        }
        return $eventModuleVars;
    }
    // eof the events module

    // bof groups module
    // getting the groups datas
    protected function getGroupsDatas($iBlockID, $groupsParams, $settings=array()){
        $modVars = $this->getGroupsModuleVars($iBlockID, $groupsParams, $settings);
        $commonContainer = $this->getModuleCommonContainer($modVars);
        unset($modVars);
        return $commonContainer;
    }

    // getting the groups module variables
    protected function getGroupsModuleVars($iBlockID, $groupsParams, $settings=array()){
        $groupsModuleVars = array(
            'title' => 'Groups',
            'class_prefix' => 'BxGroups' ,
            'class_suffix' => 'SearchResult',
            'class_name' => 'BxGroupsSearchResult',
            'view_uri' => 'view/',
            'empty_image_icon' => 'no-photo.png',
            'system' => 'bx_groups',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_photo' => 'thumb',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($groupsParams['browse_mode'])) ? $groupsParams['browse_mode']: '',
                    'param1' => (isset($groupsParams['param1'])) ? $groupsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'id',
                            'title',
                            'uri',
                            'thumb',
                        ),
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'id', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'desc',
                    'entry_uri' => 'uri',
                    'country' => 'country',
                    'city' => 'city',
                    'author_id' => 'author_id',
                    'prim_photo' => 'thumb',
                    'fans_count' => 'fans_count',
                    'entry_rate' => 'rate',
                    'categories' => 'categories',
                    'tags' => 'tags',
                ),
                'a_current' => array(
                    'replace_all' => array(
                        'ownFields' => array(
                            'id',
                            'title',
                            'desc',
                            'uri',
                            'country',
                            'city',
                            'author_id',
                            'thumb',
                            'fans_count',
                            'rate',
                            'categories',
                            'tags',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimPhoto',
            'block_id' => $iBlockID,
            'is_public' => $groupsParams['is_public'],
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($groupsParams['settings_name']),
        );
        if(isset($groupsParams['allow_view_checker']) && !empty($groupsParams['allow_view_checker'])){
            $groupsModuleVars['allow_view_checker'] = array(
                'obj' => 'default',
                'mod_main' => 'getMain',
                'allow_view_checker' => 'isAllowedView',
                'param' => 'searchResult',
                'private_container_params' => array(
                    'icon_image' => 'lock.png',
                ),
            );
        } 
        return $groupsModuleVars;
    }
    // eof the groups module

    // bof sites module
    // getting the sites datas
    protected function getSiteDatas($iBlockID, $siteParams, $settings=array()){
        $modVars = $this->getSitesModuleVars($iBlockID, $siteParams, $settings);
        $commonContainer = $this->getModuleCommonContainer($modVars);
        unset($modVars);
        return $commonContainer;
    }

    // sites module variables
    protected function getSitesModuleVars($iBlockID, $siteParams, $settings){
        $sitesModuleVars = array(
            'title' => 'Sites',
            'class_prefix' => 'BxSites' ,
            'class_suffix' => 'SearchResult',
            'class_name' => 'BxSitesSearchResult',
            'view_uri' => 'view/',
            'empty_image_icon' => 'no-photo.png',
            'system' => 'bx_sites',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'entryUri',
                    'thumb_photo' => 'photo',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($siteParams['browse_mode'])) ? $siteParams['browse_mode']: '',
                    'param1' => (isset($siteParams['param1'])) ? $siteParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                        'restriction' => array(
                            'allow_view' => array(
                                'value' => array(BX_DOL_PG_ALL,BX_DOL_PG_MEMBERS),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'id',
                            'title',
                            'entryUri',
                            'photo',
                        ),
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'id', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'description',
                    'prim_photo' => 'photo',
                    'entry_uri' => 'entryUri',
                    'author_id' => 'ownerId',
                    'tags' => 'tags',
                    'categories' => 'categories',
                    'entry_rate' => 'rate',
                    'sites_url' => 'url',
                ),
                'a_current' => array(
                    'replace_all' => array(
                        'ownFields' => array(
                            'id',
                            'title',
                            'description',
                            'photo',
                            'entryUri',
                            'ownerId',
                            'tags',
                            'categories',
                            'rate',
                            'url',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimPhoto',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($siteParams['settings_name']),
        );
        return $sitesModuleVars;
    }
    // eof the sites module

    // bof blogs module
    // getting the blogs datas
    protected function getBlogsDatas($iBlockID, $blogsParams, $settings=array()){
        $modVars = $this->getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $commonContainer = $this->getModuleCommonContainer($modVars);
        unset($modVars);
        return $commonContainer;
    }

    // blogs module variables
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = array(
            'title' => 'Blog',
            'class_prefix' => 'BxBlogs' ,
            'class_suffix' => 'SearchUnit',
            'class_name' => 'BxBlogsSearchUnit',
            'view_uri' => 'entry/',
            'empty_image_icon' => 'no-photo.png',
            'system' => 'blogposts',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_blog_photo' => 'PostPhoto',
                    'allow_view' => 'allowView',
                    'author_id' => 'ownerId',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($blogsParams['browse_mode'])) ? $blogsParams['browse_mode']: '',
                    'param1' => (isset($blogsParams['param1'])) ? $blogsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'PostID',
                            'PostCaption',
                            'PostUri',
                            'PostPhoto',
                            'PostDate',
                            'allowView',
                            'Rate',
                            'RateCount',
                        ),
                        'sorting' => (!empty($blogsParams['sort_mode'])) ? $blogsParams['sort_mode'] : '', 
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'PostID', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'bodyText',
                    'prim_blog_photo' => 'PostPhoto',
                    'entry_uri' => 'uri',
                    'entry_rate' => 'Rate',
                    'tags' => 'tag',
                    'categories' => 'Categories',
                    'entry_rate' => 'Rate',
                    'author_id' => 'ownerId',
                    'allow_view' => 'allowView',
                ),
                'a_current' => array(
                    'replace_all' => array(
                        'ownFields' => array(
                            'PostID',
                            'PostCaption',
                            'PostText',
                            'PostPhoto',
                            'PostDate',
                            'PostUri',
                            'Rate',
                            'RateCount',
                            'Tags',
                            'Categories',
                            'OwnerId',
                            'allowView',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimBlogPhoto',
            'block_id' => $iBlockID,
            'custom_uri' => 'boonex/blogs/blogs.php',
            'tags_link' => 'tag/',
            'categories_link' => 'posts/{nickname}/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($blogsParams['settings_name']),
            'check_for_restriction' => true,
            'restriction' => array(
                'value' => BX_DOL_PG_NOBODY,
                'column' => 'allow_view'
            ),
        );
        if(isset($blogsParams['restriction']) && !empty($blogsParams['restriction'])){
            $blogsModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'] = $blogsParams['restriction'];
        }
        if(isset($blogsParams['allow_view_checker']) && !empty($blogsParams['allow_view_checker'])){
            $blogsModuleVars['allow_view_checker'] = array(
                'obj' => 'blogs',
                'mod_main' => 'getBlogsMain',
                'privacy_method' => 'oPrivacy',
                'allow_view_checker' => 'check',
                'param' => 'searchResult',
                'private_container_params' => array(
                    'icon_image' => 'lock.png',
                ),
            );
        }
        return $blogsModuleVars;
    }
    // eof the blogs module

    // bof photos module
    // getting the photo data
    protected function getPhotoDatas($iBlockID, $photoParams, $settings=array()){
        $modVars = $this->getPhotoModuleVars($iBlockID, $photoParams, $settings);
        $photoParams['extra_params'] = (isset($photoParams['extra_params'])) ? $photoParams['extra_params'] : false;
        $commonContainer = $this->getModuleCommonContainer($modVars, $photoParams['extra_params']);
        unset($modVars);
        return $commonContainer;
    }

    // photo module variables
    protected function getPhotoModuleVars($iBlockID, $photoParams, $settings=array()){
        $photoModuleVars = array(
            'title' => 'Photos',
            'class_prefix' => 'BxPhotos' ,
            'class_suffix' => 'Search',
            'class_name' => 'BxPhotosSearch',
            'view_uri' => 'view/',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_photos',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_photo' => 'id',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($photoParams['browse_mode'])) ? $photoParams['browse_mode']: '',
                    'param1' => (isset($photoParams['param1'])) ? $photoParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($photoParams['sort_mode'])) ? $photoParams['sort_mode'] : '', 
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'ID', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'Desc',
                    'prim_photo' => 'id',
                    'author_id' => 'ownerId',
                    'entry_uri' => 'uri',
                    'tags' => 'Tags',
                    'categories' => 'Categories',
                    'entry_rate' => 'Rate',
                    'album_title' => 'Caption',
                    'album_uri' => 'Uri',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'join' => array(
                            'albums' => array(
                                'joinFields' => array(
                                    'Caption', 'Uri', 'AllowAlbumView',
                                ),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID',
                            'title',
                            'Desc',
                            'Date',
                            'Owner',
                            'Uri',
                            'Tags',
                            'Categories',
                            'Rate',
                            'RateCount',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimPhoto',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($photoParams['settings_name']),
        );
        if(isset($photoParams['restriction'])){ 
            $photoModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'] = $photoParams['restriction'];
        }
        if(isset($photoParams['extra_search_params'])){ 
            $photoModuleVars['thumbnails_params']['extra_search_params'] = $photoParams['extra_search_params']; 
        }
        if(isset($photoParams['has_block_addon_checker'])){
            $photoModuleVars['thumbnails_params']['block_addon_checker'] = array(
                'type' => 'file_album',
                'checker' => array(
                    'main_module' => 'oModule',
                    'privacy_method' => 'oAlbumPrivacy',
                ),
            );
        }
        return $photoModuleVars;
    }
    // eof the photos module

    // bof videos module
    // getting the video datas    
    protected function getVideosDatas($iBlockID, $videosParams, $settings = array()){
        $modVars = $this->getVideosModuleVars($iBlockID, $videosParams, $settings);
        $videosParams['extra_params'] = (isset($videosParams['extra_params'])) ? $videosParams['extra_params'] : false;
        $commonContainer = $this->getModuleCommonContainer($modVars, $videosParams['extra_params']);
        unset($modVars);
        return $commonContainer;
    }

    // videos module varialbles
    protected function getVideosModuleVars($iBlockID, $videosParams, $settings=array()){
        $videosModuleVars = array(
            'title' => 'Videos',
            'class_prefix' => 'BxVideos' ,
            'class_suffix' => 'Search',
            'class_name' => 'BxVideosSearch',
            'view_uri' => 'view/',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_videos',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_file_photo' => 'id',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($videosParams['browse_mode'])) ? $videosParams['browse_mode']: '',
                    'param1' => (isset($videosParams['param1'])) ? $videosParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($videosParams['sort_mode'])) ? $videosParams['sort_mode'] : '', 
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'ID', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'Description',
                    'author_id' => 'Owner',
                    'prim_file_photo' => 'id',
                    'entry_uri' => 'uri',
                    'tags' => 'Tags',
                    'categories' => 'Categories',
                    'entry_rate' => 'Rate',
                    'album_title' => 'Caption',
                    'album_uri' => 'Uri',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'join' => array(
                            'albums' => array(
                                'joinFields' => array(
                                    'Caption', 'Uri', 'AllowAlbumView',
                                ),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID',
                            'Title',
                            'Uri',
                            'Date',
                            'Description',
                            'Owner',
                            'Tags',
                            'Categories',
                            'Rate',
                            'RateCount',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimFilePhoto',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($videosParams['settings_name']),
        );
        $videosModuleVars['settings']['info_image_height'] = 160;
        $videosModuleVars['settings']['info_image_width'] = 198;
        if(isset($videosParams['restriction'])){ 
            $videosModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'] = $videosParams['restriction'];
        }
        if(isset($videosParams['extra_search_params'])){ 
            $videosModuleVars['thumbnails_params']['extra_search_params'] = $videosParams['extra_search_params']; 
        }
        return $videosModuleVars;
    }
    // eof the videos module

    // bof sounds module
    // getting the sound datas
    protected function getSoundsDatas($iBlockID, $soundsParams, $settings=array()){
        $modVars = $this->getSoundsModuleVars($iBlockID, $soundsParams, $settings);
        $soundsParams['extra_params'] = (isset($soundsParams['extra_params'])) ? $soundsParams['extra_params'] : false;
        $commonContainer = $this->getModuleCommonContainer($modVars, $soundsParams['extra_params']);
        unset($modVars);
        return $commonContainer;
    }

    // sound module variables
    protected function getSoundsModuleVars($iBlockID, $soundsParams, $settings=array()){
        $soundsModuleVars = array(
            'title' => 'Sounds',
            'class_prefix' => 'BxSounds' ,
            'class_suffix' => 'Search',
            'class_name' => 'BxSoundsSearch',
            'view_uri' => 'view/',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_sounds',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_file_photo' => 'id',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($soundsParams['browse_mode'])) ? $soundsParams['browse_mode']: '',
                    'param1' => (isset($soundsParams['param1'])) ? $soundsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                        'restriction' => array(
                            'allow_view' => array(
                                'value' => array(BX_DOL_PG_ALL,BX_DOL_PG_MEMBERS),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($soundsParams['sort_mode'])) ? $soundsParams['sort_mode'] : '', 
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'ID', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'Description',
                    'author_id' => 'Owner',
                    'prim_file_photo' => 'id',
                    'entry_uri' => 'uri',
                    'tags' => 'Tags',
                    'categories' => 'Categories',
                    'entry_rate' => 'Rate',
                    'album_title' => 'Caption',
                    'album_uri' => 'Uri',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'join' => array(
                            'albums' => array(
                                'joinFields' => array(
                                    'Caption', 'Uri', 'AllowAlbumView',
                                ),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID',
                            'Title',
                            'Uri',
                            'Date',
                            'Description',
                            'Owner',
                            'Tags',
                            'Categories',
                            'Rate',
                            'RateCount',
                        ),
                    ),
                ),
            ),
            'prim_key' => 'ID',
            'prim_photo_function' => 'getPrimFilePhoto',
            'prim_photo_default_name' => 'default.png',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($soundsParams['settings_name']),
        );
        $soundsModuleVars['settings']['info_image_height'] = 180;
        $soundsModuleVars['settings']['info_image_width'] = 218;
        if(isset($soundsParams['restriction'])){ 
            $soundsModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'] = $soundsParams['restriction'];
        }
        if(isset($soundsParams['extra_search_params'])){ 
            $soundsModuleVars['thumbnails_params']['extra_search_params'] = $soundsParams['extra_search_params']; 
        }
        return $soundsModuleVars;
    }
    // eof the sounds module

    // bof files module
    // getting the files datas
    protected function getFilesDatas($iBlockID, $filesParams, $settings=array()){
        $modVars = $this->getFilesModuleVars($iBlockID, $filesParams, $settings);
        $filesParams['extra_params'] = (isset($filesParams['extra_params'])) ? $filesParams['extra_params'] : false;
        $commonContainer = $this->getModuleCommonContainer($modVars, $filesParams['extra_params']);
        unset($modVars);
        return $commonContainer;
    }

    // files module variables
    protected function getFilesModuleVars($iBlockID, $filesParams, $settings){
        $filesModuleVars = array(
            'title' => 'Files',
            'class_prefix' => 'BxFiles' ,
            'class_suffix' => 'Search',
            'class_name' => 'BxFilesSearch',
            'view_uri' => 'view/',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_files',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_file_photo_two' => 'id',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($filesParams['browse_mode'])) ? $filesParams['browse_mode']: '',
                    'param1' => (isset($filesParams['param1'])) ? $filesParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => 7, 
                        ),
                        'restriction' => array(
                            'allow_view' => array(
                                'value' => array(BX_DOL_PG_ALL,BX_DOL_PG_MEMBERS),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($filesParams['sort_mode'])) ? $filesParams['sort_mode'] : '', 
                    ),
                ),
            ),
            'information_params' => array(
                'prim_key' => 'ID', 
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'description' => 'desc',
                    'author_id' => 'Owner',
                    'prim_file_photo_two' => 'id',
                    'entry_uri' => 'uri',
                    'tags' => 'Tags',
                    'categories' => 'Categories',
                    'entry_rate' => 'Rate',
                    'album_title' => 'Caption',
                    'album_uri' => 'Uri',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'join' => array(
                            'albums' => array(
                                'joinFields' => array(
                                    'Caption', 'Uri', 'AllowAlbumView',
                                ),
                            ),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID',
                            'Title',
                            'Uri',
                            'Desc',
                            'Date',
                            'Owner',
                            'Tags',
                            'Categories',
                            'Rate',
                            'RateCount',
                        ),
                    ),
                ),
            ),
            'prim_key' => 'ID',
            'prim_photo_function' => 'getPrimFilePhotoTwo',
            'prim_photo_defaults' => 'templates/base/images/icons/006.png',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($filesParams['settings_name']),
        );
        $filesModuleVars['settings']['info_image_height'] = 180;
        $filesModuleVars['settings']['info_image_width'] = 218;
        if(isset($filesParams['restriction'])){ 
            $filesModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'] = $filesParams['restriction'];
        }
        if(isset($filesParams['extra_search_params'])){ 
            $filesModuleVars['thumbnails_params']['extra_search_params'] = $filesParams['extra_search_params']; 
        }
        return $filesModuleVars;
    }
    // eof the files module
    // EOF the module getters
    // EOF THE GETTERS

    // ADDERS
    // BOF the templatate adders
    // add the css
    protected function addAllCss(){
        if(!isset($GLOBALS['ebBonConSpecialInfoCss'])){
            $this->oTemplate->addCss('main.css');
            $GLOBALS['ebBonConSpecialInfoCss'] = true;
        }
    }

    // add the javascript
    protected function addAllJs(){
        if(!isset($GLOBALS['ebBonConSpecialInfoJs'])){
            $this->oTemplate->addJs('EBBonConSpecialInfo.js');
            $this->oTemplate->addJs('json2.js');
            $GLOBALS['ebBonConSpecialInfoJs'] = true;
        }
    }
    // EOF the templatate adders
    // EOF THE ADDERS 
    
    // CHECKERS
    // checks for an ajax request
    protected function isAjaxRequest(){
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
            return true;
        }else{
            return false;
        }
    }
    // EOF THE CHECKERS

    // STRING MANIPULATIONS
    // getting the substring of the word
    protected function subWords($words, $limit=40){
        if(strlen($words) > $limit){
            return substr($words, 0, $limit) . ' ...'; 
        }else{
            return $words;
        } 
    }
    // EOF THE STRING MANIPULATIONS
}

class EmmetBytesBonConSpecialInfoD707UpHelper extends EmmetBytesBonConSpecialInfoDefaultHelper{

    // constructor
    public function EmmetBytesBonConSpecialInfoD707UpHelper($oMain){
        parent::EmmetBytesBonConInRealtimeDefaultHelper($oMain);
    }

    // homepage blogs block helper
    function homepageBlogsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_blogs_block_settings');
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'], 
            'restriction' => array(
                'allow_view' => array(
                    'value' => BX_DOL_PG_ALL,
                ),
            ),
            'allow_view_checker' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){
            return false;
        }
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    // override blogs module vars
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = parent::getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $blogsModuleVars['system'] = 'bx_blogs';
        $blogsModuleVars['check_for_restriction'] = false;
        return $blogsModuleVars;
    }

}

class EmmetBytesBonConSpecialInfoD710UpHelper extends EmmetBytesBonConSpecialInfoDefaultHelper{

    // constructor
    public function EmmetBytesBonConSpecialInfoD707UpHelper($oMain){
        parent::EmmetBytesBonConInRealtimeDefaultHelper($oMain);
    }

    // homepage files block helper
    function homepageFilesBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_files_block_settings');
        $filesParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // homepage blogs block helper
    function homepageBlogsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_blogs_block_settings');
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'], 
            'restriction' => array(
                'allow_view' => array(
                    'value' => BX_DOL_PG_ALL,
                ),
            ),
            'allow_view_checker' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){
            return false;
        }
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    // override gets the latest blog post
    function latestBlogPost($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_latest_blogs_block_settings');
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(isset($_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_special_info_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    // getting the thumbnail search results
    protected function getThumnailSearchResults($aModule, $getMoreEntries = false){
        $modVars = $aModule['mod_vars'];
        $thumbnailsParams = $modVars['thumbnails_params'];
        $searchParams = $thumbnailsParams['search_params'];
        list($o, $className) = $this->getSearchResultObject($aModule);
        if(isset($thumbnailsParams['block_addon_checker']) && sizeof($thumbnailsParams['block_addon_checker']) > 0){
            $blockAddonChecker = $thumbnailsParams['block_addon_checker'];
            if($blockAddonChecker['type'] == 'file_album'){
                $albumUri = $thumbnailsParams['a_current']['inner_replace']['restriction']['album']['value'];
                $ownerId = $thumbnailsParams['a_current']['inner_replace']['restriction']['owner']['value'];
                // create the album object
                $oAlbums = new BxDolAlbums($modVars['system']);
                // get the album info
                $albumInfo = $oAlbums->getAlbumInfo(array('fileUri' => $albumUri, 'owner' => $ownerId), array('ID')); 
                $albumId = $albumInfo['ID'];
                if(isset($blockAddonChecker['checker']) && sizeof($blockAddonChecker['checker']) > 0){
                    $blockAddonCheckerParams = $blockAddonChecker['checker'];
                    // getting the main module
                    $mainMod = $o->$blockAddonCheckerParams['main_module'];
                    $modPrivacy = $mainMod->$blockAddonCheckerParams['privacy_method'];
                    if(!$modPrivacy->check('album_view', $albumId, getLoggedId())){
                        $allowSearch = false;
                        return array(
                            'content' => $this->getPrivateContainerTwo(),
                        );
                    }
                }
            }
        }
        // added the browse modes
        $browsePage = (isset($searchParams['browse_mode'])) ? $searchParams['browse_mode'] : false; 
        if($browsePage){
            $param1 = $searchParams['param1'];
            $o->$className($browsePage, $param1);
        }
        // change the current aCurrent values
        $replaceValues = $thumbnailsParams['a_current']; 
        foreach($replaceValues as $key=>$aCurrentValues){
            switch($key){
                case 'inner_replace':
                    foreach($aCurrentValues as $key1=>$aCurrentValue){
                        $o->aCurrent[$key1] = array_replace_recursive($o->aCurrent[$key1],$aCurrentValue);
                    }
                    break;
                case 'replace_all':
                    foreach($aCurrentValues as $key1=>$aCurrentValue){
                        $o->aCurrent[$key1] = $aCurrentValue;
                    }
                    break;
            }
        }
        if(isset($modVars['thumbnails_params']['extra_search_params']) && !empty($modVars['thumbnails_params']['extra_search_params'])){
            // join
            if(isset($modVars['thumbnails_params']['extra_search_params']['joins'])){
                $joinDatas = $modVars['thumbnails_params']['extra_search_params']['joins'];
                foreach($joinDatas as $key=>$joinData){
                    if(isset($joinData['variable'])){
                        $var = $o->$joinData['variable']['content'];
                        $o->aCurrent['join'][$key] = $var[$joinData['variable']['content_key']];
                    }
                }
            }
            // restrictions
            if(isset($modVars['thumbnails_params']['extra_search_params']['restrictions'])){
                $restrictionDatas = $modVars['thumbnails_params']['extra_search_params']['restrictions'];
                foreach($restrictionDatas as $key=>$restrictionData){
                    $varName = $restrictionData['content']['variable_name'];
                    if(is_array($restrictionData['content']['field'])){
                        $keys = $restrictionData['content']['field']['keys'];
                        $restrictionData['content']['field'] = $o->$varName;
                        foreach($keys as $key){
                            $restrictionData['content']['field'] = $restrictionData['content']['field'][$key];
                        }
                    }
                    if(is_array($restrictionData['content']['table'])){
                        $keys = $restrictionData['content']['table']['keys'];
                        $restrictionData['content']['table'] = $o->$varName;
                        foreach($keys as $key){
                            $restrictionData['content']['table'] = $restrictionData['content']['table'][$key];
                        }
                    }
                    unset($restrictionData['content']['variable_name']);
                    $o->aCurrent['restriction'][$key] = $restrictionData['content'];
                }
            }
        }
        if(isset($modVars['is_public']) && !empty($modVars['is_public'])){ $o->setPublicUnitsOnly($modVars['is_public']); }
        if($getMoreEntries){
            $totalNum = $o->getCount();
            $o->aCurrent['paginate']['forcePage'] = $aModule['page'];
            $o->aCurrent['paginate']['perPage'] = $totalNum - $aModule['total_entries'] + $modVars['thumbnails_params']['a_current']['inner_replace']['paginate']['perPage'];
        }
        $searchDatas = $o->getSearchData();
        if(sizeof($searchDatas) <= 0){ return false; }
        return array(
            'search_results' => $searchDatas, 
            'total_entries' => $o->aCurrent['paginate']['totalNum'],
            'search_obj' => $o,
        );
    }

    // override gets the private container part two
    protected function getPrivateContainerTwo(){
        return MsgBox(_t('_sys_album_private'));
    }

    // override the event module vars
    protected function getEventModuleVars($iBlockID, $eventParams, $settings = array()){
        $eventModuleVars = parent::getEventModuleVars($iBlockID, $eventParams, $settings);
        $eventModuleVars['empty_image_icon'] = 'no-image-thumb-events.png';
        if(isset($eventParams['allow_view_checker']) && !empty($eventParams['allow_view_checker'])){
            $eventModuleVars['allow_view_checker']['private_container_params'] = array(
                'icon_image' => 'no-image-thumb-events.png',
            );
        }
        return $eventModuleVars;
    }

    // override the groups module vars
    protected function getGroupsModuleVars($iBlockID, $groupsParams, $settings=array()){
        $groupsModuleVars = parent::getGroupsModuleVars($iBlockID, $groupsParams, $settings);
        $groupsModuleVars['empty_image_icon'] = 'no-image-thumb-groups.png';
        $groupsModuleVars['allow_view_checker']['private_container_params'] = array(
            'icon_image' => 'no-image-thumb-groups.png',
        );
        return $groupsModuleVars;
    }

    // override the sites module vars
    protected function getSitesModuleVars($iBlockID, $siteParams, $settings){
        $sitesModuleVars = parent::getSitesModuleVars($iBlockID, $siteParams, $settings);
        $sitesModuleVars['empty_image_icon'] = 'no-image-thumb-sites.png';
        return $sitesModuleVars;
    }

    // override the get blogs module variables
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = parent::getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $blogsModuleVars['system'] = 'bx_blogs';
        $blogsModuleVars['check_for_restriction'] = false;
        $blogsModuleVars['empty_image_icon'] = 'no-image-thumb-blogs.png';
        $blogsModuleVars['allow_view_checker']['private_container_params'] = array(
            'icon_image' => 'lock.png',
        );
        return $blogsModuleVars;
    }

}

if (!function_exists('array_replace_recursive')){
    function array_replace_recursive($array, $array1){
        $args = func_get_args();
        $array = $args[0];
        if (!is_array($array)){
            return $array;
        }
        for ($i = 1; $i < count($args); $i++){
            if (is_array($args[$i])){
                $array = recurse($array, $args[$i]);
            }
        }
        return $array;
    }
    function recurse($array, $array1){
        foreach ($array1 as $key => $value){
            if (!isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key]))){
                $array[$key] = array();
            }
            if (is_array($value)){
                $value = recurse($array[$key], $value);
            }
            $array[$key] = $value;
        }
        return $array;
    }
}
?>
