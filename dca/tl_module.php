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
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Table tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['personalDataVereinsdatenbank'] = '{title_legend},name,headline,type;{config_legend},vdb_image_folder,vdb_editor_email_notification_addresses,editable;{redirect_legend},jumpTo;{template_legend:hide},vdb_memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['registrationVereinsdatenbank'] = '{title_legend},name,headline,type;{config_legend},vdb_image_folder,vdb_editor_email_notification_addresses,editable,newsletters,disableCaptcha;{account_legend},reg_groups,reg_allowLogin,reg_assignDir;{redirect_legend},jumpTo;{email_legend:hide},reg_activate;{template_legend:hide},vdb_memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['closeAccountVereinsdatenbank'] = '{title_legend},name,headline,type;{config_legend},reg_close;{redirect_legend},jumpTo;{template_legend:hide},tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';



// add fields to tl_module
$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_image_folder'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_image_folder'],
    'exclude'                 => true,
    'inputType'               => 'fileTree',
    'eval'                    => array('fieldType'=>'radio', 'files'=>false, 'filesOnly'=>false, 'mandatory'=>true, 'tl_class'=>'clr')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_editor_email_notification_addresses'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_editor_email_notification_addresses'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'tl_class'=>'clr')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_file_upload'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['vdb_file_upload'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'upload',
    'eval' => array('mandatory' => false, 'tl_class' => '', 'extensions' => 'jpg,jpeg,png,gif', 'storeFile' => true, 'uploadFolder' => '', 'feGroup' => 'imageUpload')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['vdb_memberTpl'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['memberTpl'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('tl_module_vdb', 'getMemberTemplates'),
    'eval'                    => array('tl_class'=>'w50')
);


/**
 * Class tl_module
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Controller
 */
class tl_module_vdb extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Return all member templates as array
     * @param DataContainer
     * @return array
     */
    public function getMemberTemplates(DataContainer $dc)
    {
        $intPid = $dc->activeRecord->pid;

        if ($this->Input->get('act') == 'overrideAll')
        {
            $intPid = $this->Input->get('id');
        }

        return $this->getTemplateGroup('vdb_member_', $intPid);
    }
}