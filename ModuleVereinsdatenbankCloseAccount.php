<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Vereinsdatenbank
 * @filesource
 */


/**
 * Class BackendVereindsatenbankCloseAccount
 *
 * Provide methods administrating club properties.
 * @copyright  Marko Cupic 2013
 * @author     m.cupic@gmx.ch
 * @package    Vereinsdatenbank
 */
class ModuleVereinsdatenbankCloseAccount extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'vdb_close_account';


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### CLOSE ACCOUNT ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Return if there is no logged in user
        if (!FE_USER_LOGGED_IN) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->import('FrontendUser', 'User');

        // Initialize the password widget
        $arrFields = array();
        $arrFields[] = array
        (
            'name' => 'grund_fuer_loeschung',
            'inputType' => 'textarea',
            'label' => $GLOBALS['TL_LANG']['vereinsdatenbank']['fe']['matter'],
            'eval' => array('mandatory' => true, 'required' => true, 'tableless' => $this->tableless)
        );
        $arrFields[] = array
        (
            'name' => 'password',
            'inputType' => 'text',
            'label' => $GLOBALS['TL_LANG']['MSC']['password'][0],
            'eval' => array('hideInput' => true, 'mandatory' => true, 'required' => true, 'tableless' => $this->tableless)
        );
        $temp = '';
        foreach ($arrFields as $arrField) {
            $strClass = $GLOBALS['TL_FFL'][$arrField['inputType']];

            // Continue if the class is not defined
            if (!$this->classFileExists($strClass)) {
                continue;
            }
            $objWidget = new $strClass($this->prepareForWidget($arrField, $arrField['name']));
            $objWidget->rowClass = 'row_0 row_first even';

            // Validate widget
            if ($this->Input->post('FORM_SUBMIT') == 'tl_close_account') {
                $objWidget->validate();

                if ($arrField['name'] == 'grund_fuer_loeschung') {
                    if (!$objWidget->hasErrors()) {
                        $grundDerLoeschung = $objWidget->value;
                    } else {
                        $hasErrors = true;
                    }
                }

                if ($arrField['name'] == 'password') {
                    // Validate password
                    if (!$objWidget->hasErrors()) {
                        list(, $strSalt) = explode(':', $this->User->password);

                        if (!strlen($strSalt) || sha1($strSalt . $objWidget->value) . ':' . $strSalt != $this->User->password) {
                            $objWidget->value = '';
                            $objWidget->addError($GLOBALS['TL_LANG']['ERR']['invalidPass']);
                            $hasErrors = true;
                        }
                    } else {
                        $hasErrors = true;
                    }
                }



                // Close account
                if (!$hasErrors) {
                    // HOOK: send account ID
                    if (isset($GLOBALS['TL_HOOKS']['closeAccount']) && is_array($GLOBALS['TL_HOOKS']['closeAccount'])) {
                        foreach ($GLOBALS['TL_HOOKS']['closeAccount'] as $callback) {
                            $this->import($callback[0]);
                            $this->$callback[0]->$callback[1]($this->User->id, $this->reg_close, $this);
                        }
                    }
                    // Delete children in tbl_member_staging
                    if ($this->Database->tableExists('tbl_member_staging')) {
                        $this->Database->prepare("DELETE FROM tbl_member_staging WHERE pid=?")
                        ->execute($this->User->id);
                    }

                    // Remove the account
                    if ($this->reg_close == 'close_delete') {
                        $this->Database->prepare("DELETE FROM tl_member WHERE id=?")
                        ->execute($this->User->id);

                        $this->log('User account ID ' . $this->User->id . ' (' . $this->User->email . ') has been deleted', 'ModuleCloseAccountVereinsdatenbank compile()', TL_ACCESS);
                    } // Deactivate the account
                    else {
                        $set = array('disable' => 1, 'vdb_grund_der_loeschung' => $grundDerLoeschung);
                        $this->Database->prepare("UPDATE tl_member %s WHERE id=?")->set($set)
                        ->execute($this->User->id);

                        $this->log('User account ID ' . $this->User->id . ' (' . $this->User->email . ') has been deactivated', 'ModuleCloseAccountVereinsdatenbank compile()', TL_ACCESS);
                    }

                    $this->User->logout();
                    $this->jumpToOrReload($this->jumpTo);
                }
            }

            $temp .= $objWidget->parse();
        }

        $this->Template->fields = $temp;

        $this->Template->formId = 'tlt_close_account';
        $this->Template->action = $this->getIndexFreeRequest();
        $this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['closeAccount']);
        $this->Template->rowLast = 'row_1 row_last odd';
        $this->Template->tableless = $this->tableless;
    }
}

?>