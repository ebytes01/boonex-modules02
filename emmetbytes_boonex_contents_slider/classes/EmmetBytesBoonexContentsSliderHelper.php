<?php

class EmmetBytesBoonexContentsSliderHelper{
    var $boonexVersion;
    var $helperObj;
    
    // CONSTRUCTOR
    function EmmetBytesBoonexContentsSliderHelper($oMain){
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        if($this->boonexVersion >= '7.1.0'){
            $this->helperObj = new EmmetBytesBoonexContentsSliderD710UpHelper($oMain);
        }elseif($this->boonexVersion >= '7.0.7' && $this->boonexVersion < '7.1.0'){
            $this->helperObj = new EmmetBytesBoonexContentsSliderD707UpHelper($oMain);
        }else{
            $this->helperObj = new EmmetBytesBoonexContentsSliderDefaultHelper($oMain);
        }
    }

}

// DEFAULT HELPER
class EmmetBytesBoonexContentsSliderDefaultHelper{
    // ATTRIBUTES
    var $oMain, $oDb, $oTemplate, $oConfig, $boonexVersion; 
    
    // CONSTRUCTOR
    function EmmetBytesBoonexContentsSliderDefaultHelper($oMain){
        $this->profileId = $oMain->_iProfileId;
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->oTemplate = $GLOBALS['ebBoonexContentsSliderSysTemplate'] = (isset($GLOBALS['ebBoonexContentsSliderSysTemplate'])) ? $GLOBALS['ebBoonexContentsSliderSysTemplate'] : $oMain->_oTemplate;
        $this->oConfig = $GLOBALS['ebBoonexContentsSliderSysConfig'] = (isset($GLOBALS['ebBoonexContentsSliderSysConfig'])) ? $GLOBALS['ebBoonexContentsSliderSysConfig'] : $oMain->_oConfig;
        $this->addAllCss();
        $this->addAllJs();
    }
    
    // ACTION RESPONSE
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
        $filePath =BX_DIRECTORY_PATH_MODULES . 'EmmetBytes/emmetbytes_boonex_contents_slider/files/images/' . md5(basename($sponsorImage) . $width . $height) . '.jpg';
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
    // EOF THE ACTION RESPONSE

    // HOMEPAGE SERVICE BLOCKS HELPERS
    // homepage events block helper
    function homepageEventsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_event_block_settings');
        $eventParams = array(
            'browse_mode' => $settings['default_tab'],
            'is_public' => true,
        );
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter'])){
            switch ($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter']){
                case 'featured':
                case 'recent':
                case 'top':
                case 'popular':
                case 'upcoming':
                    $eventParams['browse_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter'];
                    break;
            }
        }
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        $o = $eventDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $eventDatas['module_base_uri'] . $o->sBrowseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$eventParams['browse_mode']);
        return array(
            $eventDatas['common_container'],
            array(
                _t('_bx_events_tab_upcoming') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=upcoming', 'active' => 'upcoming' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_featured') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=featured', 'active' => 'featured' == $eventParams['browse_mode'], 'dynamic' => true),                
                _t('_bx_events_tab_recent') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=recent', 'active' => 'recent' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=top', 'active' => 'top' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=popular', 'active' => 'popular' == $eventParams['browse_mode'], 'dynamic' => true),                
            ),
            $sAjaxPaginate,
        );
    }

    // homepage groups block helper
    function homepageGroupsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_group_block_settings');
        $groupsParams = array(
            'browse_mode' => $settings['default_tab'],
            'is_public' => true,
        );
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter'])){
            switch ($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter']) {
                case 'featured':
                case 'recent':
                case 'top':
                case 'popular':
                    $groupsParams['browse_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter'];
                    break;
            }
        }
        $groupsDatas = $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
        if(!$groupsDatas){ return false; }
        $o = $groupsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $groupsDatas['module_base_uri'] . $o->sBrowseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$groupsParams['browse_mode']);
        return array(
            $groupsDatas['common_container'],
            array(
                _t('_bx_groups_tab_featured') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=featured', 'active' => 'featured' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_recent') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=recent', 'active' => 'recent' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=top', 'active' => 'top' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter=popular', 'active' => 'popular' == $groupsParams['browse_mode'], 'dynamic' => true),
            ),
            $sAjaxPaginate,
        );
    }

    // homepage sites block helper
    function homepageSitesBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_site_block_settings');
        $siteParams = array( 'browse_mode' => 'index',);
        $siteDatas = $this->getSiteDatas($iBlockID,$siteParams,$settings);
        $o = $siteDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $siteDatas['module_base_uri'] . $o->sSitesBrowseAll, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$siteParams['browse_mode']);
        return array(
            $siteDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
    }

    // homepage blogs block helper
    function homepageBlogsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_blogs_block_settings');
        $blogsParams = $this->getHomepageBlogsParams($settings);
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        $o = $blogsDatas['search_obj'];
        $browseUrl = 'all_posts';
        switch($blogsParams['sort_mode']){
            case 'last':
                $browseUrl = 'all_posts';
                break;
            case 'top':
                $browseUrl = 'top_posts';
                break;
        }
        $sAjaxPaginate = $this->getPaginationData($o, $blogsDatas['module_base_uri'] . $browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='. $blogsParams['sort_mode']);
        return array(
            $blogsDatas['common_container'],
            array(
            ),
            $sAjaxPaginate,
        );
    }

    // getting the homepage blogs params
    protected function getHomepageBlogsParams($settings){
        return array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'], 
            'restriction' => array(
                'allow_view' => array(
                    'value' => '',
                ),
            ),
            'allow_view_checker' => true,
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $photoParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        $browseUrl = 'browse/all';
        switch($photoParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $photosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $photosDatas['module_base_uri'] . $browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$photoParams['sort_mode']);
        return array(
            $photosDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $photoParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $photoParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        $o = $videosDatas['search_obj'];
        switch($videosParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $sAjaxPaginate = $this->getPaginationData($o, $videosDatas['module_base_uri'] . $browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$videosParams['sort_mode']);
        return array(
            $videosDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $videosParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $videosParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        switch($soundsParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $soundsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $soundsDatas['module_base_uri'] . $browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$soundsParams['sort_mode']);
        return array(
            $soundsDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $soundsParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $soundsParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
        );
    }

     //y_date_ende homepage files block helper
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        switch($filesParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'popular':
                $browseUrl = 'browse/popular';
                break;
        }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] .$browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$filesParams['sort_mode']);
        return array(
            $filesDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=popular', 'active' => 'popular' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate
        );
    }

    // homepage ads block helper
    function homepageAdsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_ads_block_settings');
        $adsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'last',
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $adsParams['restriction']['allow_view']['value'] = array(BX_DOL_PG_ALL, BX_DOL_PG_MEMBERS);
        }
        $adsDatas = $this->getAdsDatas($iBlockID, $adsParams, $settings);
        if(!$adsDatas){ return false; }
        $o = $adsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $adsDatas['module_base_uri'] . $browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$adsParams['sort_mode']);
        return array(
            $adsDatas['common_container'],
            array(),
            $sAjaxPaginate
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
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        $o = $eventDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $eventDatas['module_base_uri'] . $o->sBrowseUrl, getProfileLink($profileID));
        return array(
            $eventDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        $o = $eventDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $eventDatas['module_base_uri'] . $o->sBrowseUrl, getProfileLink($profileID));
        return array(
            $eventDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        $groupsDatas = $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
        if(!$groupsDatas){ return false; }
        $o = $groupsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $groupsDatas['module_base_uri'] . $o->sBrowseUrl, getProfileLink($profileID));
        return array(
            $groupsDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        $groupsDatas = $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
        if(!$groupsDatas){ return false; }
        $o = $groupsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $groupsDatas['module_base_uri'] . $o->sBrowseUrl, getProfileLink($profileID));
        return array(
            $groupsDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        $sitesDatas = $this->getSiteDatas($iBlockID, $groupsParams, $settings);
        if(!$sitesDatas){ return false; }
        $o = $sitesDatas['search_obj'];
        $browseUrl = 'browse/user/' . $aProfile['NickName'];
        $sAjaxPaginate = $this->getPaginationData($o, $sitesDatas['module_base_uri'] . $browseUrl, getProfileLink($profileID));
        return array(
            $sitesDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
    }
    // eof the sites

    // bof blogs
    // profile own blogs
    function profileOwnBlogsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_blogs_block_settings');
        $profileInfo = getProfileInfo($profileID);
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'last',
            'restriction' => array('owner' => array('value' => $profileID)),
            'allow_view_checker' => true,
        );
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        $o = $blogsDatas['search_obj'];
        $browseUrl = 'posts/' . $profileInfo['NickName'];
        $sAjaxPaginate = $this->getPaginationData($o, $blogsDatas['module_base_uri'] . $browseUrl, getProfileLink($profileID));
        return array(
            $blogsDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $photosParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $photosDatas = $this->getPhotoDatas($iBlockID, $photosParams, $settings);
        if(!$photosDatas){ return false; }
        $albumUri = $photosDatas['album_uri'];
        $o = $photosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $photosDatas['module_base_uri'] . $albumUri, getProfileLink($profileID));
        return array(
            $photosDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
    }
    // eof the photos

    // bof videos
    // profile own videos
    function profileOwnVideosBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_videos_block_settings');
        $videosParams = array(
            'browse_mode' => '',
            'sort_mode' => 'album_order',
            'extra_params' => array(
                'restriction' => array(
                    'album' => '',
                ),
            ),
        );
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        $albumUri = $videosDatas['album_uri'];
        $o = $videosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $videosDatas['module_base_uri'] . $albumUri, getProfileLink($profileID));
        return array(
            $videosDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        $albumUri = $soundsDatas['album_uri'];
        $o = $soundsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $soundsDatas['module_base_uri'] . $albumUri, getProfileLink($profileID));
        return array(
            $soundsDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
    }

    // ads
    function profileOwnAdsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_ads_block_settings');
        $adsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'all',
            'restriction' => array(
                'owner' => array('value' => $profileID),
            ),
        );
        $adsDatas = $this->getAdsDatas($iBlockID, $adsParams, $settings);
        if(!$adsDatas){ return false; }
        $o = $adsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $adsDatas['module_base_uri'] . $o->sBrowseUrl, getProfileLink($profileID));
        return array(
            $adsDatas['common_container'],
            '',
            $sAjaxPaginate
        );
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
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        $o = $eventDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $eventDatas['module_base_uri'] . $o->sBrowseUrl, $eventDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$eventParams['browse_mode']);
        if (!$aMenu){
            $rssLink = $eventDatas['module_base_uri'] . 'browse/' . $eventParams['browse_mode'] . '?rss=1';
            $aMenu = array(_t('_RSS') => array('href' => $rssLink, 'icon' => 'rss'));
        }
        return array(
            $eventDatas['common_container'],
            $aMenu,
            $sAjaxPaginate,
        );
    }

    // past events
    function mainEventsPast($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_past_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'past',
            'is_public' => true,
        );
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        $o = $eventDatas['search_obj'];
        if (!$aMenu){
            $rssLink = $eventDatas['module_base_uri'] . 'browse/' . $eventParams['browse_mode'] . '?rss=1';
            $aMenu = array(_t('_RSS') => array('href' => $rssLink, 'icon' => 'rss'));
        }
        $sAjaxPaginate = $this->getPaginationData($o, $eventDatas['module_base_uri'] . $o->sBrowseUrl, $eventDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$eventParams['browse_mode']);
        return array(
            $eventDatas['common_container'],
            $aMenu,
            $sAjaxPaginate,
        );
    }

    // recent
    function mainEventsRecent($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'recent',
            'is_public' => true,
        );
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        $o = $eventDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $eventDatas['module_base_uri'] . $o->sBrowseUrl, $eventDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$eventParams['browse_mode']);
        if (!$aMenu){
            $rssLink = $eventDatas['module_base_uri'] . 'browse/' . $eventParams['browse_mode'] . '?rss=1';
            $aMenu = array(_t('_RSS') => array('href' => $rssLink, 'icon' => 'rss'));
        }
        return array(
            $eventDatas['common_container'],
            $aMenu,
            $sAjaxPaginate,
        );
    }
    // EOF the main events page
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
        $groupsDatas = $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
        if(!$groupsDatas){ return false; }
        $o = $groupsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $groupsDatas['module_base_uri'] . $o->sBrowseUrl, $groupsDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$groupsParams['browse_mode']);
        if (!$aMenu){
            $rssLink = $groupsDatas['module_base_uri'] . 'browse/' . $groupsParams['browse_mode'] . '?rss=1';
            $aMenu = array(_t('_RSS') => array('href' => $rssLink, 'icon' => 'rss'));
        }
        return array(
            $groupsDatas['common_container'],
            $aMenu,
            $sAjaxPaginate,
        );
    }
    // EOF the main groups page
    // EOF THE GROUPS SERVICE BLOCKS

    // BOF SITES SERVICE BLOCKS
    // bof main page sites
    // main featured sites
    function mainFeaturedSites($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_site_block_settings');
        $siteParams = array( 'browse_mode' => 'featuredshort',);
        $siteDatas = $this->getSiteDatas($iBlockID, $siteParams, $settings);
        if(!$siteDatas){ return false; }
        $o = $siteDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $siteDatas['module_base_uri'] . 'browse/featured', $siteDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$siteParams['browse_mode']);
        return array(
            $siteDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
    }

    // main recent sites
    function mainRecentSites($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_site_block_settings');
        $siteParams = array( 'browse_mode' => 'all',);
        $siteDatas = $this->getSiteDatas($iBlockID, $siteParams, $settings);
        if(!$siteDatas){ return false; }
        $o = $siteDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $siteDatas['module_base_uri'] . 'browse/all', $siteDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$siteParams['browse_mode']);
        if (!$aMenu){
            $rssLink = $siteDatas['module_base_uri'] . 'browse/' . $siteParams['browse_mode'] . '?rss=1';
            $aMenu = array(_t('_RSS') => array('href' => $rssLink, 'icon' => 'rss'));
        }
        return array(
            $siteDatas['common_container'],
            $aMenu,
            $sAjaxPaginate,
        );
    }
    // eof the main page sites
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        $o = $blogsDatas['search_obj'];
        $browseUrl = 'all_posts/';
        switch($blogsParams['sort_mode']){
            case 'last':
                $browseUrl = 'all_posts/';
                break;
            case 'top':
                $browseUrl = 'top_posts/';
                break;
        }
        $sAjaxPaginate = $this->getPaginationData($o, $blogsDatas['module_base_uri'] . $browseUrl, $blogsDatas['module_base_uri'] . 'home', 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='. $blogsParams['sort_mode']);
        return array(
            $blogsDatas['common_container'],
            array(
            ),
            $sAjaxPaginate,
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $photoParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        $browseUrl = 'browse/all';
        switch($photoParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $photosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $photosDatas['module_base_uri'] . $browseUrl, $photosDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$photoParams['sort_mode']);
        return array(
            $photosDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/photos/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $photoParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/photos/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $photoParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
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
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        $o = $photosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $photosDatas['module_base_uri'] . 'browse/favorited/', $photosDatas['module_base_uri']);
        return array(
            $photosDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        $o = $photosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $photosDatas['module_base_uri'] . 'browse/featured/', $photosDatas['module_base_uri']);
        return array(
            $photosDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        switch($videosParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $videosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $videosDatas['module_base_uri'] . $browseUrl, $videosDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$videosParams['sort_mode']);
        return array(
            $videosDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/videos/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $videosParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/videos/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $videosParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
        );
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
        $videosDatas = $this->getVideosDatas($iBlockID, $videoParams, $settings);
        if(!$videosDatas){ return false; }
        $o = $videosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $videosDatas['module_base_uri'] . 'browse/favorited/', $videosDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$videosParams['sort_mode']);
        return array(
            $videosDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        $videosDatas = $this->getVideosDatas($iBlockID, $vidoesParams, $settings);
        if(!$videosDatas){ return false; }
        $o = $videosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $videosDatas['module_base_uri'] . 'browse/featured/', $videosDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$videosParams['sort_mode']);
        return array(
            $videosDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        $browseUrl = 'browse/all';
        switch($soundsParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $soundsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $soundsDatas['module_base_uri'] . $browseUrl, $soundsDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$soundsParams['sort_mode']);
        return array(
            $soundsDatas['common_container'], 
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/sounds/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $soundsParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/sounds/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $soundsParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
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
        $soundsDatas = $this->getSoundsDatas($iBlockID, $videoParams, $settings);
        if(!$soundsDatas){ return false; }
        $o = $soundsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $soundsDatas['module_base_uri'] . 'browse/favorited', $soundsDatas['module_base_uri']);
        return array(
            $soundsDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        $o = $soundsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $soundsDatas['module_base_uri'] . 'browse/featured', $soundsDatas['module_base_uri']);
        return array(
            $soundsDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        switch($filesParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/popular';
                break;
        }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] . $browseUrl, $filesDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$filesParams['sort_mode']);
        return array(
            $filesDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Popular') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=popular', 'active' => 'popular' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
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
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] . 'browse/top', $filesDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$filesParams['sort_mode']);
        return array(
            $filesDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
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
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] . 'browse/favorited', $filesDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$filesParams['sort_mode']);
        return array(
            $filesDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] . 'browse/featured', $filesDatas['module_base_uri']);
        return array(
            $filesDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
    }
    // eof the files main page service blocks
    // EOF THE FILES SERVICE BLOCKS

    // BOF THE ADS SERVICE BLOCKS
    // for the main last ads
    function mainLastAds($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_last_ads_block_settings');
        $adsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'last',
            'restriction' => array(
                'allow_view' => array(
                    'value' => array(BX_DOL_PG_ALL),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $adsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        $adsDatas = $this->getAdsDatas($iBlockID, $adsParams, $settings);
        if(!$adsDatas){ return false; }
        $o = $adsDatas['search_obj'];
        $browseUrl = 'all_ads';
        $sAjaxPaginate = $this->getPaginationData($o, $adsDatas['module_base_uri'] . $browseUrl, $adsDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last');
        return array(
            $adsDatas['common_container'],
            '',
            $sAjaxPaginate
        );
    }
    // EOF THE ADS SERVICE BLOCKS
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
            $aModule = db_assoc_arr ('SELECT `id`, `title`, `vendor`, `version`, `update_url`, `path`, `uri`, `class_prefix` FROM `sys_modules` WHERE `class_prefix` = "'.$modVars['class_prefix'].'" LIMIT 1');
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

    // parse the subcategories
    protected function parseSubCategories($commonData){
        $subcategories = '';
        if(isset($commonData['sub_categories'])){
            $subcategories = $this->oTemplate->_parseAnything ($commonData['sub_categories'], ',', $commonData['module_base_uri'] . $commonData['sub_categories_suffix']);
        }
        return (empty($subcategories)) ? '' : '- ' . $subcategories;
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

    // getting the size to date conversion
    protected function getSizeToDateConversion($iTime){
        $iTime = (int)round($iTime/1000);
    	if ($iTime < 60) {
    		$aLength[1] = '0';
    		$aLength[0] = $iTime;
    	}
    	elseif ($iTime < 3600) {
    		$aLength[1] = (int)($iTime/60);
    		$aLength[0] = $iTime%60;
    	}
        else {
            $aLength[2] = (int)($iTime/3600);
            $iOther = $iTime - $aLength[2]*3600;
            $aLength[1] = (int)($iOther/60);
            $aLength[0] = $iOther%60;
        }
    	$sCode = '';
    	for ($i = count($aLength)-1; $i >= 0; $i--) {
    		$sCode .= strlen($aLength[$i]) < 2 ? '0' . $aLength[$i] : $aLength[$i];
    		$sCode .= ':';
    	}
    	return	trim($sCode, ':');
    }

    // getting the pagination data
    protected function getPaginationData($searchObj, $browseUrl, $urlStart, $filter=''){
        $filter = (empty($filter) ? '?1=1' : '?'.$filter);
        $searchObjPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'count' => $searchObj->aCurrent['paginate']['totalNum'],
            'per_page' => $searchObj->aCurrent['paginate']['perPage'],
            'page' => $searchObj->aCurrent['paginate']['page'],
            'on_change_page' => 'return !loadDynamicBlock({id}, \'' . $urlStart . $filter . '&page={page}&per_page={per_page}\');',
        ));
        return $searchObjPaginate->getSimplePaginate($browseUrl);
    }
    // EOF the system methods

    // BOF the data getters
    // getting the slider search results
    protected function getSliderSearchResults($aModule, $getMoreEntries = false){
        $modVars = $aModule['mod_vars'];
        $thumbnailsParams = $modVars['slider_params'];
        $searchParams = $thumbnailsParams['search_params'];
        list($o, $className) = $this->getSearchResultObject($aModule);
        if(isset($modVars['slider_params']['clear_filters']) && $modVars['slider_params']['clear_filters']){
            $aJoins = array('albumsObjects', 'albums');
            $o->clearFilters(array('activeStatus', 'albumType', 'album_status', 'ownerStatus'), $aJoins);
            $o->addCustomParts();
        }
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
                        return $this->getAlbumView();
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
        if(isset($modVars['slider_params']['extra_search_params']) && !empty($modVars['slider_params']['extra_search_params'])){
            // join
            if(isset($modVars['slider_params']['extra_search_params']['joins'])){
                $joinDatas = $modVars['slider_params']['extra_search_params']['joins'];
                foreach($joinDatas as $key=>$joinData){
                    if(isset($joinData['variable'])){
                        $var = $o->$joinData['variable']['content'];
                        $o->aCurrent['join'][$key] = $var[$joinData['variable']['content_key']];
                    }
                }
            }
            // restrictions
            if(isset($modVars['slider_params']['extra_search_params']['restrictions'])){
                $restrictionDatas = $modVars['slider_params']['extra_search_params']['restrictions'];
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
        $searchDatas = $o->getSearchData();
        if(sizeof($searchDatas) <= 0){ return false; }
        return array(
            'search_results' => $searchDatas, 
            'total_entries' => $o->aCurrent['paginate']['totalNum'],
            'search_obj' => $o,
        );
    }

    // getting the album view
    protected function getAlbumView(){
        return array();
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

    // getting the slider common data
    protected function getSliderCommonData($searchObj, $resultData, $modVars, $sysModules, $sliderColumnsMap, $settings){
        $searchResult = $resultData;
        $modVars['voting_system'] = (isset($modVars['voting_system'])) ? $modVars['voting_system'] : $modVars['system'];
        $resultData = $commonData = $this->getRemappedColumns($resultData, $sliderColumnsMap, $sysModules);
        // getting the files data
        if(isset($searchObj->aConstants) && isset($searchObj->aConstants) && !empty($searchObj->aConstants['filesDir'])){
            $filesDir = $searchObj->aConstants['filesDir'];
            $filesPostfix = (isset($searchObj->aConstants['picPostfix'])) ? ((array_key_exists('browse', (array)$searchObj->aConstants['picPostfix'])) ? $searchObj->aConstants['picPostfix']['browse'] : $searchObj->aConstants['picPostfix']) : '';
        }
        // getting the thumbnails photo
        if(isset($resultData['thumb_photo']) && !empty($resultData['thumb_photo'])){
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_photo'], 'browse');
        }elseif(isset($modVars['system']) && $modVars['system'] == 'bx_ads'){
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_ads_photo'], $searchObj);
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
        if(isset($commonData['description']) && !empty($commonData['description'])){
            $description = strip_tags($commonData['description']);
            if(strlen($description) > $settings['maximum_description_characters']){
                $description = process_text_withlinks_output(mb_substr($description, 0, (int)$settings['maximum_description_characters'])) . '...';
            }
        }
        if(isset($modVars['custom_category']) && sizeof($modVars['custom_category'])){
            $categories = $this->$modVars['custom_category']['method']($searchObj, $commonData);
        }else{
            $categories = $this->parseCategories($commonData);
        }
        $commonData = array(
            'title' => (strlen($commonData['title']) > $settings['maximum_title_characters']) ? substr($commonData['title'], 0, $settings['maximum_title_characters']) . '...' : $commonData['title'],
            'entry_uri' => $commonData['module_uri'],
            'slider_photo' => $primPhoto['file'],
            'image_width' => $primPhoto['width'],
            'image_height' => $primPhoto['height'],
            'spacer' => getTemplateIcon('spacer.gif'),
            // display the comments count
            'bx_if:display_comments_count' => array(
                'condition' => ($settings['display_comments_count']) ? true : false,
                'content' => array(
                    'comments_count' => ($settings['display_comments_count']) ? $commonData['comments_count'] . ' ' . _t('_comments') : '' 
                ),
            ),
            // display the start date
            'bx_if:display_date_start' => array(
                'condition' => (isset($commonData['start_date']) && !empty($commonData['start_date']) && $settings['display_date_start']) ? true : false,
                'content' => array(
                    'start_date' => (!isset($commonData['start_date']) || empty($commonData['start_date'])) ? '' : defineTimeInterval($commonData['start_date'], BX_DOL_LOCALE_DATE_SHORT),
                ),
            ),
            // display the created date
            'bx_if:display_created_date' => array(
                'condition' => (isset($commonData['created_date']) && !empty($commonData['created_date']) && $settings['display_created_date']) ? true : false,
                'content' => array(
                    'created_date' => (!isset($commonData['created_date']) || empty($commonData['created_date'])) ? '' : defineTimeInterval($commonData['created_date'], BX_DOL_LOCALE_DATE_SHORT),
                ),
            ),
            // display the categories
            'bx_if:display_categories' => array(
                'condition' => (isset($resultData['categories']) && !empty($commonData['categories']) && $settings['display_categories']) ? true : false,
                'content' => array(
                    'categories_caption' => _t('_Categories'),
                    'categories' => $categories,
                )
            ),
            // display the tags
            'bx_if:display_tags' => array(
                'condition' => (isset($commonData['tags']) && !empty($commonData['tags']) && $settings['display_tags']),
                'content' => array(
                    'tags_caption' => _t('_Tags'),
                    'tags' => $this->parseTags($commonData),
                ),
            ),
            // display the author
            'bx_if:display_author' => array(
                'condition' => (isset($commonData['author_id']) && !empty($commonData['author_id']) && $settings['display_author']) ? true : false,
                'content' => array(
                    'from_text' => _t('_from'),
                    'author' => getNickName($commonData['author_id']),
                    'author_link' => getProfileLink($commonData['author_id']),
                ),
            ),
            // display the views
            'bx_if:display_views' => array(
                'condition' => ($settings['display_views']) ? true : false,
                'content' => array(
                    'views' => $commonData['views'] . ' ' . (($commonData['views'] > 0) ? _t('_Views') : _t('_Views')),
                ),
            ),
            // display the size separator 
            'bx_if:display_size_separator' => array(
                'condition' => ($settings['display_size'] && $settings['display_views']),
                'content' => array(
                    'separator' => '.',
                ),
            ),
            // display the size
            'bx_if:display_size' => array(
                'condition' => ($settings['display_size']) ? true : false,
                'content' => array(
                    'size' => ($modVars['system'] == 'bx_videos' || $modVars['system'] == 'bx_sounds') ? $this->getSizeToDateConversion($commonData['size']) : $commonData['size'],
                ),
            ),
            // display the rate separator
            'bx_if:display_rate_separator' => array(
                'condition' => (
                    $settings['display_rate'] && 
                    (
                        (isset($commonData['country']) && 
                        !empty($commonData['country']) && 
                        $settings['display_fans_count']
                    ) || 
                    $settings['display_comments_count'] || 
                    $settings['display_size'] || 
                    $settings['display_views'] || 
                    $settings['display_ads_price']
                    )) ? true : false,
                'content' => array(
                    'separator' => '.',
                ),
            ),
            // display the rate
            'bx_if:display_rate' => array(
                'condition' => ($settings['display_rate']) ? true : false,
                'content' => array(
                    'rate' => $this->getVotes($modVars['voting_system'], $commonData['id']),
                ),
            ),
            // display the fans separator
            'bx_if:display_fans_separator' => array(
                'condition' => (isset($commonData['country']) && !empty($commonData['country']) && $settings['display_location']) ? true : false,
                'content' => array(
                    'separator' => '.',
                ),
            ),
            // display the fans count
            'bx_if:display_fans_count' => array(
                'condition' => ($settings['display_fans_count']) ? true : false,
                'content' => array(
                    'fans_count' => ($commonData['fans_count'] . ' ' . (($commonData['fans_count'] <= 1) ? _t('_emmetbytes_boonex_contents_slider_display_fans_caption_singular') : _t('_emmetbytes_boonex_contents_slider_display_fans_caption_multiple'))),
                ),
            ),
            // display the location
            'bx_if:display_location' => array(
                'condition' => (isset($commonData['country']) && !empty($commonData['country']) && $settings['display_location']) ? true : false,
                'content' => array(
                    'location' => isset($commonData['country']) ? (genFlag($commonData['country']) . ' ' . ((isset($commonData['place'])) ? $commonData['place'] . ', ' : '') . $commonData['city'] . ', ' . _t($GLOBALS['aPreValues']['Country'][$commonData['country']]['LKey'])) : '',
                ),
            ),
            // displya the sites url
            'bx_if:display_sites_url' => array(
                'condition' => (isset($commonData['sites_url']) && !empty($commonData['sites_url']) && $settings['display_site_url']) ? true : false,
                'content' => array(
                    'sites_url' => (isset($commonData['sites_url'])) ? $commonData['sites_url'] : '',
                    'sites_url_link' => (isset($commonData['sites_url'])) ? ((!preg_match('/http:\//', $commonData['sites_url'])) ? 'http://'. $commonData['sites_url'] : $commonData['sites_url']) : '',
                )
            ),
            // display the ads price
            'bx_if:display_ads_price' => array(
                'condition' => ($settings['display_ads_price'] && isset($commonData['fieldValue1']) && !empty($commonData['fieldValue1'])) ? true : false,
                'content' => array(
                    'ads_price' => process_text_output($commonData['fieldName1']) . ' : ' . process_text_output($commonData['unit1']) . ' ' . process_text_output($commonData['fieldValue1']),
                    'ads_field_2' => (isset($commonData['fieldValue2']) && !empty($commonData['fieldValue2'])) ? ' - ' . process_text_output($commonData['fieldValue2']) . process_text_output($commonData['fieldName2']) : '',
                )
            ),
            // display the description
            'bx_if:display_description' => array(
                'condition' => (isset($commonData['description']) && !empty($commonData['description']) && $settings['display_description']) ? true : false,
                'content' => array(
                    'description' => $description
                )
            )
        );
        return $commonData;
    }

    // getting the images, the photo object
    protected function getPrimPhotoOld($primPhotoId, $type='file'){
        $modVars=array('title'=>'Photos','class_prefix'=>'BxPhotos','class_suffix'=>'Search','class_name'=>'BxPhotosSearch');
        $aModule = $this->getModuleArray($modVars);
        $o = $this->getSearchResultObject($aModule);
        return $o->_getImageFullInfo($primPhotoId, $type);
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

    // getting the ads images
    protected function getPrimAdsPhoto($photoName, $searchObj){
        $adsMain = $searchObj->getAdsMain();
        $adsImage = $adsMain->getAdCover($photoName);
        return array(
            'file' => $adsImage,
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

    // gets the settings
    protected function getBlockSettings($name){
        return $this->oDb->getBoonexContentsSliderSettings($name);
    }
    // EOF the data getters

    // BOF the content getters
    // gets the modules common container
    protected function getModuleCommonContainer($modVars, $extraParams = false){
        $albumUri = '';
        $aModule = $this->getModuleArray($modVars);
        if(sizeof($aModule['sys_modules']) <= 0){ return false; }
        if($extraParams){
            if(isset($extraParams['restriction'])){
                $ownerId = $modVars['slider_params']['a_current']['inner_replace']['restriction']['owner']['value'];
                $moduleConfig = $this->getModuleConfig($aModule['sys_modules']);
                $profileInfo = getProfileInfo($ownerId);
                $sCaption = str_replace('{nickname}', $profileInfo['NickName'], $moduleConfig->getGlParam('profile_album_name'));
                $sUri = uriFilter($sCaption);
                $extraRestrictions = array('album' => array('value'=>$sUri, 'field'=>'Uri', 'operator'=>'=', 'paramName'=>'albumUri', 'table'=>'sys_albums'));
                if(isset($aModule['mod_vars']['slider_params']['a_current']['inner_replace']['restriction'])){
                    $aModule['mod_vars']['slider_params']['a_current']['inner_replace']['restriction'] = array_merge_recursive($aModule['mod_vars']['slider_params']['a_current']['inner_replace']['restriction'], $extraRestrictions);
                }else{
                    $aModule['mod_vars']['slider_params']['a_current']['inner_replace']['restriction'] = $extraRestrictions;
                }
                $albumUri = str_replace('{album_uri}', $sUri, $aModule['mod_vars']['album_uri']);
                $albumUri = str_replace('{owner_name}', $profileInfo['NickName'], $albumUri);
            }
        }
        $searchResultDatas = $this->getSliderSearchResults($aModule);
        if(!$searchResultDatas){
            $commonContainer = $this->getEmptyContainer();
        }else{
            $aModule['total_entries'] = $searchResultDatas['total_entries'];
            $aModule['num_of_displayed_entries'] = sizeof($searchResultDatas['search_results']);
            $moduleDatas = array( 'mod_datas' => $aModule, 'result_datas' => $searchResultDatas);
            $commonContainer = $this->getCommonMainContainer($moduleDatas);
            if(isset($modVars['custom_uri']) && !empty($modVars['custom_uri'])){
                $commonData['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true);
            }elseif(isset($sysModules['uri']) && !empty($sysModules['uri'])){
                $commonData['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']);
            }
        }
        $sysModules = $aModule['sys_modules'];
        $moduleBaseUri = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']);
        if(isset($modVars['custom_uri']) && !empty($modVars['custom_uri'])){
            $moduleBaseUri = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true);
        }
        return array(
            'search_obj' => $searchResultDatas['search_obj'],
            'common_container' => $commonContainer,
            'module_base_uri' => $moduleBaseUri,
            'album_uri' => (isset($albumUri) ? $albumUri : ''),
        );
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
        $sliderParams = $modVars['slider_params'];
        $sliderColumnsMap = $sliderParams['columns_map'];
        $informationParams = $modVars['information_params'];
        $settings = $modVars['settings'];
        $sliderDatas = array();
        $templateVars = $this->getMainTemplateVars($iBlockID);
        foreach($searchResults as $key=>$searchResult){
            $sliderDatas[$key]['container'] = $this->getCommonSliderContainer($searchObj, $searchResult, $modVars, $sysModules, $sliderColumnsMap, $templateVars, $settings);
            $thumbActiveClass = '';
        }
        $mcVars = array(
            'block_id' => $iBlockID,
            'bx_repeat:slider_datas' => $sliderDatas, 
            'script' => $this->getMainJavascript($templateVars, $moduleVars),
        );
        $mcVars = array_merge($mcVars, $templateVars);
        return $this->oTemplate->parseHTMLByName('common_main_container', $mcVars);
    }

    // getting of the template variables
    protected function getMainTemplateVars($iBlockID){
        return array(
            'main_container_class' => 'emmetbytes_boonex_contents_slider_common_main_container',
            'main_container_navigation_class' => 'emmetbytes_boonex_contents_slider_common_main_navigation_container',
            'main_container_id' => 'emmetbytes_boonex_contents_slider_common_main_container_' . $iBlockID,
            'loader_container_class' => 'emmetbytes_boonex_contents_slider_loader_container',
            'sliders_container_class' => 'emmetbytes_boonex_contents_slider_common_sliders_container', 
            'sliders_nav_container' => 'emmetbytes_boonex_contents_slider_common_sliders_nav_main_container',
            'sliders_nav_inner_containers' => 'emmetbytes_boonex_contents_slider_navigation_inner_containers',
            'sliders_nav_counters_container' => 'emmetbytes_boonex_contents_slider_navigation_counters_container',
            'sliders_nav_counter_container' => 'emmetbytes_boonex_contents_slider_navigation_counter_container',
            'sliders_nav_counter_container_active' => 'emmetbytes_boonex_contents_slider_navigation_counter_container_active',
            'sliders_nav_container_prev' => 'emmetbytes_boonex_contents_slider_common_sliders_nav_prev_container',
            'sliders_nav_container_next' => 'emmetbytes_boonex_contents_slider_common_sliders_nav_next_container',
            'sliders_container_inner_class' => 'emmetbytes_boonex_contents_slider_common_sliders_inner_container',
            'slider_container_main_class' => 'emmetbytes_boonex_contents_slider_common_slider_container_main',
            'slider_container_class' => 'emmetbytes_boonex_contents_slider_common_slider_container',
            'slider_container_inner_class' => 'emmetbytes_boonex_contents_slider_common_inner_slider_container',
            'slider_inner_container_class' => 'emmetbytes_boonex_contents_slider_common_slider_inner_container',
            'slider_image_container_class' => 'emmetbytes_boonex_contents_slider_common_slider_image_container',
            'slider_informations_container' => 'emmetbytes_boonex_contents_slider_common_slider_informations_container',
            'slider_restricted_informations_container' => 'emmetbytes_boonex_contents_slider_common_slider_restricted_informations_container',
            'slider_title_container' => 'emmetbytes_boonex_contents_slider_common_slider_title_container',
            'slider_datas_container' => 'emmetbytes_boonex_contents_slider_common_slider__datas_container',
            'slider_additional_datas_container' => 'emmetbytes_boonex_contents_slider_common_slider_additional_datas_container',
            'slider_additional_first_datas_container' => 'emmetbytes_boonex_contents_slider_common_slider_additional_first_datas_container',
            'slider_additional_data_container' => 'emmetbytes_boonex_contents_slider_common_slider_additional_data_container',
            'slider_additional_data_separator' => 'emmetbytes_boonex_contents_slider_common_slider_additional_data_separator',
            'slider_location_container' => 'emmetbytes_boonex_contents_slider_common_slider_location_container',
            'slider_fans_count_container' => 'emmetbytes_boonex_contents_slider_common_slider_fans_count_container',
            'slider_rating_container' => 'emmetbytes_boonex_contents_slider_common_slider_rating_container',
            'slider_author_container' => 'emmetbytes_boonex_contents_slider_common_slider_author_container',
            'slider_description_container' => 'emmetbytes_boonex_contents_slider_common_slider_description_container',
        );
    }

    // getting the restricted data container
    protected function getRestrictedInfoDataContainer($infoDatas){
        $infoDatas['icon'] = $this->oTemplate->getIconUrl($infoDatas['icon']);
        $infoDatas['spacer'] = getTemplateIcon('spacer.gif');
        $infoDatas['caption'] = _t('_emmetbytes_boonex_contents_slider_restricted_access_caption');
        return $this->oTemplate->parseHTMLByName('restricted_info_container', $infoDatas);
    }

    // gets the private container part two
    protected function getPrivateContainerTwo(){
        return $this->getEmptyContainer();
    }

    // getting the common slider container
    protected function getCommonSliderContainer($searchObj, $searchResult, $modVars, $sysModules, $sliderColumnsMap, $templateVars, $settings){
        $sliderData = $this->getSliderCommonData($searchObj, $searchResult, $modVars, $sysModules, $sliderColumnsMap, $settings);
        if(isset($sliderData['restricted']) && $sliderData['restricted']){
            return $this->getRestrictedSliderContainer($sliderData, $templateVars, $modVars);
        }else{
            $sliderDatas = array_merge($sliderData, $templateVars);
            return $this->oTemplate->parseHTMLByName('common_slider_container', $sliderDatas);
        }
    }

    // getting the restricted slider container
    protected function getRestrictedSliderContainer($commonData, $templateVars, $modVars){
        $sliderDatas = array(
            'id' => $commonData['id'],
            'caption' => _t('_emmetbytes_boonex_contents_slider_restricted_access_caption'),
            'is_active' => $isActive,
            'spacer' => getTemplateIcon('spacer.gif'),
            'slider_photo' => $this->oTemplate->getIconUrl($modVars['allow_view_checker']['private_container_params']['icon_image']),
            'slider_width' => $commonData['width'],
            'slider_height' => $commonData['height'],
        );
        $sliderDatas = array_merge($sliderDatas, $templateVars);
        return $this->oTemplate->parseHTMLByName('restricted_slider_container', $sliderDatas);
    }

    // getting the main javascript code
    protected function getMainJavascript($templateVars, $moduleVars){
        $iBlockID = $moduleVars['mod_vars']['block_id'];
        $jsVars = array(
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'ID',
                    'title' => 'Title',
                    'entry_uri' => 'EntryUri',
                    'country' => 'Country',
                    'city' => 'City',
                    'place' => 'Place',
                    'start_date' => 'EventStart',
                    'author_id' => 'ResponsibleID',
                    'thumb_photo' => 'PrimPhoto',
                    'fans_count' => 'FansCount',
                    'rate' => 'Rate',
                    'nickname' => 'NickName',
                    'description' => false,
                ),
                'search_params' => array(
                    'browse_mode' => (isset($eventParams['browse_mode'])) ? $eventParams['browse_mode']: '',
                    'param1' => (isset($eventParams['param1'])) ? $eventParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_datas'], 
                        ),
                    ),
                    'primary_key' => 'ID',
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'created',
                    'author_id' => 'author_id',
                    'thumb_photo' => 'thumb',
                    'rate' => 'rate',
                    'fans_count' => 'fans_count',
                    'country' => 'country',
                    'city' => 'city',
                    'nickname' => 'NickName',
                    'description' => '',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($groupsParams['browse_mode'])) ? $groupsParams['browse_mode']: '',
                    'param1' => (isset($groupsParams['param1'])) ? $groupsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_datas'],
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'sites_url' => 'url',
                    'title' => 'title',
                    'entry_uri' => 'entryUri',
                    'description' => 'description',
                    'thumb_photo' => 'photo',
                    'comments_count' => 'commentsCount',
                    'created_date' => 'date',
                    'author_id' => 'ownerid',
                    'categories' => 'categories',
                    'tags' => 'tags',
                    'rate' => 'rate',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($siteParams['browse_mode'])) ? $siteParams['browse_mode']: '',
                    'param1' => (isset($siteParams['param1'])) ? $siteParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_datas'], 
                        ),
                        'restriction' => array(
                            'allow_view' => array(
                                'value' => array(BX_DOL_PG_ALL,BX_DOL_PG_MEMBERS),
                            ),
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'date',
                    'description' => 'bodyText',
                    'tags' => 'tag',
                    'rate' => 'Rate',
                    'rate_count' => 'RateCount',
                    'comments_count' => 'CommentsCount',
                    'categories' => 'Categories',
                    'thumb_blog_photo' => 'PostPhoto',
                    'author_id' => 'ownerId',
                    'owner_name' => 'ownerName',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($blogsParams['browse_mode'])) ? $blogsParams['browse_mode']: '',
                    'param1' => (isset($blogsParams['param1'])) ? $blogsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_datas'], 
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
            $blogsModuleVars['slider_params']['a_current']['inner_replace']['restriction'] = $blogsParams['restriction'];
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'date',
                    'size' => 'size',
                    'views' => 'view',
                    'rate' => 'Rate',
                    'hash' => 'Hash',
                    'author_id' => 'ownerId',
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
                            'perPage' => $settings['maximum_datas'],
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($photoParams['sort_mode'])) ? $photoParams['sort_mode'] : '', 
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
            $photoModuleVars['slider_params']['a_current']['inner_replace']['restriction'] = $photoParams['restriction'];
        }
        if(isset($photoParams['extra_search_params'])){ 
            $photoModuleVars['slider_params']['extra_search_params'] = $photoParams['extra_search_params']; 
        }
        if(isset($photoParams['has_block_addon_checker'])){
            $photoModuleVars['slider_params']['block_addon_checker'] = array(
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'date',
                    'size' => 'size',
                    'views' => 'view',
                    'rate' => 'Rate',
                    'author_id' => 'ownerId',
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
                            'perPage' => $settings['maximum_datas'], 
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($videosParams['sort_mode'])) ? $videosParams['sort_mode'] : '', 
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
            $videosModuleVars['slider_params']['a_current']['inner_replace']['restriction'] = $videosParams['restriction'];
        }
        if(isset($videosParams['extra_search_params'])){ 
            $videosModuleVars['slider_params']['extra_search_params'] = $videosParams['extra_search_params']; 
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'date',
                    'size' => 'size',
                    'views' => 'view',
                    'rate' => 'Rate',
                    'author_id' => 'ownerId',
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
                            'perPage' => $settings['maximum_datas'], 
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
            $soundsModuleVars['slider_params']['a_current']['inner_replace']['restriction'] = $soundsParams['restriction'];
        }
        if(isset($soundsParams['extra_search_params'])){ 
            $soundsModuleVars['slider_params']['extra_search_params'] = $soundsParams['extra_search_params']; 
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
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'date',
                    'size' => 'size',
                    'views' => 'view',
                    'rate' => 'Rate',
                    'author_id' => 'ownerId',
                    'extension' => 'ext',
                    'description' => 'desc',
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
                            'perPage' => $settings['maximum_datas'], 
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
            $filesModuleVars['slider_params']['a_current']['inner_replace']['restriction'] = $filesParams['restriction'];
        }
        if(isset($filesParams['clear_filters'])){
            $filesModuleVars['slider_params']['clear_filters'] = true;
        }
        if(isset($filesParams['extra_search_params'])){ 
            $filesModuleVars['slider_params']['extra_search_params'] = $filesParams['extra_search_params']; 
        }
        return $filesModuleVars;
    }
    // eof the files module

    // bof the ads module
    // getting the ads datas
    protected function getAdsDatas($iBlockID, $adsParams, $settings=array()){
        $modVars = $this->getAdsModuleVars($iBlockID, $adsParams, $settings);
        $commonContainer = $this->getModuleCommonContainer($modVars);
        unset($modVars);
        return $commonContainer;
    }

    // getting the ads module variables
    protected function getAdsModuleVars($iBlockID, $adsParams, $settings){
        $adsModuleVars = array(
            'title' => 'Ads',
            'class_prefix' => 'BxAds' ,
            'class_suffix' => 'SearchUnit',
            'class_name' => 'BxAdsSearchUnit',
            'view_uri' => 'entry/',
            'system' => 'bx_ads',
            'voting_system' => 'ads',
            'slider_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'created_date' => 'date',
                    'author_id' => 'ownerId',
                    'description' => 'bodyText',
                    'thumb_ads_photo' => 'media',
                    'fieldValue1' => 'CustomFieldValue1',
                    'fieldName1' => 'CustomFieldName1',
                    'unit1' => 'Unit1',
                    'fieldValue2' => 'CustomFieldValue2',
                    'fieldName2' => 'CustomFieldName2',
                    'unit2' => 'Unit2',
                    'categories' => 'categoryName',
                    'categories_uri' => 'categoryUri',
                    'categories_id' => 'categoryId',
                    'sub_categories' => 'subcategoryName',
                    'sub_categories_uri' => 'subcategoryUri',
                    'sub_categories_id' => 'subcategoryId',
                    'rate' => 'Rate',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($adsParams['browse_mode'])) ? $adsParams['browse_mode']: '',
                    'param1' => (isset($adsParams['param1'])) ? $adsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_datas'], 
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($adsParams['sort_mode'])) ? $adsParams['sort_mode'] : '', 
                    ),
                ),
            ),
            'custom_category' => array(
                'method' => 'getAdsCategories'
            ),
            'prim_key' => 'ID',
            'prim_photo_function' => 'getPrimAdsPhoto',
            'custom_uri' => 'boonex/ads/classifieds.php?Browse=1',
            'prim_photo_defaults' => 'templates/base/images/icons/006.png',
            'block_id' => $iBlockID,
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($adsParams['settings_name']),
        );
        $adsModuleVars['settings']['info_image_height'] = 180;
        $adsModuleVars['settings']['info_image_width'] = 218;
        if(isset($adsParams['restriction'])){ 
            $adsModuleVars['slider_params']['a_current']['inner_replace']['restriction'] = $adsParams['restriction'];
        }
        if(isset($adsParams['extra_search_params'])){ 
            $adsModuleVars['slider_params']['extra_search_params'] = $adsParams['extra_search_params']; 
        }
        return $adsModuleVars;

    }

    // adding the ads categories
    protected function getAdsCategories($searchObj, $commonData){
        $oMain = $searchObj->getAdsMain();
        $categoryName = process_text_output($commonData['categories']);
        $categoryUri = process_text_output($commonData['categories_uri']);
        $subCategoryName = process_text_output($commonData['sub_categories']);
        $subCategoryUri = process_text_output($commonData['sub_categories_uri']);
        $iCatId = $commonData['categories_id'];
        $iSubCatID = $commonData['sub_categories_id'];
		$sCategUrl = ($oMain->_oConfig->bUseFriendlyLinks) ? BX_DOL_URL_ROOT . 'ads/cat/'.$categoryUri : "{$oMain->_oConfig->sCurrBrowsedFile}?bClassifiedID={$iCatID}";
		$sSubCategUrl = ($oMain->_oConfig->bUseFriendlyLinks) ? BX_DOL_URL_ROOT . 'ads/subcat/'.$subCategoryUri : "{$oMain->_oConfig->sCurrBrowsedFile}?bSubClassifiedID={$iSubCatID}";
        return "<a href='$sCategUrl'>$categoryName</a>" . ' - ' . "<a href='$sSubCategUrl'>$subCategoryName</a>";
    }
    // eof the ads module
    // EOF the module getters
    // EOF THE GETTERS

    // ADDERS
    // BOF the templatate adders
    // add the css
    protected function addAllCss(){
        if(!isset($GLOBALS['ebBoonexContentsSliderCss'])){
            $this->oTemplate->addCss('main.css');
            $GLOBALS['ebBoonexContentsSliderCss'] = true;
        }
    }

    // add the javascript
    protected function addAllJs(){
        if(!isset($GLOBALS['ebBoonexContentsSliderJs'])){
            $this->oTemplate->addJs('EmmetBytesBoonexContentsSlider.js');
            $this->oTemplate->addJs('json2.js');
            $GLOBALS['ebBoonexContentsSliderJs'] = true;
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

// 707-709 HELPER
class EmmetBytesBoonexContentsSliderD707UpHelper extends EmmetBytesBoonexContentsSliderDefaultHelper{
    
    // CONSTRUCTOR
    function EmmetBytesBoonexContentsSliderD707UpHelper($oMain){
        parent::EmmetBytesBoonexContentsSliderDefaultHelper($oMain);
    }

    // get homepage blogs params
    protected function getHomepageBlogsParams($settings){
        $homepageBlogsParams = parent::getHomepageBlogsParams($settings);
        $homepageBlogsParams['restriction']['allow_view']['value'] = BX_DOL_PG_ALL;
        return $homepageBlogsParams;
    }

    // get blogs module vars 
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = parent::getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $blogsModuleVars['system'] = 'bx_blogs';
        $blogsModuleVars['check_for_restriction'] = false;
        return $blogsModuleVars;
    }
    
}

// 710-CURRENT
class EmmetBytesBoonexContentsSliderD710UpHelper extends EmmetBytesBoonexContentsSliderDefaultHelper{

    // constructor
    function EmmetBytesBoonexContentsSliderD710UpHelper($oMain){
        parent::EmmetBytesBoonexContentsSliderDefaultHelper($oMain);
    }

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
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        switch($filesParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] .$browseUrl, BX_DOL_URL_ROOT, 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$filesParams['sort_mode']);
        return array(
            $filesDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate
        );
    }
    // get homepage blogs params
    protected function getHomepageBlogsParams($settings){
        $homepageBlogsParams = parent::getHomepageBlogsParams($settings);
        $aView = array(BX_DOL_PG_ALL);
        if($this->oMain->getUserId()){
            $aView[] = BX_DOL_PG_MEMBERS;
        }
        $homepageBlogsParams['restriction']['allow_view']['value'] = $aView;
        return $homepageBlogsParams;
    }

    // gets the latest blog post
    function latestBlogPost($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_latest_blogs_block_settings');
        $aView = array(BX_DOL_PG_ALL);
        if($this->oMain->getUserId()){
            $aView[] = BX_DOL_PG_MEMBERS;
        }
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => $settings['default_tab'],
            'restriction' => array(
                'allow_view' => array(
                    'value' => $aView,
                ),
            ),
        );
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        $o = $blogsDatas['search_obj'];
        $browseUrl = 'all_posts/';
        $sAjaxPaginate = $this->getPaginationData($o, $blogsDatas['module_base_uri'] . $browseUrl, $blogsDatas['module_base_uri'] . 'home', 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='. $blogsParams['sort_mode']);
        return array(
            $blogsDatas['common_container'],
            '',
            $sAjaxPaginate,
        );
    }

    // getting the blogs module vars
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = parent::getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $blogsModuleVars['system'] = 'bx_blogs';
        $blogsModuleVars['check_for_restriction'] = false;
        $blogsModuleVars['empty_image_icon'] = 'no-image-thumb-blogs.png';
        return $blogsModuleVars;
    }

    // getting the album view
    protected function getAlbumView(){
        return array(
            'content' => $this->getPrivateContainerTwo(),
        );
    }

    // getting the second private container
    protected function getPrivateContainerTwo(){
        return MsgBox(_t('_sys_album_private'));
    }

    // event module variables
    protected function getEventModuleVars($iBlockID, $eventParams, $settings = array()){
        $eventModuleVars = parent::getEventModuleVars($iBlockID, $eventParams, $settings);
        $eventModuleVars['empty_image_icon'] = 'no-image-thumb-events.png';
        $eventModuleVars['slider_params']['columns_map']['description'] = 'Description';
        if(isset($eventParams['allow_view_checker']) && !empty($eventParams['allow_view_checker'])){
            $eventModuleVars['allow_view_checker']['private_container_params']['icon_image'] = 'no-image-thumb-events.png';
        }
        return $eventModuleVars;
    }

    // getting the groups module variables
    protected function getGroupsModuleVars($iBlockID, $groupsParams, $settings=array()){
        $groupsModuleVars = parent::getGroupsModuleVars($iBlockID, $groupsParams, $settings);
        $groupsModuleVars['empty_image_icon'] = 'no-image-thumb-groups.png';
        $groupsModuleVars['slider_params']['columns_map']['description'] = 'desc';
        if(isset($groupsParams['allow_view_checker']) && !empty($groupsParams['allow_view_checker'])){
            $groupsModuleVars['allow_view_checker']['private_container_params']['icon_image'] = 'no-image-thumb-groups.png'; 
        } 
        return $groupsModuleVars;
    }

    // getting the sites module variables
    protected function getSitesModuleVars($iBlockID, $siteParams, $settings){
        $sitesModuleVars = parent::getSitesModuleVars($iBlockID, $siteParams, $settings);
        $sitesModuleVars['empty_image_icon'] = 'no-image-thumb-sites.png';
        return $sitesModuleVars;
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
            'sort_mode' => 'album_order',
        );
        if(getLoggedId() > 0){
            $photoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        $o = $photosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $photosDatas['module_base_uri'] . 'browse/featured/', $photosDatas['module_base_uri']);
        return array(
            $photosDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
            'sort_mode' => 'album_order',
        );
        if(getLoggedId() > 0){
            $vidoesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        $videosDatas = $this->getVideosDatas($iBlockID, $vidoesParams, $settings);
        if(!$videosDatas){ return false; }
        $o = $videosDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $videosDatas['module_base_uri'] . 'browse/featured/', $videosDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_filter='.$videosParams['sort_mode']);
        return array(
            $videosDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
            'sort_mode' => 'album_order',
        );
        if(getLoggedId() > 0){
            $soundsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        $o = $soundsDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $soundsDatas['module_base_uri'] . 'browse/featured', $soundsDatas['module_base_uri']);
        return array(
            $soundsDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
    }

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
            'clear_filters' => true,
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter'];
        }        
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        switch($filesParams['sort_mode']){
            case 'last':
                $browseUrl = 'browse/all';
                break;
            case 'top':
                $browseUrl = 'browse/top';
                break;
        }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] . $browseUrl, $filesDatas['module_base_uri'], 'emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter='.$filesParams['sort_mode']);
        return array(
            $filesDatas['common_container'],
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_boonex_contents_slider_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
            $sAjaxPaginate,
        );
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
            'clear_filters' => true,
        );
        if(getLoggedId() > 0){
            $filesParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        $o = $filesDatas['search_obj'];
        $sAjaxPaginate = $this->getPaginationData($o, $filesDatas['module_base_uri'] . 'browse/featured', $filesDatas['module_base_uri']);
        return array(
            $filesDatas['common_container'],
            array(),
            $sAjaxPaginate,
        );
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
