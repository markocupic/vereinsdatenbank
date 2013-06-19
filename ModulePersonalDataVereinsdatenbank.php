<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 * Formerly known as TYPOlight Open Source CMS.
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 * PHP version 5
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */


/**
 * Class ModulePersonalData
 * Front end module "personal data".
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class ModulePersonalDataVereinsdatenbank extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'member_default';


    /**
     * Return a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        /***************/
        // Create Staging Table, if there are no inserts
        $this->createStagingTable();
        /***************/

        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### PERSONAL DATA ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->editable = deserialize($this->editable);

        // Return if there are not editable fields or if there is no logged in user
        if (!is_array($this->editable) || empty($this->editable) || !FE_USER_LOGGED_IN) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;
        $this->import('FrontendUser', 'User');

        $GLOBALS['TL_LANGUAGE'] = $objPage->language;

        $this->loadLanguageFile('tl_member');
        $this->loadDataContainer('tl_member');
        $this->loadLanguageFile('tl_module');
        $this->loadDataContainer('tl_module');


        // Call onload_callback (e.g. to check permissions)
        if (is_array($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]();
                }
            }
        }

        // Set template
        if (strlen($this->memberTpl)) {
            $this->Template = new FrontendTemplate($this->memberTpl);
            $this->Template->setData($this->arrData);
        }

        $this->Template->fields = '';
        $this->Template->tableless = $this->tableless;

        $arrFields = array();
        $doNotSubmit = false;
        $hasUpload = false;
        $row = 0;

        /***************/
        //synchronize tables
        if (!$this->Input->post('FORM_SUBMIT')) {
            // $this->synchronize();
        }
        /***************/


        // Build form
        foreach ($this->editable as $field) {

            $arrData = &$GLOBALS['TL_DCA']['tl_member']['fields'][$field];

            // Map checkboxWizard to regular checkbox widget
            if ($arrData['inputType'] == 'checkboxWizard') {
                $arrData['inputType'] = 'checkbox';
            }

            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!$this->classFileExists($strClass) || !$arrData['eval']['feEditable']) {
                continue;
            }

            $strGroup = $arrData['eval']['feGroup'];

            $arrData['eval']['tableless'] = $this->tableless;
            $arrData['eval']['required'] = ($this->User->$field == '' && $arrData['eval']['mandatory']) ? true : false;

            /***************/
            // get tablename tl_member || tbl_member_staging
            $strTable = $this->getStrTable($field, $objWidget);
            $varValue = $strTable == 'tl_member' ? $this->User->$field : $this->getStagingValue($this->User->id, $field);
            $objWidget = new $strClass($this->prepareForWidget($arrData, $field, $varValue, '', $strTable));
            //$objWidget = new $strClass($this->prepareForWidget($arrData, $field, $this->User->$field, '', $strTable));
            /***************/

            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_' . $row . (($row == 0) ? ' row_first' : '') . ((($row % 2) == 0) ? ' even' : ' odd');

            // Increase the row count if it is a password field
            if ($objWidget instanceof FormPassword) {
                ++$row;
                $objWidget->rowClassConfirm = 'row_' . $row . ((($row % 2) == 0) ? ' even' : ' odd');
            }

            // Validate input
            if ($this->Input->post('FORM_SUBMIT') == 'tl_member_' . $this->id) {
                $objWidget->validate();
                $varValue = $objWidget->value;

                $rgxp = $arrData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '') {
                    try {
                        $objDate = new Date($varValue);
                        $varValue = $objDate->tstamp;
                    } catch (Exception $e) {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Make sure that unique fields are unique (check the eval setting first -> #3063)
                if ($arrData['eval']['unique'] && $varValue != '') {
                    $objUnique = $this->Database->prepare("SELECT * FROM tl_member WHERE " . $field . "=? AND id!=?")
                        ->limit(1)
                        ->execute($varValue, $this->User->id);

                    if ($objUnique->numRows) {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field)));
                    }
                }

                // Save callback (see #5247)
                if (!$objWidget->hasErrors() && is_array($arrData['save_callback'])) {
                    foreach ($arrData['save_callback'] as $callback) {
                        $this->import($callback[0]);

                        try {
                            $varValue = $this->$callback[0]->$callback[1]($varValue, $this->User, $this);
                        } catch (Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                // Do not submit if there are errors
                if ($objWidget->hasErrors()) {
                    $doNotSubmit = true;
                } // Store current value
                elseif ($objWidget->submitInput()) {
                    // Set new value
                    $this->User->$field = $varValue;
                    $_SESSION['FORM_DATA'][$field] = $varValue;
                    $varSave = is_array($varValue) ? serialize($varValue) : $varValue;

                    /***************/
                    // Save values to tl_member || tbl_member_staging
                    $this->saveField($objWidget, $field, $varSave);
                    // Save field
                    // $this->Database->prepare("UPDATE tl_member SET " . $field . "=? WHERE id=?")->execute($varSave, $this->User->id);
                    /***************/

                    // HOOK: set new password callback
                    if ($objWidget instanceof FormPassword && isset($GLOBALS['TL_HOOKS']['setNewPassword']) && is_array($GLOBALS['TL_HOOKS']['setNewPassword'])) {
                        foreach ($GLOBALS['TL_HOOKS']['setNewPassword'] as $callback) {
                            $this->import($callback[0]);
                            $this->$callback[0]->$callback[1]($this->User, $varValue, $this);
                        }
                    }
                }
            }


            $temp = $objWidget->parse();

            $this->Template->fields .= $temp;
            $arrFields[$strGroup][$field] .= $temp;
            ++$row;
        }

        /********Add File Uploader to Template*******/
        if (strlen($this->vdb_image_folder)) {
            $hasUpload = true;
            $field = 'vdb_file_upload';

            // set the uplaod-folder for the image
            $arrData = $GLOBALS['TL_DCA']['tl_module']['fields'][$field];
            $arrData['eval']['uploadFolder'] = $this->vdb_image_folder;
            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
            $objWidget = new $strClass($this->prepareForWidget($arrData, $field));
            unset($fileSRC);
            if (!is_dir(TL_ROOT . '/' . $this->vdb_image_folder)) {
                $objWidget->addError('Upload-folder not defined!');
            } else {
                if ($_FILES[$field]['name']) {
                    $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                    $filename = time() . '.' . $ext;
                    $_FILES[$field]['name'] = $filename;
                    $fileSRC = $this->vdb_image_folder . '/' . $filename;
                }
            }
            $objWidget->validate();
            if ($fileSRC && !$objWidget->hasErrors()) {
                // If all ok, write to db
                $this->saveField($objWidget, 'vdb_bild', $fileSRC);
            }
            // add uploadfield to template
            $strGroup = $arrData['eval']['feGroup'];
            $temp = $objWidget->parse();
            $this->Template->fields .= $temp;
            $arrFields[$strGroup][$field] .= $temp;
        }
        /***************/



        $this->Template->hasError = $doNotSubmit;

        // Redirect or reload if there was no error
        if ($this->Input->post('FORM_SUBMIT') == 'tl_member_' . $this->id && !$doNotSubmit) {
            // HOOK: updated personal data
            if (isset($GLOBALS['TL_HOOKS']['updatePersonalData']) && is_array($GLOBALS['TL_HOOKS']['updatePersonalData'])) {
                foreach ($GLOBALS['TL_HOOKS']['updatePersonalData'] as $callback) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($this->User, $_SESSION['FORM_DATA'], $this);
                }
            }

            $this->jumpToOrReload($this->jumpTo);
        }

        /***************/
        // watch for unconfirmed changes in tbl_member_staging
        if ($this->Input->post('FORM_SUBMIT') != 'tl_member_' . $this->id) {

            $objMemberStaging = $this->Database->prepare("SELECT id FROM tbl_member_staging WHERE pid=?")
                ->executeUncached($this->User->id);
            if ($objMemberStaging->numRows > 0) {
                $this->Template->unconfirmedChanges = true;
            }
        }
        /***************/

        $this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_member']['loginDetails'];
        $this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_member']['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_member']['contactDetails'];
        $this->Template->personalData = $GLOBALS['TL_LANG']['tl_member']['personalData'];

        // Add groups
        foreach ($arrFields as $k => $v) {
            $this->Template->$k = $v;
        }

        $this->Template->formId = 'tl_member_' . $this->id;
        $this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
        $this->Template->action = $this->getIndexFreeRequest();
        $this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->rowLast = 'row_' . $row . ((($row % 2) == 0) ? ' even' : ' odd');

        // HOOK: add memberlist fields
        if (in_array('memberlist', $this->Config->getActiveModules())) {
            $this->Template->profile = $arrFields['profile'];
            $this->Template->profileDetails = $GLOBALS['TL_LANG']['tl_member']['profileDetails'];
        }

        // HOOK: add newsletter fields
        if (in_array('newsletter', $this->Config->getActiveModules())) {
            $this->Template->newsletter = $arrFields['newsletter'];
            $this->Template->newsletterDetails = $GLOBALS['TL_LANG']['tl_member']['newsletterDetails'];
        }

        // HOOK: add helpdesk fields
        if (in_array('helpdesk', $this->Config->getActiveModules())) {
            $this->Template->helpdesk = $arrFields['helpdesk'];
            $this->Template->helpdeskDetails = $GLOBALS['TL_LANG']['tl_member']['helpdeskDetails'];
        }
    }

    /**
     * @param $field
     * @param $varSave
     */
    private function saveField($objWidget, $field, $varSave)
    {

        // HOOK: set new password callback
        if ($objWidget instanceof FormPassword || $field == 'username') {
            // Save field
            $this->Database->prepare("UPDATE tl_member SET " . $field . "=? WHERE id=?")
                ->execute($varSave, $this->User->id);
        } else {
            $arrEditable = $this->editable;
            if (!in_array('vdb_bild', $arrEditable)) {
                $arrEditable[] = 'vdb_bild';
            }
            $objMember = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->executeUncached($this->User->id);
            $objMemberStaging = $this->Database->prepare("SELECT * FROM tbl_member_staging WHERE pid=?")
                ->executeUncached($this->User->id);
            if ($objMemberStaging->numRows < 1) {
                // insert only a new row, if there is actualy a change
                if ($varSave != $objMember->{$field}) {
                    $objMember->reset();
                    $set = $objMember->fetchAssoc();
                    $set['tstamp'] = time();
                    $set['editableFields'] = serialize($arrEditable);
                    $set['pid'] = $this->User->id;
                    $set['moduleId'] = $this->id;
                    $set[$field] = $varSave;
                    unset($set['id']);
                    $this->Database->prepare("INSERT INTO tbl_member_staging %s")->set($set)->executeUncached();
                }
            } else {
                // update tbl_member_staging
                $set = array(
                    'tstamp' => time(),
                    'editableFields' => serialize($arrEditable),
                    'moduleId' => $this->id,
                    $field => $varSave
                );
                $this->Database->prepare("UPDATE tbl_member_staging %s WHERE pid=?")->set($set)->executeUncached($this->User->id);
            }
        }
    }

    /**
     * synchronize tbl_member_staging -> tl_member
     */
    private function synchronize()
    {
        $objDbStage = $this->Database->prepare("SELECT id FROM tbl_member_staging WHERE pid=?")->executeUncached($this->User->id);
        if ($objDbStage->numRows < 1) {
            return;
        } else {

            foreach ($this->editable as $field) {
                $objDbStage = $this->Database->prepare("SELECT " . $field . " AS value FROM tbl_member_staging WHERE pid=?")->executeUncached($this->User->id);
                $set = array($field => $objDbStage->value);
                $this->Database->prepare('UPDATE tl_member %s WHERE id=?')->set($set)->executeUncached($this->User->id);
            }
            $this->Database->prepare('DELETE FROM tbl_member_staging WHERE pid=?')->executeUncached($this->User->id);
            $this->reload();
        }
    }

    /**
     * @param $field
     * @param $objWidget
     * @return string
     */
    private function getStrTable($field, $objWidget)
    {
        // set str table only to tbl_member_staging, if there is a form submit
        if ($this->Input->post('FORM_SUBMIT') != 'tl_member_' . $this->id) {
            //return 'tl_member';
        }

        // password and username changes can be made directly
        if ($objWidget instanceof FormPassword || $field == 'username') {
            return 'tl_member';
        } else {
            $objMember = $this->Database->prepare("SELECT " . $field . " AS fieldcontent FROM tbl_member_staging WHERE pid=?")
                ->executeUncached($this->User->id);
            if ($objMember->numRows < 1) {
                return 'tl_member';
            } else {
                return 'tbl_member_staging';
            }
        }
    }

    /**
     * Create Staging Table / copy from tl_member
     */
    public function createStagingTable()
    {
        if ($this->Database->tableExists('tbl_member_staging')) {
            $blnTableExists = true;
            $objMemberStaging = $this->Database->execute('SELECT * FROM tbl_member_staging');
        } else {
            $blnTableExists = false;
        }
        if ($objMemberStaging->numRows < 1 || $blnTableExists === false) {
            $this->Database->query("DROP TABLE IF EXISTS tbl_member_staging");
            $this->Database->query("CREATE TABLE tbl_member_staging AS SELECT * FROM tl_member");
            $this->Database->query("TRUNCATE TABLE tbl_member_staging");
            $this->Database->query("ALTER TABLE tbl_member_staging ADD PRIMARY KEY (id)");
            $this->Database->query("ALTER TABLE tbl_member_staging ADD pid INT(12) NOT NULL AFTER id");
            $this->Database->query("ALTER TABLE tbl_member_staging ADD moduleId INT(12) NOT NULL AFTER pid");
            $this->Database->query("ALTER TABLE tbl_member_staging ADD editableFields text NULL AFTER moduleId");
        }
    }

    /**
     * @param $pid
     * @param $field
     * @return mixed
     */
    public function getStagingValue($pid, $field)
    {
        $objMemberStaging = $this->Database->prepare('SELECT ' . $field . ' AS value FROM tbl_member_staging WHERE pid=?')->execute($pid);
        return $objMemberStaging->value;

    }


}
