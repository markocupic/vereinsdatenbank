--
-- Table `tl_member`
--
CREATE TABLE `tl_member` (
  `vdb_belongs_to_vdb` char(1) NOT NULL default '',

  `vdb_bereich_sport` char(1) NOT NULL default '',
  `vdb_bereich_kultur` char(1) NOT NULL default '',
  `vdb_bereich_freizeit` char(1) NOT NULL default '',
  `vdb_bereich_soziales` char(1) NOT NULL default '',
  `vdb_bereich_gesundheit` char(1) NOT NULL default '',
  `vdb_bereich_kindergarten` char(1) NOT NULL default '',
  `vdb_bereich_jugendarbeit` char(1) NOT NULL default '',
  `vdb_bereich_umwelt` char(1) NOT NULL default '',
  `vdb_bereich_politik` char(1) NOT NULL default '',
  `vdb_bereich_berufl_interessenvertretung` char(1) NOT NULL default '',
  `vdb_bereich_wirtschaftl_selbsthilfe` char(1) NOT NULL default '',
  `vdb_bereich_kirche_religion` char(1) NOT NULL default '',
  `vdb_bereich_feuerwehr_rettungsdienst` char(1) NOT NULL default '',
  `vdb_bereich_buergerschaftliche_aktivitaeten` char(1) NOT NULL default '',
  `vdb_bereich_senioren` char(1) NOT NULL default '',
  `vdb_bereich_frauenverein` char(1) NOT NULL default '',
  `vdb_bereich_integration_migration` char(1) NOT NULL default '',

  `vdb_vereinsname` varchar(255) NOT NULL default '',
  `vdb_aktiv_engagierte_mitglieder` varchar(255) NOT NULL default '',
  `vdb_unterstuetzer` varchar(255) NOT NULL default '',
  `vdb_wer_ist_engagiert` varchar(255) NOT NULL default '',
  `vdb_taetigkeitsmerkmale` text NULL,
  `vdb_taetigkeitsmerkmale_zweitsprache` text NULL,
  `vdb_egagiert_fuer` text NULL,
  `vdb_egagiert_fuer_zweitsprache` text NULL,
  `vdb_gruendungsdatum` varchar(255) NOT NULL default '',
  `vdb_besondere_aktion` text NULL,
  `vdb_besondere_aktion` text NULL,
  `vdb_besondere_aktion_zweitsprache` varchar(255) NOT NULL default '',
  `vdb_nachrichten_erlauben` char(1) NOT NULL default '',
  `vdb_bild` varchar(255) NOT NULL default '',
  `vdb_grund_der_loeschung` text NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



--
-- Table `tl_module`
--
CREATE TABLE `tl_module` (
  `vdb_image_folder` varchar(255) NOT NULL default '',

) ENGINE=MyISAM DEFAULT CHARSET=utf8;

