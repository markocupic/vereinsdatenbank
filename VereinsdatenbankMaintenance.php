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
     * Create Staging Table / copy from tl_member
     */
    public function createStagingTable($strContent, $strTemplate)
    {
        return $strContent;
        $this->import('Database');
        //$this->Database->query("DROP TABLE IF EXISTS tbl_member_staging");
        if ($this->Database->tableExists('tbl_member_staging')) {
            $blnTableExists = true;
            $objMemberStaging = $this->Database->execute('SELECT * FROM tbl_member_staging');
        } else {
            $blnTableExists = false;
        }
        if ($objMemberStaging->numRows < 1 || $blnTableExists === false) {
            try {
                $this->Database->query("DROP TABLE IF EXISTS tbl_member_staging");
                $this->Database->query("CREATE TABLE IF NOT EXISTS tbl_member_staging AS SELECT * FROM tl_member");
                //sleep(1);
                $this->Database->query("TRUNCATE TABLE tbl_member_staging");
                //$this->Database->query("ALTER TABLE tbl_member_staging ADD PRIMARY KEY AUTO_INCREMENT (id)");
                $this->Database->query("ALTER TABLE tbl_member_staging ADD pid INT(12) NOT NULL AFTER id");
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
