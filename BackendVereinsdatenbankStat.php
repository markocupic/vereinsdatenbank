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

class BackendVereinsdatenbankStat extends BackendModule
{
    public $strTemplate = 'be_vereinsdatenbank_stat';

    public function generate()
    {
        if (TL_MODE == 'BE') {
            // Include the sylesheet
            $GLOBALS['TL_CSS'][] = 'system/modules/vereinsdatenbank/assets/css/be_css.css';
        }

        return parent::generate();


    }

    public function compile()
    {
        $this->Template->headline = $GLOBALS['TL_LANG']['vereinsdatenbank']['be']['headline_2'];
        $objDb = $this->Database->execute("SELECT pid FROM tbl_member_staging WHERE vdb_belongs_to_vdb='1'");
        $arrStagedRecords = $objDb->fetchEach('pid');
        $subQuery_1 = count($arrStagedRecords) ? "AND id NOT IN (" . implode(',', $arrStagedRecords) . ") " : "";
        // active (reviewed) records | col1
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' " . $subQuery_1 . "AND disable=''");
        $this->Template->activeRecords = $objDb->numRows;

        // new records | col2
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND activation!='' AND disable='1'");
        $this->Template->newRecords = $objDb->numRows;

        // modified records temporary saved in tbl_member_staging | col3
        $objDb = $this->Database->execute("SELECT id FROM tbl_member_staging WHERE vdb_belongs_to_vdb='1'");
        $this->Template->modifiedRecords = $objDb->numRows;

        // closed records | col4
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND activation='' AND disable='1'");
        $this->Template->closedRecords = $objDb->numRows;

        // total records | col5
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1'");
        $this->Template->totalRecords = $objDb->numRows;

        //  records with e-mail address | col1 - col5
        $recordsWithEmail = array();
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' " . $subQuery_1 . "AND disable='' AND email != ''");
        $recordsWithEmail[1] = $objDb->numRows;
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND activation!='' AND disable='1' AND email != ''");
        $recordsWithEmail[2] = $objDb->numRows;
        $objDb = $this->Database->execute("SELECT id FROM tbl_member_staging WHERE vdb_belongs_to_vdb='1' AND email != ''");
        $recordsWithEmail[3] = $objDb->numRows;
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND activation='' AND disable='1' AND email != ''");
        $recordsWithEmail[4] = $objDb->numRows;
        $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND email != ''");
        $recordsWithEmail[5] = $objDb->numRows;
        $this->Template->recordsWithEmail = $recordsWithEmail;

        $arrRecords = array();
        $row = 6;
        $this->loadDataContainer('tl_member');
        $this->loadLanguageFile('tl_member');

        foreach ($GLOBALS['TL_DCA']['tl_member']['fields']['categories'] as $category) {
            // col0
            $arrRecords[$row]['label'] = strlen($GLOBALS['TL_LANG']['tl_member']['vdb_vereinsname'][0]) ? $GLOBALS['TL_LANG']['tl_member'][$category][0] : $category;
            // col1
            $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' " . $subQuery_1 . "AND disable='' AND " . $category . "='1'");
            $arrRecords[$row]['col_1'] = $objDb->numRows;
            // col2
            $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND activation!='' AND disable='1' AND " . $category . "='1'");
            $arrRecords[$row]['col_2'] = $objDb->numRows;
            // col3
            $objDb = $this->Database->execute("SELECT id FROM tbl_member_staging WHERE vdb_belongs_to_vdb='1' AND " . $category . "='1'");
            $arrRecords[$row]['col_3'] = $objDb->numRows;
            // col4
            $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND activation='' AND disable='1' AND " . $category . "='1'");
            $arrRecords[$row]['col_4'] = $objDb->numRows;
            // col5
            $objDb = $this->Database->execute("SELECT id FROM tl_member WHERE vdb_belongs_to_vdb='1' AND " . $category . "='1'");
            $arrRecords[$row]['col_5'] = $objDb->numRows;

            $row++;
        }
        $this->Template->arrCategories = $arrRecords;

    }


}

