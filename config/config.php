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
 * @package    Frontend
 * @license    LGPL
 * @filesource
 */


/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['vereinsdatenbank']['personalDataVereinsdatenbank'] = 'ModuleVereinsdatenbankPersonalData';
$GLOBALS['FE_MOD']['vereinsdatenbank']['registrationVereinsdatenbank'] = 'ModuleVereinsdatenbankRegistration';
$GLOBALS['FE_MOD']['vereinsdatenbank']['closeAccountVereinsdatenbank'] = 'ModuleVereinsdatenbankCloseAccount';



/**
 * Backend modules
 */

$GLOBALS['BE_MOD']['vdb_vereinsdatenbank']['vdb_member_staging'] = array
(
    'icon' => 'system/modules/vereinsdatenbank/assets/images/award_star_gold.png',
    'callback' => 'BackendVereinsdatenbankMemberStaging'
);
$GLOBALS['BE_MOD']['vdb_vereinsdatenbank']['vdb_stat'] = array
(
    'icon' => 'system/modules/vereinsdatenbank/assets/images/report.png',
    'callback' => 'BackendVereinsdatenbankStat'
);
$GLOBALS['BE_MOD']['vdb_vereinsdatenbank']['vdb_member_administration'] = array
(
    'icon' => 'system/modules/vereinsdatenbank/assets/images/group.png',
    'tables' => array('tl_member')
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage'][] = array('VereinsdatenbankEmailNotification', 'notifyEditor');


