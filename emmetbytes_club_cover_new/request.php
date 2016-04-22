<?
/************************************************************************************************************
 * EmmetBytes Default Club Cover
 * Default Club Cover For Creating New Boonex Modules
 ************************************************************************************************************/

require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

check_logged();

bx_import('BxDolRequest');

class EmmetBytesClubCoverRequest extends BxDolRequest {

    function EmmetBytesClubCoverRequest() {
        parent::BxDolRequest();
    }

    function processAsAction($aModule, &$aRequest, $sClass = "Module") {

        $sClassRequire = $aModule['class_prefix'] . $sClass;
        $oModule = BxDolRequest::_require($aModule, $sClassRequire);
        $aVars = array ('BaseUri' => $oModule->_oConfig->getBaseUri());
        $GLOBALS['oTopMenu']->setCustomSubActions($aVars, 'emmet_bytes_club_cover_title', false);

        return BxDolRequest::processAsAction($aModule, $aRequest, $sClass);
    }
}

EmmetBytesClubCoverRequest::processAsAction($GLOBALS['aModule'], $GLOBALS['aRequest']);

?>
