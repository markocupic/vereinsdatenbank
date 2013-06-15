<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
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
 * @copyright  Marko Cupic 2013
 * @author     Marko Cupic <m.cupic@gmx.ch>
 * @package    OrganizationSearch
 * @license    LGPL
 * @filesource
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = str_replace('{groups_legend}', '{organisation_legend},organization_name,organization_description,bereich_sport,bereich_gesundheit,bereich_politik;{groups_legend}', $GLOBALS['TL_DCA']['tl_member']['palettes']['default']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_belongs_to_vdb'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_belongs_to_vdb'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'checkbox',
    'eval' => array('mandatory' => false, 'feEditable' => false, 'feViewable' => false, 'tl_class' => '')
);

// Bereiche
$GLOBALS['TL_DCA']['tl_member']['fields']['categories'] = array(
    'vdb_bereich_sport',
    'vdb_bereich_kultur',
    'vdb_bereich_freizeit',
    'vdb_bereich_soziales',
    'vdb_bereich_gesundheit',
    'vdb_bereich_kindergarten',
    'vdb_bereich_jugendarbeit',
    'vdb_bereich_umwelt',
    'vdb_bereich_politik',
    'vdb_bereich_berufl_interessenvertretung',
    'vdb_bereich_wirtschaftl_selbsthilfe',
    'vdb_bereich_kirche_religion',
    'vdb_bereich_feuerwehr_rettungsdienst',
    'vdb_bereich_buergerschaftliche_aktivitaeten',
    'vdb_bereich_senioren',
    'vdb_bereich_frauenverein',
    'vdb_bereich_integration_migration',
);

foreach ($GLOBALS['TL_DCA']['tl_member']['fields']['categories'] as $categoryName) {
    $GLOBALS['TL_DCA']['tl_member']['fields'][$categoryName] = array
    (
        'label' => &$GLOBALS['TL_LANG']['tl_member'][$categoryName],
        'inputType' => 'checkbox',
        'eval' => array('feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile')
    );
}



// Weitere Felder
$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_vereinsname'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_vereinsname'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'personal', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['country'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['country'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'select',
    'options' => array('de', 'fr', 'ch'),
    'eval' => array('mandatory' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'address', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_aktiv_engagierte_mitglieder'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_aktiv_engagierte_mitglieder'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '', 'rgxp' => 'digit')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_unterstuetzer'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_unterstuetzer'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '', 'rgxp' => 'digit')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_wer_ist_engagiert'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_wer_ist_engagiert'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_taetigkeitsmerkmale'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_taetigkeitsmerkmale'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_taetigkeitsmerkmale_zweitsprache'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_taetigkeitsmerkmale_zweitsprache'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_egagiert_fuer'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_egagiert_fuer'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_egagiert_fuer_zweitsprache'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_egagiert_fuer_zweitsprache'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_gruendungsdatum'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_gruendungsdatum'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '', 'rgxp' => 'date')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_besondere_aktion'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_besondere_aktion'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_besondere_aktion_zweitsprache'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_besondere_aktion_zweitsprache'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_nachrichten_erlauben'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_nachrichten_erlauben'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'checkbox',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_bild'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_bild'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'text',
    'eval' => array('mandatory' => false, 'feEditable' => false, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_grund_der_loeschung'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_grund_der_loeschung'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'textarea',
    'eval' => array('mandatory' => false, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_member']['fields']['vdb_agb'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_member']['vdb_agb'],
    'exclude' => true,
    'search' => true,
    'sorting' => true,
    'flag' => 1,
    'inputType' => 'checkbox',
    'eval' => array('mandatory' => true, 'feEditable' => true, 'feViewable' => true, 'feGroup' => 'profile', 'tl_class' => '')
);



