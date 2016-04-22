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
bx_import ('BxDolFormMedia');
class EmmetBytesBonConInRealtimeAdministrationsHelper{
    var $boonexVersion, $oMain;
    // CONSTRUCTOR
    function EmmetBytesBonConInRealtimeAdministrationsHelper($oMain){
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        if($this->boonexVersion >= '7.1.0'){
            $this->helperObj = new EmmetBytesBonConInRealtimeAdministrationsd710UpHelper($oMain);
        }else{
            $this->helperObj = new EmmetBytesBonConInRealtimeAdministrationsDefaultHelper($oMain);
        }
    }
}

class EmmetBytesBonConInRealtimeAdministrationsDefaultHelper extends BxDolFormMedia{
    
    function EmmetBytesBonConInRealtimeAdministrationsDefaultHelper($oMain){
        $this->_oMain = $oMain;
        $this->oDb = $oMain->_oDb;
    }

    // GETTING THE HOMEPAGE SETTINGS
    function getHomepageSettings(){
        // event tabs
        $defaultEventTabDatas = array(
            'upcoming' => _t('_bx_events_tab_upcoming'),
            'featured' => _t('_bx_events_tab_featured'),
            'recent' => _t('_bx_events_tab_recent'),
            'top' => _t('_bx_events_tab_top'),
            'popular' => _t('_bx_events_tab_popular'),
        );
        $homePageSettingsContent = $this->getEventForm('homepage', $defaultEventTabDatas);
        // groups tabs
        $defaultGroupTabDatas = array(
            'featured' => _t('_bx_groups_tab_featured'),
            'recent' => _t('_bx_groups_tab_recent'),
            'top' => _t('_bx_groups_tab_top'),
            'popular' => _t('_bx_groups_tab_popular'),
        );
        $homePageSettingsContent .= $this->getGroupsForm('homepage', $defaultGroupTabDatas);
        // sites tabs
        $homePageSettingsContent .= $this->getSitesForm('homepage');
        // blogs tabs
        $defaultBlogsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getBlogsForm('homepage', $defaultBlogsTabDatas);
        // photos form
        $defaultPhotosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getPhotosForm('homepage', $defaultPhotosTabDatas);
        // videos form
        $defaultVideosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getVideosForm('homepage', $defaultVideosTabDatas);
        // sounds form
        $defaultSoundsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getSoundsForm('homepage', $defaultSoundsTabDatas);
        // files form
        $defaultFilesTabDatas = array(
            'last' => _t('_Latest'),
            'popular' => _t('_Popular'),
        );
        $homePageSettingsContent .= $this->getFilesForm('homepage', $defaultFilesTabDatas);
        return $homePageSettingsContent;
    }

    // GETTING THE PROFILE SETTINGS
    function getProfileSettings(){
        // events tabs
        $profileSettingsContent = $this->getEventForm('profile_my');
        $profileSettingsContent .= $this->getEventForm('profile_joined');
        // groups tabs
        $profileSettingsContent .= $this->getGroupsForm('profile_my');
        $profileSettingsContent .= $this->getGroupsForm('profile_joined');
        // sites tabs
        $profileSettingsContent .= $this->getSitesForm('profile_my');
        // blogs tabs
        $profileSettingsContent .= $this->getBlogsForm('profile_my');
        // photos form
        $profileSettingsContent .= $this->getPhotosForm('profile_my');
        // videos form
        $profileSettingsContent .= $this->getVideosForm('profile_my');
        // sounds form
        $profileSettingsContent .= $this->getSoundsForm('profile_my');
        return $profileSettingsContent;
    }

    // GETTING THE MODULE BLOCKS SETTINGS
    function getModuleBlocksSettings(){
        // event tabs
        $moduleBlocksSettingsContent = $this->getEventForm('module_blocks_main_upcoming');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_past');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_recent');
        // groups tabs
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_main_recent');
        // sites tabs
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_featured');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_recent');
        // blogs tabs
        // blogs tabs
        $defaultBlogsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getBlogsForm('module_blocks_main_latest', $defaultBlogsTabDatas);
        // photos form
        $defaultPhotosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getPhotosForm('module_blocks_main_public', $defaultPhotosTabDatas);
        $moduleBlocksSettingsContent .= $this->getPhotosForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getPhotosForm('module_blocks_main_featured');
        // videos form
        $defaultVideosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        // videos form
        $moduleBlocksSettingsContent .= $this->getVideosForm('module_blocks_main_public', $defaultVideosTabDatas);
        $moduleBlocksSettingsContent .= $this->getVideosForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getVideosForm('module_blocks_main_featured');
        // sounds form
        $defaultSoundsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getSoundsForm('module_blocks_main_public', $defaultSoundsTabDatas);
        $moduleBlocksSettingsContent .= $this->getSoundsForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getSoundsForm('module_blocks_main_featured');
        // files form
        $defaultFilesTabDatas = array(
            'last' => _t('_Latest'),
            'popular' => _t('_Popular'),
        );
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_public', $defaultFilesTabDatas);
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_top');
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_featured');
        return $moduleBlocksSettingsContent;
    }

    // getting the form attributes
    protected function getFormAttrs($formName){
        $formAttrs['name'] = $formName;
        $formAttrs['action']  = '';
        $formAttrs['method']   = 'post';
        $formAttrs['enctype'] = 'multipart/form-data';
        return $formAttrs;
    }

    // form header block
    protected function getFormInputHeaderBlock($caption, $collapsable = true, $collapsed = true){
        $input['type'] = 'block_header';
        $input['caption'] = $caption;
        $input['collapsable'] = $collapsable;
        $input['collapsed'] = $collapsed;
        return $input;
    }

    // form input
    protected function getFormInput($name, $caption, $value='', $values='', $type='text', $required=false){
        $input['type'] = $type;
        $input['name'] = $name;
        $input['caption'] = $caption;
        $input['value'] = $value;
        $input['required'] = $required;
        $input['db'] = array ('pass' => 'Xss');
        $input['display'] = true;

        if($type=='checkbox' && $value == 1){ $input['checked']  = true; }

        if($type=='select'){ 
            $input['values']  = $values; 
        }
        return $input;
    }

    // GETTING THE EVENT FORM DATAS
    function getEventForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_event_button'])){
            $this->insertFormInputValues();
        }
        $settingsName = $type.'_event_block_settings';
        $eventFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $eventForm['form_attrs'] = $this->getFormAttrs($type . '_form_events');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_event_form_caption');// change me later
        $eventForm['inputs']['event_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);// change me later
        // input name captions
        $inputNameCaption = '';
        $eventForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $eventForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $eventFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $eventForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $eventFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayMaxNumberOfDatasCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $eventForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayMaxNumberOfDatasCaption, $eventFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $eventForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $eventFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $eventForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $eventFormDatas['display_date'], '', 'checkbox');
        // display the location
        $displayLocationCaption = _t('_emmetbytes_bon_con_in_realtime_display_location_caption');
        $eventForm['inputs']['display_location'] = $this->getFormInput('display_location', $displayLocationCaption, $eventFormDatas['display_location'], '', 'checkbox');
        // display the fans_count
        $displayFansCountCaption = _t('_emmetbytes_bon_con_in_realtime_display_fans_count_caption');
        $eventForm['inputs']['display_fans_count'] = $this->getFormInput('display_fans_count', $displayFansCountCaption, $eventFormDatas['display_fans_count'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $eventForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $eventFormDatas['display_rating'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $eventForm['inputs'][$type.'admin_submit_event_button'] = $this->getFormInput($type.'admin_submit_event_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($eventForm);
        return $this->getCode();
    }

    // GETTING THE GROUPS FORM DATAS
    protected function getGroupsForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_group_button'])){
            $this->insertFormInputValues();
        }

        $settingsName = $type.'_group_block_settings';
        $groupFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $groupForm['form_attrs'] = $this->getFormAttrs($type . '_form_groups');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_group_form_caption');// change me later
        $groupForm['inputs']['group_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $groupForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $groupForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $groupFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $groupForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $groupFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $groupForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $groupFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $groupForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $groupFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $groupForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $groupFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $groupForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $groupFormDatas['display_rating'], '', 'checkbox');
        // display the fans_count
        $displayFansCountCaption = _t('_emmetbytes_bon_con_in_realtime_display_fans_count_caption');
        $groupForm['inputs']['display_fans_count'] = $this->getFormInput('display_fans_count', $displayFansCountCaption, $groupFormDatas['display_fans_count'], '', 'checkbox');
        // display the location
        $displayLocationCaption = _t('_emmetbytes_bon_con_in_realtime_display_location_caption');
        $groupForm['inputs']['display_location'] = $this->getFormInput('display_location', $displayLocationCaption, $groupFormDatas['display_location'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $groupForm['inputs'][$type.'admin_submit_group_button'] = $this->getFormInput($type.'admin_submit_group_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($groupForm);
        return $this->getCode();
    }

    // GETTING THE SITES FORM DATAS
    protected function getSitesForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_site_button'])){
            $this->insertFormInputValues();
        }
        $settingsName = $type.'_site_block_settings';
        $siteFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $siteForm['form_attrs'] = $this->getFormAttrs($type . '_form_sites');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_site_form_caption');// change me later
        $siteForm['inputs']['site_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $siteForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $siteForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $siteFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $siteForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $siteFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $siteForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $siteFormDatas['maximum_numbers_of_datas']);
        // display sites url
        $displaySitesUrlCaption = _t('_emmetbytes_bon_con_in_realtime_display_site_url_caption');
        $siteForm['inputs']['display_sites_url'] = $this->getFormInput('display_sites_url', $displaySitesUrlCaption, $siteFormDatas['display_sites_url'], '', 'checkbox');
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $siteForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $siteFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $siteForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $siteFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $siteForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $siteFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_in_realtime_display_tags_caption');
        $siteForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $siteFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_in_realtime_display_categories_caption');
        $siteForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $siteFormDatas['display_categories'], '', 'checkbox');
        // display the comments_count_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_comments_count_caption');
        $siteForm['inputs']['display_comments_count'] = $this->getFormInput('display_comments_count', $displayDateStartCaption, $siteFormDatas['display_comments_count'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_in_realtime_display_description_caption');
        $siteForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $siteFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_description_characters_description');
        $siteForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $siteFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $siteForm['inputs'][$type.'admin_submit_site_button'] = $this->getFormInput($type.'admin_submit_site_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($siteForm);
        return $this->getCode();

    }

    // GETTING THE BLOGS FORM DATAS
    protected function getBlogsForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_blogs_button'])){
            $this->insertFormInputValues();
        }

        $settingsName = $type.'_blogs_block_settings';
        $blogsFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $blogsForm['form_attrs'] = $this->getFormAttrs($type . '_form_blogs');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_blogs_form_caption');// change me later
        $blogsForm['inputs']['blogs_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $blogsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $blogsForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $blogsFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $blogsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $blogsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $blogsForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $blogsFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $blogsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $blogsFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $blogsForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $blogsFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $blogsForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $blogsFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_in_realtime_display_tags_caption');
        $blogsForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $blogsFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_in_realtime_display_categories_caption');
        $blogsForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $blogsFormDatas['display_categories'], '', 'checkbox');
        // display the comments_count_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_comments_count_caption');
        $blogsForm['inputs']['display_comments_count'] = $this->getFormInput('display_comments_count', $displayDateStartCaption, $blogsFormDatas['display_comments_count'], '', 'checkbox');
        // display the contents
        $displayContentsCaption = _t('_emmetbytes_bon_con_in_realtime_display_contents_caption');
        $blogsForm['inputs']['display_contents'] = $this->getFormInput('display_contents', $displayContentsCaption, $blogsFormDatas['display_contents'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the contents
        $displayContentsCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_contents_characters_contents');
        $blogsForm['inputs']['max_contents_chars'] = $this->getFormInput('max_contents_chars', $displayContentsCaption, $blogsFormDatas['max_contents_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $blogsForm['inputs'][$type.'admin_submit_blogs_button'] = $this->getFormInput($type.'admin_submit_blogs_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($blogsForm);
        return $this->getCode();

    }

    // GETTING THE PHOTOS FORM DATAS
    protected function getPhotosForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_photos_button'])){
            $this->insertFormInputValues();
        }

        $settingsName = $type.'_photos_block_settings';
        $photosFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $photosForm['form_attrs'] = $this->getFormAttrs($type . '_form_photos');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_photos_form_caption');// change me later
        $photosForm['inputs']['photos_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $photosForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $photosForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $photosFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $photosForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $photosFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $photosForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $photosFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $photosForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $photosFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $photosForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $photosFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $photosForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $photosFormDatas['display_rating'], '', 'checkbox');
        // display the size
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_size_caption');
        $photosForm['inputs']['display_size'] = $this->getFormInput('display_size', $displayViewsCaption, $photosFormDatas['display_size'], '', 'checkbox');
        // display the view
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_view_caption');
        $photosForm['inputs']['display_view'] = $this->getFormInput('display_view', $displayViewsCaption, $photosFormDatas['display_view'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $photosForm['inputs'][$type.'admin_submit_photos_button'] = $this->getFormInput($type.'admin_submit_photos_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($photosForm);
        return $this->getCode();
    }

    // GETTING THE VIDEOS FORM DATAS
    protected function getVideosForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_videos_button'])){
            $this->insertFormInputValues();
        }

        $settingsName = $type.'_videos_block_settings';
        $videosFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $videosForm['form_attrs'] = $this->getFormAttrs($type . '_form_videos');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_videos_form_caption');// change me later
        $videosForm['inputs']['videos_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $videosForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $videosForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $videosFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $videosForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $videosFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $videosForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $videosFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $videosForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $videosFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $videosForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $videosFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $videosForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $videosFormDatas['display_rating'], '', 'checkbox');
        // display the length
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_length_caption');
        $videosForm['inputs']['display_length'] = $this->getFormInput('display_length', $displayViewsCaption, $videosFormDatas['display_length'], '', 'checkbox');
        // display the view
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_view_caption');
        $videosForm['inputs']['display_view'] = $this->getFormInput('display_view', $displayViewsCaption, $videosFormDatas['display_view'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $videosForm['inputs'][$type.'admin_submit_videos_button'] = $this->getFormInput($type.'admin_submit_videos_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($videosForm);
        return $this->getCode();

    }
    
    // GETTING THE SOUNDS FORM DATAS
    protected function getSoundsForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_sounds_button'])){
            $this->insertFormInputValues();
        }
        $settingsName = $type.'_sounds_block_settings';
        $soundsFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $soundsForm['form_attrs'] = $this->getFormAttrs($type . '_form_sounds');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_sounds_form_caption');// change me later
        $soundsForm['inputs']['sounds_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $soundsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $soundsForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $soundsFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $soundsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $soundsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $soundsForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $soundsFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $soundsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $soundsFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $soundsForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $soundsFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $soundsForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $soundsFormDatas['display_rating'], '', 'checkbox');
        // display the length
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_length_caption');
        $soundsForm['inputs']['display_length'] = $this->getFormInput('display_length', $displayViewsCaption, $soundsFormDatas['display_length'], '', 'checkbox');
        // display the view
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_view_caption');
        $soundsForm['inputs']['display_view'] = $this->getFormInput('display_view', $displayViewsCaption, $soundsFormDatas['display_view'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $soundsForm['inputs'][$type.'admin_submit_sounds_button'] = $this->getFormInput($type.'admin_submit_sounds_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($soundsForm);
        return $this->getCode();
    }

    // GETTING THE FILES FORM DATAS
    protected function getFilesForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_files_button'])){
            $this->insertFormInputValues();
        }
        $settingsName = $type.'_files_block_settings';
        $filesFormDatas = $this->oDb->getBonConInRealtimeSettings($settingsName);
        $filesForm['form_attrs'] = $this->getFormAttrs($type . '_form_files');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_in_realtime_' . $type . '_files_form_caption');// change me later
        $filesForm['inputs']['files_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $filesForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // fetch type
        $fetchTypeDatas = array(
            'automatic' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_automatic'),
            'show_new_entries_button' => _t('_emmetbytes_bon_con_in_realtime_fetch_type_new_entries_button'),
        );
        $fetchTypeCaption = _t('_emmetbytes_bon_con_in_realtime_fetch_type_caption');
        $filesForm['inputs']['fetch_type_caption'] = $this->getFormInput('fetch_type', $fetchTypeCaption, $filesFormDatas['fetch_type'], $fetchTypeDatas, 'select');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_in_realtime_default_tab');
            $filesForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $filesFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_maximum_number_of_datas_caption');
        $filesForm['inputs']['maximum_numbers_of_datas'] = $this->getFormInput('maximum_numbers_of_datas', $displayAuthorCaption, $filesFormDatas['maximum_numbers_of_datas']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_in_realtime_display_author_caption');
        $filesForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $filesFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_in_realtime_display_date_caption');
        $filesForm['inputs']['display_date'] = $this->getFormInput('display_date', $displayDateStartCaption, $filesFormDatas['display_date'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_in_realtime_display_rating_caption');
        $filesForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $filesFormDatas['display_rating'], '', 'checkbox');
        // display the view
        $displayViewsCaption = _t('_emmetbytes_bon_con_in_realtime_display_view_caption');
        $filesForm['inputs']['display_view'] = $this->getFormInput('display_view', $displayViewsCaption, $filesFormDatas['display_view'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_in_realtime_admin_submit_caption');
        $filesForm['inputs'][$type.'admin_submit_files_button'] = $this->getFormInput($type.'admin_submit_files_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($filesForm);
        return $this->getCode();
    }

    // BOF SUBMITTING THE FORM DATAS
    protected function insertFormInputValues(){
        $inputVals = array(
            'name' => ((isset($_POST['name'])) && (!empty($_POST['name']))) ? $_POST['name'] : '',
            'default_tab' => ((isset($_POST['default_tab'])) && (!empty($_POST['default_tab']))) ? $_POST['default_tab'] : '',
            'maximum_numbers_of_datas' => ((isset($_POST['maximum_numbers_of_datas'])) && (!empty($_POST['maximum_numbers_of_datas']))) ? $_POST['maximum_numbers_of_datas'] : '',
            'fetch_type' => ((isset($_POST['fetch_type'])) && (!empty($_POST['fetch_type']))) ? $_POST['fetch_type'] : '',
            'display_sites_url' => ((isset($_POST['display_sites_url']))) ? 1 : '',
            'display_author' => ((isset($_POST['display_author']))) ? 1 : '',
            'display_date' => ((isset($_POST['display_date']))) ? 1 : '',
            'display_location' => ((isset($_POST['display_location']))) ? 1 : '',
            'display_fans_count' => ((isset($_POST['display_fans_count']))) ? 1 : '',
            'display_rating' => ((isset($_POST['display_rating']))) ? 1 : '',
            'display_size' => ((isset($_POST['display_size']))) ? 1 : '',
            'display_length' => ((isset($_POST['display_length']))) ? 1 : '',
            'display_view' => ((isset($_POST['display_view']))) ? 1 : '',
            'display_categories' => ((isset($_POST['display_categories']))) ? 1 : '',
            'display_tags' => ((isset($_POST['display_tags']))) ? 1 : '',
            'display_comments_count' => ((isset($_POST['display_comments_count']))) ? 1 : '',
            'display_description' => ((isset($_POST['display_description']))) ? 1 : '',
            'max_description_chars' => ((isset($_POST['max_description_chars'])) && (!empty($_POST['max_description_chars']))) ? $_POST['max_description_chars'] : '',
            'display_contents' => ((isset($_POST['display_contents']))) ? 1 : '',
            'max_contents_chars' => ((isset($_POST['max_contents_chars'])) && (!empty($_POST['max_contents_chars']))) ? $_POST['max_contents_chars'] : '',
        );
        return $this->oDb->insertBonConInRealtimeSettings($inputVals);
    }

}

class EmmetBytesBonConInRealtimeAdministrationsd710UpHelper extends EmmetBytesBonConInRealtimeAdministrationsDefaultHelper{

    // constructor
    function EmmetBytesBonConInRealtimeAdministrationsd710UpHelper($oMain){
        parent::EmmetBytesBonConInRealtimeAdministrationsDefaultHelper($oMain);
    }

    function getHomepageSettings(){
        // event tabs
        $defaultEventTabDatas = array(
            'upcoming' => _t('_bx_events_tab_upcoming'),
            'featured' => _t('_bx_events_tab_featured'),
            'recent' => _t('_bx_events_tab_recent'),
            'top' => _t('_bx_events_tab_top'),
            'popular' => _t('_bx_events_tab_popular'),
        );
        $homePageSettingsContent = $this->getEventForm('homepage', $defaultEventTabDatas);
        // groups tabs
        $defaultGroupTabDatas = array(
            'featured' => _t('_bx_groups_tab_featured'),
            'recent' => _t('_bx_groups_tab_recent'),
            'top' => _t('_bx_groups_tab_top'),
            'popular' => _t('_bx_groups_tab_popular'),
        );
        $homePageSettingsContent .= $this->getGroupsForm('homepage', $defaultGroupTabDatas);
        // sites tabs
        $homePageSettingsContent .= $this->getSitesForm('homepage');
        // blogs tabs
        $defaultBlogsTabDatas = array(
        );
        $homePageSettingsContent .= $this->getBlogsForm('homepage', $defaultBlogsTabDatas);
        // photos form
        $defaultPhotosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getPhotosForm('homepage', $defaultPhotosTabDatas);
        // videos form
        $defaultVideosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getVideosForm('homepage', $defaultVideosTabDatas);
        // sounds form
        $defaultSoundsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getSoundsForm('homepage', $defaultSoundsTabDatas);
        // files form
        $defaultFilesTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $homePageSettingsContent .= $this->getFilesForm('homepage', $defaultFilesTabDatas);
        return $homePageSettingsContent;
    }

    // GETTING THE MODULE BLOCKS SETTINGS
    function getModuleBlocksSettings(){
        // event tabs
        $moduleBlocksSettingsContent = $this->getEventForm('module_blocks_main_upcoming');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_past');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_recent');
        // groups tabs
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_main_recent');
        // sites tabs
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_featured');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_recent');
        // blogs tabs
        // blogs tabs
        $defaultBlogsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getBlogsForm('module_blocks_main_latest', $defaultBlogsTabDatas);
        // photos form
        $defaultPhotosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getPhotosForm('module_blocks_main_public', $defaultPhotosTabDatas);
        $moduleBlocksSettingsContent .= $this->getPhotosForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getPhotosForm('module_blocks_main_featured');
        // videos form
        $defaultVideosTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        // videos form
        $moduleBlocksSettingsContent .= $this->getVideosForm('module_blocks_main_public', $defaultVideosTabDatas);
        $moduleBlocksSettingsContent .= $this->getVideosForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getVideosForm('module_blocks_main_featured');
        // sounds form
        $defaultSoundsTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getSoundsForm('module_blocks_main_public', $defaultSoundsTabDatas);
        $moduleBlocksSettingsContent .= $this->getSoundsForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getSoundsForm('module_blocks_main_featured');
        // files form
        $defaultFilesTabDatas = array(
            'last' => _t('_Latest'),
            'top' => _t('_Top'),
        );
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_public', $defaultFilesTabDatas);
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_top');
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_favorite');
        $moduleBlocksSettingsContent .= $this->getFilesForm('module_blocks_main_featured');
        return $moduleBlocksSettingsContent;
    }
}
?>
