<?php
/**********************************************************************************************
 * Created By : EmmetBytes Software Solutions
 * Created Date : June 10, 2012
 * Email : emmetbytes@gmail.com
 *
 * Copyright : (c) EmmetBytes Software Solutions 2012
 * Product Name : Boonex Contents in Realtime
 * Product Version : 1.0
 * 
 * Important : This is a commercial product by EmmetBytes Software Solutions and 
 *   cannot be modified, redistributed or resold without any written permission 
 *   from EmmetBytes Software Solutions
 **********************************************************************************************/

 // TODO, setup $oldSearchResult for myPHPGuard

class EmmetBytesBonConInRealtimeHelper{
    var $boonexVersion;
    var $helperObj;
    
    // CONSTRUCTOR
    function EmmetBytesBonConInRealtimeHelper($oMain){
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        if($this->boonexVersion >= '7.1.0'){
            $this->helperObj = new EmmetBytesBonConInRealtimeD710UpHelper($oMain);
        } else if ($this->boonexVersion >= '7.0.7' && $this->boonexVersion < '7.1.0'){
            $this->helperObj = new EmmetBytesBonConInRealtimeD707UpHelper($oMain);
        }else{
            $this->helperObj = new EmmetBytesBonConInRealtimeDefaultHelper($oMain);
        }
    }

}

class EmmetBytesBonConInRealtimeDefaultHelper{
    // ATTRIBUTES
    var $oMain, $oDb, $oTemplate, $oConfig, $boonexVersion; 
    
    // CONSTRUCTOR
    function EmmetBytesBonConInRealtimeDefaultHelper($oMain){
        $this->profileId = $oMain->_iProfileId;
        $this->oDb = $oMain->_oDb;
        $this->oTemplate = $GLOBALS['ebBonConInRealtimeSysTemplate'] = (isset($GLOBALS['ebBonConInRealtimeSysTemplate'])) ? $GLOBALS['ebBonConInRealtimeSysTemplate'] : $oMain->_oTemplate;
        $this->oConfig = $GLOBALS['ebBonConInRealtimeSysConfig'] = (isset($GLOBALS['ebBonConInRealtimeSysConfig'])) ? $GLOBALS['ebBonConInRealtimeSysConfig'] : $oMain->_oConfig;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        $this->addAllCss();
        $this->addAllJs();
    }
    
    // BOF THE ACTION RESPONSE
    // getting new entries
    function getNewEntriesResponse(){
        $moduleVars = json_decode($_POST['moduleVars'], true);
        $oldSearchResults = json_decode($_POST['searchResults'], true);
        $modVars = $moduleVars['mod_vars'];
        $sysModules = $moduleVars['sys_modules'];
        $iBlockID = $modVars['block_id'];
        $oldDataIds = $dataIds = $moduleVars['data_ids'];
        $templateVars = $this->getMainTemplateVars($iBlockID);
        $primKey = $modVars['prim_key'];
        $thumbnailsColumnsMap = $modVars['thumbnails_params']['columns_map'];
        $searchResultDatas = $this->getThumnailSearchResults($moduleVars);
        $searchResults = ($searchResultDatas['search_results']) ? $searchResultDatas['search_results'] : array();
        $hasAdditionalChecker = false;
        if(isset($modVars['thumbnails_params']['additional_checker'])){
            $hasAdditionalChecker = true;
            $additionalCheckerMethod = $modVars['thumbnails_params']['additional_checker']['method'];
            $additionalCheckerParams = $modVars['thumbnails_params']['additional_checker']['param'];
        }
        $searchObj = $searchResultDatas['search_obj'];
        $searchResultsIds = $refreshedEntries = $additionalEntriesIds = $sortableEntries = $additionalEntries = $steadyEntries = array();
        foreach($searchResults as $key=>$value){
            // additional checker/verifier
            if($hasAdditionalChecker){
                if($additionalCheckerParams == 'entryResult'){
                    $isValid = $this->$additionalCheckerMethod($value);
                    if(!$isValid){ continue; }
                }
            }
            // getting the search result ids
            $searchResultsIds[] = $value[$primKey];
            $cmpIdKey = array_search($value[$primKey], $dataIds);
            $oldKeyId = array_search($value[$primKey], $oldDataIds);// getting the old key id
            $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars);
            if(($cmpIdKey || $cmpIdKey===0)){
                if($key!=$cmpIdKey){
                    $dataIds[$cmpIdKey] = (isset($dataIds[$key])) ? $dataIds[$key] : 0;
                    $dataIds[$key] = $value[$primKey];
                    $sortableEntries[] = array(
                        'after' => ($key<=0) ? 'first' : $searchResults[$key-1][$primKey],
                        'changed' => (($value[$primKey]==$oldSearchResults[$oldKeyId][$primKey]) && ($oldSearchResults[$oldKeyId] != $value)) ? 'true' : 'false',
                        'id' => $value[$primKey],
                        'thumbnailContainer' => $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars),
                    );
                }else{
                    if(isset($oldSearchResults[$oldKeyId]) && $value!=$oldSearchResults[$oldKeyId]){
                        $sortableEntries[] = array(
                            'after' => ($key<=0) ? 'first' : $searchResults[$key-1][$primKey],
                            'changed' => 'true',
                            'id' => $value[$primKey],
                            'thumbnailContainer' => $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars),
                        );
                    }else{
                        $steadyEntries[] = $value[$primKey];
                    }
                }
            }else{
                $additionalEntries[] = array(
                    'current_id' => $value[$primKey],
                    'before' => isset($searchResults[$key+1]) ? $searchResults[$key+1][$primKey] : 'last',
                    'thumbnailContainer' => $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars),
                );
            }
        }
        $removableEntries = array_values(array_diff($dataIds,$searchResultsIds));
        $additionalEntries = array_reverse($additionalEntries);
        $returnVals = array(
            'total_entries' => $searchResultDatas['total_entries'],
            'steady_entries' => $steadyEntries,
            'sortable_entries' => $sortableEntries,
            'refreshed_entries' => $refreshedEntries,
            'new_data_ids' => $searchResultsIds, 
            'removable_entries' => $removableEntries,
            'additional_entries' => $additionalEntries,
            'additional_entries_count' => sizeof($additionalEntries),
            'removable_entries_count' => sizeof($removableEntries),
            'search_result_datas' => $searchResults,
            'is_private' => 'false',
        );
        return json_encode($returnVals);
    }
    // EOF THE ACTION RESPONSE

    // HOMEPAGE SERVICE BLOCKS HELPERS
    // homepage events block helper
    function homepageEventsBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_event_block_settings');
        $eventParams = array(
            'browse_mode' => $settings['default_tab'],
            'is_public' => true,
            'allow_view_checker' => true
        );
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter'])){
            switch ($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter']){
                case 'featured':
                case 'recent':
                case 'top':
                case 'popular':
                case 'upcoming':
                    $eventParams['browse_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter'];
                    break;
            }
        }
        // getting the languages
        switch($eventParams['browse_mode']){
            case 'featured':
                $eventParams['language'] = _t('_bx_events_caption_browse_featured');
                break;
            case 'recent':
                $eventParams['language'] = _t('_bx_events_caption_browse_recently_added');
                break;
            case 'top':
                $eventParams['language'] = _t('_bx_events_caption_browse_top_rated');
                break;
            case 'popular':
                $eventParams['language'] = _t('_bx_events_caption_browse_popular');
                break;
            case 'upcoming':
                $eventParams['language'] = _t('_bx_events_caption_browse_upcoming');
                break;
        }
        $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $eventParams['language']);
        $eventParams['browse_mode_url'] = $eventParams['browse_mode'];
        $eventDatas = $this->getEventDatas($iBlockID, $eventParams, $settings);
        if(!$eventDatas){ return false; }
        return array(
            $eventDatas,
            array(
                _t('_bx_events_tab_upcoming') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=upcoming', 'active' => 'upcoming' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_featured') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=featured', 'active' => 'featured' == $eventParams['browse_mode'], 'dynamic' => true),                
                _t('_bx_events_tab_recent') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=recent', 'active' => 'recent' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=top', 'active' => 'top' == $eventParams['browse_mode'], 'dynamic' => true),
                _t('_bx_events_tab_popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=popular', 'active' => 'popular' == $eventParams['browse_mode'], 'dynamic' => true),                
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter'])){
            switch ($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter']) {
                case 'featured':
                case 'recent':
                case 'top':
                case 'popular':
                    $groupsParams['browse_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter'];
                    break;
            }
        }
        // getting the languages
        switch($groupsParams['browse_mode']){
            case 'featured':
                $groupsParams['language'] = _t('_bx_groups_page_title_browse_featured');
                break;
            case 'recent':
                $groupsParams['language'] = _t('_bx_groups_page_title_browse_recent');
                break;
            case 'top':
                $groupsParams['language'] = _t('_bx_groups_page_title_browse_top_rated');
                break;
            case 'popular':
                $groupsParams['language'] = _t('_bx_groups_page_title_browse_popular');
                break;
        }
        $groupsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $groupsParams['language']);
        $groupsParams['browse_mode_url'] = $groupsParams['browse_mode'];
        $groupsDatas = $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
        if(!$groupsDatas){ return false; }
        return array(
            $groupsDatas,
            array(
                _t('_bx_groups_tab_featured') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=featured', 'active' => 'featured' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_recent') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=recent', 'active' => 'recent' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=top', 'active' => 'top' == $groupsParams['browse_mode'], 'dynamic' => true),
                _t('_bx_groups_tab_popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_filter=popular', 'active' => 'popular' == $groupsParams['browse_mode'], 'dynamic' => true),
            )
        );
    }

    // homepage sites block helper
    function homepageSitesBlockHelper($iBlockID){
        $settings = $this->getBlockSettings('homepage_site_block_settings');
        $siteParams = array( 'browse_mode' => 'index');
        $siteParams['language'] = _t('_bx_sites_caption_public_latest');
        $siteParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $siteParams['language']);
        $siteParams['browse_mode_url'] = 'all';
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($blogsParams['sort_mode']){
            case 'last':
                $blogsParams['language'] = _t('_bx_blog_Latest_posts');
                $blogsParams['browse_mode_url'] = 'all_posts';
                break;
            case 'top':
                $blogsParams['language'] = _t('_bx_blog_Top_Posts');
                $blogsParams['browse_mode_url'] = 'top_posts';
                break;
        }
        $blogsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $blogsParams['language']);
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        return array(
            $blogsDatas,
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $photoParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($photoParams['sort_mode']){
            case 'last':
                $photoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_photos_page_caption');
                $photoParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $photoParams['language'] = _t('_bx_photos_top');
                $photoParams['browse_mode_url'] = 'top';
                break;
        }
        $photoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $photoParams['language']);
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        return array(
            $photosDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $photoParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $photoParams['sort_mode'], 'dynamic' => true), 
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
                    'value' => array(BX_DOL_PG_ALL,),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $videosParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($videosParams['sort_mode']){
            case 'last':
                $videosParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_videos_page_caption');
                $videosParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $videosParams['language'] = _t('_bx_videos_top');
                $videosParams['browse_mode_url'] = 'top';
                break;
        }
        $videosParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $videosParams['language']);
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        return array(
            $this->getVideosDatas($iBlockID, $videosParams, $settings),
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $videosParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $videosParams['sort_mode'], 'dynamic' => true), 
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($soundsParams['sort_mode']){
            case 'last':
                $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_sounds_page_caption');
                $soundsParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $soundsParams['language'] = _t('_bx_sounds_top');
                $soundsParams['browse_mode_url'] = 'top';
                break;
        }
        $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $soundsParams['language']);
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        return array(
            $soundsDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $soundsParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $soundsParams['sort_mode'], 'dynamic' => true), 
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($filesParams['sort_mode']){
            case 'last':
                $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_files_page_caption');
                $filesParams['browse_mode_url'] = 'all';
                break;
            case 'popular':
                $filesParams['language'] = _t('_bx_files_top_menu_popular');
                $filesParams['browse_mode_url'] = 'popular';
                break;
        }
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Popular') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=popular', 'active' => 'popular' == $filesParams['sort_mode'], 'dynamic' => true), 
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
            'browse_mode_url' => 'user/' . $aProfile['NickName'],
            'allow_view_checker' => true,
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_events_page_caption', $aProfile['NickName']);
        if(getLoggedId() == $profileID){
            $eventParams['language'] = _t('_bx_events_block_my_events');
            $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_page_caption', $eventParams['language']);
        }
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }

    // profile joined events block helper
    function profileJoinedEventsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_joined_event_block_settings');
        $aProfile = getProfileInfo($profileID);
        $eventParams = array(
            'browse_mode' => 'joined',
            'browse_mode_url' => 'joined/' . $aProfile['NickName'],
            'allow_view_checker' => true,
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_joined_events_page_caption', $aProfile['NickName']);
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
            'browse_mode_url' => 'user/' . $aProfile['NickName'],
            'allow_view_checker' => true,
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        $groupsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_groups_page_caption', $aProfile['NickName']);
        if(getLoggedId() == $profileID){
            $groupsParams['language'] = _t('_bx_groups_block_my_groups');
            $groupsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_page_caption', $groupsParams['language']);
        }
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }

    // profile joined groups
    function profileJoinedGroupsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_joined_group_block_settings');
        $aProfile = getProfileInfo($profileID);
        $groupsParams = array(
            'browse_mode' => 'joined',
            'browse_mode_url' => 'joined/' . $aProfile['NickName'],
            'allow_view_checker' => true,
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        $groupsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_joined_groups_page_caption', $aProfile['NickName']);
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }
    // eof the groups

    // bof sites
    // profile own sites
    function profileOwnSitesBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_site_block_settings');
        $aProfile = getProfileInfo($profileID);
        $siteParams = array(
            'browse_mode' => 'profile',
            'param1' => process_db_input ($aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION),
        );
        $siteParams['browse_mode_url'] = 'user/' . $aProfile['NickName'];
        $siteParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_site_page_caption', $aProfile['NickName']);
        return $this->getSiteDatas($iBlockID, $siteParams, $settings);
    }
    // eof the sites

    // bof blogs
    // profile own blogs
    function profileOwnBlogsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_blogs_block_settings');
        $aProfile = getProfileInfo($profileID);
        $blogsParams = array(
            'browse_mode' => '',
            'sort_mode' => 'last',
            'restriction' => array('owner' => array('value' => $profileID)),
            'allow_view_checker' => true,
        );
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        $blogsParams['browse_mode_url'] = 'posts/' . $aProfile['NickName'];
        $blogsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_blog_posts_page_caption', $aProfile['NickName']);
        return $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
    }
    // eof the blogs

    // bof photos
    // profile own photos
    function profileOwnPhotosBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_photos_block_settings');
        $aProfile = getProfileInfo($profileID);
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
        $photosParams['browse_mode_url'] = 'album';
        $photosParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_photos_page_caption', $aProfile['NickName']);
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $photosParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        return $this->getPhotoDatas($iBlockID, $photosParams, $settings);
    }
    // eof the photos

    // bof videos
    // profile own videos
    function profileOwnVideosBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_videos_block_settings');
        $aProfile = getProfileInfo($profileID);
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
        $videosParams['browse_mode_url'] = 'album';
        $videosParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_videos_page_caption', $aProfile['NickName']);
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        return $this->getVideosDatas($iBlockID, $videosParams, $settings);
    }
    // eof the videos

    // sounds
    function profileOwnSoundsBlockHelper($iBlockID, $profileID){
        $settings = $this->getBlockSettings('profile_my_sounds_block_settings');
        $aProfile = getProfileInfo($profileID);
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
        $soundsParams['browse_mode_url'] = 'album';
        $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_users_sounds_page_caption', $aProfile['NickName']);
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
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
            'browse_mode_url' => 'upcoming',
            'is_public' => true,
        );
        $eventParams['language'] = _t('_bx_events_caption_browse_upcoming');
        $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $eventParams['language']);
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }

    // past events
    function mainEventsPast($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_past_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'past',
            'browse_mode_url' => 'past',
            'is_public' => true,
        );
        $eventParams['language'] = _t('_bx_events_caption_browse_past');
        $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $eventParams['language']);
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
    }

    // recent
    function mainEventsRecent($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_event_block_settings');
        $eventParams = array(
            'browse_mode' => 'recent',
            'browse_mode_url' => 'recent',
            'is_public' => true,
        );
        $eventParams['language'] = _t('_bx_events_caption_browse_recently_added');
        $eventParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $eventParams['language']);
        return $this->getEventDatas($iBlockID, $eventParams, $settings);
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
        $groupsParams['language'] = _t('_bx_groups_page_title_browse_recent');
        $groupsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $groupsParams['language']);
        $groupsParams['browse_mode_url'] = $groupsParams['browse_mode'];
        return $this->getGroupsDatas($iBlockID, $groupsParams, $settings);
    }
    // EOF the main groups page
    // EOF THE GROUPS SERVICE BLOCKS

    // BOF SITES SERVICE BLOCKS
    // bof main page sites
    // main featured sites
    function mainFeaturedSites($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_featured_site_block_settings');
        $siteParams = array( 'browse_mode' => 'featuredshort',);
        $siteParams['browse_mode_url'] = 'featured/';
        $sitesParams['language'] = _t('_bx_sites_caption_browse_featured');
        $siteParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $sitesParams['language']);
        return $this->getSiteDatas($iBlockID, $siteParams, $settings);
    }

    // main recent sites
    function mainRecentSites($iBlockID){
        $settings = $this->getBlockSettings('module_blocks_main_recent_site_block_settings');
        $siteParams = array( 'browse_mode' => 'home',);
        $siteParams['browse_mode_url'] = 'all/';
        $sitesParams['language'] = _t('_bx_sites_caption_public_latest');
        $siteParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $sitesParams['language']);
        return $this->getSiteDatas($iBlockID, $siteParams, $settings);
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($blogsParams['sort_mode']){
            case 'last':
                $blogsParams['language'] = _t('_bx_blog_Latest_posts');
                $blogsParams['browse_mode_url'] = 'all_posts';
                break;
            case 'top':
                $blogsParams['language'] = _t('_bx_blog_Top_Posts');
                $blogsParams['browse_mode_url'] = 'top_posts';
                break;
        }
        $blogsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $blogsParams['language']);
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
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
                    'value' => array(BX_DOL_PG_ALL,),
                ),
            ),
        );
        if(getLoggedId() > 0){
            $photoParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $photoParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($photoParams['sort_mode']){
            case 'last':
                $photoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_photos_page_caption');
                $photoParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $photoParams['language'] = _t('_bx_photos_top');
                $photoParams['browse_mode_url'] = 'top';
                break;
        }
        $photoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $photoParams['language']);
        $photosDatas = $this->getPhotoDatas($iBlockID, $photoParams, $settings);
        if(!$photosDatas){ return false; }
        return array(
            $photosDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/photos/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $photoParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/photos/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $photoParams['sort_mode'], 'dynamic' => true), 
            )
        );
    }
    
    // main favorite photos
    function mainFavoritePhotos($iBlockID, $profileId){
        if($profileId <= 0){ return false; }
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
        $photoParams['browse_mode_url'] = 'favorited';
        $photoParams['language'] = _t('_bx_photos_favorited');
        $photoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $photoParams['language']);
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
        $photoParams['browse_mode_url'] = 'featured';
        $photoParams['language'] = _t('_bx_photos_featured');
        $photoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $photoParams['language']);
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $videosParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($videosParams['sort_mode']){
            case 'last':
                $videosParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_videos_page_caption');
                $videosParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $videosParams['language'] = _t('_bx_videos_top');
                $videosParams['browse_mode_url'] = 'top';
                break;
        }
        $videosParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $videosParams['language']);
        $videosDatas = $this->getVideosDatas($iBlockID, $videosParams, $settings);
        if(!$videosDatas){ return false; }
        return array(
            $videosDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/videos/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $videosParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/videos/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $videosParams['sort_mode'], 'dynamic' => true), 
            ),
        );
        // return $this->getVideosDatas($iBlockID, $videosParams, $settings);
    }
    
    // main favorite videos
    function mainFavoriteVideos($iBlockID, $profileId){
        if($profileId<=0){ return; }
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
        $videoParams['browse_mode_url'] = 'favorited';
        $videoParams['language'] = _t('_bx_videos_favorited');
        $videoParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $videoParams['language']);
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
        $vidoesParams['browse_mode_url'] = 'featured';
        $vidoesParams['language'] = _t('_bx_videos_featured');
        $vidoesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $vidoesParams['language']);
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $soundsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($soundsParams['sort_mode']){
            case 'last':
                $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_sounds_page_caption');
                $soundsParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $soundsParams['language'] = _t('_bx_sounds_top');
                $soundsParams['browse_mode_url'] = 'top';
                break;
        }
        $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $soundsParams['language']);
        $soundsDatas = $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
        if(!$soundsDatas){ return false; }
        return array(
            $soundsDatas, 
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/sounds/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $soundsParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/sounds/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $soundsParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // main favorite sounds 
    function mainFavoriteSounds($iBlockID, $profileId){
        if(getLoggedId() <= 0){ return ''; }
        $settings = $this->getBlockSettings('module_blocks_main_favorite_sounds_block_settings');
        $soundsParams = array(
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
            $soundsParams['restriction']['allow_view']['value'][] = BX_DOL_PG_MEMBERS;
        }
        $soundsParams['browse_mode_url'] = 'favorited';
        $soundsParams['language'] = _t('_bx_sounds_favorited');
        $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $soundsParams['language']);
        return $this->getSoundsDatas($iBlockID, $soundsParams, $settings);
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
        $soundsParams['browse_mode_url'] = 'featured';
        $soundsParams['language'] = _t('_bx_sounds_featured');
        $soundsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $soundsParams['language']);
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($filesParams['sort_mode']){
            case 'last':
                $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_files_page_caption');
                $filesParams['browse_mode_url'] = 'all';
                break;
            case 'popular':
                $filesParams['language'] = _t('_bx_files_top_menu_popular');
                $filesParams['browse_mode_url'] = 'popular';
                break;
        }
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Popular') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=popular', 'active' => 'popular' == $filesParams['sort_mode'], 'dynamic' => true), 
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
        $filesParams['language'] = _t('_bx_files_top');
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesParams['browse_mode_url'] = 'top';
        return $this->getFilesDatas($iBlockID, $filesParams, $settings);
    }
    
    // main favorite files
    function mainFavoriteFiles($iBlockID, $profileId){
        if(getLoggedId() <= 0){ return ''; }
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
        $filesParams['language'] = _t('_bx_files_favorited');
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesParams['browse_mode_url'] = 'favorited';
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
        $filesParams['language'] = _t('_bx_files_featured');
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesParams['browse_mode_url'] = 'featured';
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
    
    // gets the correct url
    protected function getConvertedUrl($url){
        $parseUrl = parse_url($url);
        if(!isset($parseUrl['scheme'])){
            return 'http://' . $url;
        }else{
            return $url;
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
    // getting the thumbnail image
    function getThumbImage($imagePath){
        $imagePath = base64_decode($imagePath);
        header('Cache-Control: private, max-age=10800, pre-check=10800');
        header('Content-type: image/jpeg');
        readfile($imagePath);
    }

    // gets the custom photo datas, based on the photo module
    protected function getCustomPhotoDatas($iId, $sType = 'browse'){
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
    protected function _getImageFullUrl ($aImageInfo, $photosConfig, $sType = 'browse') {
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
        $allowSearch = true;
        $searchDatas = array();
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
                    }
                }
            }
        }
        if($allowSearch){
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
        }
        if(sizeof($searchDatas) <= 0){ return false; }
        return array(
            'search_results' => $searchDatas, 
            'total_entries' => $o->aCurrent['paginate']['totalNum'],
            'search_obj' => $o,
        );
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
    protected function getThumbnailCommonData($searchObj, $resultData, $modVars, $sysModules){
        $commonData = $resultData;
        // getting the files data
        if(isset($searchObj->aConstants) && isset($searchObj->aConstants) && !empty($searchObj->aConstants['filesDir'])){
            $filesDir = $searchObj->aConstants['filesDir'];
            $filesPostfix = (isset($searchObj->aConstants['picPostfix'])) ? ((array_key_exists('browse', (array)$searchObj->aConstants['picPostfix'])) ? $searchObj->aConstants['picPostfix']['browse'] : $searchObj->aConstants['picPostfix']) : '';
        }
        // getting the thumbnails photo
        if(isset($resultData['thumb_photo']) && !empty($resultData['thumb_photo'])){
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_photo'], 'browse');
        }elseif(isset($resultData['thumb_blog_photo']) && !empty($resultData['thumb_blog_photo'])){
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_blog_photo']);
        }elseif(isset($resultData['thumb_file_photo']) && !empty($resultData['thumb_file_photo'])){
            $imageFile = (file_exists($filesDir . $resultData['thumb_file_photo'] . $filesPostfix)) ? $filesDir . $resultData['thumb_file_photo'] . $filesPostfix : $filesDir . $modVars['prim_photo_default_name'];
            $primPhoto = $this->$modVars['prim_photo_function']($resultData['thumb_file_photo'], $searchObj); // gets the file images
        }elseif(isset($resultData['thumb_file_photo_two']) && !empty($resultData['thumb_file_photo_two'])){
            $imageFile = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['path'], true) . $modVars['prim_photo_defaults'];
            $primPhoto = $this->$modVars['prim_photo_function']($imageFile); // gets the file images
        }else{
            $primPhoto=array('path'=>false, 'file'=>$this->oTemplate->getIconUrl($modVars['empty_image']),'width'=>140,'height'=>140); 
        }
        $commonData['thumb_photos'] = $primPhoto;
        $moduleUriDatas = $this->getModuleUriDatas($modVars, $resultData, $sysModules);
        $commonData['module_uri'] = $moduleUriDatas['module_uri'];
        $commonData['module_base_uri'] = $moduleUriDatas['module_base_uri'];
        if(isset($resultData['categories']) && !empty($resultData['categories'])){
            $commonData['categories_suffix'] = str_replace('{nickname}', getNickName($resutData['author_id']), $modVars['categories_link']);
            $commonData['categories'] = $this->parseCategories($commonData);
        }
        if(isset($resultData['tags']) && !empty($resultData['tags'])){
            $commonData['tags_suffix'] = str_replace('{nickname}', getNickName($resutData['author_id']), $modVars['tags_link']);
            $commonData['tags'] = $this->parseTags($commonData);
        }
        $commonData['place'] = (isset($resultData['place']) && !empty($resultData['place'])) ? $resultData['place'] . ', ' : '';
        $commonData['city'] = (isset($resultData['city']) && !empty($resultData['city'])) ? $resultData['city'] . ', ' : '';
        
        return $commonData;
    }

    // getting the module uri datas
    protected function getModuleUriDatas($modVars, $resultData, $sysModules){
        if(isset($modVars['custom_uri']) && !empty($modVars['custom_uri'])){
            $moduleUriDatas['module_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true) . $modVars['view_uri'] . $resultData['entry_uri'];
            $moduleUriDatas['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true);
        }elseif(isset($sysModules['uri']) && !empty($sysModules['uri'])){
            $moduleUriDatas['module_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']) . $modVars['view_uri'] . $resultData['entry_uri'];
            $moduleUriDatas['module_base_uri'] = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']);
        }
        return $moduleUriDatas;
    }

    // getting the module browse uri data
    protected function getModuleBrowseUri($modVars, $sysModules){
        if(isset($modVars['custom_uri']) && !empty($modVars['custom_uri'])){
            $moduleBrowseUri = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($modVars['custom_uri'], true) . $modVars['browse_suffix'] . $modVars['thumbnails_params']['search_params']['browse_mode'];
        }elseif(isset($sysModules['uri']) && !empty($sysModules['uri'])){
            $moduleBrowseUri = BX_DOL_URL_ROOT . $this->getModuleBaseUrl($sysModules['uri']) . $modVars['browse_suffix'];
        }
        return $moduleBrowseUri;
    }

    // getting the primary photo, based on the photo object
    protected function getPrimPhoto($primPhotoId, $type='file'){
        return $this->getCustomPhotoDatas($primPhotoId, $type);
    }

    // getting the blog images
    protected function getPrimBlogPhoto($photoName){
        return array(
            'path' => BX_DIRECTORY_PATH_ROOT . 'media/images/blog/big_' . $photoName,
            'file' => BX_DOL_URL_ROOT . 'media/images/blog/big_' . $photoName,
            'width' => 140,
            'height' => 140,
        );
    }

    // getting the file images
    protected function getPrimFilePhoto($primPhotoId, $searchObj){
        return array(
            'file' => $searchObj->getImgUrl($primPhotoId), 
            'width' => 140,
            'height' => 140
        );
    }

    // getting the file images part two
    protected function getPrimFilePhotoTwo($path){
        return array(
            'file' => $path, 
            'width' => 140,
            'height' => 140
        );
    }

    // gets the settings
    protected function getBlockSettings($name){
        return $this->oDb->getBonConInRealtimeSettings($name);
    }

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
    // EOF the data getters

    // BOF the content getters
    // gets the modules common container
    protected function getModuleCommonContainer($modVars, $extraParams = false){
        $aModule = $this->getModuleArray($modVars);
        if(sizeof($aModule['sys_modules']) <= 0){ return false; }
        if($extraParams){
            if(isset($extraParams['restriction'])){
                $ownerId = $modVars['thumbnails_params']['a_current']['inner_replace']['restriction']['owner']['value'];
                $moduleConfig = $this->getModuleConfig($aModule['sys_modules']);
                $profileInfo = getProfileInfo($ownerId);
                $sCaption = str_replace('{nickname}', $profileInfo['NickName'], $moduleConfig->getGlParam('profile_album_name'));
                $sUri = uriFilter($sCaption);
                $extraRestrictions = array('album' => array('value'=>$sUri, 'field'=>'Uri', 'operator'=>'=', 'paramName'=>'albumUri', 'table'=>'sys_albums'));
                $aModule['mod_vars']['thumbnails_params']['a_current']['inner_replace']['restriction'] = array_merge_recursive($aModule['mod_vars']['thumbnails_params']['a_current']['inner_replace']['restriction'], $extraRestrictions);
                $aModule['mod_vars']['browse_suffix'] = str_replace('{album_uri}', $sUri, $aModule['mod_vars']['album_uri']);
                $aModule['mod_vars']['browse_suffix'] = str_replace('{owner_name}', $profileInfo['NickName'], $aModule['mod_vars']['browse_suffix']);
            }
        }
        $searchResultDatas = $this->getThumnailSearchResults($aModule);
        $aModule['total_entries'] = $searchResultDatas['total_entries'];
        $aModule['num_of_displayed_entries'] = sizeof($searchResultDatas['search_results']);
        $moduleDatas = array( 'mod_datas' => $aModule, 'result_datas' => $searchResultDatas);
        $commonContainer = $this->getCommonMainContainer($moduleDatas);
        return $commonContainer;
    }

    // gets the empty container
    protected function getEmptyContainer(){
        return MsgBox(_t('_Empty'));
    }

    // gets the private container
    protected function getPrivateContainer($commonData, $tData, $modVars, $templateVars=array(), $privateContainerParams=array()){
        $aVars = array(
            'id' => $commonData['id'],
            'entry_dom_id' => 'entry_' . $modVars['block_id'] . '_' . $commonData['id'],
            'caption' => _t('_emmetbytes_bon_con_in_realtime_restricted_access_caption'),
            'spacer' => getTemplateIcon('spacer.gif'),
            'thumbnail_image' => $this->oTemplate->getIconUrl($privateContainerParams['icon_image']),
            'thumbnail_width' => $tData['width'],
            'thumbnail_height' => $tData['height'],
        );
        $aVars = array_merge($aVars, $templateVars);
        return $this->oTemplate->parseHTMLByName('restricted_thumbnail_container', $aVars);
    }

    // gets the private container part two
    protected function getPrivateContainerTwo(){
        return $this->getEmptyContainer();
    }

    // getting the common main container
    protected function getCommonMainContainer($moduleDatas){
        $moduleVars = $moduleDatas['mod_datas'];
        $modVars = $moduleVars['mod_vars'];
        $primKey = $modVars['prim_key'];
        $sysModules = $moduleVars['sys_modules'];
        $iBlockID = $modVars['block_id'];
        $thumbnailsParams = $modVars['thumbnails_params'];
        $thumbnailsColumnsMap = $thumbnailsParams['columns_map'];
        $templateVars = $this->getMainTemplateVars($iBlockID);
        $thumbnailDatas = $resultIds = array();
        $resultDatas = $moduleDatas['result_datas'];
        $searchResults = array();
        $browseUrl = $this->getModuleBrowseUri($modVars, $sysModules);
        if($resultDatas && !isset($resultDatas['content'])){
            $searchResults = $resultDatas['search_results'];
            $searchObj = $resultDatas['search_obj'];
            foreach($searchResults as $key=>$searchResult){
                $resultIds[$key] = $searchResult[$primKey];
                $thumbnailDatas[$key]['container'] = $this->getCommonThumbContainer($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars);
            }
        } 
        $moduleVars['data_ids'] = $resultIds;
        $mcVars = array(
            'goto_page_caption' => $modVars['go_to_page_caption'],
            'goto_page_hidden' => ($modVars['settings']['maximum_numbers_of_datas'] < $moduleVars['total_entries']) ? ''  : 'style="display: none"',
            'goto_page_uri' => $browseUrl,
            'block_id' => $iBlockID,
            'bx_if:hasThumbnailDatas' => array(
                'condition' => (sizeof($thumbnailDatas) > 0) ? true : false,
                'content' => array(
                    'bx_repeat:thumbnail_datas' => $thumbnailDatas, 
                ),
            ),
            'bx_if:noThumbnailDatas' => array(
                'condition' => (sizeof($thumbnailDatas) > 0) ? false : true,
                'content' => array(
                    'empty_container_class' => $templateVars['empty_container_class'],
                    'empty_container' => $this->getEmptyContainer(), 
                ),
            ),
            'script' => $this->getMainJavascript($templateVars, $searchResults, $moduleVars),
        );
        $mcVars = array_merge($mcVars, $templateVars);
        return $this->oTemplate->parseHTMLByName('common_main_container', $mcVars);
    }

    // getting of the template variables
    protected function getMainTemplateVars($iBlockID){
        return array(
            'main_container_class' => 'emmetbytes_bon_con_in_realtime_common_main_container',
            'main_container_id' => 'emmetbytes_bon_con_in_realtime_common_main_container_' . $iBlockID,
            'thumbnails_container_class' => 'emmetbytes_bon_con_in_realtime_common_thumbnails_container',
            'information_container_contents' => 'emmetbytes_bon_con_in_realtime_information_container_contents',
            'information_container_content' => 'emmetbytes_bon_con_in_realtime_information_container_content',
            'information_caption_container' => 'emmetbytes_bon_con_in_realtime_information_caption_container',
            'information_content_container' => 'emmetbytes_bon_con_in_realtime_information_content_container',
            'information_fans_container' => 'emmetbytes_bon_con_in_realtime_information_fans_container',
            'information_restricted_container' => 'emmetbytes_bon_con_in_realtime_information_container_restricted_contents',
            'thumbnails_container_inner_class' => 'emmetbytes_bon_con_in_realtime_common_thumbnails_inner_container',
            'thumb_container_class' => 'emmetbytes_bon_con_in_realtime_common_thumb_container',
            'thumbnail_container_class' => 'emmetbytes_bon_con_in_realtime_common_thumbnail_container',
            'thumbnails_container_new_entry_counter' => 'emmetbytes_bon_con_in_realtime_new_entry_counter',
            'thumbnail_container_inner_class' => 'emmetbytes_bon_con_in_realtime_common_inner_thumbnail_container',
            'thumbnail_inner_container_class' => 'emmetbytes_bon_con_in_realtime_common_thumbnail_inner_container',
            'empty_container_class' => 'emmetbytes_bon_con_in_realtime_common_thumbnail_empty_container',
            'new_entries_caption' => _t('_emmetbytes_bon_con_in_realtime_new_entry_count_caption'),
            'removed_entries_caption' => _t('_emmetbytes_bon_con_in_realtime_removed_entry_count_caption'),
            'thumbnails_container_go_to_page' => 'emmetbytes_bon_con_in_realtime_go_to_page_container',
        );
    }

    // getting the template captions
    protected function getTemplateCaptions(){
        return array(
            'title_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_title_caption'),
            'site_url_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_site_url_caption'),
            'location_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_location_caption'),
            'date_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_date_caption'),
            'categories_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_categories_caption'),
            'tags_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_tags_caption'),
            'comments_count_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_comments_count_caption'),
            'author_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_author_caption'),
            'fans_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_fans_caption'),
            'rate_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_rating_caption'),
            'view_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_view_caption'),
            'size_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_size_caption'),
            'length_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_length_caption'),
            'description_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_description_caption'),
            'contents_caption' => _t('emmetbytes_bon_con_in_realtime_common_info_contents_caption'),
        );
    }

    // getting the common thumbnail container
    protected function getCommonThumbContainer($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars){
        $oldSearchResult = $searchResult;
        $searchResult = $this->getRemappedColumns($searchResult, $thumbnailsColumnsMap, $sysModules);
        $commonData = $this->getThumbnailCommonData($searchObj, $searchResult, $modVars, $sysModules);
        $tData = $commonData['thumb_photos'];
        $settings = $modVars['settings'];
        if(isset($modVars['allow_view_checker']) && !empty($modVars['allow_view_checker'])){
            $viewCheckerParams = $modVars['allow_view_checker'];
            if($viewCheckerParams['obj'] == 'default'){
                $modMain = $searchObj->$viewCheckerParams['mod_main']();
                $params = $oldSearchResult;
                if(!$modMain->$viewCheckerParams['allow_view_checker']($params)){
                    return $this->getPrivateContainer($commonData, $tData, $modVars, $templateVars, $viewCheckerParams['private_container_params']);
                }
            }elseif($viewCheckerParams['obj'] == 'blogs'){
                $modMain = $searchObj->$viewCheckerParams['mod_main']();
                $modMain = $modMain->$viewCheckerParams['privacy_method'];
                $viewCheckerParam = $oldSearchResult;
                if(!$modMain->$viewCheckerParams['allow_view_checker']('view', $viewCheckerParam['id'], getLoggedId())){
                    return $this->getPrivateContainer($commonData, $tData, $modVars, $templateVars, $viewCheckerParams['private_container_params']);
                }
            }
        }
        $templateCaptions = $this->getTemplateCaptions();
        $profileInfo = getProfileInfo($commonData['author_id']);
        if(isset($commonData['description']) && !empty($commonData['description'])){
            // change me later
            $commonData['description'] = strip_tags($commonData['description']);
            if(strlen($commonData['description']) > $settings['max_description_chars']){
                $commonData['description'] = mb_substr($commonData['description'], 0, $settings['max_description_chars']) . '...';
            }
            $commonData['description'] = process_text_withlinks_output($commonData['description']);
        }
        if(isset($commonData['contents']) && !empty($commonData['contents'])){
            // change me later
            $commonData['contents'] = strip_tags($commonData['contents']);
            if(strlen($commonData['contents']) > $settings['max_contents_chars']){
                $commonData['contents'] = mb_substr($commonData['contents'], 0, $settings['max_contents_chars']) . '...';
            }
            $commonData['contents'] = process_text_withlinks_output($commonData['contents']);
        }
        $thumbnailDatas = array(
                'id' => $commonData['id'],
                'entry_dom_id' => 'entry_' . $modVars['block_id'] . '_' . $commonData['id'],
                'spacer' => getTemplateIcon('spacer.gif'),
                'thumbnail_image' => ($tData['path']) ? BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_thumb_image/' . base64_encode($tData['path']) : $tData['file'],
                'thumbnail_width' => $tData['width'],
                'thumbnail_height' => $tData['height'],
                'title_caption' => $templateCaptions['title_caption'],
                'title' => $commonData['title'],
                'module_uri' => $commonData['module_uri'],
                'bx_if:display_site_url' => array(
                    'condition' => ($settings['display_sites_url'] && isset($commonData['site_url']) && !empty($commonData['site_url'])) ? true : false,
                    'content' => array(
                        'url_caption' => $templateCaptions['site_url_caption'],
                        'site_url' => (isset($commonData['site_url'])) ? $this->getConvertedUrl($commonData['site_url']) : '',
                        'url_content' => (isset($commonData['site_url'])) ? $commonData['site_url'] : '',
                        ),
                    ),
                'bx_if:display_location' => array(
                    'condition' => ($settings['display_location'] && isset($commonData['country']) && !empty($commonData['country'])) ? true : false,
                    'content' => array(
                        'location_caption' => $templateCaptions['location_caption'],
                        'location' => (isset($commonData['country'])) ? (genFlag($commonData['country']) . ' ' . $commonData['place'] . $commonData['city'] . _t($GLOBALS['aPreValues']['Country'][$commonData['country']]['LKey'])) : '',
                        ),
                    ),
                'bx_if:display_date' => array(
                        'condition' => ($settings['display_date'] && isset($commonData['entry_start']) && !empty($commonData['entry_start'])) ? true : false,
                        'content' => array(
                            'date_caption' => $templateCaptions['date_caption'],
                            'date_formatted' => (isset($commonData['entry_start'])) ? getLocaleDate($commonData['entry_start'], BX_DOL_LOCALE_DATE_SHORT) : '',
                            'date_ago' => (isset($commonData['entry_start'])) ? defineTimeInterval($commonData['entry_start']) : '',
                            ),
                        ),
                'bx_if:display_categories' => array(
                        'condition' => ($settings['display_categories'] && isset($commonData['categories']) && !empty($commonData['categories'])) ? true : false,
                        'content' => array(
                            'categories_caption' => $templateCaptions['categories_caption'],
                            'categories' => (isset($commonData['categories'])) ? $commonData['categories'] : '',
                            ),
                        ),
                'bx_if:display_tags' => array(
                        'condition' => ($settings['display_tags'] && isset($commonData['tags']) && !empty($commonData['tags'])) ? true : false,
                        'content' => array(
                            'tags_caption' => $templateCaptions['tags_caption'],
                            'tags' => (isset($commonData['tags'])) ? $commonData['tags'] : '',
                            ),
                        ),
                'bx_if:display_comments_count' => array(
                        'condition' => ($settings['display_comments_count'] && isset($commonData['comments_count'])) ? true : false,
                        'content' => array(
                            'comments_count_caption' => $templateCaptions['comments_count_caption'],
                            'comments_count' => (isset($commonData['comments_count'])) ? $commonData['comments_count'] : '',
                            ),
                        ),
                'bx_if:display_author' => array(
                        'condition' => ($settings['display_author'] && isset($commonData['author_id']) && !empty($commonData['author_id'])) ? true : false,
                        'content' => array(
                            'author_caption' => $templateCaptions['author_caption'],
                            'author_username' => getNickName($commonData['author_id']),
                            'author_uri' => getProfileLink($commonData['author_id']),
                            ),
                        ),
                'bx_if:display_fans' => array(
                        'condition' => ($settings['display_fans_count'] && isset($commonData['fans_count'])) ? true : false,
                        'content' => array(
                            'fans_caption' => $templateCaptions['fans_caption'],
                            'fans' => $commonData['fans_count'],
                            ),
                        ),
                'bx_if:display_rate' => array(
                        'condition' => ($settings['display_rating'] && isset($commonData['rate'])) ? true : false,
                        'content' => array(
                            'rate_caption' => $templateCaptions['rate_caption'],
                            'rate' => $this->getVotes($modVars['system'], $commonData['id']),
                            ),
                        ),
                'bx_if:display_view' => array(
                        'condition' => ($settings['display_view'] && isset($commonData['view'])) ? true : false,
                        'content' => array(
                            'view_caption' => $templateCaptions['view_caption'],
                            'view' => (isset($commonData['view'])) ? $commonData['view'] : '',
                            ),
                        ),
                'bx_if:display_size' => array(
                        'condition' => ($settings['display_size'] && isset($commonData['size'])) ? true : false,
                        'content' => array(
                            'size_caption' => $templateCaptions['size_caption'],
                            'size' => (isset($commonData['size'])) ? $commonData['size'] : '',
                            ),
                        ),
                'bx_if:display_length' => array(
                        'condition' => ($settings['display_length'] && isset($commonData['length'])) ? true : false,
                        'content' => array(
                            'length_caption' => $templateCaptions['length_caption'],
                            'length' => (isset($commonData['length'])) ? $this->getSizeToDateConversion($commonData['length']) : '',
                            ),
                        ),
                'bx_if:display_description' => array(
                        'condition' => ($settings['display_description'] && isset($commonData['description']) && !empty($commonData['description'])) ? true : false,
                        'content' => array(
                            'description_caption' => $templateCaptions['description_caption'],
                            'description' => (isset($commonData['description'])) ? $commonData['description'] : '',
                            ),
                        ),
                'bx_if:display_contents' => array(
                        'condition' => ($settings['display_contents'] && isset($commonData['contents']) && !empty($commonData['contents'])) ? true : false,
                        'content' => array(
                            'contents_caption' => $templateCaptions['contents_caption'],
                            'contents' => (isset($commonData['contents'])) ? $commonData['contents'] : '',
                            ),
                        ),
                );
        if(isset($modVars['date_format']) && sizeof($modVars['date_format']) > 0){
            $dateFormat = $modVars['date_format'];
            if($dateFormat['type'] == 'common'){
                $modMain = $searchObj->$dateFormat['parent_module']();
                $thumbnailDatas['bx_if:display_date']['content']['date_formatted'] = $thumbnailDatas['bx_if:display_date']['content']['date_ago'];
                $thumbnailDatas['bx_if:display_date']['content']['date_ago'] = $modMain->$dateFormat['date_format_method']($$dateFormat['parameter']);
                // $thumbnailDatas['bx_if:display_date']['content']['date_formatted'] = '';
            }
        }
        $thumbnailDatas = array_merge($thumbnailDatas, $templateVars, $templateCaptions);
        return $this->oTemplate->parseHTMLByName('common_thumbnail_container', $thumbnailDatas);
    }

    // getting the main javascript code
    protected function getMainJavascript($templateVars, $searchResults, $moduleVars){
        $iBlockID = $moduleVars['mod_vars']['block_id'];
        $emptyContainer = '<div class="'. $templateVars['empty_container_class'] .'">' . $this->getEmptyContainer() . '</div>';
        $emptyContainer = rawurlencode($emptyContainer);
        $privateContainer = '<div class="'. $templateVars['empty_container_class'] .'">' . $this->getPrivateContainerTwo() . '</div>';
        $privateContainer = rawurlencode($privateContainer);
        $jsVars = array(
            'get_new_entries_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_new_entries',
            'block_id' => $iBlockID,
            'module_vars' => (!is_null($moduleVars)) ? json_encode($moduleVars) : '',
            'empty_container' => $emptyContainer,
            'private_container' => $privateContainer,
            'empty_container_class' => $templateVars['empty_container_class'],
            'display_show_new_button_value' => ($moduleVars['mod_vars']['settings']['fetch_type'] == 'automatic') ? 'false' : 'true',
            'search_results' => json_encode($searchResults),
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
            'empty_image' => 'no-photo.png',
            'browse_suffix' => 'browse/' . $eventParams['browse_mode_url'],
            'system' => 'bx_events',
            'prim_key' => 'ID',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'ID',
                    'title' => 'Title',
                    'entry_uri' => 'EntryUri',
                    'country' => 'Country',
                    'city' => 'City',
                    'place' => 'Place',
                    'entry_start' => 'EventStart',
                    'author_id' => 'ResponsibleID',
                    'thumb_photo' => 'PrimPhoto',
                    'fans_count' => 'FansCount',
                    'rate' => 'Rate',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($eventParams['browse_mode'])) ? $eventParams['browse_mode']: '',
                    'param1' => (isset($eventParams['param1'])) ? $eventParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'], 
                        ),
                        'restriction' => array(
                            'categories' => array('value' => "(SELECT ID FROM `sys_categories` WHERE `type` = 'bx_events')", 'no_quote_value' => true, 'field'=>'ID', 'operator'=>'IN'),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'ID',
                            'Title',
                            'EntryUri',
                            'Country',
                            'City',
                            'Place',
                            'EventStart',
                            'ResponsibleID',
                            'PrimPhoto',
                            'FansCount',
                            'Rate',
                            'allow_view_event_to',
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
            'go_to_page_caption' => $eventParams['language'],
            'date_format' => '',
        );
        if(isset($eventParams['allow_view_checker']) && !empty($eventParams['allow_view_checker'])){
            $eventModuleVars['allow_view_checker'] = array(
                'obj' => 'default',
                'mod_main' => 'getMain',
                'allow_view_checker' => 'isAllowedView',
                'param' => 'oldSearchResult',
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
            'empty_image' => 'no-photo.png',
            'browse_suffix' => 'browse/' . $groupsParams['browse_mode_url'],
            'system' => 'bx_groups',
            'prim_key' => 'id',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'thumb_photo' => 'thumb',
                    'author_id' => 'author_id',
                    'entry_start' => 'created',
                    'rate' => 'rate',
                    'fans_count' => 'fans_count',
                    'country' => 'country',
                    'city' => 'city'
                ),
                'search_params' => array(
                    'browse_mode' => (isset($groupsParams['browse_mode'])) ? $groupsParams['browse_mode']: '',
                    'param1' => (isset($groupsParams['param1'])) ? $groupsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'], 
                        ),
                        'restriction' => array(
                            'tags' => array('value' => "(SELECT ObjID FROM `sys_tags` WHERE `type` = 'bx_groups')", 'no_quote_value' => true, 'field'=>'ID', 'operator'=>'IN'),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'id',
                            'title',
                            'uri',
                            'created',
                            'author_id',
                            'thumb',
                            'rate',
                            'fans_count',
                            'country',
                            'city',
                            'allow_view_group_to',
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
            'go_to_page_caption' => $groupsParams['language'],
        );
        if(isset($groupsParams['allow_view_checker']) && !empty($groupsParams['allow_view_checker'])){
            $groupsModuleVars['allow_view_checker'] = array(
                'obj' => 'default',
                'mod_main' => 'getMain',
                'allow_view_checker' => 'isAllowedView',
                'param' => 'oldSearchResult',
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
            'empty_image' => 'no-photo.png',
            'class_prefix' => 'BxSites' ,
            'class_suffix' => 'SearchResult',
            'class_name' => 'BxSitesSearchResult',
            'view_uri' => 'view/',
            'browse_suffix' => 'browse/' . $siteParams['browse_mode_url'],
            'system' => 'bx_sites',
            'prim_key' => 'id',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'thumb_photo' => 'photo',
                    'title' => 'title',
                    'site_url' => 'url',
                    'author_id' => 'ownerId',
                    'entry_start' => 'date',
                    'entry_uri' => 'entryUri',
                    'rate' => 'rate',
                    'tags' => 'tags',
                    'categories' => 'categories',
                    'description' => 'description',
                    'comments_count' => 'commentsCount',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($siteParams['browse_mode'])) ? $siteParams['browse_mode']: '',
                    'param1' => (isset($siteParams['param1'])) ? $siteParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'], 
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
                            'url',
                            'title',
                            'entryUri',
                            'description',
                            'photo',
                            'commentsCount',
                            'date',
                            'ownerId',
                            'categories',
                            'tags',
                            'rate',
                            'allowView',
                        ),
                    ),
                ),
            ),
            'prim_photo_function' => 'getPrimPhoto',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($siteParams['settings_name']),
            'go_to_page_caption' => $siteParams['language'],
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
            'empty_image' => 'no-photo.png',
            'system' => 'blogposts',
            'browse_suffix' => $blogsParams['browse_mode_url'],
            'prim_key' => 'id',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'thumb_blog_photo' => 'PostPhoto',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'author_id' => 'ownerId',
                    'entry_start' => 'date',
                    'rate' => 'Rate',
                    'tags' => 'tag',
                    'categories' => 'Categories',
                    'comments_count' => 'CommentsCount',
                    'allow_view' => 'allowView',
                    'contents' => 'bodyText',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($blogsParams['browse_mode'])) ? $blogsParams['browse_mode']: '',
                    'param1' => (isset($blogsParams['param1'])) ? $blogsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'], 
                        ),
                        'restriction' => array(
                            'category_check' => array('value' => "(SELECT ID FROM `sys_categories` WHERE `type` = 'bx_blogs')", 'no_quote_value' => true, 'field'=>'PostID', 'operator'=>'IN'),
                        ),
                    ),
                    'replace_all' => array(
                        'ownFields' => array(
                            'PostID',
                            'PostCaption',
                            'PostUri',
                            'PostDate',
                            'PostText',
                            'Tags',
                            'PostPhoto',
                            'PostStatus',
                            'Rate',
                            'RateCount',
                            'CommentsCount',
                            'Categories',
                            'allowView'
                        ),
                        'sorting' => (!empty($blogsParams['sort_mode'])) ? $blogsParams['sort_mode'] : '', 
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
            'go_to_page_caption' => $blogsParams['language'],
        );
        if(isset($blogsParams['restriction']) && !empty($blogsParams['restriction'])){
            $blogsModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'] = array_merge($blogsModuleVars['thumbnails_params']['a_current']['inner_replace']['restriction'], $blogsParams['restriction']);
        }
        if(isset($blogsParams['allow_view_checker']) && !empty($blogsParams['allow_view_checker'])){
            $blogsModuleVars['allow_view_checker'] = array(
                'obj' => 'blogs',
                'mod_main' => 'getBlogsMain',
                'privacy_method' => 'oPrivacy',
                'allow_view_checker' => 'check',
                'param' => 'oldSearchResult',
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
            'empty_image' => 'no-photo.png',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_photos',
            'browse_suffix' => 'browse/' . $photoParams['browse_mode_url'],
            'prim_key' => 'id',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'thumb_photo' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'author_id' => 'ownerId',
                    'entry_start' => 'date',
                    'rate' => 'Rate',
                    'size' => 'size',
                    'view' => 'view',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($photoParams['browse_mode'])) ? $photoParams['browse_mode']: '',
                    'param1' => (isset($photoParams['param1'])) ? $photoParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'], 
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($photoParams['sort_mode'])) ? $photoParams['sort_mode'] : '', 
                    ),
                ),
                'additional_checker' => array(
                    'method' => 'checkPhotoValidity',
                    'param' => 'entryResult',
                ),
            ),
            'prim_photo_function' => 'getPrimPhoto',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($photoParams['settings_name']),
            'go_to_page_caption' => $photoParams['language'],
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
        unset($photoParams);
        return $photoModuleVars;
    }

    // validating the to be displayed photos
    protected function checkPhotoValidity($entry, $sType='browse'){
        $photoConfig = $this->_getPhotosConfig();
        $path = $photoConfig['filesPath'] . $entry['id'] . $photoConfig['picPostfix'][$sType];
        if(is_file($path)){
            return true;
        }else{
            return false;
        }
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
            'empty_image' => 'no-photo.png',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_videos',
            'browse_suffix' => 'browse/' . $videosParams['browse_mode_url'],
            'prim_key' => 'id',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'thumb_file_photo' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'author_id' => 'ownerId',
                    'entry_start' => 'date',
                    'rate' => 'Rate',
                    'length' => 'size',
                    'view' => 'view',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($videosParams['browse_mode'])) ? $videosParams['browse_mode']: '',
                    'param1' => (isset($videosParams['param1'])) ? $videosParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'],
                        ),
                    ),
                    'replace_all' => array(
                        'sorting' => (!empty($videosParams['sort_mode'])) ? $videosParams['sort_mode'] : '', 
                    ),
                ),
                'additional_checker' => array(
                    'method' => 'checkVideoValidity',
                    'param' => 'entryResult',
                ),
            ),
            'prim_photo_function' => 'getPrimFilePhoto',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'settings' => (sizeof($settings) > 0) ? $settings : $this->getBlockSettings($videosParams['settings_name']),
            'go_to_page_caption' => $videosParams['language'],
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
    
    // checking for the validity of the video
    protected function checkVideoValidity($entryResult){
        return true;
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
            'empty_image' => 'no-photo.png',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_sounds',
            'browse_suffix' => 'browse/' . $soundsParams['browse_mode_url'],
            'prim_key' => 'id',
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'entry_uri' => 'uri',
                    'entry_start' => 'date',
                    'view' => 'view',
                    'rate' => 'Rate',
                    'author_id' => 'ownerId',
                    'thumb_file_photo' => 'id',
                    'length' => 'size',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($soundsParams['browse_mode'])) ? $soundsParams['browse_mode']: '',
                    'param1' => (isset($soundsParams['param1'])) ? $soundsParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'],
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
            'prim_photo_function' => 'getPrimFilePhoto',
            'prim_photo_default_name' => 'default.png',
            'block_id' => $iBlockID,
            'tags_link' => 'browse/tag/',
            'categories_link' => 'browse/category/',
            'go_to_page_caption' => $soundsParams['language'],
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
            'empty_image' => 'no-photo.png',
            'album_uri' => 'browse/album/{album_uri}/owner/{owner_name}',
            'system' => 'bx_files',
            'browse_suffix' => 'browse/' . $filesParams['browse_mode_url'],
            'thumbnails_params' => array(
                'columns_map' => array(
                    'id' => 'id',
                    'title' => 'title',
                    'author_id' => 'ownerId',
                    'entry_uri' => 'uri',
                    'entry_start' => 'date',
                    'view' => 'view',
                    'rate' => 'Rate',
                    'description' => 'desc',
                    'thumb_file_photo_two' => 'id',
                    'size' => 'size',
                ),
                'search_params' => array(
                    'browse_mode' => (isset($filesParams['browse_mode'])) ? $filesParams['browse_mode']: '',
                    'param1' => (isset($filesParams['param1'])) ? $filesParams['param1']: '',
                ),
                'a_current' => array(
                    'inner_replace' => array(
                        'paginate' => array(
                            'page' => 1,
                            'perPage' => $settings['maximum_numbers_of_datas'], 
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
            'go_to_page_caption' => $filesParams['language'],
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
        if(!isset($GLOBALS['ebBonConInRealtimeCss'])){
            $this->oTemplate->addCss('main.css');
            $GLOBALS['ebBonConInRealtimeCss'] = true;
        }
    }

    // add the javascript
    protected function addAllJs(){
        if(!isset($GLOBALS['ebBonConInRealtimeJs'])){
            $this->oTemplate->addJs('EmmetBytesBonConInRealtime.js');
            $this->oTemplate->addJs('json2.js');
            $GLOBALS['ebBonConInRealtimeJs'] = true;
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


// FOR DOLPHIN 7.0.7 - 7.0.9
class EmmetBytesBonConInRealtimeD707UpHelper extends EmmetBytesBonConInRealtimeDefaultHelper{

    // constructor
    function EmmetBytesBonConInRealtimeD707UpHelper($oMain){
        parent::EmmetBytesBonConInRealtimeDefaultHelper($oMain);
    }

    // override blogs block helper
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($blogsParams['sort_mode']){
            case 'last':
                $blogsParams['language'] = _t('_bx_blog_Latest_posts');
                $blogsParams['browse_mode_url'] = 'all_posts';
                break;
            case 'top':
                $blogsParams['language'] = _t('_bx_blog_Top_Posts');
                $blogsParams['browse_mode_url'] = 'top_posts';
                break;
        }
        $blogsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $blogsParams['language']);
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    // blogs module variables
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = parent::getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $blogsModuleVars['system'] = 'bx_blogs';
        $blogsModuleVars['check_for_restriction'] = false;
        return $blogsModuleVars;
    }
}

// for dolphin 7.1.0 to latest version
class EmmetBytesBonConInRealtimeD710UpHelper extends EmmetBytesBonConInRealtimeDefaultHelper{

    // CONSTRUCTOR
    function EmmetBytesBonConInRealtimeD710UpHelper($oMain){
        parent::EmmetBytesBonConInRealtimeDefaultHelper($oMain);
    }

    // override the homepage files block helper
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($filesParams['sort_mode']){
            case 'last':
                $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_files_page_caption');
                $filesParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $filesParams['language'] = _t('_bx_files_top_menu_top');
                $filesParams['browse_mode_url'] = 'top';
                break;
        }
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . '?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // override main public files
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $filesParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($filesParams['sort_mode']){
            case 'last':
                $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_latest_files_page_caption');
                $filesParams['browse_mode_url'] = 'all';
                break;
            case 'top':
                $filesParams['language'] = _t('_bx_files_top_menu_top');
                $filesParams['browse_mode_url'] = 'top';
                break;
        }
        $filesParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $filesParams['language']);
        $filesDatas = $this->getFilesDatas($iBlockID, $filesParams, $settings);
        if(!$filesDatas){ return false; }
        return array(
            $filesDatas,
            array(
                _t('_Latest') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=last', 'active' => 'last' == $filesParams['sort_mode'], 'dynamic' => true),
                _t('_Top') => array('href' => BX_DOL_URL_ROOT . 'm/files/home?emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter=top', 'active' => 'top' == $filesParams['sort_mode'], 'dynamic' => true), 
            ),
        );
    }

    // override blogs block helper
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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        $blogsParams['language'] = _t('_bx_blog_Latest_posts');
        $blogsParams['browse_mode_url'] = 'all_posts';
        $blogsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $blogsParams['language']);
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    // getting new entries
    function getNewEntriesResponse(){
        $moduleVars = json_decode($_POST['moduleVars'], true);
        $oldSearchResults = json_decode($_POST['searchResults'], true);
        $modVars = $moduleVars['mod_vars'];
        $sysModules = $moduleVars['sys_modules'];
        $iBlockID = $modVars['block_id'];
        $oldDataIds = $dataIds = $moduleVars['data_ids'];
        $templateVars = $this->getMainTemplateVars($iBlockID);
        $primKey = $modVars['prim_key'];
        $thumbnailsColumnsMap = $modVars['thumbnails_params']['columns_map'];
        $searchResultDatas = $this->getThumnailSearchResults($moduleVars);
        $searchResults = ($searchResultDatas['search_results']) ? $searchResultDatas['search_results'] : array();
        $hasAdditionalChecker = false;
        if(isset($modVars['thumbnails_params']['additional_checker'])){
            $hasAdditionalChecker = true;
            $additionalCheckerMethod = $modVars['thumbnails_params']['additional_checker']['method'];
            $additionalCheckerParams = $modVars['thumbnails_params']['additional_checker']['param'];
        }
        $searchObj = $searchResultDatas['search_obj'];
        $searchResultsIds = $refreshedEntries = $additionalEntriesIds = $sortableEntries = $additionalEntries = $steadyEntries = array();
        foreach($searchResults as $key=>$value){
            // additional checker/verifier
            if($hasAdditionalChecker){
                if($additionalCheckerParams == 'entryResult'){
                    $isValid = $this->$additionalCheckerMethod($value);
                    if(!$isValid){ continue; }
                }
            }
            // getting the search result ids
            $searchResultsIds[] = $value[$primKey];
            $cmpIdKey = array_search($value[$primKey], $dataIds);
            $oldKeyId = array_search($value[$primKey], $oldDataIds);// getting the old key id
            $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars);
            if(($cmpIdKey || $cmpIdKey===0)){
                if($key!=$cmpIdKey){
                    $dataIds[$cmpIdKey] = (isset($dataIds[$key])) ? $dataIds[$key] : 0;
                    $dataIds[$key] = $value[$primKey];
                    $sortableEntries[] = array(
                        'after' => ($key<=0) ? 'first' : $searchResults[$key-1][$primKey],
                        'changed' => (($value[$primKey]==$oldSearchResults[$oldKeyId][$primKey]) && ($oldSearchResults[$oldKeyId] != $value)) ? 'true' : 'false',
                        'id' => $value[$primKey],
                        'thumbnailContainer' => $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars),
                    );
                }else{
                    if(isset($oldSearchResults[$oldKeyId]) && $value!=$oldSearchResults[$oldKeyId]){
                        $sortableEntries[] = array(
                            'after' => ($key<=0) ? 'first' : $searchResults[$key-1][$primKey],
                            'changed' => 'true',
                            'id' => $value[$primKey],
                            'thumbnailContainer' => $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars),
                        );
                    }else{
                        $steadyEntries[] = $value[$primKey];
                    }
                }
            }else{
                $additionalEntries[] = array(
                    'current_id' => $value[$primKey],
                    'before' => isset($searchResults[$key+1]) ? $searchResults[$key+1][$primKey] : 'last',
                    'thumbnailContainer' => $this->getCommonThumbContainer($searchObj, $value, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars),
                );
            }
        }
        $removableEntries = array_values(array_diff($dataIds,$searchResultsIds));
        $additionalEntries = array_reverse($additionalEntries);
        $returnVals = array(
            'total_entries' => $searchResultDatas['total_entries'],
            'steady_entries' => $steadyEntries,
            'sortable_entries' => $sortableEntries,
            'refreshed_entries' => $refreshedEntries,
            'new_data_ids' => $searchResultsIds, 
            'removable_entries' => $removableEntries,
            'additional_entries' => $additionalEntries,
            'additional_entries_count' => sizeof($additionalEntries),
            'removable_entries_count' => sizeof($removableEntries),
            'search_result_datas' => $searchResults,
            'is_private' => (isset($searchResultDatas['content']) && !empty($searchResultDatas['content'])) ? 'true' : 'false',
        );
        return json_encode($returnVals);
    }

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
        if(isset($_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'])){
           $blogsParams['sort_mode'] = $_GET['emmetbytes_bon_con_in_realtime_'.$iBlockID.'_sort_filter'];
        }        
        switch($blogsParams['sort_mode']){
            case 'last':
                $blogsParams['language'] = _t('_bx_blog_Latest_posts');
                $blogsParams['browse_mode_url'] = 'all_posts';
                break;
            case 'top':
                $blogsParams['language'] = _t('_bx_blog_Top_Posts');
                $blogsParams['browse_mode_url'] = 'top_posts';
                break;
        }
        $blogsParams['language'] = _t('_emmetbytes_bon_con_in_realtime_go_to_page_caption', $blogsParams['language']);
        $blogsDatas = $this->getBlogsDatas($iBlockID, $blogsParams, $settings);
        if(!$blogsDatas){ return false; }
        return array(
            $blogsDatas,
            array(
            ),
        );
    }

    protected function getThumnailSearchResults($aModule, $getMoreEntries = false){
        $modVars = $aModule['mod_vars'];
        $thumbnailsParams = $modVars['thumbnails_params'];
        $allowSearch = true;
        $searchDatas = array();
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
        if($allowSearch){
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
        }
        if(sizeof($searchDatas) <= 0){ return false; }
        return array(
            'search_results' => $searchDatas, 
            'total_entries' => $o->aCurrent['paginate']['totalNum'],
            'search_obj' => $o,
        );
    }

    // gets the private container part two
    protected function getPrivateContainerTwo(){
        return MsgBox(_t('_sys_album_private'));
    }

    // getting the common main container
    protected function getCommonMainContainer($moduleDatas){
        $moduleVars = $moduleDatas['mod_datas'];
        $modVars = $moduleVars['mod_vars'];
        $primKey = $modVars['prim_key'];
        $sysModules = $moduleVars['sys_modules'];
        $iBlockID = $modVars['block_id'];
        $thumbnailsParams = $modVars['thumbnails_params'];
        $thumbnailsColumnsMap = $thumbnailsParams['columns_map'];
        $templateVars = $this->getMainTemplateVars($iBlockID);
        $thumbnailDatas = $resultIds = array();
        $resultDatas = $moduleDatas['result_datas'];
        $searchResults = array();
        $browseUrl = $this->getModuleBrowseUri($modVars, $sysModules);
        if($resultDatas && !isset($resultDatas['content'])){
            $searchResults = $resultDatas['search_results'];
            $searchObj = $resultDatas['search_obj'];
            foreach($searchResults as $key=>$searchResult){
                $resultIds[$key] = $searchResult[$primKey];
                $thumbnailDatas[$key]['container'] = $this->getCommonThumbContainer($searchObj, $searchResult, $modVars, $sysModules, $thumbnailsColumnsMap, $templateVars);
            }
        } 
        $moduleVars['data_ids'] = $resultIds;
        $mcVars = array(
            'goto_page_caption' => $modVars['go_to_page_caption'],
            'goto_page_hidden' => ($modVars['settings']['maximum_numbers_of_datas'] < $moduleVars['total_entries']) ? ''  : 'style="display: none"',
            'goto_page_uri' => $browseUrl,
            'block_id' => $iBlockID,
            'bx_if:hasThumbnailDatas' => array(
                'condition' => (sizeof($thumbnailDatas) > 0) ? true : false,
                'content' => array(
                    'bx_repeat:thumbnail_datas' => $thumbnailDatas, 
                ),
            ),
            'bx_if:noThumbnailDatas' => array(
                'condition' => (sizeof($thumbnailDatas) > 0) ? false : true,
                'content' => array(
                    'empty_container_class' => $templateVars['empty_container_class'],
                    'empty_container' => (!empty($resultDatas['content'])) ? $resultDatas['content'] : $this->getEmptyContainer(), 
                ),
            ),
            'script' => $this->getMainJavascript($templateVars, $searchResults, $moduleVars),
        );
        $mcVars = array_merge($mcVars, $templateVars);
        return $this->oTemplate->parseHTMLByName('common_main_container', $mcVars);
    }

    // event module variables
    protected function getEventModuleVars($iBlockID, $eventParams, $settings = array()){
        $eventModuleVars = parent::getEventModuleVars($iBlockID, $eventParams, $settings);
        $eventModuleVars['empty_image'] = 'no-image-thumb-events.png';
        $eventModuleVars['date_format'] = array(
            'type' => 'common',
            'parent_module' => 'getMain',
            'date_format_method' => '_formatDateInBrowse',
            'parameter' => 'oldSearchResult',
        );
        if(isset($eventParams['allow_view_checker']) && !empty($eventParams['allow_view_checker'])){
            $eventModuleVars['allow_view_checker']['private_container_params'] = array(
                'icon_image' => 'no-image-thumb-events.png',
            );
        }
        $replaceOwnFieldsAddon = array(
            'EventEnd', 
            'Description'
        );
        $eventModuleVars['thumbnails_params']['a_current']['replace_all']['ownFields'] = array_merge($eventModuleVars['thumbnails_params']['a_current']['replace_all']['ownFields'], $replaceOwnFieldsAddon);
        return $eventModuleVars;
    }

    // getting the groups module variables
    protected function getGroupsModuleVars($iBlockID, $groupsParams, $settings=array()){
        $groupsModuleVars = parent::getGroupsModuleVars($iBlockID, $groupsParams, $settings);
        $groupsModuleVars['empty_image'] = 'no-image-thumb-groups.png';
        if(isset($groupsParams['allow_view_checker']) && !empty($groupsParams['allow_view_checker'])){
            $groupsModuleVars['allow_view_checker']['private_container_params'] = array(
                'icon_image' => 'no-image-thumb-groups.png',
            );
        } 
        return $groupsModuleVars;
    }

    // sites module variables
    protected function getSitesModuleVars($iBlockID, $siteParams, $settings){
        $sitesModuleVars = parent::getSitesModuleVars($iBlockID, $siteParams, $settings);
        $sitesModuleVars['empty_image'] = 'no-image-thumb-sites.png';
        return $sitesModuleVars;
    }

    // blogs module variables
    protected function getBlogsModuleVars($iBlockID, $blogsParams, $settings=array()){
        $blogsModuleVars = parent::getBlogsModuleVars($iBlockID, $blogsParams, $settings);
        $blogsModuleVars['empty_image'] = 'no-image-thumb-blogs.png';
        $blogsModuleVars['system'] = 'bx_blogs';
        $blogsModuleVars['check_for_restriction'] = false;
        return $blogsModuleVars;
    }

    // photo module variables
    protected function getPhotoModuleVars($iBlockID, $photoParams, $settings=array()){
        $photoModuleVars = parent::getPhotoModuleVars($iBlockID, $photoParams, $settings);
        $photoModuleVars['empty_image'] = 'no-image-thumb-photos.png';
        return $photoModuleVars;
    }

    // videos module varialbles
    protected function getVideosModuleVars($iBlockID, $videosParams, $settings=array()){
        $videosModuleVars = parent::getVideosModuleVars($iBlockID, $videosParams, $settings);
        $videosModuleVars['empty_image'] = 'no-image-thumb-videos.png';
        return $videosModuleVars;
    }

    // sound module variables
    protected function getSoundsModuleVars($iBlockID, $soundsParams, $settings=array()){
        $soundsModuleVars = parent::getSoundsModuleVars($iBlockID, $soundsParams, $settings);
        $soundsModuleVars['empty_image'] = 'no-image-thumb-sounds.png';
        return $soundsModuleVars;
    }

    // files module variables
    protected function getFilesModuleVars($iBlockID, $filesParams, $settings){
        $filesModuleVars = parent::getFilesModuleVars($iBlockID, $filesParams, $settings);
        $filesModuleVars['empty_image'] = 'no-image-thumb-files.png';
        return $filesModuleVars;
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
