<?php
/**********************************************************************************************
 * Created By : EmmetBytes Software Solutions
 * Created Date : February 20, 2013
 * Email : emmetbytes@gmail.com
 *
 * Copyright : (c) EmmetBytes Software Solutions 2012
 * Product Name : Profile Cover Mnmal
 * Product Version : 1.1
 * 
 * Important : This is a commercial product by EmmetBytes Software Solutions and 
 *   cannot be modified, redistributed or resold without any written permission 
 *   from EmmetBytes Software Solutions
 **********************************************************************************************/
    
bx_import('BxDolProfilesController');
bx_import('BxDolProfileFields');
class EmmetBytesProfileCvrHelper{

    // constructor
    function EmmetBytesProfileCvrHelper(&$oMain){
        global $logged;
        // getting the aArea
        if($logged['member']){
            $this->aArea = 2;
        }else if($logged['admin']){
            $this->aArea = 3;
        }else{
            $this->aArea = 4;
        }
        $this->thumbnailWidth = 166;
        $this->thumbnailHeight = 166;
        $this->thumbnailFileSize = getParam('emmet_bytes_profile_cvr_avatar_size');
        $this->backgroundWidth = (int)getParam('main_div_width');
        $this->backgroundHeight = 285;
        $this->backgroundFileSize = getParam('emmet_bytes_profile_cvr_background_size');
        $this->profileCvrImageUrl = BX_DOL_URL_ROOT . 'modules/EmmetBytes/emmetbytes_profile_cvr/images/profile_covers/';
        $this->compressionLevel = 75;
        $this->oMain = $oMain;
        $this->oDb = $oMain->_oDb;
        $this->oTemplate = $oMain->_oTemplate;
        $this->oConfig = $oMain->_oConfig;
        $this->oPC = new BxDolProfilesController();
        $this->oPF = new BxDolProfileFields($this->aArea);
        $this->boonexVersion = (float)$GLOBALS['ebModuleBoonexVersion'] = (isset($GLOBALS['ebModuleBoonexVersion'] )) ? $GLOBALS['ebModuleBoonexVersion'] : $this->oDb->oParams->_aParams['sys_tmp_version']; 
    }

    // BOF THE METHODS FOR THE PROFILE CVR ACTIONS
    // submitting the temporary background
    function submitTmpBackground($formParams){
        $returnVals = array();
        $this->compressionLevel = getParam('emmet_bytes_profile_cvr_profile_cvr_background_compr_level');
        $ext = explode('.', $formParams['background_image']['name']);
        $ext = $ext[sizeof($ext) - 1];
        $imageName = getLoggedId() . '_' . time() . '.' . $ext;
        $checkBackgroundFilters = $this->checkBackgroundImage($formParams);
        $hasError = $checkBackgroundFilters['has_error'];
        $returnVals['error'] = $checkBackgroundFilters['error'];
        $profileCvrImgDir = $this->profileCvrImageDir . $imageName;
        if(!$hasError && move_uploaded_file($formParams['background_image']['tmp_name'], $profileCvrImgDir)){
            chmod($profileCvrImgDir, 0777);
            $resized = $this->imageResize($profileCvrImgDir, $this->backgroundWidth, $this->backgroundHeight);
            if($resized){
                $imagePath = $this->getProfileCvrOwnImages($profileCvrImgDir);
                $returnVals = array(
                    'error' => false,
                    'image_path' => $imagePath,
                    'image_name' => $imageName
                );
            }else{
                $returnVals['error'] = _t('_emmetbytes_profile_cvr_background_image_upload_problem');
            }
        }
        echo json_encode($returnVals);
        exit;
    }

    // checking for the background image filters
    private function checkBackgroundImage($formParams){
        $returnVals = array('has_error' => false, 'error' => '',);
        $fileSize = $formParams['background_image']['size'];
        list($width, $height) = getimagesize($formParams['background_image']['tmp_name']);
        if($width < $this->backgroundWidth || $height < $this->backgroundHeight){
            $returnVals['has_error'] = true;
            $returnVals['error'] = _t('_emmetbytes_profile_cvr_background_image_size_problem', $this->backgroundWidth, $this->backgroundHeight);
        }else if($this->backgroundFileSize < $fileSize){
            $returnVals['has_error'] = true;
            $returnVals['error'] =  _t('_emmetbytes_profile_cvr_background_image_file_size_problem', $this->backgroundFileSize);
        }
        return $returnVals;
    }

    // submitting the images
    function submitBackgroundImage($_POST){
        if($_POST['submit'] == 'Save'){ // save the background image datas
            $retVal = $this->saveProfileCvrBgImgDatas($_POST);
        }else{ // cancel the background image
            $retVal = $this->cancelProfileCvrBgImgDatas($_POST);
        }
        echo json_encode($retVal);
    }

    // reposition the background
    function repositionBackground(){
        $profileCvrDatas = $this->getProfileCvrDatas(getLoggedId());    
        echo json_encode($profileCvrDatas);
        exit;
    }

    // remove the background
    function removeProfileCvrBackground(){
        $profileId = getLoggedId();
        $profileCvrImgDatas = $this->getProfileCvrDatas($profileId);
        $origImagePath = $profileCvrImgDatas['bg_image_path'];
        $croppedImagePath = $profileCvrImgDatas['bg_image_cropped_path'];
        // remove the original image
        $this->removeImage($origImagePath);
        // remove the cropped image path
        $this->removeImage($croppedImagePath);
        // alert parameters
        $alertParams = array(
            'action' => 'remove_background',
            'entry_id' => $profileCvrImgDatas['id'],
            'profile_id' => $profileId,
            'status' => 'approved',
        );
        $this->alert($alertParams);
        // remove the database data
        $removed = $this->oDb->removeBackgroundProfileCvr($profileCvrImgDatas['id']);
    }

    // getting the profile cvr background menu options
    function getProfileCvrBackgroundMenuOptions(){
        $profileId = getLoggedId();
        $profileCvrDatas = $this->getProfileCvrDatas($profileId);
        echo $this->getInsertBackgroundOptionsContents($profileCvrDatas);
    }

    // submitting the temporary avatar
    function submitTmpAvatar($formParams){
        $returnVals = array();
        $ext = explode('.', $formParams['avatar_image']['name']);
        $ext = $ext[sizeof($ext) - 1];
        $imageName = 'avatar_' . getLoggedId() . '_' . time() . '.' . $ext;
        $checkBackgroundFilters = $this->checkAvatarImage($formParams);
        $hasError = $checkBackgroundFilters['has_error'];
        $returnVals['error'] = $checkBackgroundFilters['error'];
        $profileCvrImgDir = $this->profileCvrImageDir . $imageName;
        if(!$hasError && move_uploaded_file($formParams['avatar_image']['tmp_name'], $profileCvrImgDir)){
            chmod($profileCvrImgDir, 0777);
            $resized = $this->imageResize($profileCvrImgDir, $this->thumbnailWidth, $this->thumbnailHeight);
            if($resized){
                $imagePath = $this->getProfileCvrOwnImages($profileCvrImgDir);
                $returnVals = array(
                    'error' => false,
                    'image_path' => $imagePath,
                    'image_name' => $imageName,
                );
            }
        }
        echo json_encode($returnVals);
        exit;
    }

    // method that checks the avatar image
    private function checkAvatarImage($formParams){
        $returnVals = array('has_error' => false, 'error' => '',);
        $fileSize = $formParams['avatar_image']['size'];
        list($width, $height) = getimagesize($formParams['avatar_image']['tmp_name']);
        if($width < $this->thumbnailWidth || $height < $this->thumbnailHeight){
            $returnVals['has_error'] = true;
            $returnVals['error'] = _t('_emmetbytes_profile_cvr_background_image_size_problem', $this->thumbnailWidth, $this->thumbnailHeight);
        }else if($this->thumbnailFileSize < $fileSize){
            $returnVals['has_error'] = true;
            $returnVals['error'] =  _t('_emmetbytes_profile_cvr_background_image_file_size_problem', $this->thumbnailFileSize);
        }
        return $returnVals;

    }

    // submitting the avatar image
    function submitAvatarImage($formData){
        $this->insertAvatar($formData);
    }

    // submitting the friend request
    function friendRequest($friendId, $action){
        $memberButtons = $this->getMemberButtonsLangs();
        $class = '';
        switch($action){
            case 'add':
                $this->addFriend($friendId);
                $lang = $memberButtons['cancel_friend_request'];
                $class = 'ebytes_profile_cvr_cancel_friend_request';
                $action = 'remove';
                break;
            case 'accept':
                $this->acceptFriendRequest($friendId);
                $lang = $memberButtons['remove_friend'];
                $class = 'ebytes_profile_cvr_remove_friend';
                $action = 'remove';
                break;
            case 'remove':
                $this->removeFriend($friendId);
                $lang = $memberButtons['add_friend'];
                $class = 'ebytes_profile_cvr_add_friend';
                $action = 'add';
                break;
        }
        $returnVals = array(
            'lang' => $lang,
            'action' => $action,
            'className' => $class,
        );
        echo json_encode($returnVals);
    }

    // removing the friend
    private function removeFriend($friendId){
        $oCommunicator = $this->commonFriendRequestCommunicatorClass();
        $aParams = array($friendId);
        $execFunction = $oCommunicator->execFunction( 
            '_deleteRequest', 
            'sys_friend_list', 
            $aParams, 
            array(1, 1)
        );
    }

    // adding the friend
    private function addFriend($iMemberId){
        $loggedId = getLoggedId();
		$sQuery = "INSERT INTO `sys_friend_list` SET 
			`ID` = '{$loggedId}', `Profile` = '{$iMemberId}', `Check` = '0'";

		if ( db_res($sQuery, 0) ) {
            $alertParams = array(
                'unit' => 'friend',
                'action' => 'request',
                'object_id' => $iMemberId,
                'sender_id' => $loggedId,
            );
            $this->sendSystemAlerts($alertParams);
            $emailNotificationParams = array(
                'member_id' => $iMemberId,
                'logged_id' => $loggedId,
                'template_name' => 't_FriendRequest',
                'request_link' => BX_DOL_URL_ROOT  . 'communicator.php?communicator_mode=friends_requests',
            );
            $this->sendEmailNotification($emailNotificationParams);
            return true;
 		} else {
            return false;
		}

    }

    // accepting the friend request
    private function acceptFriendRequest($friendId){
        $oCommunicator = $this->commonFriendRequestCommunicatorClass();
        $aParams = array($friendId);
        $execFunction = $oCommunicator->execFunction( 
            '_acceptFriendInvite', 
            'sys_friend_list', 
            $aParams
        );
    }

    // getting the friends popup datas
    function getFriendPopupDatas($profileId){
        $nickName = getNickName($profileId);
        $profileCvrDatas = $this->oDb->getCoverDataByProfileId($profileId);
        $backgroundImage = '';
        if(sizeof($profileCvrDatas) && !empty($profileCvrDatas['background_image'])){
            $backgroundImage = $this->profileCvrImageDir . 'small_' . $profileCvrDatas['background_image'];
            $backgroundImageArray = $this->getProfileCvrOwnImages($backgroundImage);
            $backgroundImage = $backgroundImageArray[0];
        }
        $avatar = $GLOBALS['oFunctions']->getMemberAvatar($profileId);
        $profileDatas = array(
            'nickname' => $nickName,
            'avatar' => $avatar,
            'backgroundImage' => $backgroundImage,
            'link' => getProfileLink($profileId),
        );
        echo json_encode($profileDatas);
    }


    private function sendSystemAlerts($alertParams){
        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts(
            $alertParams['unit'],
            $alertParams['action'],
            $alertParams['object_id'], 
            $alertParams['sender_id']
        );
        $oZ -> alert();
    }


    private function sendEmailNotification($emailNotificationParams){
        $oEmailTemplate = new BxDolEmailTemplates();
        $aTemplate = $oEmailTemplate -> getTemplate($emailNotificationParams['template_name']);
        $aRecipient = getProfileInfo($emailNotificationParams['member_id']);
        $aPlus = array(
                'Recipient'     => $aRecipient['NickName'],
                'SenderLink'	=> getProfileLink($emailNotificationParams['logged_id']),
                'Sender'		=> getNickName($emailNotificationParams['logged_id']),
                'RequestLink'	=> $emailNotificationParams['request_link'],
                );
        sendMail( $aRecipient['Email'], $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus );
    }
    // EOF THE METHODS FOR THE PROFILE CVR ACTIONS

    // GETTING THE PROFILE HEADER
    function getProfileCvr(){
        // displays on the profile page only
        if(!defined('BX_PROFILE_PAGE')){ return ''; }
        // the profile id 
        $profileId = getID($_GET['ID']);
        // logged id
        $loginId = getLoggedId();
        // insert the css
        $this->oTemplate->addCss(array('main.css', 'form_adv.css'));
        // insert the javascript
        if($this->boonexVersion < 7.1){
            $this->oTemplate->addJs(array('jquery-ui.js', 'EmmetBytesProfileCvr.js', 'ui.datepicker.js', 'ui.draggable.js'));
        }else{
            $this->oTemplate->addJs(array('jquery.ui.all.min.js', 'EmmetBytesProfileCvr.js', 'jquery.ui.datepicker.min.js', 'jquery.ui.draggable.min.js'));
        }
        $aVars = array(
            'profile_cvr_top' => $this->getProfileCvrTopContents($profileId, $loginId),
            'background_menu_insert_caption' => _t('_emmetbytes_profile_cvr_insert_background_caption'),
            'background_menu_change_caption' => _t('_emmetbytes_profile_cvr_change_background_caption'),
            'profile_cvr_bottom' => $this->getProfileCvrBottomContents($profileId, $loginId),
            'base_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri(),
            'background_form_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'submit_background_image',
            'background_menu_options_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_profile_cvr_background_menu_options',
            'avatar_form_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'submit_avatar_image',
        );
        return $this->oTemplate->parseHTMLByName('main_ebytes_profile_cvr_container', $aVars);
    }

    // BOF THE TOP CONTENTS METHODS
    // getting the profile cvr top part
    private function getProfileCvrTopContents($profileId, $loginId){
        $profileCvrDatas = $this->getProfileCvrDatas($profileId);
        $aVars = array(
            'extra_top_container_class' => (empty($profileCvrDatas['bg_image'])) ? 'ebytes_profile_cvr_top_container_no_background' : '',
            'bx_if:allow_change_cover' => array(
                'condition' => getLoggedId() == $profileId ? true : false,
                'content' => array(
                    'insert_background_caption' => (sizeof($profileCvrDatas) && !empty($profileCvrDatas['bg_image'])) ? _t('_emmetbytes_profile_cvr_change_background_caption') : _t('_emmetbytes_profile_cvr_insert_background_caption'),
                    'insert_background_options' => $this->getInsertBackgroundOptionsContents($profileCvrDatas),
                ),
            ),
            'bx_if:show_bg_image' => array(
                'condition' => !empty($profileCvrDatas['bg_image']) ? true : false,
                'content' => array(
                    'bg_image_path' => $profileCvrDatas['bg_image_cropped'],
                ),
            ),
            'bx_if:allow_thumbnail_change' => array(
                'condition' => ($profileId == $loginId) ? true : false,
                'content' => array(
                    'thumbnail_button_caption' => _t('_emmetbytes_profile_cvr_insert_avatar_caption'),
                    'form_avatar_action' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'submit_tmp_avatar',
                ),
            ),
            'member_thumbnail' => $profileCvrDatas['member_avatar'],
            'member_name' => $profileCvrDatas['member_name'],
            'member_buttons' => $this->getMemberButtons($profileId, $loginId),
        );
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_top_container', $aVars);
    }

    // getting the profile cvr datas
    private function getProfileCvrDatas($profileId){
        $profileCvrDatas = $this->oDb->getCoverDataByProfileId($profileId);
        $dProfileCvrDatas = array(
            'id' => $profileCvrDatas['id'],
            'bg_image_name' => '',
            'bg_image' => '',
            'bg_image_cropped' => '',
            'bg_pos_y' => '', 
            'bg_pos_x' => '', 
            'thumbnail_image' => '', 
            'thumbnail_image_cropped' => '', 
            't_pos_y' => '', 
            't_pos_x' => '',
            'member_name' => $this->getMemberName($profileId),
        );
        // sets up the profile cvr background datas
        if(!empty($profileCvrDatas['background_image'])){
            $backgroundImage = $profileCvrDatas['background_image'];
            $backgroundImagePath = $this->profileCvrImageDir . $backgroundImage;
            $backgroundImageUrl = $this->getProfileCvrOwnImages($backgroundImagePath);
            $backgroundImageCroppedPath = $this->profileCvrImageDir . 'crop_' . $backgroundImage;
            $backgroundImageCroppedUrl = $this->getProfileCvrOwnImages($backgroundImageCroppedPath);
            $dProfileCvrDatas['bg_image_name'] = $backgroundImage;
            $dProfileCvrDatas['bg_image'] = $backgroundImageUrl[0];
            $dProfileCvrDatas['bg_image_path'] = $backgroundImagePath;
            $dProfileCvrDatas['bg_image_cropped'] = $backgroundImageCroppedUrl[0] . '?'. time();
            $dProfileCvrDatas['bg_image_cropped_path'] = $backgroundImageCroppedPath;
            $dProfileCvrDatas['bg_pos_y'] = $profileCvrDatas['bg_pos_y'];
            $dProfileCvrDatas['bg_pos_x'] = $profileCvrDatas['bg_pos_x'];
        }
        // sets up the profile cvr thumbnail datas
        $dProfileCvrDatas['member_avatar'] = $this->getMemberAvatar($profileId);
        return $dProfileCvrDatas;
    }

    // getting the insert background options
    private function getInsertBackgroundOptionsContents($profileCvrDatas){
        $aVars = array(
            'bx_repeat:background_options' => array(
                $this->getUploadForm(), // form uploader
                $this->getRepositionForm($profileCvrDatas),// reposition button
                $this->getRemoveForm($profileCvrDatas),// remove button
            ),
        );
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_insert_background_options', $aVars);
    }

    // getting the background uploader form
    private function getUploadForm(){
        $aVars = array(
            'caption' => _t('_emmetbytes_profile_cvr_upload_background_caption'),
            'class' => 'ebytes_profile_cvr_upload_background_container',
            'form_action' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'submit_tmp_background',
        );
        return array(
            'content' => $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_background_uploader_container', $aVars),
        );
    }

    // getting the reposition form
    private function getRepositionForm($profileCvrDatas){
        $content = array( 'content' => '');
        if(sizeof($profileCvrDatas) && !empty($profileCvrDatas['bg_image'])){
            $aVars = array(
                'caption' => _t('_emmetbytes_profile_cvr_reposition_background_caption'),
                'class' => 'ebytes_profile_cvr_reposition_background_container',
                'data_id' => $profileCvrDatas['id'],
                'form_action' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'reposition_image',
            );
            $content['content'] = $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_background_reposition_container', $aVars);
        }
        return $content;
    }

    // getting the remove form
    private function getRemoveForm($profileCvrDatas){
        $content = array( 'content' => '');
        if(sizeof($profileCvrDatas) && !empty($profileCvrDatas['bg_image'])){
            $aVars = array(
                'caption' => _t('_emmetbytes_profile_cvr_remove_background_caption'),
                'class' => 'ebytes_profile_cvr_remove_background_container',
                'data_id' => $profileCvrDatas['id'],
                'form_action' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'remove_image',
            );
            $content['content'] = $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_background_remove_container', $aVars);
        }
        return $content;
    }

    // save the profile cvr background image datas
    private function saveProfileCvrBgImgDatas($datas){
        // initialize variables
        $this->compressionLevel = getParam('emmet_bytes_profile_cvr_profile_cvr_background_compr_level');
        $imageName = $datas['image_name'];
        $srcX = abs($datas['x_pos']);
        $srcY = abs($datas['y_pos']);
        $loggedId = getLoggedId();
        // query parameters
        $queryParams = array(
            'profile_id' => $loggedId,
            'background_image' => $imageName,
            'bg_pos_x' => $srcX,
            'bg_pos_y' => $srcY
        );
        // image parameters
        $imageParams = array(
            'image_name' => $imageName, 
            'image_width' => $this->backgroundWidth,
            'image_height' => $this->backgroundHeight,
            'src_x' => $srcX,
            'src_y' => $srcY,
        );
        $imagePath = $this->cropImage($imageParams); 
        $resizeImageParams = array(
            'image_path' => $imagePath,
            'image_name' => $imageName,
            'image_width' => $this->backgroundWidth,
            'image_height' => $this->backgroundHeight,
            'resized_image_width' => round($this->backgroundWidth / 3),
            'resized_image_height' => round($this->backgroundHeight / 3),
        );
        $this->resizeImage($resizeImageParams);
        $profileData = $this->oDb->getCoverDataByProfileId($loggedId);
        if(sizeof($profileData)){
            $this->oDb->updateBackgroundInfo($queryParams);
            $action = 'change_background';
            $entryId = $profileData['id']; 
        }else{
            $entryId = $this->oDb->insertBackgroundInfo($queryParams);
            $action = 'add_background';
        }
        if($datas['type'] == 'reposition'){
            $action = 'repositioned_background';
        }
        // alert parameters
        $alertParams = array(
            'action' => $action,
            'entry_id' => $entryId,
            'profile_id' => $loggedId,
            'status' => 'approved',
        );
        $this->alert($alertParams);
        $retVal = $this->getProfileCvrDatas(getLoggedId());
        $retVal['action'] = 'insert';
        return $retVal;
    }

    // cancel the insertion of the new profile cvr background image
    private function cancelProfileCvrBgImgDatas($datas){
        $imagePath = $this->profileCvrImageDir . $datas['image_name'];
        if($datas['fresh'] == 'true'){
            $this->removeImage($imagePath);
        }
        $profileCvrImgDatas = $this->getProfileCvrDatas(getLoggedId());
        $retVal = array(
            'hasData' => true,
            'action' => 'cancel',
        );
        if(sizeof($profileCvrImgDatas) && !empty($profileCvrImgDatas['bg_image'])){
            $retVal = array_merge($retVal, $profileCvrImgDatas);
        }else{
            $retVal['hasData'] = false;
        }
        return $retVal;
    }

    // save the avatar image
    private function insertAvatar($formData){
        // initialize variables
        $imageName = $formData['image_name'];
        $srcX = abs($formData['x_pos']);
        $srcY = abs($formData['y_pos']);
        $loggedId = getLoggedId();
        // query parameters
        $queryParams = array(
            'profile_id' => $loggedId,
            'thumbnail_image' => $imageName,
            't_pos_x' => $srcX,
            't_pos_y' => $srcY
        );
        // image parameters
        $imageParams = array(
            'image_name' => $imageName, 
            'image_width' => $this->thumbnailWidth,
            'image_height' => $this->thumbnailHeight,
            'src_x' => $srcX,
            'src_y' => $srcY,
        );
        $imagePath = $this->cropImage($imageParams);
        $profileData = $this->oDb->getCoverDataByProfileId($loggedId);
        if($formData['submit'] == 'Save'){
            if(sizeof($profileData)){
                $this->oDb->updateAvatarInfo($queryParams);
            }else{
                $entryId = $this->oDb->insertAvatarInfo($queryParams);
            }
            $this->createAvatar($imagePath);
            $imagePath = $this->getProfileCvrOwnImages($imagePath);
            $returnVal = array(
                'action' => 'save',
                'image_path' => $imagePath
            );
        }else{
            $imagePath = $this->profileCvrImageDir . 'crop_' . $profileData['thumbnail_image'];
            $imagePath = $this->getProfileCvrOwnImages($imagePath);
            $returnVal = array(
                'action' => 'cancel',
                'image_path' => $imagePath
            );
        }
        echo json_encode($returnVal);
    }

    // getting the member buttons
    private function getMemberButtons($profileId, $loginId){
        if($loginId <= 0){
            return false;
        }else if($profileId == $loginId){
            return $this->getMemberOwnButtons($profileId);
        }else if($this->isFriend($profileId, $loginId)){
            return $this->getFriendsOwnButtons($profileId, $loginId); 
        }else if($this->isPendingFriendRequest($loginId, $profileId)){
            return $this->getInvitedMemberButtons($profileId, $loginId);
        }else if($this->isPendingFriendRequest($profileId, $loginId)){
            return $this->getMembersWhoInviteMeButtons($profileId, $loginId);
        }else{
            return $this->getOtherMemberButtons($profileId, $loginId);
        }
    }

    // getting the users own buttons
    private function getMemberOwnButtons($loginId){
        $aVars = array(
            'caption' => _t('_emmetbytes_profile_cvr_update_info_caption'),
            'link' => BX_DOL_URL_ROOT . 'pedit.php?ID=' . $loginId,
            'function' => '',
            'eb_link' => '',
            'eb_action' => '',
            'class' => 'ebytes_profile_cvr_view_profile',
        );
        return $this->createMemberButton($aVars);
    }

    // getting the users friends buttons
    private function getFriendsOwnButtons($profileId, $loginId){
        $buttonsLang = $this->getMemberButtonsLangs();
        $aVars = array(
            'caption' => $buttonsLang['remove_friend'],
            'link' => '#',
            'function' => 'onclick="ebProfileCvrFriendButtonAction(this, \'' . $profileId . '\'); return false;"',
            'eb_link' => 'eb_link=' . BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'friend_request',
            'eb_action' => 'eb_action="remove"',
            'class' => 'ebytes_profile_cvr_remove_friend',
        );
        $buttons =  $this->createMemberButton($aVars);
        $buttons .=  $this->getSendMessageButton($profileId);
        return $buttons;
    }

    // getting the invited member buttons
    private function getInvitedMemberButtons($profileId, $loginId){
        $buttonsLang = $this->getMemberButtonsLangs();
        $aVars = array(
            'caption' => $buttonsLang['cancel_friend_request'],
            'link' => '#',
            'function' => 'onclick="ebProfileCvrFriendButtonAction(this, \'' . $profileId . '\'); return false;"',
            'eb_link' => 'eb_link=' . BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'friend_request',
            'eb_action' => 'eb_action="remove"',
            'class' => 'ebytes_profile_cvr_cancel_friend_request',
        );
        $buttons =  $this->createMemberButton($aVars);
        $buttons .=  $this->getSendMessageButton($profileId);
        return $buttons;
    }

    // getting the buttons for the members who invited me
    private function getMembersWhoInviteMeButtons($profileId, $loginId){
        $buttonsLang = $this->getMemberButtonsLangs();
        $aVars = array(
            'caption' => $buttonsLang['accept_friend_request'],
            'link' => '#',
            'function' => 'onclick="ebProfileCvrFriendButtonAction(this, \'' . $profileId . '\'); return false;"',
            'eb_link' => 'eb_link=' . BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'friend_request',
            'eb_action' => 'eb_action="accept"',
            'class' => 'ebytes_profile_cvr_accept_friend_request',
        );
        $buttons =  $this->createMemberButton($aVars);
        $buttons .=  $this->getSendMessageButton($profileId);
        return $buttons;

    }

    // getting the other members buttons
    private function getOtherMemberButtons($profileId, $loginId){
        $memberButtons = $this->getMemberButtonsLangs();
        $aVars = array(
            'caption' => $memberButtons['add_friend'],
            'link' => '#',
            'function' => 'onclick="ebProfileCvrFriendButtonAction(this, \'' . $profileId . '\'); return false;"',
            'eb_link' => 'eb_link=' . BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'friend_request',
            'eb_action' => 'eb_action="add"',
            'class' => 'ebytes_profile_cvr_add_friend',
        );
        $buttons =  $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_member_button_container', $aVars);
        $buttons .= $this->getSendMessageButton($profileId);
        return $buttons;
    }

    // getting the send a message button
    private function getSendMessageButton($profileId){
        $aVars = array(
            'caption' => _t('_emmetbytes_profile_cvr_send_message_caption'),
            'link' => BX_DOL_URL_ROOT . 'mail.php?mode=compose&recipient_id=' . $profileId,
            'function' => '',
            'class' => 'ebytes_profile_cvr_send_message',
        );
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_member_button_container', $aVars);
    }

    // create members button
    private function createMemberButton($aVars){
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_member_button_container', $aVars);
    }

    // getting the member buttons languages
    private function getMemberButtonsLangs(){
        return array(
            'remove_friend' => _t('_emmetbytes_profile_cvr_remove_friend_caption'),
            'cancel_friend_request' => _t('_emmetbytes_profile_cvr_member_cancel_friend_request'),
            'accept_friend_request' => _t('_emmetbytes_profile_cvr_accept_friend_request_caption'),
            'add_friend' => _t('_emmetbytes_profile_cvr_add_friend_caption'),
        );
    }
    // EOF THE TOP CONTENTS METHODS

    // BOF THE BOTTOM CONTENTS METHODS
    // getting the bottom contents
    private function getProfileCvrBottomContents($profileId, $loginId){
        $aVars = array(
            'bottom_menus' => $this->getBottomContentsMenus(),
        );
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_bottom_container', $aVars);
    }

    // getting the bottom contents menus
    private function getBottomContentsMenus(){
        $aTopMenuObj = $GLOBALS['oTopMenu'];
        $menuInfo = $aTopMenuObj->getMenuInfo();
        $currentTop = $aTopMenuObj->aMenuInfo['currentTop'];
        $topMenus = $aTopMenuObj->aTopMenu;
        $totalLinks = getParam('emmet_bytes_profile_cvr_max_menu_count');
        $linksCount = 0;
        $aVars = array(
            'bx_repeat:display_menus' => array(),
            'more_menus' => '',
        );
        $moreMenusContents = array();
        foreach($topMenus as $iItemId => $aItem){
            if($aItem['Type'] != 'custom' || $aItem['Parent'] != $currentTop || !$aTopMenuObj->checkToShow($aItem) || $iItemId == $aTopMenuObj->aMenuInfo['currentCustom']){
                continue;
            }
            list($aItem['Link']) = explode('|', $aItem['Link']);
            $aItem['Link'] = $aTopMenuObj->replaceMetas($aItem['Link']);
            $link = $aItem['Link'];
            $caption = _t($aItem['Caption']);
            if($linksCount < $totalLinks){
                $aVars['bx_repeat:display_menus'][] = array(
                    'link' => $link,
                    'title' => $caption,
                );
            }else{
                $moreMenusContents['bx_repeat:more_menus'][] = array(
                    'link' => $link,
                    'title' => $caption,
                );
            }
            $linksCount++;
        }
        if(sizeof($moreMenusContents) > 0){
            $aVars['more_menus'] = $this->getBottomMoreMenus($moreMenusContents);
        }
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_bottom_menus_container', $aVars);
    }

    private function getBottomMoreMenus($moreMenusContents){
        $aVars = $moreMenusContents;
        $aVars['more_menus_caption'] = _t('_emmetbytes_profile_cvr_more_menus_caption');
        return $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_bottom_more_menus_container', $aVars);
    }

    // getting the member avatar
    private function getMemberAvatar($profileId){
        $memberAvatar = $this->getUserAvatar($profileId);
        $memberAvatarDir = str_replace(BX_DOL_URL_ROOT, BX_DIRECTORY_PATH_ROOT, $memberAvatar);
        list($iw, $ih) = getimagesize($memberAvatarDir);
        $leftMargin = ($this->thumbnailWidth - $iw) / 2;
        $topMargin = ($this->thumbnailHeight - $ih) / 2;
        $aVars = array(
            'member_avatar' => $memberAvatar,
            'margin_left' => $leftMargin,
            'margin_top' => $topMargin,
        );
        return $this->oTemplate->parseHTMLByName('ebytes_member_avatar_container', $aVars);
    }

    // getting the members avatar, based on the from BxBaseFunction
    private function getUserAvatar($iId, $sType = 'emmetbytes'){
        $aProfile = getProfileInfo($iId);
        if (!$aProfile || !@include_once (BX_DIRECTORY_PATH_MODULES . 'boonex/avatar/include.php'))
            return false;
        if($sType == 'emmetbytes' && is_file(BX_AVA_DIR_USER_AVATARS . $aProfile['Avatar'] . 'eb' . BX_AVA_EXT)){
            return BX_AVA_URL_USER_AVATARS . $aProfile['Avatar'] . 'eb' . BX_AVA_EXT;
        }else{
            if($sType == 'emmetbytes'){ $sType = 'medium'; }
            return $aProfile['Avatar'] ? BX_AVA_URL_USER_AVATARS . $aProfile['Avatar'] . ($sType == 'small' ? 'i' : '') . BX_AVA_EXT : $GLOBALS['oFunctions']->getSexPic($aProfile['Sex'], $sType);
        }
    }
    // EOF THE BOTTOM CONTENTS METHODS
    // BOF THE PROFILE CONTROLLER ACTIONS
    // update the profiles
    private function updateProfile($profileId, $formData){
        return $this->oPC->updateProfile($profileId, $formData);
    }
    // EOF THE PROFILE CONTROLLER ACTIONS

    // BOF THE FORM CONTAINER
    // generate the select container
    private function generateSelectContainer($datas, $name, $class=''){
        $htmlSelect = '<select name="' . $name . '" class="'. $class . '">';
        foreach ($datas as $value=>$data) {
            $htmlSelect .= '<option value="' . $value . '">' . $data . '</option>';
        }
        $htmlSelect .= '</select>';
        return $htmlSelect;
    }

    // generate the form error contents
    private function generateFormErrorContents($errors = array()){
        $contents = '';
        foreach($errors as $error){
            $aVars = array( 'error' => $error,); 
            $contents .= $this->oTemplate->parseHTMLByName('ebytes_profile_cvr_form_error_container', $aVars);
        }
        return $contents;
    }
    // EOF THE FORM CONTAINER

    // BOF THE METHODS THAT MIMICKS THE SYSTEM DATAS
    // getting the video files path
    private function getVideoFilesPath(){
        return BX_DIRECTORY_PATH_ROOT . 'flash/modules/video/files/';
    }

    // getting the video files url
    private function getVideoFilesUrl(){
        return BX_DOL_URL_ROOT . 'flash/modules/video/files/';
    }

    // getting the members videos
    private function getMemberVideo($id){
        return $this->getVideoFilesUrl() . $id . '_small.jpg';
    }

    // getting the members ads files path
    private function getAdsFilesPath(){
        return BX_DOL_URL_ROOT . 'media/images/classifieds/';
    }

    // getting the members ads images
    private function getMemberAdsImage($adsImageDatas){
        $adsImages = array();
        foreach($adsImageDatas as $adsImageData){
            $adsImages[] = $this->getAdsFilesPath() . 'thumb_' . $adsImageData;
        }
        return $adsImages;
    }

    // getting the members blog posts file path
    private function getMembersBlogPostsFilePath(){
        return BX_DOL_URL_ROOT . 'media/images/blog/big_';
    }

    // getting the members blog post image
    private function getMemberBlogPostsImage($blogPostsImageDatas){
        $blogPostsImages = array();
        foreach($blogPostsImageDatas as $blogPostsImageData){
            $blogPostsImages[] = $this->getMembersBlogPostsFilePath() . $blogPostsImageData['PostPhoto'];
        }
        return $blogPostsImages;
    }
    
    // getting the members website photo
    private function getMemberWebsitePhotos($photos){
        $websitePhotos = array();
        foreach($photos as $photo){
            $aPhoto = BxDolService::call('photos', 'get_photo_array', array($photo['photo'], 'browse'), 'Search'); 
            $websitePhotos[] = $aPhoto['file'];
        }
        return $websitePhotos;
    }

    // getting the members event photos
    private function getMemberEventPhotos($photos){
       $eventPhotos = array(); 
       foreach($photos as $photo){
            $aPhoto = BxDolService::call('photos', 'get_photo_array', array($photo['PrimPhoto'], 'browse'), 'Search'); 
            $eventPhotos[] = $aPhoto['file'];
       }
       return $eventPhotos;
    }

    // getting the members store photos
    private function getMemberStorePhotos($photos){
        $storePhotos = array();
        foreach($photos as $photo){
            $aPhoto = BxDolService::call('photos', 'get_photo_array', array($photo['thumb'], 'browse'), 'Search');
            $storePhotos[] = $aPhoto['file'];
        }
        return $storePhotos;
    }

    // getting the members group photos
    private function getMemberGroupPhotos($photos){
        $memberGroupPhotos = array();
        foreach($photos as $photo){
            $aPhoto = BxDolService::call('photos', 'get_photo_array', array($photo['thumb'], 'browse'), 'Search');
            $memberGroupPhotos[] = $aPhoto['file'];
        }
        return $memberGroupPhotos;
    }
    // EOF THE METHODS THAT MIMICKS THE SYSTEM DATAS

    // BOF THE METHODS THAT MIMICKS THE AVATAR MODULE
    // create the avatar
    private function createAvatar($img){
        BxDolService::call('avatar', 'make_avatar_from_image', array($img));
    }
    // EOF THE METHODS THAT MIMICKS THAT AVATAR MODULE

    // BOF THE METHODS FOR THE BxTemplCommunicator CLASS
    // creating an instance of the common friend request communicator class
    private function commonFriendRequestCommunicatorClass(){
        bx_import('BxTemplCommunicator');
        $loggedId = getLoggedId();
        $aCommunicatorSettings = array(
            'member_id' => $loggedId
        );
        if($this->boonexVersion >= '7.1.0'){
            return new BxTemplCommunicator(
                $aCommunicatorSettings
            );
        }else{
            return new BxTemplCommunicator(
                'communicator_page', 
                $aCommunicatorSettings
            );
        }
    }
    // EOF THE METHODS FOR THE BxTemplCommunicator CLASS

    // BOF THE IMAGE METHODS
    // getting the profile cvr own images
    private function getProfileCvrOwnImages($imagePath){
        $imageUrlPath = base64_encode($imagePath);
        return array(BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'get_own_image/' . $imageUrlPath);
    }

    // getting the default image
    function getOwnImage($defaultImage){
        $image = base64_decode($defaultImage);
        $imageInfo = getimagesize($image);
        header('Content-Type: ' . $imageInfo['mime']);
        echo readfile($image);
    }

    // resize the image
    private function imageResize($imagePath, $defaultWidth, $defaultHeight){
        $imageInfo = getimagesize($imagePath);        
        switch($imageInfo[2]){
            case IMAGETYPE_JPEG:
                $imageRsrc = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $imageRsrc = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $imageRsrc = imagecreatefromgif($imagePath);
                break;
        }
        $imageSrcWidth = $imageInfo[0];
        $imageSrcHeight = $imageInfo[1];
        $widthRatio = $defaultWidth / $imageSrcWidth;
        $heightRatio = $defaultHeight / $imageSrcHeight;
        if($widthRatio > $heightRatio){
            $imageWidth = $defaultWidth;
            $imageHeight = $imageSrcHeight * $widthRatio;
        }else{
            $imageWidth = $imageSrcWidth * $heightRatio;
            $imageHeight = $defaultHeight;
        }
        $newImage = imagecreatetruecolor($imageWidth, $imageHeight);
        imagecopyresampled($newImage, $imageRsrc, 0, 0, 0, 0, $imageWidth, $imageHeight, $imageSrcWidth, $imageSrcHeight);
        return (imagejpeg($newImage, $imagePath, (int)$this->compressionLevel)) ? true : false;
    }

    // crop the profile cvr image
    private function cropImage($imageParams){
        // initialize image parameter
        $imgName = $imageParams['image_name'];
        $imgWidth = $imageParams['image_width'];
        $imgHeight = $imageParams['image_height'];
        $srcX = $imageParams['src_x'];
        $srcY = $imageParams['src_y'];
        $tmpImagePath = $this->profileCvrImageDir . $imgName;
        $imagePath = $this->profileCvrImageDir . 'crop_' . $imgName;
        $imageSize = getimagesize($tmpImagePath);
        $imageRsrc = $this->getImageResource($tmpImagePath);
        $newImage = imagecreatetruecolor($imgWidth, $imgHeight);
        imagecopyresampled($newImage, $imageRsrc, 0, 0, $srcX, $srcY, $imageSize[0], $imageSize[1], $imageSize[0], $imageSize[1]);
        imagejpeg($newImage, $imagePath, (int)$this->compressionLevel);
        chmod($imagePath, 0777);
        return $imagePath;
    }

    // resize the images
    private function resizeImage($imageParams){
        $imageName = $imageParams['image_name'];
        $imagePath = $imageParams['image_path'];
        $newImagePath = $this->profileCvrImageDir . 'small_' . $imageName;
        $imageWidth = $imageParams['image_width'];
        $imageHeight = $imageParams['image_height'];
        $rImageWidth = $imageParams['resized_image_width'];
        $rImageHeight = $imageParams['resized_image_height'];
        $imgRsrc = $this->getImageResource($imagePath);
        $newImage = imagecreatetruecolor($rImageWidth, $rImageHeight);
        imagecopyresized(
            $newImage, 
            $imgRsrc, 
            0, 
            0, 
            0, 
            0, 
            $rImageWidth, 
            $rImageHeight, 
            $imageWidth,
            $imageHeight
        );
        imagejpeg($newImage, $newImagePath);
        chmod($newImagePath, 0777);
        return $newImagePath;
    }

    // getting the image resource
    private function getImageResource($imagePath){
        $imageSize = getimagesize($imagePath);
        switch($imageSize[2]){
            case IMAGETYPE_JPEG:
                $imageRsrc = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $imageRsrc = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $imageRsrc = imagecreatefromgif($imagePath);
                break;
        }
        return $imageRsrc;
    }

    // remove the image
    private function removeImage($imagePath){
        unlink($imagePath);
    }
    // EOF THE IMAGE METHODS

    // BOF THE ALERT METHODS
    // alert
    private function alert($alertParams){
        // initialize the alert parameters
        $action = $alertParams['action'];
        $entryId = $alertParams['entry_id'];
        $profileId = $alertParams['profile_id'];
        $status = $alertParams['status'];
        $oAlert = new BxDolAlerts($this->oMain->_sPrefix, $action, $entryId, $profileId, array('status' => $status)); 
        $oAlert->alert();
    }

    // getting the alert common languages
    private function getAlertLanguages($type){
        return array(
            'add_background' => '_eb_profile_cvr_add_' . $type . '_post_background',
            'change_background' => '_eb_profile_cvr_change_' . $type . '_post_background',
            'repositioned_background' => '_eb_profile_cvr_repositioned_' . $type . '_post_background',
            'remove_background' => '_eb_profile_cvr_remove_' . $type . '_post_background',
            'add_thumbnail' => '_eb_profile_cvr_add_' . $type . '_post_thumbnail',
            'change_thumbnail' => '_eb_profile_cvr_change_' . $type . '_post_thumbnail',
        );
    }
    
    // getting the spy data
    function getSpyData(){
        return $this->commonAlertDatas('get_spy_post');
    }

    // getting the spy post
    function getSpyPost($sAction, $iObjectId, $iSenderId, $aExtraParams){
        $lang = $this->getAlertLanguages('spy');
        return $this->oMain->_serviceGetSpyPost($sAction, $iObjectId, $iSenderId, $aExtraParams, $lang);
    }

    // getting the wall data
    function getWallData(){
        return $this->commonAlertDatas('get_wall_post');
    }

    // getting the wall post
    function getWallPost($aEvent){
        $languages = $this->getAlertLanguages('wall');
        $sTextAction = _t($languages[$aEvent['action']]);
        $sTextWallObject = 'test wall object';
        // check for the ownership
        if (!($aProfile = getProfileInfo($aEvent['owner_id']))){
            return '';
        }
        // check for the correct data entry
	    if (!($aDataEntry = $this->oMain->_oDb->getEntryByIdAndOwner ($aEvent['object_id'], $aEvent['owner_id'], 0))){
            return '';
        }
        $sCss = '';        
        if($aEvent['js_mode']){
            $sCss = $this->oMain->_oTemplate->addCss('wall_post.css', true);
        }else{
            $this->oMain->_oTemplate->addCss('wall_post.css');
        }
        $aVars = array(
                'cpt_user_name' => $aProfile['NickName'],
                'cpt_action' => $sTextAction,
                'cpt_object' => $sTextWallObject,
                'cpt_item_url' => BX_DOL_URL_ROOT . $this->oMain->_oConfig->getBaseUri() . 'view/' . $aDataEntry[$this->oMain->_oDb->_sFieldUri],
                'post_id' => $aEvent['id'],
        );
        return array(
            'title' => $aProfile['username'] . ' ' . $sTextAction . ' ' . $sTextWallObject,
            'description' => 'test description', // $aDataEntry[$this->oMain->_oDb->_sFieldDesc],
            'content' => $sCss . $this->oMain->_oTemplate->parseHtmlByName('wall_post', $aVars)
        );
    }

    // common alert datas
    private function commonAlertDatas($alertMethod){
        return array(
            'handlers' => array(
                array('alert_unit' => $this->oMain->_sPrefix, 'alert_action' => 'add_background', 'module_uri' => $this->oMain->_aModule['uri'], 'module_class' => 'Module', 'module_method' => $alertMethod),
                array('alert_unit' => $this->oMain->_sPrefix, 'alert_action' => 'change_background', 'module_uri' => $this->oMain->_aModule['uri'], 'module_class' => 'Module', 'module_method' => $alertMethod),
                array('alert_unit' => $this->oMain->_sPrefix, 'alert_action' => 'repositioned_background', 'module_uri' => $this->oMain->_aModule['uri'], 'module_class' => 'Module', 'module_method' => $alertMethod),
                array('alert_unit' => $this->oMain->_sPrefix, 'alert_action' => 'remove_background', 'module_uri' => $this->oMain->_aModule['uri'], 'module_class' => 'Module', 'module_method' => $alertMethod),
                array('alert_unit' => $this->oMain->_sPrefix, 'alert_action' => 'add_thumbnail', 'module_uri' => $this->oMain->_aModule['uri'], 'module_class' => 'Module', 'module_method' => $alertMethod),
                array('alert_unit' => $this->oMain->_sPrefix, 'alert_action' => 'change_thumbnail', 'module_uri' => $this->oMain->_aModule['uri'], 'module_class' => 'Module', 'module_method' => $alertMethod),
            ),
            'alerts' => array(
                array('unit' => $this->oMain->_sPrefix, 'action' => 'add_background'),
                array('unit' => $this->oMain->_sPrefix, 'action' => 'change_background'),
                array('unit' => $this->oMain->_sPrefix, 'action' => 'repositioned_background'),
                array('unit' => $this->oMain->_sPrefix, 'action' => 'remove_background'),
                array('unit' => $this->oMain->_sPrefix, 'action' => 'add_thumbnail'),
                array('unit' => $this->oMain->_sPrefix, 'action' => 'change_thumbnail'),
            )
        );
    }
    // EOF THE ALERT METHODS

    // getting the member name
    private function getMemberName($profileId){
        $profileInfo = getProfileInfo($profileId);
        return (!empty($profileInfo['FirstName']) || !empty($profileInfo['LastName'])) 
            ? $profileInfo['FirstName'] . ' ' . $profileInfo['LastName'] 
            : $profileInfo['NickName'];
    }

    // checks if the member is a friend
    private function isFriend($profileId, $loginId){
        $isFriends = is_friends($profileId, $loginId);
        return $isFriends;
    }

    // checks if the logged id has already sent an invite
    private function memberIsInvited($loggedId, $profileId){
        return $this->isPendingFriendRequest($loggedId, $profileId);
    }

    // checks if the member was already added or has added the logged member as a friend
    private function isPendingFriendRequest($memberId1, $memberId2){
        $checkMemberConnection = $this->oDb->checkMemberConnection($memberId1, $memberId2);
        if(isset($checkMemberConnection['Check']) && !$checkMemberConnection['Check']){
            return true;
        }else{
            return false;
        }
    }
}
?>
