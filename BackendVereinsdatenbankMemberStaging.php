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

class BackendVereinsdatenbankMemberStaging extends BackendModule
{
    public $strTemplate = 'be_vereinsdatenbank';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            // Include the sylesheet
            $GLOBALS['TL_CSS'][] = 'system/modules/vereinsdatenbank/assets/css/be_css.css';

            // delete orphaned data records
            if ($this->Database->tableExists('tbl_member_staging')) {
                $objMemberStaging = $this->Database->execute('SELECT id, pid FROM tbl_member_staging');
                while ($objMemberStaging->next()) {
                    $objMember = $this->Database->prepare('SELECT * FROM tl_member WHERE id=?')->execute($objMemberStaging->pid);
                    if (!$objMember->numRows || $objMember->disable == 1) {
                        $this->Database->prepare('DELETE FROM tbl_member_staging WHERE id=?')->execute($objMemberStaging->id);
                    }
                }
            } else {
                die('ERROR in ' . __METHOD__ . ' on line ' . __LINE__ . '. Table tbl_member_staging doesn\'t exist!');
            }
        }

        return parent::generate();


    }

    public function compile()
    {
        $systemMessages = array();
        switch ($this->Input->get('action')) {
            default:
                $objTemplate = new BackendTemplate('partial_member_staging_list_entries');
                $objMemberStaging = $this->Database->execute('SELECT * FROM tbl_member_staging,tl_member WHERE tbl_member_staging.pid=tl_member.id ORDER BY tl_member.id');
                if ($objMemberStaging->numRows < 1) {
                    $systemMessages[] = $GLOBALS['TL_LANG']['vereinsdatenbank']['be']['system_message']['no_entries_available'];
                } else {
                    $objTemplate->arrRows = $objMemberStaging->fetchAllAssoc();
                }
                $this->Template->partial = $objTemplate->parse();
                $this->Template->headline = $GLOBALS['TL_LANG']['vereinsdatenbank']['be']['headline_0'];
                $this->Template->systemMessages = count($systemMessages) ? $systemMessages : null;

                break;
            case ("show_entry"):

                // Delete dataRecord in tbl_member_staging if the discard button was clicked
                if ($this->Input->post('FORM_SUBMIT') == 'member_staging' && $this->Input->post('discard')) {
                    $this->Database->prepare('DELETE FROM tbl_member_staging WHERE pid=?')->execute($this->Input->get('id'));
                    $this->redirect('contao/main.php?do=vdb_member_staging');
                }

                $this->loadLanguageFile('tl_member');
                $this->loadDataContainer('tl_member');

                $objTemplate = new BackendTemplate('partial_member_staging_watch_entry');

                // Load data records in tbl_member_staging & tl_member
                $objMemberStaging = $this->Database->prepare('SELECT * FROM tbl_member_staging WHERE pid=?')->execute($this->Input->get('id'));
                $objMember = $this->Database->prepare('SELECT * FROM tl_member WHERE id=?')->execute($this->Input->get('id'));
                $arrMemberStaging = $objMemberStaging->fetchAllAssoc();
                $arrMemberStaging = $arrMemberStaging[0];
                $arrMember = $objMember->fetchAllAssoc();
                $arrMember = $arrMember[0];

                $arrDataRecord = array();
                $arrModifiedFields = array();
                $arrEditableFields = unserialize($arrMemberStaging['editableFields']);
                foreach ($arrEditableFields as $field) {

                    if ($arrMemberStaging[$field] != $arrMember[$field]) {
                        $arrData = &$GLOBALS['TL_DCA']['tl_member']['fields'][$field];

                        // Map checkboxWizard to regular checkbox widget
                        if ($arrData['inputType'] == 'checkboxWizard') {
                            $arrData['inputType'] = 'checkbox';
                        }

                        $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

                        // Continue if the class is not defined
                        if (!$this->classFileExists($strClass)) {
                            continue;
                        }
                        $strGroup = $arrData['eval']['feGroup'];
                        $arrData['eval']['tableless'] = true;
                        $arrData['eval']['required'] = ($arrMemberStaging[$field] == '' && $arrData['eval']['mandatory']) ? true : false;

                        $objWidgetStaging = new $strClass($this->prepareForWidget($arrData, $field . '_new', $arrMemberStaging[$field], '', 'tbl_member_staging'));

                        // Continue if field is a password field
                        if ($objWidgetStaging instanceof FormPassword) {
                            continue;
                        }

                        // disable input fields and set the readonly property for the current entries
                        $arrData['eval']['readonly'] = true;
                        if ($arrData['inputType'] != 'text' && $arrData['inputType'] != 'textares') {
                            $arrData['eval']['disabled'] = true;
                        }
                        $objWidgetCurrent = new $strClass($this->prepareForWidget($arrData, $field . '_current', $arrMember[$field], '', 'tl_member'));

                        // adopt changes from tbl_member_staging to tl_member
                        if ($this->Input->post('FORM_SUBMIT') == 'member_staging' && $this->Input->post('adopt') && $this->Input->post('group_' . $field) == 'adopt_modification') {
                            $objWidgetStaging->value = $this->Input->post($field . '_new');
                            $objWidgetStaging->validate();
                            $varValue = $objWidgetStaging->value;

                            $rgxp = $arrData['eval']['rgxp'];

                            // Convert date formats into timestamps (check the eval setting first -> #3063)
                            if (($rgxp == 'date' || $rgxp == 'time' || $rgxp == 'datim') && $varValue != '') {
                                try {
                                    $objDate = new Date($varValue);
                                    $varValue = $objDate->tstamp;
                                } catch (Exception $e) {
                                    $objWidgetStaging->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                                }
                            }

                            // Make sure that unique fields are unique (check the eval setting first -> #3063)
                            if ($arrData['eval']['unique'] && $varValue != '') {
                                $objUnique = $this->Database->prepare("SELECT * FROM tl_member WHERE " . $field . "=? AND id!=?")
                                    ->limit(1)
                                    ->execute($varValue, $this->Input->get('id'));

                                if ($objUnique->numRows) {
                                    $objWidgetStaging->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], (strlen($arrData['label'][0]) ? $arrData['label'][0] : $field)));
                                }
                            }

                            $_SESSION['FORM_DATA'][$field] = $varValue;

                            // Serialize if value is array
                            $varSave = is_array($varValue) ? serialize($varValue) : $varValue;

                            // Do not submit if there are errors
                            if ($objWidgetStaging->hasErrors()) {
                                $hasErrors = true;
                            } // Update
                            elseif ($objWidgetStaging->submitInput() && $this->Input->post('group_' . $field) == 'adopt_modification') {
                                $set = array($field => $varSave);
                                $this->Database->prepare('UPDATE tl_member %s WHERE id=?')->set($set)->execute($this->Input->get('id'));
                            }
                        }

                        $arrDataRecord[$field]['inputFieldNew'] = $objWidgetStaging->parse();
                        $arrDataRecord[$field]['inputFieldCurrent'] = $objWidgetCurrent->parse();
                        $arrDataRecord[$field]['label'] = strlen($GLOBALS['TL_LANG']['tl_member'][$field][0]) ? $GLOBALS['TL_LANG']['tl_member'][$field][0] : $field;

                        $arrModifiedFields[] = $field;
                    }
                }

                // Delete dataRecord in tbl_member_staging if there are no errors and the adopt button was clicked
                if ($this->Input->post('FORM_SUBMIT') == 'member_staging' && $this->Input->post('adopt') && !$hasErrors) {
                    $this->Database->prepare('DELETE FROM tbl_member_staging WHERE pid=?')->execute($this->Input->get('id'));
                    // redirect to the main page
                    $this->redirect('contao/main.php?do=vdb_member_staging');
                }

                // Add som Template vars
                $objTemplate->modifiedFields = implode(',', $arrModifiedFields);
                $objTemplate->action = 'contao/main.php?do=vdb_member_staging&action=show_entry&id=' . $this->Input->get('id');
                $objTemplate->arrDataRecord = $arrDataRecord;
                $this->Template->headline = $GLOBALS['TL_LANG']['vereinsdatenbank']['be']['headline_1'];
                $this->Template->partial = $objTemplate->parse();
                break;
        }
    }


}

