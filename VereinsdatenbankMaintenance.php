<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Marko
 * Date: 05.07.13
 * Time: 15:46
 * To change this template use File | Settings | File Templates.
 */
class VereinsdatenbankMaintenance extends System
{
    /**
     * delete orphaned images
     * @param $strFolder
     */
    public function tidyUpImageFolder($strFolder)
    {
        if (!file_exists(TL_ROOT . '/' . $strFolder)) {
            return;
        }

        $arrImages_1 = array();
        $arrImages_2 = array();

        $this->import('Database');
        if ($this->Database->fieldExists('vdb_bild', 'tl_member')) {
            $objMember = $this->Database->execute('SELECT vdb_bild FROM tl_member');
            if ($objMember->numRows) {
                $arrImages_1 = $objMember->fetchEach('vdb_bild');
            }
        }
        if ($this->Database->tableExists('tbl_member_staging')) {
            if ($this->Database->fieldExists('vdb_bild', 'tbl_member_staging')) {
                $objMember = $this->Database->execute('SELECT vdb_bild FROM tbl_member_staging');
                if ($objMember->numRows) {
                    $arrImages_2 = $objMember->fetchEach('vdb_bild');
                }

            }
        }

        $arrImages = array_merge($arrImages_1,$arrImages_2);

        $arrFiles = scan(TL_ROOT . '/' . $strFolder);
        foreach ($arrFiles as $strFile) {
            $src = $strFolder . '/' . $strFile;
            if (is_file(TL_ROOT . '/' . $src)) {
                if (!in_array($src, $arrImages))
                {
                    // Delete files with the vdb_ prefix
                    if (strpos($src, 'db_'))
                    {
                        $file = new File($src);
                        $file->delete();
                    }
                }
            }
        }
    }

    /**
     * Create Staging Table / copy from tl_member
     * @param $strContent
     * @param $strTemplate
     * @return mixed
     */
    public function createStagingTable($strContent, $strTemplate)
    {
        $this->import('Database');
        //$this->Database->query("DROP TABLE IF EXISTS tbl_member_staging");
        if ($this->Database->tableExists('tbl_member_staging')) {
            $blnTableExists = true;
            $objMemberStaging = $this->Database->execute('SELECT * FROM tbl_member_staging');
        } else {
            $blnTableExists = false;
        }
        if ($objMemberStaging->numRows < 1 || !$blnTableExists) {
            try {
                $this->Database->query("DROP TABLE IF EXISTS tbl_member_staging");
                $this->Database->query("CREATE TABLE IF NOT EXISTS tbl_member_staging AS SELECT * FROM tl_member");
                //sleep(1);
                $this->Database->query("TRUNCATE TABLE tbl_member_staging");
                $this->Database->query("ALTER TABLE  `tbl_member_staging` ENGINE = MYISAM");
                $this->Database->query("ALTER TABLE  `tbl_member_staging` ADD PRIMARY KEY (  `id` )");
                $this->Database->query("ALTER TABLE  `tbl_member_staging` CHANGE  `id`  `id` INT( 12 ) UNSIGNED NOT NULL AUTO_INCREMENT");
                $this->Database->query("ALTER TABLE tbl_member_staging ADD pid INT(12) NOT NULL AFTER id");
                $this->Database->query("ALTER TABLE  `tbl_member_staging` ADD UNIQUE ( `pid` )");
                $this->Database->query("ALTER TABLE tbl_member_staging ADD moduleId INT(12) NOT NULL AFTER pid");
                $this->Database->query("ALTER TABLE tbl_member_staging ADD editableFields text NULL AFTER moduleId");
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }


        // check for MySQL ver. > 4.0.1
        $objVer = $this->Database->query("SHOW VARIABLES LIKE 'version'");
        if (version_compare($objVer->first()->Value, '4.0.1', '<')) {
            die('Sorry, the extension requires MySQL > 4.0.1');
        }

        // add FULLTEXT KEY to tl_member
        try {
            $this->Database->query('ALTER TABLE tl_member DROP INDEX `vdb_vereinsdatenbank_suche`');
        } catch (Exception $e) {
        }
        $this->arrSearchableFields = array(vdb_vereinsname, firstname, lastname, city, vdb_taetigkeitsmerkmale, vdb_taetigkeitsmerkmale_zweitsprache, vdb_egagiert_fuer, vdb_egagiert_fuer_zweitsprache, vdb_besondere_aktion, vdb_besondere_aktion_zweitsprache);
        $objKey = $this->Database->prepare('SHOW INDEX FROM tl_member WHERE Key_name=?')->execute('vdb_vereinsdatenbank_suche');
        if (!$objKey->numRows) {
            $this->Database->query('ALTER TABLE tl_member ADD FULLTEXT KEY `vdb_vereinsdatenbank_suche` (' . implode(',', $this->arrSearchableFields) . ')');
        }

        // first drop stored procedure and then recreate it
        $this->Database->query("DROP FUNCTION IF EXISTS GoogleDistance_KM;");
        $this->Database->query("
        CREATE FUNCTION `GoogleDistance_KM`(
            geo_breitengrad_p1 double,
            geo_laengengrad_p1 double,
            geo_breitengrad_p2 double,
            geo_laengengrad_p2 double ) RETURNS double
            RETURN (6371 * acos( cos( radians(geo_breitengrad_p2) ) * cos( radians( geo_breitengrad_p1 ) )
            * cos( radians( geo_laengengrad_p1 ) - radians(geo_laengengrad_p2) )
            + sin( radians(geo_breitengrad_p2) ) * sin( radians( geo_breitengrad_p1 ) ) )
        );
        ");

        return $strContent;
    }
}
