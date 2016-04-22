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
bx_import ('BxDolFormMedia');
class EmmetBytesBonConSpecialInfoAdministrationsHelper{
    var $boonexVersion, $oMain;
    // CONSTRUCTOR
    function EmmetBytesBonConSpecialInfoAdministrationsHelper($oMain){
        $this->oDb = $oMain->_oDb;
        $this->oMain = $oMain;
        $this->boonexVersion = $GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
        if($this->boonexVersion >= '7.1.0'){
            $this->helperObj = new EmmetBytesBonConSpecialInfoAdministrationsd710UpHelper($oMain);
        }else{
            $this->helperObj = new EmmetBytesBonConSpecialInfoAdministrationsDefaultHelper($oMain);
        }
    }
}

class EmmetBytesBonConSpecialInfoAdministrationsDefaultHelper extends BxDolFormMedia{
    
    function EmmetBytesBonConSpecialInfoAdministrationsDefaultHelper($oMain){
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
    // EOF GETTING THE HOMEPAGE SETTINGS

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
    // EOF GETTING THE PROFILE SETTINGS

    // GETTING THE MODULE BLOCKS SETTINGS
    function getModuleBlocksSettings(){
        // event tabs
        $moduleBlocksSettingsContent = $this->getEventForm('module_blocks_main_upcoming');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_past');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_recent');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_users');
        // groups tabs
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_main_recent');
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_users');
        // sites tabs
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_featured');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_recent');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_users');
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
    // EOF GETTING THE MODULE BLOCKS SETTINGS

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

        $settingsName = $type.'_event_block_settings';
        $eventFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $eventForm['form_attrs'] = $this->getFormAttrs($type . '_form_events');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_event_form_caption');// change me later
        $eventForm['inputs']['event_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);// change me later
        // input name captions
        $inputNameCaption = '';
        $eventForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $eventForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $eventFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // image height input
        $imageHeightCaption = _t('_emmetbytes_bon_con_special_info_image_height_caption');
        $eventForm['inputs']['info_image_height'] = $this->getFormInput('info_image_height', $imageHeightCaption, $eventFormDatas['info_image_height']);
        // image width input
        $imageWidthCaption = _t('_emmetbytes_bon_con_special_info_image_width_caption');
        $eventForm['inputs']['info_image_width'] = $this->getFormInput('info_image_width', $imageWidthCaption, $eventFormDatas['info_image_width']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $eventForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $eventFormDatas['display_author'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $eventForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $eventFormDatas['display_rating'], '', 'checkbox');
        // display the location
        $displayLocationCaption = _t('_emmetbytes_bon_con_special_info_display_location_caption');
        $eventForm['inputs']['display_location'] = $this->getFormInput('display_location', $displayLocationCaption, $eventFormDatas['display_location'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $eventForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $eventFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $eventForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $eventFormDatas['display_categories'], '', 'checkbox');
        // display the date_start
        $displayDateStartCaption = _t('_emmetbytes_bon_con_special_info_display_date_start_caption');
        $eventForm['inputs']['display_date_start'] = $this->getFormInput('display_date_start', $displayDateStartCaption, $eventFormDatas['display_date_start'], '', 'checkbox');
        // display the date_end
        $displayDateEndCaption = _t('_emmetbytes_bon_con_special_info_display_date_end_caption');
        $eventForm['inputs']['display_date_end'] = $this->getFormInput('display_date_end', $displayDateEndCaption, $eventFormDatas['display_date_end'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $eventForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $eventFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $eventForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $eventFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
        $eventForm['inputs'][$type.'admin_submit_event_button'] = $this->getFormInput($type.'admin_submit_event_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($eventForm);
        return $this->getCode();
    }
    // EOF GETTING THE EVENT FORM DATAS

    // GETTING THE GROUPS FORM DATAS
    protected function getGroupsForm($type, $defaultTabDatas = array()){
        if(isset($_POST[$type.'admin_submit_group_button'])){
            $this->insertFormInputValues();
        }

        $settingsName = $type.'_group_block_settings';
        $groupFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $groupForm['form_attrs'] = $this->getFormAttrs($type . '_form_groups');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_group_form_caption');// change me later
        $groupForm['inputs']['group_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $groupForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $groupForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $groupFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // image height input
        $imageHeightCaption = _t('_emmetbytes_bon_con_special_info_image_height_caption');
        $groupForm['inputs']['info_image_height'] = $this->getFormInput('info_image_height', $imageHeightCaption, $groupFormDatas['info_image_height']);
        // image width input
        $imageWidthCaption = _t('_emmetbytes_bon_con_special_info_image_width_caption');
        $groupForm['inputs']['info_image_width'] = $this->getFormInput('info_image_width', $imageWidthCaption, $groupFormDatas['info_image_width']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $groupForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $groupFormDatas['display_author'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $groupForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $groupFormDatas['display_rating'], '', 'checkbox');
        // display the location
        $displayLocationCaption = _t('_emmetbytes_bon_con_special_info_display_location_caption');
        $groupForm['inputs']['display_location'] = $this->getFormInput('display_location', $displayLocationCaption, $groupFormDatas['display_location'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $groupForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $groupFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $groupForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $groupFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $groupForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $groupFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $groupForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $groupFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
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
        $siteFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $siteForm['form_attrs'] = $this->getFormAttrs($type . '_form_sites');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_site_form_caption');// change me later
        $siteForm['inputs']['site_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $siteForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $siteForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $siteFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // image height input
        $imageHeightCaption = _t('_emmetbytes_bon_con_special_info_image_height_caption');
        $siteForm['inputs']['info_image_height'] = $this->getFormInput('info_image_height', $imageHeightCaption, $siteFormDatas['info_image_height']);
        // image width input
        $imageWidthCaption = _t('_emmetbytes_bon_con_special_info_image_width_caption');
        $siteForm['inputs']['info_image_width'] = $this->getFormInput('info_image_width', $imageWidthCaption, $siteFormDatas['info_image_width']);
        // display sites url
        $displaySitesUrlCaption = _t('_emmetbytes_bon_con_special_info_display_site_url_caption');
        $siteForm['inputs']['display_sites_url'] = $this->getFormInput('display_sites_url', $displaySitesUrlCaption, $siteFormDatas['display_sites_url'], '', 'checkbox');
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $siteForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $siteFormDatas['display_author'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $siteForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $siteFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $siteForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $siteFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $siteForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $siteFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $siteForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $siteFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $siteForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $siteFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
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
        $blogsFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $blogsForm['form_attrs'] = $this->getFormAttrs($type . '_form_blogs');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_blogs_form_caption');// change me later
        $blogsForm['inputs']['blogs_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $blogsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $blogsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $blogsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // image height input
        $imageHeightCaption = _t('_emmetbytes_bon_con_special_info_image_height_caption');
        $blogsForm['inputs']['info_image_height'] = $this->getFormInput('info_image_height', $imageHeightCaption, $blogsFormDatas['info_image_height']);
        // image width input
        $imageWidthCaption = _t('_emmetbytes_bon_con_special_info_image_width_caption');
        $blogsForm['inputs']['info_image_width'] = $this->getFormInput('info_image_width', $imageWidthCaption, $blogsFormDatas['info_image_width']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $blogsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $blogsFormDatas['display_author'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $blogsForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $blogsFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $blogsForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $blogsFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $blogsForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $blogsFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $blogsForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $blogsFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $blogsForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $blogsFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
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
        $photosFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $photosForm['form_attrs'] = $this->getFormAttrs($type . '_form_photos');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_photos_form_caption');// change me later
        $photosForm['inputs']['photos_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $photosForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $photosForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $photosFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // image height input
        $imageHeightCaption = _t('_emmetbytes_bon_con_special_info_image_height_caption');
        $photosForm['inputs']['info_image_height'] = $this->getFormInput('info_image_height', $imageHeightCaption, $photosFormDatas['info_image_height']);
        // image width input
        $imageWidthCaption = _t('_emmetbytes_bon_con_special_info_image_width_caption');
        $photosForm['inputs']['info_image_width'] = $this->getFormInput('info_image_width', $imageWidthCaption, $photosFormDatas['info_image_width']);
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $photosForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $photosFormDatas['display_author'], '', 'checkbox');
        // display album
        $displayAlbumCaption = _t('_emmetbytes_bon_con_special_info_display_album_caption');
        $photosForm['inputs']['display_album'] = $this->getFormInput('display_album', $displayAlbumCaption, $photosFormDatas['display_album'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $photosForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $photosFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $photosForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $photosFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $photosForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $photosFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $photosForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $photosFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $photosForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $photosFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
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
        $videosFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $videosForm['form_attrs'] = $this->getFormAttrs($type . '_form_videos');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_videos_form_caption');// change me later
        $videosForm['inputs']['videos_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $videosForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $videosForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $videosFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $videosForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $videosFormDatas['display_author'], '', 'checkbox');
        // display album
        $displayAlbumCaption = _t('_emmetbytes_bon_con_special_info_display_album_caption');
        $videosForm['inputs']['display_album'] = $this->getFormInput('display_album', $displayAlbumCaption, $videosFormDatas['display_album'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $videosForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $videosFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $videosForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $videosFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $videosForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $videosFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $videosForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $videosFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $videosForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $videosFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
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
        $soundsFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $soundsForm['form_attrs'] = $this->getFormAttrs($type . '_form_sounds');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_sounds_form_caption');// change me later
        $soundsForm['inputs']['sounds_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $soundsForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $soundsForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $soundsFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $soundsForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $soundsFormDatas['display_author'], '', 'checkbox');
        // display album
        $displayAlbumCaption = _t('_emmetbytes_bon_con_special_info_display_album_caption');
        $soundsForm['inputs']['display_album'] = $this->getFormInput('display_album', $displayAlbumCaption, $soundsFormDatas['display_album'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $soundsForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $soundsFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $soundsForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $soundsFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $soundsForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $soundsFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $soundsForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $soundsFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $soundsForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $soundsFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
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
        $filesFormDatas = $this->oDb->getBonConSpecialInfoSettings($settingsName);
        $filesForm['form_attrs'] = $this->getFormAttrs($type . '_form_files');
        // form header
        $formCaption = _t('_emmetbytes_bon_con_special_info_' . $type . '_files_form_caption');// change me later
        $filesForm['inputs']['files_header_info'] = $this->getFormInputHeaderBlock($formCaption, true, true);
        // input name captions
        $inputNameCaption = '';
        $filesForm['inputs']['name'] = $this->getFormInput('name', $inputNameCaption, $settingsName, '', 'hidden');
        // input default tab
        if(sizeof($defaultTabDatas) > 0){
            $inputTabCaption = _t('_emmetbytes_bon_con_special_info_default_tab');
            $filesForm['inputs']['default_tab'] = $this->getFormInput('default_tab', $inputTabCaption, $filesFormDatas['default_tab'], $defaultTabDatas, 'select');
        }
        // display author
        $displayAuthorCaption = _t('_emmetbytes_bon_con_special_info_display_author_caption');
        $filesForm['inputs']['display_author'] = $this->getFormInput('display_author', $displayAuthorCaption, $filesFormDatas['display_author'], '', 'checkbox');
        // display album
        $displayAlbumCaption = _t('_emmetbytes_bon_con_special_info_display_album_caption');
        $filesForm['inputs']['display_album'] = $this->getFormInput('display_album', $displayAlbumCaption, $filesFormDatas['display_album'], '', 'checkbox');
        // display the rating
        $displayRatingCaption = _t('_emmetbytes_bon_con_special_info_display_rating_caption');
        $filesForm['inputs']['display_rating'] = $this->getFormInput('display_rating', $displayRatingCaption, $filesFormDatas['display_rating'], '', 'checkbox');
        // display the tags
        $displayTagsCaption = _t('_emmetbytes_bon_con_special_info_display_tags_caption');
        $filesForm['inputs']['display_tags'] = $this->getFormInput('display_tags', $displayTagsCaption, $filesFormDatas['display_tags'], '', 'checkbox');
        // display the categories
        $displayCategoriesCaption = _t('_emmetbytes_bon_con_special_info_display_categories_caption');
        $filesForm['inputs']['display_categories'] = $this->getFormInput('display_categories', $displayCategoriesCaption, $filesFormDatas['display_categories'], '', 'checkbox');
        // display the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_display_description_caption');
        $filesForm['inputs']['display_description'] = $this->getFormInput('display_description', $displayDescriptionCaption, $filesFormDatas['display_description'], '', 'checkbox');
        // maximum numbers of characters to be displayed in the description
        $displayDescriptionCaption = _t('_emmetbytes_bon_con_special_info_maximum_description_characters_description');
        $filesForm['inputs']['max_description_chars'] = $this->getFormInput('max_description_chars', $displayDescriptionCaption, $filesFormDatas['max_description_chars']);
        // create the submit input
        $submitCaption = _t('_emmetbytes_bon_con_special_info_admin_submit_caption');
        $filesForm['inputs'][$type.'admin_submit_files_button'] = $this->getFormInput($type.'admin_submit_files_button', '', $submitCaption, '', 'submit');
        parent::BxDolFormMedia($filesForm);
        return $this->getCode();
    }

    // BOF SUBMITTING THE FORM DATAS
    protected function insertFormInputValues(){
        $inputVals = array(
            'name' => ((isset($_POST['name']) && !empty($_POST['name'])) ? $_POST['name'] : ''),
            'default_tab' => ((isset($_POST['default_tab']) && !empty($_POST['default_tab'])) ? $_POST['default_tab'] : ''),
            'info_image_height' => ((isset($_POST['info_image_height']) && !empty($_POST['info_image_height'])) ? $_POST['info_image_height'] : ''),
            'info_image_width' => ((isset($_POST['info_image_width']) && !empty($_POST['info_image_width'])) ? $_POST['info_image_width'] : ''),
            'display_sites_url' => ((isset($_POST['display_sites_url'])) ? '1' : '0'),
            'display_author' => ((isset($_POST['display_author'])) ? '1' : '0'),
            'display_album' => ((isset($_POST['display_album'])) ? '1' : '0'),
            'display_rating' => ((isset($_POST['display_rating'])) ? '1' : '0'),
            'display_location' => ((isset($_POST['display_location'])) ? '1' : '0'),
            'display_tags' => ((isset($_POST['display_tags'])) ? '1' : '0'),
            'display_categories' => ((isset($_POST['display_categories'])) ? '1' : '0'),
            'display_date_start' => ((isset($_POST['display_date_start'])) ? '1' : '0'),
            'display_date_end' => ((isset($_POST['display_date_end'])) ? '1' : '0'),
            'display_description' => ((isset($_POST['display_description'])) ? '1' : '0'),
            'max_description_chars' => ((isset($_POST['max_description_chars']) && !empty($_POST['max_description_chars'])) ? $_POST['max_description_chars'] : ''),
        );
        return $this->oDb->insertBonConSpecialInfoSettings($inputVals);
    }
    // EOF SUBMITTING  THE FORM DATAS

}

class EmmetBytesBonConSpecialInfoAdministrationsd710UpHelper extends EmmetBytesBonConSpecialInfoAdministrationsDefaultHelper{

    // constructor
    function EmmetBytesBonConSpecialInfoAdministrationsd710UpHelper($oMain){
        parent::EmmetBytesBonConSpecialInfoAdministrationsDefaultHelper($oMain);
    }

    // override the homepage settings
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

    // override the module blocks settings
    function getModuleBlocksSettings(){
        // event tabs
        $moduleBlocksSettingsContent = $this->getEventForm('module_blocks_main_upcoming');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_past');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_main_recent');
        $moduleBlocksSettingsContent .= $this->getEventForm('module_blocks_users');
        // groups tabs
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_main_recent');
        $moduleBlocksSettingsContent .= $this->getGroupsForm('module_blocks_users');
        // sites tabs
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_featured');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_main_recent');
        $moduleBlocksSettingsContent .= $this->getSitesForm('module_blocks_users');
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
