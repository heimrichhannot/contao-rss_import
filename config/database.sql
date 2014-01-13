-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_news_archive`
-- 

CREATE TABLE `tl_news_archive` (      
    `rssimp_imp` char(1) NOT NULL default '',
    `rssimp_impurl` varchar(255) NOT NULL default '',
    `rssimp_imgpath` varchar(255) NOT NULL default '',
    `rssimp_published` char(1) NOT NULL default '',
    `rssimp_teaserhtml` varchar(1) NOT NULL default '',
    `rssimp_subtitlesrc` varchar(64) NOT NULL default '',
    `rssimp_allowedTags` text NOT NULL
	`rssimp_author` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

-- 
-- Table `tl_news`
--

CREATE TABLE `tl_news` (      
    `rssimp_guid` mediumtext NULL,
    `rssimp_link` mediumtext NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- 
-- Table `tl_calendar`
-- 

CREATE TABLE `tl_calendar` (      
    `rssimp_imp` char(1) NOT NULL default '',
    `rssimp_impurl` varchar(255) NOT NULL default '',
    `rssimp_imgpath` varchar(255) NOT NULL default '',
    `rssimp_published` char(1) NOT NULL default '',
    `rssimp_teaserhtml` varchar(1) NOT NULL default '',
    `rssimp_subtitlesrc` varchar(64) NOT NULL default '',
    `rssimp_allowedTags` text NOT NULL
	`rssimp_author` int(10) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

-- 
-- Table `tl_calendar_events`
--

CREATE TABLE `tl_calendar_events` (      
    `rssimp_guid` mediumtext NULL,
    `rssimp_link` mediumtext NULL,
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

