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
 * @package    Registration
 * @license    LGPL
 * @filesource
 */


/**
 * Class ModuleRegistration
 * Front end module "registration".
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class VereinsdatenbankEmailNotification extends System
{

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function notifyEditor()
    {
        if (TL_MODE == 'BE') {
            return;
        }

        try {
            $this->import('Database');
            $oneDay = 24 * 60 * 60;
            $today_at_midnight = strtotime(date("Ymd"));

            $objTblMemberStaging = $this->Database->execute('SELECT * FROM tbl_member_staging');
            if ($objTblMemberStaging->numRows) {
                $objTlLog = $this->Database->prepare('SELECT * FROM tl_log WHERE vdb_email_editor_notification_tstamp > ? ORDER BY tstamp DESC LIMIT 0,1')->execute('0');

                // inform editors only once per day
                if ($objTlLog->numRows < 1 || ($objTlLog->vdb_email_editor_notification_tstamp + $oneDay) <= $today_at_midnight) {
                    $objTblMemberStaging->reset();
                    $strBody = 'Hello! There are some new records in tl_member' . chr(10) . chr(13);
                    $arrTo = array();
                    while ($objTblMemberStaging->next()) {
                        $objModule = $this->Database->prepare("SELECT vdb_editor_email_notification_addresses AS recipients FROM tl_module WHERE id=?")
                        ->executeUncached($objTblMemberStaging->moduleId);
                        $arrTo = array_merge(explode(',', trim($objModule->recipients)), $arrTo);
                        $strBody .= 'Please check the profile of ' . $objTblMemberStaging->vdb_vereinsname . ' with the ID ' . $objTblMemberStaging->pid . chr(10) . chr(13);
                    }
                    $arrTo = array_unique(array_values($arrTo));
                    $strBody .= 'This message has been generated automaticaly from the system at ' . $_SERVER['SERVER_NAME'] . chr(10) . chr(13);

                    // Notify editors
                    if (count($arrTo)) {
                        $email = new Email;
                        $email->from = $GLOBALS['TL_CONFIG']['adminEmail'];
                        $email->replyTo($GLOBALS['TL_CONFIG']['adminEmail']);
                        $email->subject = 'Modification Report Vereinsdatenbank at ' . $_SERVER['SERVER_NAME'];
                        $email->text = $strBody;
                        $email->html = $strBody;
                        $email->sendTo(implode(',', $arrTo));
                        // Write to tl_log
                        $this->log('Vereinsdatenbank: The Editors (' . implode(',', $arrTo) . ') were notified about new entries in tbl_member_staging.', 'VereinsdatenbankEmailNotification notifyEditor()', TL_GENERAL);
                        $objTlLog = $this->Database->execute('SELECT id FROM tl_log ORDER BY id DESC LIMIT 0,1');
                        $set = array('vdb_email_editor_notification_tstamp' => $today_at_midnight);
                        $this->Database->prepare('UPDATE tl_log %s WHERE id=?')->set($set)->execute($objTlLog->id);
                    }
                }
            }
        } catch (Exception $e) {
            $this->log($e->getMessage(), 'VereinsdatenbankEmailNotification notifyEditor()', TL_ERROR);
        }

    }
}