<?php
/**********************************************************************************************
 * Created By : EmmetBytes Software Solutions
 * Created Date : January 10, 2013
 * Email : emmetbytes@gmail.com
 *
 * Copyright : (c) EmmetBytes Software Solutions 2012
 * Product Name : Boonex Contents Slider
 * Product Version : 1.0
 * 
 * Important : This is a commercial product by EmmetBytes Software Solutions and 
 *   cannot be modified, redistributed or resold without any written permission 
 *   from EmmetBytes Software Solutions
 **********************************************************************************************/
bx_import ('BxDolFormMedia');
class EmmetBytesBoonexContentsSliderAdministrationsHelper{
    var $boonexVersion, $oMain;
    // CONSTRUCTOR
    function EmmetBytesBoonexContentsSliderAdministrationsHelper($oMain){
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        if($this->boonexVersion >= '7.1.0'){
            $this->helperObj = new EmmetBytesBoonexContentsSliderAdministrationsd710UpHelper($oMain);
        }else{
            $this->helperObj = new EmmetBytesBoonexContentsSliderAdministrationsDefaultHelper($oMain);
        }
    }
}

// DEFAULT HELPER
class EmmetBytesBoonexContentsSliderAdministrationsDefaultHelper extends BxDolFormMedia{
    
    // CONSTRUCTOR
    function EmmetBytesBoonexContentsSliderAdministrationsDefaultHelper($oMain){
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
        $homePageSettingsContent .= $this->getAdsForm('homepage');
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
        // ads form
        $profileSettingsContent .= $this->getAdsForm('profile_my');
        return $profileSettingsContent;
    }

    // GETTING THE MODULE BLOCKS SETTINGS
    function getModuleBlocksSettings(){
        // event tabs
        $moduleBlocksSettingsContent = $this->getEventForm('module_blocks_main_upcoming');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_past');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_recent');
        // $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_users');
        // groups tabs
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_main_recent');
        // $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_users');
        // sites tabs
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_featured');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_recent');
        // $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_users');
        // blogs tabs
        $defaultBlogsTabDatas = array();
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

    // GETTING THE FORM HELPERS
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
    // EOF THE GETTING OF THE FORM HELPERS

    // GETTING THE EVENT FORM DATAS
    function getEventForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_event_button'])){
            $this->insertFormInputValues();
        }

        $eventForm = $this->getEventFormVars($type, $defaultTabDatas);
        parent::BxDolFormMedia($eventForm);
        return $this->getCode();
    }

    // getting the event form vars
    protected function getEventFormVars($type, $defaultTabDatas = array()){
        $settingsName = $type.'_event_block_settings';
        $eventFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $eventForm['form_attrs'] = $this->getFormAttrs($type . '_form_events');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_event_form_caption');// change me later
        $eventForm['inputs']['event_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);// change me later
        // input name captions
        $inputNameCaption = '';
        $eventForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $eventForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $eventFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $eventForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $eventFormDatas['maximum_datas']);
        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $eventForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $eventFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $eventForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $eventFormDatas['display_author'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_boonex_contents_slider_display_date_start_caption');
        $eventForm['inputs']['display_date_start'] = $this->getFormInput('display_date_start', $displayDateStartCaption, $eventFormDatas['display_date_start'], '', 'checkbox');
        // display the location
        $displayLocationCaption = _t('_emmetbytes_boonex_contents_slider_display_location_caption');
        $eventForm['inputs']['display_location'] = $this->getFormInput('display_location', $displayLocationCaption, $eventFormDatas['display_location'], '', 'checkbox');
        // display the fans_count
        $displayFansCountCaption = _t('_emmetbytes_boonex_contents_slider_display_fans_count_caption');
        $eventForm['inputs']['display_fans_count'] = $this->getFormInput('display_fans_count', $displayFansCountCaption, $eventFormDatas['display_fans_count'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $eventForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $eventFormDatas['display_rate'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
        $eventForm['inputs'][$type.'admin_submit_event_button'] = $this->getFormInput($type.'admin_submit_event_button', '', $submitCaption, '', 'submit');
        return $eventForm;
    }

    // GETTING THE GROUPS FORM DATAS
    protected function getGroupsForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_group_button'])){
            $this->insertFormInputValues();
        }
        $groupForm = $this->getGroupsFormVars($type, $defaultTabDatas);
        parent::BxDolFormMedia($groupForm);
        return $this->getCode();
    }

    // getting the groups form vars
    protected function getGroupsFormVars($type, $defaultTabDatas = array()){
        $settingsName = $type.'_group_block_settings';
        $groupFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $groupForm['form_attrs'] = $this->getFormAttrs($type . '_form_groups');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_group_form_caption');// change me later
        $groupForm['inputs']['group_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $groupForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $groupForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $groupFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $groupForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $groupFormDatas['maximum_datas']);
        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $groupForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $groupFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $groupForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $groupFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $groupForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayAuthorCaption, $groupFormDatas['display_created_date'], '', 'checkbox');
        // display the location
        $displayLocationCaption = _t('_emmetbytes_boonex_contents_slider_display_location_caption');
        $groupForm['inputs']['display_location'] = $this->getFormInput('display_location', $displayLocationCaption, $groupFormDatas['display_location'], '', 'checkbox');
        // display the fans count
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_fans_count_caption');
        $groupForm['inputs']['display_fans_count'] = $this->getFormInput('display_fans_count', $displayRatingCaption, $groupFormDatas['display_fans_count'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $groupForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $groupFormDatas['display_rate'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
        $groupForm['inputs'][$type.'admin_submit_group_button'] = $this->getFormInput($type.'admin_submit_group_button', '', $submitCaption, '', 'submit');
        return $groupForm;
    }

    // GETTING THE SITES FORM DATAS
    protected function getSitesForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_site_button'])){
            $this->insertFormInputValues();
        }

        $settingsName = $type.'_site_block_settings';
        $siteFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $siteForm['form_attrs'] = $this->getFormAttrs($type . '_form_sites');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_site_form_caption');// change me later
        $siteForm['inputs']['site_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $siteForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $siteForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $siteFormDatas['default_tab'], $defaultTabDatas, 'select');
        }

        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $siteForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $siteFormDatas['maximum_datas']);

        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $siteForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $siteFormDatas['maximum_title_characters']);

        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $siteForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $siteFormDatas['display_author'], '', 'checkbox');

        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $siteForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $siteFormDatas['display_created_date'], '', 'checkbox');

        // display the tags
        $displayTagsCaption = _t('_emmetbytes_boonex_contents_slider_display_tags_caption');
        $siteForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $siteFormDatas['display_tags'], '', 'checkbox');

        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_boonex_contents_slider_display_categories_caption');
        $siteForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $siteFormDatas['display_categories'], '', 'checkbox');

        // display the comments_count
        $displayCommentsCountCaption = _t('_emmetbytes_boonex_contents_slider_display_comments_count_caption');
        $siteForm['inputs']['display_comments_count'] = $this->getFormInput('display_comments_count', $displayCommentsCountCaption, $siteFormDatas['display_comments_count'], '', 'checkbox');

        // display the rating
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rating_caption');
        $siteForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $siteFormDatas['display_rate'], '', 'checkbox');

        // display sites url
        $displaySitesUrlCaption = _t('_emmetbytes_boonex_contents_slider_display_site_url_caption');
        $siteForm['inputs']['display_site_url'] = $this->getFormInput('display_site_url', $displaySitesUrlCaption, $siteFormDatas['display_site_url'], '', 'checkbox');

        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_display_description_caption');
        $siteForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $siteFormDatas['display_description'], '', 'checkbox');

        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_maximum_description_characters_description');
        $siteForm['inputs']['maximum_description_characters'] = $this->getFormInput('maximum_description_characters', $displayDescriptionCaption, $siteFormDatas['maximum_description_characters']);

        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
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
        $blogsFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $blogsForm['form_attrs'] = $this->getFormAttrs($type . '_form_blogs');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_blogs_form_caption');// change me later
        $blogsForm['inputs']['blogs_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $blogsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $blogsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $blogsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $blogsForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $blogsFormDatas['maximum_datas']);

        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $blogsForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $blogsFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $blogsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $blogsFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $blogsForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $blogsFormDatas['display_created_date'], '', 'checkbox');
        // display the comments count
        $displayCommentsCountCaption = _t('_emmetbytes_boonex_contents_slider_display_comments_count_caption');
        $blogsForm['inputs']['display_comments_count'] = $this->getFormInput('display_comments_count', $displayCommentsCountCaption, $blogsFormDatas['display_comments_count'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rating_caption');
        $blogsForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $blogsFormDatas['display_rate'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_boonex_contents_slider_display_tags_caption');
        $blogsForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $blogsFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_boonex_contents_slider_display_categories_caption');
        $blogsForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $blogsFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_display_post_text_caption');
        $blogsForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $blogsFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_maximum_post_text_characters_description');
        $blogsForm['inputs']['maximum_description_characters'] = $this->getFormInput('maximum_description_characters', $displayDescriptionCaption, $blogsFormDatas['maximum_description_characters']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
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
        $photosFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $photosForm['form_attrs'] = $this->getFormAttrs($type . '_form_photos');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_photos_form_caption');// change me later
        $photosForm['inputs']['photos_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $photosForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $photosForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $photosFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $photosForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $photosFormDatas['maximum_datas']);

        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $photosForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $photosFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $photosForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $photosFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $photosForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $photosFormDatas['display_created_date'], '', 'checkbox');
        // display the views
        $displayViewsCaption = _t('_emmetbytes_boonex_contents_slider_display_views_caption');
        $photosForm['inputs']['display_views'] = $this->getFormInput('display_views', $displayViewsCaption, $photosFormDatas['display_views'], '', 'checkbox');
        // display the size
        $displaySizeCaption = _t('_emmetbytes_boonex_contents_slider_display_size_caption');
        $photosForm['inputs']['display_size'] = $this->getFormInput('display_size', $displaySizeCaption, $photosFormDatas['display_size'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $photosForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $photosFormDatas['display_rate'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
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
        $videosFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $videosForm['form_attrs'] = $this->getFormAttrs($type . '_form_videos');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_videos_form_caption');// change me later
        $videosForm['inputs']['videos_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $videosForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $videosForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $videosFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $videosForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $videosFormDatas['maximum_datas']);
        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $videosForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $videosFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $videosForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $videosFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $videosForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $videosFormDatas['display_created_date'], '', 'checkbox');
        // display the views
        $displayViewsCaption = _t('_emmetbytes_boonex_contents_slider_display_views_caption');
        $videosForm['inputs']['display_views'] = $this->getFormInput('display_views', $displayViewsCaption, $videosFormDatas['display_views'], '', 'checkbox');
        // display the size
        $displaySizeCaption = _t('_emmetbytes_boonex_contents_slider_display_length_caption');
        $videosForm['inputs']['display_size'] = $this->getFormInput('display_size', $displaySizeCaption, $videosFormDatas['display_size'], '', 'checkbox');
        // display the rate
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $videosForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $videosFormDatas['display_rate'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
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
        $soundsFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $soundsForm['form_attrs'] = $this->getFormAttrs($type . '_form_sounds');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_sounds_form_caption');// change me later
        $soundsForm['inputs']['sounds_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $soundsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $soundsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $soundsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $soundsForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $soundsFormDatas['maximum_datas']);
        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $soundsForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $soundsFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $soundsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $soundsFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $soundsForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $soundsFormDatas['display_created_date'], '', 'checkbox');
        // display the views
        $displayViewsCaption = _t('_emmetbytes_boonex_contents_slider_display_views_caption');
        $soundsForm['inputs']['display_views'] = $this->getFormInput('display_views', $displayViewsCaption, $soundsFormDatas['display_views'], '', 'checkbox');
        // display the size
        $displaySizeCaption = _t('_emmetbytes_boonex_contents_slider_display_length_caption');
        $soundsForm['inputs']['display_size'] = $this->getFormInput('display_size', $displaySizeCaption, $soundsFormDatas['display_size'], '', 'checkbox');
        // display the rate
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $soundsForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $soundsFormDatas['display_rate'], '', 'checkbox');
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
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
        $filesFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $filesForm['form_attrs'] = $this->getFormAttrs($type . '_form_files');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_files_form_caption');// change me later
        $filesForm['inputs']['files_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $filesForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $filesForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $filesFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $filesForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $filesFormDatas['maximum_datas']);
        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $filesForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $filesFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $filesForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $filesFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $filesForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $filesFormDatas['display_created_date'], '', 'checkbox');
        // display the views
        $displayViewsCaption = _t('_emmetbytes_boonex_contents_slider_display_views_caption');
        $filesForm['inputs']['display_views'] = $this->getFormInput('display_views', $displayViewsCaption, $filesFormDatas['display_views'], '', 'checkbox');
        // display the size
        $displaySizeCaption = _t('_emmetbytes_boonex_contents_slider_display_length_caption');
        $filesForm['inputs']['display_size'] = $this->getFormInput('display_size', $displaySizeCaption, $filesFormDatas['display_size'], '', 'checkbox');
        // display the rate
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $filesForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $filesFormDatas['display_rate'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_display_description_caption');
        $filesForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $filesFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_maximum_description_characters_description');
        $filesForm['inputs']['maximum_description_characters'] = $this->getFormInput('maximum_description_characters', $displayDescriptionCaption, $filesFormDatas['maximum_description_characters']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
        $filesForm['inputs'][$type.'admin_submit_files_button'] = $this->getFormInput($type.'admin_submit_files_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($filesForm);
        return $this->getCode();
    }

    // GETTING THE ADS FORM DATAS
    protected function getAdsForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_ads_button'])){
            $this->insertFormInputValues();
        }
        $settingsName = $type.'_ads_block_settings';
        $adsFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $adsForm['form_attrs'] = $this->getFormAttrs($type . '_form_ads');
        // form header
        $formCaption = _t('_emmetbytes_boonex_contents_slider_' . $type . '_ads_form_caption');// change me later
        $adsForm['inputs']['ads_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $adsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_boonex_contents_slider_default_tab');
            $adsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $adsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // maximum numbers of datas 
        $maximumDatasCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_datas_caption');
        $adsForm['inputs']['maximum_datas'] = $this->getFormInput('maximum_datas', $maximumDatasCaption, $adsFormDatas['maximum_datas']);
        // maximum number of characters for the title
        $maximumTitleCharsCaption = _t('_emmetbytes_boonex_contents_slider_maximum_numbers_of_title_characters_caption');
        $adsForm['inputs']['maximum_title_characters'] = $this->getFormInput('maximum_title_characters', $maximumTitleCharsCaption, $adsFormDatas['maximum_title_characters']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_boonex_contents_slider_display_author_caption');
        $adsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $adsFormDatas['display_author'], '', 'checkbox');
        // display the created date
        $displayCreatedDateCaption = _t('_emmetbytes_boonex_contents_slider_display_date_created_caption');
        $adsForm['inputs']['display_created_date'] = $this->getFormInput('display_created_date', $displayCreatedDateCaption, $adsFormDatas['display_created_date'], '', 'checkbox');
        // display the rate
        $displayRatingCaption = _t('_emmetbytes_boonex_contents_slider_display_rate_caption');
        $adsForm['inputs']['display_rate'] = $this->getFormInput('display_rate', $displayRatingCaption, $adsFormDatas['display_rate'], '', 'checkbox');
        // display the ads_price
        $displayAdsPriceCaption = _t('_emmetbytes_boonex_contents_slider_display_ads_price_caption');
        $adsForm['inputs']['display_ads_price'] = $this->getFormInput('display_ads_price', $displayAdsPriceCaption, $adsFormDatas['display_ads_price'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_boonex_contents_slider_display_categories_caption');
        $adsForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $adsFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_display_description_caption');
        $adsForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $adsFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_maximum_description_characters_description');
        $adsForm['inputs']['maximum_description_characters'] = $this->getFormInput('maximum_description_characters', $displayDescriptionCaption, $adsFormDatas['maximum_description_characters']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
        $adsForm['inputs'][$type.'admin_submit_ads_button'] = $this->getFormInput($type.'admin_submit_ads_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($adsForm);
        return $this->getCode();
    }

    // BOF SUBMITTING THE FORM DATAS
    protected function insertFormInputValues(){
        $inputVals = array(
            'name' => ((isset($_POST['name']) && !empty($_POST['name'])) ? $_POST['name'] : ''),
            'default_tab' => ((isset($_POST['default_tab']) && !empty($_POST['default_tab'])) ? $_POST['default_tab'] : ''),
            'maximum_datas' => (isset($_POST['maximum_datas']) && !empty($_POST['maximum_datas'])) ? $_POST['maximum_datas'] : '',
            'maximum_title_characters' => (isset($_POST['maximum_title_characters']) && !empty($_POST['maximum_title_characters'])) ? $_POST['maximum_title_characters'] : '',
            'display_date_start' => (isset($_POST['display_date_start'])) ? '1' : '0',
            'display_created_date' => ((isset($_POST['display_created_date'])) ? '1' : '0'),
            'display_views' => ((isset($_POST['display_views'])) ? '1' : '0'),
            'display_size' => ((isset($_POST['display_size'])) ? '1' : '0'),
            'display_categories' => ((isset($_POST['display_categories'])) ? '1' : '0'),
            'display_tags' => ((isset($_POST['display_tags'])) ? '1' : '0'),
            'display_comments_count' => ((isset($_POST['display_comments_count'])) ? '1' : '0'),
            'display_author' => ((isset($_POST['display_author'])) ? '1' : '0'),
            'display_rate' => ((isset($_POST['display_rate'])) ? '1' : '0'),
            'display_fans_count' => ((isset($_POST['display_fans_count'])) ? '1' : '0'),
            'display_location' => ((isset($_POST['display_location'])) ? '1' : '0'),
            'display_site_url' => ((isset($_POST['display_site_url'])) ? '1' : '0'),
            'display_ads_price' => ((isset($_POST['display_ads_price'])) ? '1' : '0'),
            'display_description' => ((isset($_POST['display_description'])) ? '1' : '0'),
            'maximum_description_characters' => ((isset($_POST['maximum_description_characters']) && !empty($_POST['maximum_description_characters'])) ? $_POST['maximum_description_characters'] : ''),
        );
        return $this->oDb->insertBoonexContentsSliderSettings($inputVals);
    }

}

class EmmetBytesBoonexContentsSliderAdministrationsd710UpHelper extends EmmetBytesBoonexContentsSliderAdministrationsDefaultHelper{

    // CONSTRUCTOR
    function EmmetBytesBoonexContentsSliderAdministrationsd710UpHelper($oMain){
        parent::EmmetBytesBoonexContentsSliderAdministrationsDefaultHelper($oMain);
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

        // ads
        $homePageSettingsContent .= $this->getAdsForm('homepage');
        return $homePageSettingsContent;
    }

    // event form vars
    protected function getEventFormVars($type, $defaultTabDatas = array()){
        $settingsName = $type.'_event_block_settings';
        $eventFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $eventForm = parent::getEventFormVars($type, $defaultTabDatas);
        unset($eventForm['inputs'][$type.'admin_submit_event_button']);
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_display_description_caption');
        $eventForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $eventFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_maximum_description_characters_description');
        $eventForm['inputs']['maximum_description_characters'] = $this->getFormInput('maximum_description_characters', $displayDescriptionCaption, $eventFormDatas['maximum_description_characters']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
        $eventForm['inputs'][$type.'admin_submit_event_button'] = $this->getFormInput($type.'admin_submit_event_button', '', $submitCaption, '', 'submit');
        return $eventForm;
    }

    // groups form vars
    protected function getGroupsFormVars($type, $defaultTabDatas = array()){
        $settingsName = $type.'_group_block_settings';
        $groupFormDatas = $this->oDb->getBoonexContentsSliderSettings($settingsName);
        $groupForm = parent::getGroupsFormVars($type, $defaultTabDatas);
        unset($groupForm['inputs'][$type.'admin_submit_group_button']);
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_display_description_caption');
        $groupForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $groupFormDatas['display_description'], '', 'checkbox');

        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_boonex_contents_slider_maximum_description_characters_description');
        $groupForm['inputs']['maximum_description_characters'] = $this->getFormInput('maximum_description_characters', $displayDescriptionCaption, $groupFormDatas['maximum_description_characters']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_boonex_contents_slider_admin_submit_caption');
        $groupForm['inputs'][$type.'admin_submit_group_button'] = $this->getFormInput($type.'admin_submit_group_button', '', $submitCaption, '', 'submit');
        return $groupForm;
    }

    // GETTING THE MODULE BLOCKS SETTINGS
    function getModuleBlocksSettings(){
        // event tabs
        $moduleBlocksSettingsContent = $this->getEventForm('module_blocks_main_upcoming');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_past');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_recent');
        // $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_users');
        // groups tabs
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_main_recent');
        // $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_users');
        // sites tabs
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_featured');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_recent');
        // $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_users');
        // blogs tabs
        $defaultBlogsTabDatas = array();
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

        // for the ads
        $moduleBlocksSettingsContent .= $this->getAdsForm('module_blocks_main_last');
        return $moduleBlocksSettingsContent;
    }
}
?>
