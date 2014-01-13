<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
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
 * @copyright  2011 agentur fipps e.K. 
 * @author     Arne Borchert, Nikolaus Dulgeridis 
 * @package    RssImport 
 * @license    LGPL 
 * @filesource
 */

/**
 * Include SimplePie classes
 */
// require_once(TL_ROOT . '/plugins/simplepie/simplepie.inc');
// if (!class_exists('idna_convert', false))
// {
	// if ( version_compare(VERSION, "2.11", "<" ))
		// require_once(TL_ROOT . '/plugins/simplepie/idna_convert.class.php');
	// else
		// require_once(TL_ROOT . '/plugins/idna/idna_convert.class.php');
// }

require_once('RssImportClasses.php');

/**
 * Class RssImport 
 */
class RssImport extends Backend
{
	private $_iStatsItemsRead;
	private $_iSatsItemsInserted;
	private $_iStatsItemsUpdated;
	private $_sMakeLocalErrorWarning;
	private $_sTable;
	const TL_NEWS = 'tl_news';
	const TL_EVENTS = 'tl_calendar_events';
		
	/**
	 * Import all new designated feeds for news, could periodically be called by a Cron-Job   
	 * 
	 */	
	public function importAllNewsFeeds()
	{
		$this->_sTable = self::TL_NEWS;
		$aNewsArchives= $this->_fetchDatasForFeedimport();
		if(is_array($aNewsArchives))
			foreach ($aNewsArchives as $aNewsArchiveRow) // Für alle Archive
				{	
					$this->_writeFeed($aNewsArchiveRow);
				}
	}
	
	/**
	 * Import all new designated feeds for events, could periodically be called by a Cron-Job   
	 * 
	 */	
	public function importAllEventFeeds()
	{
		$this->_sTable = 'tl_calendar_events';
		$aNewsArchives= $this->_fetchDatasForFeedimport();
		if(is_array($aNewsArchives))
			foreach ($aNewsArchives as $aNewsArchiveRow) // Für alle Archive
				{	
					$this->_writeFeed($aNewsArchiveRow);
				}
	}
	
	/**
	 * Callback-Function for updating a specific newsfeed 
	 *   
	 * @param Datacontainer $dc
	 */	
	public function importNewFeeds(Datacontainer $dc)
	{
		$this->_sTable = $dc->table;
		if ( $this->_sTable == self::TL_NEWS )
			$sql = "SELECT * FROM tl_news_archive WHERE id=? AND rssimp_imp = ?";
		elseif ( $this->_sTable == self::TL_EVENTS )
			$sql = "SELECT * FROM tl_calendar WHERE id=? AND rssimp_imp = ?";
			
		if (isset($sql))
		{
			$oResult = $this->Database->prepare($sql)
										->execute($dc->id, '1');
			if ($oResult->numRows > 0)
			{
				$aRssImportRow = $oResult->fetchAssoc();
				$this->_writeFeed($aRssImportRow);
			}
		}
	}
	
	/**
	 * Callback Function for deleting attachments
	 *   
	 * @param Datacontainer $dc
	 */
	public function deleteAttachments(Datacontainer $dc)
	{
		$this->_sTable = $dc->table; 
		if ( $this->_sTable == self::TL_NEWS )
			$sql = "SELECT tl_news.* FROM tl_news INNER JOIN tl_news_archive ON tl_news.pid = tl_news_archive.id  WHERE tl_news.id=? AND tl_news_archive.rssimp_imp = ?";
		elseif ( $this->_sTable == self::TL_EVENTS )
			$sql = "SELECT tl_calendar_events.* FROM tl_calendar_events INNER JOIN tl_calendar ON tl_calendar_events.pid = tl_calendar.id  WHERE tl_calendar_events.id=? AND tl_calendar.rssimp_imp = ?";
		if (isset($sql))
		{
			$oResult = $this->Database->prepare($sql)
										->execute($dc->id,'1');
			if ($oResult->numRows > 0)
			{
				$oRow= $oResult->fetchAssoc();
				if (strlen($oRow[singleSRC])>0)
					unlink (TL_ROOT . '/' . $oRow[singleSRC]);
			}
		}
	}
	
	/**
	 * generate a unique alias
	 * 
	 * @param string $sHeadline
	 * @param int $iId
	 * @return string
	 */
	private function _generateNewAlias($sHeadline, $iId)
	{
		$sAlias = standardize($sHeadline);
		// Check if alias already exists
		$oResult = $this->Database->prepare("SELECT id FROM $this->_sTable WHERE alias=? ")
									->execute($sAlias);
		
		if ($oResult->numRows > 0)
			$sAlias .= '-'.$iId;
		return $sAlias;
	}

	/**
	 * simply returns an empty string if $value is not set  
	 * 
	 * @param  string $value
	 * @return string
	 */	
	private function _notempty($value) 	
	{ 
		return isset($value)? $value: '';	
	}
	
	/**
	 * fetch from tl_news_archive all entries that import a feed   
	 * 
	 * @return array
	 */	
	private function _fetchDatasForFeedimport()
	{
		if ( $this->_sTable == self::TL_NEWS )
			$sql = "SELECT * FROM tl_news_archive WHERE rssimp_imp = ? ";
		elseif ( $this->_sTable == self::TL_EVENTS )
			$sql = "SELECT * FROM tl_calendar WHERE rssimp_imp = ? ";
			
		if(isset($sql))
		{
			$oResult= $this->Database->prepare($sql)
									->execute(1);
			if ($oResult->numRows)
			{
				$arRows= $oResult->fetchAllAssoc();
				return $arRows;
			}
		}
	  	return null;
	}
	
	/**
	 * write feed data for a single archive to tl_news 
	 * 
	 * @param array $aNewsArchiveRow
	 * @return boolean
	 */	
	private function _writeFeed($aRssImportRow) 	
	{
		if ( $this->_sTable == self::TL_NEWS )
			$sPartForLog = "Update News, Archive ID: ".$aRssImportRow['id'];
		elseif ( $this->_sTable == self::TL_EVENTS )
			$sPartForLog = "Update Events, Calendar ID: ".$aRssImportRow['id'];
		
		//Url ist leer? => return
		if (strlen(trim($aRssImportRow['rssimp_impurl']))<1)
		{

			$this->log($sPartForLog . " - Url is empty!", 'RssImport _writefeed', TL_GENERAL);			
			return false;
		}  
		// initialisiere Werte für Statistik 
		$this->_iStatsItemsRead=$this->_iSatsItemsInserted=$this->_iStatsItemsUpdated=0; 
		// lese den Feed
		$oFeed = new FeedChannelFps();
		if (! $oFeed->getFeed($aRssImportRow['rssimp_impurl']) ) 
		{
			$this->log($sPartForLog . "Could not read Url (" . $aRssImportRow['rssimp_impurl'] . ") " . $oFeed->sError, 
				'RssImport _writefeed', TL_ERROR);
			return false; 			// Feed konnte nicht gelesen werden
		}
		$arSimplePieItems = $oFeed->arItems;
		
		// Für alle Beitraege ...
		if ($arSimplePieItems) 
		foreach ($arSimplePieItems as $oResultItem)
		 {	
			$this->_iStatsItemsRead+= 1;

			// hole erlaubte tags
			$sAllowedTags= $aRssImportRow['rssimp_allowedTags']; 	
			
			// hole subtitle
			if ($aRssImportRow['rssimp_subtitlesrc'])
			{
				switch($aRssImportRow['rssimp_subtitlesrc'])
					{
					case ('category'):
						if ($oResultItem->arCategoryLabels)
							$oResultItem->sSubtitle = implode(', ',$oResultItem->arCategoryLabels);
						break;
					case ('contributor'):
						$oResultItem->sSubtitle= $oResultItem->sContributorName;
						break;
					case ('rights'):
						$oResultItem->sSubtitle= $oResultItem->sCopyright;
						break;
					default:
						$oResultItem->sSubtitle= '';
					}
			}
			
			// hole teaser
			$teaser = $this->_notempty($oResultItem->sDescription);
			// convert {space,t,n} to a single space 
			$teaser = preg_replace('/\s+/', ' ', substr($teaser, 0, 4096)); 
			// entferne tags 
			if ($aRssImportRow['rssimp_teaserhtml'] < 1) 
				$teaser = strip_tags( html_entity_decode($teaser, ENT_NOQUOTES, $GLOBALS['TL_CONFIG']['characterSet']));
			else
				$teaser = strip_tags( html_entity_decode($teaser, ENT_NOQUOTES, $GLOBALS['TL_CONFIG']['characterSet']), $sAllowedTags);
				
			if ($this->_sTable == self::TL_NEWS)
			{
				//Prepare record for tl_news
				$aSet = array(
					//id => auto;
					'pid' => $this->_notempty($aRssImportRow['id']),
					'tstamp' => $this->_notempty($oResultItem->iUpdated),
					//'tstamp' => $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $this->notempty($arMyItem->updated)),
					'headline' => $this->_notempty($oResultItem->sTitle),
					'alias' => '',
					'author' => $this->_notempty($aRssImportRow['rssimp_author']),
					'date' => $this->_notempty($oResultItem->iPublished),
					'time' => $this->_notempty($oResultItem->iPublished),
					'subheadline' => $this->_notempty($oResultItem->sSubtitle),
					'teaser' => $teaser,
					'text' => $this->_notempty(strip_tags($oResultItem->sContent, $sAllowedTags)),
					'singleSRC'=> '', 
					//alt
					'addImage'=> isset($oResultItem->oImage),
					'imagemargin' => $this->_notempty($aRssImportRow['imgdefaults_imgmargin']),
					'size' => $this->_notempty($aRssImportRow['imgdefaults_imgsize']),
					'fullsize' => $this->_notempty($aRssImportRow['imgdefaults_imgfullsize']),
					'imageUrl'=> $this->_notempty($oResultItem->oImage->sLink),
					//caption
					'floating' => $this->_notempty($aRssImportRow['imgdefaults_imgfloating']),
					//addEnclosure
					//enclosure
					source => 'default',
					//jumpTo => ''; //Weiterleitungsziel intern
					//articleId => '';
					'url' => $this->_notempty($oResultItem->sLink), // Weiterleitungsziel
					//target 
					'cssClass' => $this->_notempty($aRssImportRow['expertdefaults_cssclass']),
					//noComments => '';
					//featured => '';
					'published' => $this->_notempty($aRssImportRow['rssimp_published']),
					//start => '';
					//stop => '';
					//tags => '';
					'rssimp_guid' => $this->_notempty($oResultItem->sGuid),
					'rssimp_link' => $this->_notempty($oResultItem->sLink),
				);
			}
			elseif ($this->_sTable == self::TL_EVENTS)
			{
				//Prepare record for tl_calendar_events
				$aSet = array(
					//id => auto;
					'pid' => $this->_notempty($aRssImportRow['id']),
					'tstamp' => time(), //$this->_notempty($oResultItem->iUpdated),
					//'tstamp' => $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $this->notempty($arMyItem->updated)),
					'title' => $this->_notempty($oResultItem->sTitle),
					'alias' => '',
					'author' => $this->_notempty($aRssImportRow['rssimp_author']),
					'addTime' => '',
					'startDate' => $this->_notempty($oResultItem->iPublished),
					'startTime' => $this->_notempty($oResultItem->iPublished),
					//'subheadline' => $this->_notempty($oResultItem->sSubtitle),
					'teaser' => $teaser,
					'details' => $this->_notempty(strip_tags($oResultItem->sContent, $sAllowedTags)),
					'singleSRC'=> '', 
					//alt
					'addImage'=> isset($oResultItem->oImage),
					'imagemargin' => $this->_notempty($aRssImportRow['imgdefaults_imgmargin']),
					'size' => $this->_notempty($aRssImportRow['imgdefaults_imgsize']),
					'fullsize' => $this->_notempty($aRssImportRow['imgdefaults_imgfullsize']),
					'imageUrl'=> $this->_notempty($oResultItem->oImage->sLink),
					//caption
					'floating' => $this->_notempty($aRssImportRow['imgdefaults_imgfloating']),
					//addEnclosure
					//enclosure
					source => 'default',
					//jumpTo => ''; //Weiterleitungsziel intern
					//articleId => '';
					'url' => $this->_notempty($oResultItem->sLink), // Weiterleitungsziel
					//target 
					'cssClass' => $this->_notempty($aRssImportRow['expertdefaults_cssclass']),
					//noComments => '';
					//featured => '';
					'published' => $this->_notempty($aRssImportRow['rssimp_published']),
					//start => '';
					//stop => '';
					//tags => '';
					'rssimp_guid' => $this->_notempty($oResultItem->sGuid),
					'rssimp_link' => $this->_notempty($oResultItem->sLink),
				);
			} 
			
			if(is_array($aSet))
				$this->_writeSingleItem($aSet, $aRssImportRow);
			else
				return false;
		} // endforeach $arSimplePieItems
		
		$sLog = "";
		$this->log($sPartForLog.' '.
					'Rss/Atom-Items found:' . $this->_iStatsItemsRead. ' '. 
					'new:' . $this->_iSatsItemsInserted. ' '. 
					'updated:' . $this->_iStatsItemsUpdated. ' '. 
					'Url:'. $aRssImportRow['rssimp_impurl'],
					'Rssimport->_writefeed', TL_GENERAL);
		return true;   
	}

	/**
	 * write single feed item to tl_news 
	 * 
	 * @param array $aSet
	 * @param array $aNewsArchiveRow
	 */	
	private function _writeSingleItem($aSet, $aRssImportRow)
	{
		// Lese parent id
		$iPid = $aRssImportRow['id'];

		// Lese aktuelles Datum (Unix Timestamp) vom Beitrag	
		$iItemDate = ($aSet['tstamp'] > $aSet['date'])? $aSet['tstamp']: $aSet['date']; 

		// Lese id von gelesenem Beitrag
		$sGuid = $aSet['rssimp_guid'] ;

		// pruefe, ob Beitrag bereits in DB existiert
		$oResult = $this->Database->prepare("SELECT * FROM $this->_sTable WHERE rssimp_guid=? AND pid=? ")
									->execute($sGuid,$iPid);
		if ($oResult->numRows < 1)     	 // Beitrag existiert noch nicht => sql insert
		{
			$this->_iSatsItemsInserted+=1;
			// neuen Beitrag einfuegen
			$oResult = $this->Database->prepare("INSERT INTO $this->_sTable %s")->set($aSet)->execute();
			$iNewsId= $oResult->insertId;				// hole last_insert_id
			
			// Alias generieren
			$aSet['alias'] = $this->_generateNewAlias($aSet['headline'], $iNewsId);
			// (id hinzufügen)
			$aSet['alias'] .= '-' . $iNewsId;
			
			// lokale Kopie für enclosures (images) bereitstellen 
			$this->_makeLocal($aSet, $iNewsId, $aRssImportRow);
			
			// update tl_news
			$this->Database->prepare("UPDATE $this->_sTable %s WHERE id=? ")->set($aSet)->execute($iNewsId);
		}
		else						  	 	// Beitrag existiert, ist aber aktueller => sql update 
		{ 
			$oRow = $oResult->fetchAssoc(); // lies ersten Datensatz
			$iNewsId = $oRow['id'];			// lies id (DS mit selber guid wie Beitrag)
			$iTlDate = ($oRow['tstamp'] > $oRow['date'])? $oRow['tstamp']: $oRow['date'];     // lies update-Datum 
			if ($iTlDate<$iItemDate)		// Beitrag ist aktueller?
			{
				$this->_iStatsItemsUpdated+=1;
				// alte lokale Kopie fuer enclosures (images) loeschen, wenn vorhanden 
				if (strlen($oRow['singleSRC'])> 1)
					unlink (TL_ROOT . '/' . $oRow[singleSRC]);

				// lokale Kopie fuer enclosures (images) bereitstellen 
				if (strlen($aSet['imageUrl']) >1)
				$this->_makeLocal($aSet, $iNewsId, $aRssImportRow);
				// update ausfuehren
				$this->Database->prepare("UPDATE $this->_sTable %s WHERE id=? ")->set($aSet)->execute($iNewsId);
			}
		}
	}
	
	/**
	 * generate local Url for images and download links (affects the fields imageUrl and singleSrc of tl_news)
	 * 
	 * @param array $aSet
	 * @param int $iItemId 
	 * @param array $aArchiveRow
	 */	
	private function _makeLocal(&$aSet, $iItemId, $aArchiveRow)
	{
		if (strlen($aSet['imageUrl'])>1) 
		{
			if ($sNewpath = $this->_storeLocal($aSet['imageUrl'], $aArchiveRow['rssimp_imgpath'], $iItemId))
			{
				$aSet['imageUrl']=  ''; 				// put local link to singleSRC, delete imageUrl   
				$aSet['singleSRC']= $sNewpath;
			}
			else 
			{
				$this->log('Warning, cannot make local copy of file(' .$aSet['imageUrl']. ') reason: ' .
							$this->_sMakeLocalErrorWarning , 'RssImport->_makelocal', TL_ERROR);
			}
		}
	}

	/**
	 * provides archive with local copies of external download/image files 
	 * @param string $sExtUrl 
	 * @param string $sLocalPath 
	 * @param int $iId
	 * @return string
	 */
	private function _storeLocal($sExtUrl, $sLocalPath, $iId)
	{
		// reset warning message 
		$this->_sMakeLocalErrorWarning = '';
		
		// Positivliste für Datei-Extensions
		$sAllowedSuffixes = $GLOBALS['TL_CONFIG']['allowedDownload'];
		
		if ( strlen($sExtUrl)== 0) 		// Leerstring als ext. URL ist sinnlos
			$this->_sMakeLocalErrorWarning .= ' empty URL not allowed';
			
		if (strlen($sLocalPath)<2) 		// dulde keinen Leerstring als Basispfad
			$this->_sMakeLocalErrorWarning .= ' empty basepath for downloads not allowed';
			
		// bastele lokalen Dateinamen: sLocalPath + filename + _ + id + extension
		$arInfo = pathinfo($sExtUrl);
		$arInfo['extension'] = strtolower($arInfo['extension']);					// hole suffix;
		$sFilename =  standardize(basename($sExtUrl,'.'.$arInfo['extension'])); 	// hole dateinamen (ohne suffix)
		$sLocalFilename =  $sFilename .'_' . $iId . '.' . $arInfo['extension'];
		$sLocalfile = $sLocalPath . '/' . $sFilename .'_' . $iId . '.' . $arInfo['extension'];
		
		if (!in_array($arInfo['extension'], trimsplit(',', strtolower($sAllowedSuffixes))))
			$this->_sMakeLocalErrorWarning .=' Suffix not supported ';

		if (strpos($sExtUrl, '?') !== false)
		    $this->_sMakeLocalErrorWarning .= ' special char in url not allowed ('.$sExtUrl.')';
		 
		if (file_exists(TL_ROOT .'/'. $sLocalfile))
		   $this->_sMakeLocalErrorWarning .= ' output file alrady exists ';
		
		if (strlen($this->_sMakeLocalErrorWarning) != 0) {
			return NULL;		// Abbruch
		}
		 
		//read
		try
		{
			$sData = @file_get_contents($sExtUrl);
		}
		catch(Exception $oException) 
		{
			$this->_sMakeLocalErrorWarning .= ' could not read from url(' .$oException->getMessage(). ')';   
			return NULL;		// Abbruch
		}

		if (strlen($sData)<= 0) 
		{ 
			$this->_sMakeLocalErrorWarning .= ' no file data(' .$sExtUrl. ')';
			return NULL;		// Abbruch
		}
		
		//write
		try
		{
			@file_put_contents(TL_ROOT .'/'. $sLocalfile, $sData);
		}
		catch(Exception $oException) 
		{
			$this->_sMakeLocalErrorWarning .= ' could not write file(' .$oException->getMessage(). ')';   
			return NULL;		// Abbruch
		}
		return $sLocalfile;	// Erfolg
	}
}
