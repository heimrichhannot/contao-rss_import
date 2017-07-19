<?php

/**
 * xRssImport3
 *
 * Copyright (c) 2011, 2014 agentur fipps e.K
 *
 * @copyright 2011, 2014 agentur fipps e.K.
 * @author    Arne Borchert
 * @package   fipps\xRssImport
 * @license   LGPL
 */

namespace fipps\xRssImport;


/**
 * Class RssImport3
 */
class RssImport3 extends \Backend
{
    const TL_NEWS         = 'tl_news';
    const TL_NEWS_ARCHIVE = 'tl_news_archive';
    const TL_EVENTS       = 'tl_calendar_events';
    const TL_CALENDAR     = 'tl_calendar';
    private $_iStatsItemsRead;
    private $_iStatsItemsInserted;
    private $_iStatsItemsUpdated;
    private $_sMakeLocalErrorWarning;
    private $_sTable;

    /**
     * Invoke parent constructor
     */
    public function __construct()
    {
        return parent::__construct();
    }


    /**
     * Import all new designated feeds for news, could periodically be called by a Cron-Job
     */
    public function importAllNewsFeeds()
    {
        $this->_sTable = self::TL_NEWS;
        $aNewsArchives = $this->_fetchDatasForFeedimport();

        if (is_array($aNewsArchives))
        {
            foreach ($aNewsArchives as $aNewsArchiveRow)             // Für alle Archive
            {
                $this->_writeFeed($aNewsArchiveRow);
            }
        }
    }

    /**
     * fetch from tl_news_archive all entries that import a feed
     *
     * @return array
     */
    private function _fetchDatasForFeedimport()
    {
        //$sTable = ($this->_sTable == self::TL_NEWS) ? self::TL_NEWS_ARCHIVE : self::TL_CALENDAR;
        $sTable = self::TL_NEWS_ARCHIVE;
        $sql    = "SELECT $sTable.*, tl_files.path FROM $sTable";
        $sql    .= " LEFT JOIN tl_files ON rssimp_imgpath LIKE uuid";
        $sql    .= " WHERE rssimp_imp = ?";

        if (isset($sql))
        {
            $oResult = \Database::getInstance()->prepare($sql)->execute(1);
            if ($oResult->numRows)
            {
                $arRows = $oResult->fetchAllAssoc();

                return $arRows;
            }
        }

        return null;
    }

    /**
     * write feed data for a single archive to tl_news
     *
     * @param array $aRssImportRow
     *
     * @return boolean
     */
    private function _writeFeed($aRssImportRow)
    {
        $sPartForLog = '';

        if ($this->_sTable == self::TL_NEWS)
        {
            $sPartForLog = "Update News, Archive ID: " . $aRssImportRow['id'];
        }

        // Url ist leer? => return
        if (strlen(trim($aRssImportRow['rssimp_impurl'])) < 1)
        {
            \System::log($sPartForLog . " - Url is empty!", 'RssImport _writefeed', TL_GENERAL);

            return false;
        }
        // initialisiere Werte für Statistik
        $this->_iStatsItemsRead = $this->_iStatsItemsInserted = $this->_iStatsItemsUpdated = 0;
        // lese den Feed
        $oFeed = new FeedChannelFps();
        if (!$oFeed->getFeed($aRssImportRow['rssimp_impurl']))
        {
            \System::log(
                $sPartForLog . "Could not read Url (" . $aRssImportRow['rssimp_impurl'] . ") " . $oFeed->sError,
                'RssImport _writefeed',
                TL_ERROR
            );

            return false; // Feed konnte nicht gelesen werden
        }
        $arSimplePieItems = $oFeed->arItems;

        // hole erlaubte tags
        $sAllowedTags = $aRssImportRow['rssimp_allowedTags'];

        // Für alle Beiträge ...
        if ($arSimplePieItems)
        {
            foreach ($arSimplePieItems as $oResultItem)
            {
                $aTmpArr[]              = $oResultItem;
                $this->_iStatsItemsRead += 1;

                // hole subtitle
                if ($aRssImportRow['rssimp_subtitlesrc'])
                {
                    switch ($aRssImportRow['rssimp_subtitlesrc'])
                    {
                        case ('category'):
                            if ($oResultItem->arCategoryLabels)
                            {
                                $oResultItem->sSubtitle = implode(', ', $oResultItem->arCategoryLabels);
                            }
                            break;
                        case ('contributor'):
                            $oResultItem->sSubtitle = $oResultItem->sContributorName;
                            break;
                        case ('rights'):
                            $oResultItem->sSubtitle = $oResultItem->sCopyright;
                            break;
                        default:
                            $oResultItem->sSubtitle = '';
                    }
                }

                // hole teaser
                $teaser = $this->_notempty($oResultItem->sDescription);
                // convert {space,t,n} to a single space
                $teaser = preg_replace('/\s+/', ' ', substr($teaser, 0, 4096));
                // entferne tags
                if ($aRssImportRow['rssimp_teaserhtml'] < 1)
                {
                    $teaser = strip_tags(html_entity_decode($teaser, ENT_NOQUOTES, $GLOBALS['TL_CONFIG']['characterSet']));
                }
                else
                {
                    $teaser = strip_tags(html_entity_decode($teaser, ENT_NOQUOTES, $GLOBALS['TL_CONFIG']['characterSet']), $sAllowedTags);
                }

                if ($this->_sTable == self::TL_NEWS)
                {
                    // Prepare record for tl_news
                    $aSet = [
                        // id => auto;
                        'pid'         => $this->_notempty($aRssImportRow['id']),
                        'tstamp'      => $this->_notempty($oResultItem->iUpdated),
                        'headline'    => $this->_notempty($oResultItem->sTitle),
                        'alias'       => '',
                        'author'      => $this->_notempty($aRssImportRow['rssimp_author']),
                        'date'        => $this->_notempty($oResultItem->iPublished),
                        'time'        => $this->_notempty($oResultItem->iPublished),
                        'subheadline' => $this->_notempty($oResultItem->sSubtitle),
                        'teaser'      => $teaser,
                        'singleSRC'   => '',
                        'addImage'    => isset($oResultItem->oImage),
                        'imagemargin' => $this->_notempty($aRssImportRow['rssimp_imagemargin']),
                        'size'        => $this->_notempty($aRssImportRow['rssimp_size']),
                        'fullsize'    => $this->_notempty($aRssImportRow['rssimp_fullsize']),
                        'imageUrl'    => $this->_notempty($oResultItem->oImage->sLink),
                        'floating'    => $this->_notempty($aRssImportRow['rssimp_floating']),
                        'url'         => $this->_notempty($oResultItem->sLink),  // Weiterleitungsziel
                        'cssClass'    => $this->_notempty($aRssImportRow['expertdefaults_cssclass']),
                        'published'   => $this->_notempty($aRssImportRow['rssimp_published']),
                        'rssimp_guid' => $this->_notempty($oResultItem->sGuid),
                        'rssimp_link' => $this->_notempty($oResultItem->sLink),
                        'source'      => $this->_notempty($aRssImportRow['rssimp_source']),
                        'target'      => $this->_notempty($aRssImportRow['rssimp_target']),
                    ];
                }
                if (isset($aSet))
                {
                    $_sContent = strip_tags(html_entity_decode($oResultItem->sContent, ENT_NOQUOTES, $GLOBALS['TL_CONFIG']['characterSet']), $sAllowedTags);
                    $this->_writeSingleItem($aSet, $aRssImportRow, $_sContent);
                }

                else
                {
                    return false;
                }
            } // endforeach $arSimplePieItems
        }

        \System::log(
            $sPartForLog . ' ' . 'Rss/Atom-Items found:' . $this->_iStatsItemsRead . ' ' . 'new:' . $this->_iStatsItemsInserted . ' ' . 'updated:' . $this->_iStatsItemsUpdated
            . ' ' . 'Url:' . $aRssImportRow['rssimp_impurl'],
            'Rssimport->_writefeed',
            TL_GENERAL
        );

        return true;
    }

    /**
     * simply returns an empty string if $value is not set
     *
     * @param string $value
     *
     * @return string
     */
    private function _notempty($value)
    {
        return isset($value) ? $value : '';
    }

    /**
     * write single feed item to tl_news
     *
     * @param array  $aSet
     * @param array  $aRssImportRow
     * @param string $sContent
     */
    private function _writeSingleItem($aSet, $aRssImportRow, $sContent = null)
    {
        // Lese parent id
        $iPid = $aRssImportRow['id'];

        // Lese aktuelles Datum (Unix Timestamp) vom Beitrag
        $iItemDate = ($aSet['tstamp'] > $aSet['date']) ? $aSet['tstamp'] : $aSet['date'];

        // Lese id von gelesenem Beitrag
        $sGuid = $aSet['rssimp_guid'];

        // pruefe, ob Beitrag bereits in DB existiert
        $oResult = \Database::getInstance()->prepare("SELECT * FROM $this->_sTable WHERE rssimp_guid=? AND pid=? ")->execute($sGuid, $iPid);
        if ($oResult->numRows < 1)
        {
            // Beitrag existiert noch nicht => sql insert
            $this->_iStatsItemsInserted += 1;
            // neuen Beitrag einfuegen
            $oResult = \Database::getInstance()->prepare("INSERT INTO $this->_sTable %s")->set($aSet)->execute();
            $iNewsId = $oResult->insertId; // hole last_insert_id

            // Alias generieren
            $aSet['alias'] = $this->_generateNewAlias($aSet['headline'], $iNewsId);
            // (id hinzufügen)
            $aSet['alias'] .= '-' . $iNewsId;

            // lokale Kopie für enclosures (images) bereitstellen
            $this->_makeLocal($aSet, $iNewsId, $aRssImportRow);

            // update tl_news
            \Database::getInstance()->prepare("UPDATE $this->_sTable %s WHERE id=? ")->set($aSet)->execute($iNewsId);

            // Content Element generieren, falls vorhanden
            if (isset($sContent) && $sContent != '')
            {
                $_aContent = [
                    'pid'     => $iNewsId,
                    'ptable'  => $this->_sTable,
                    'sorting' => 128,
                    'tstamp'  => $aSet['tstamp'],
                    'type'    => 'text',
                    'text'    => $sContent,
                ];
                \Database::getInstance()->prepare("INSERT INTO tl_content %s")->set($_aContent)->execute();
            }

        }
        else
        {
            // Beitrag existiert, ist aber aktueller => sql update
            $oRow    = $oResult->fetchAssoc(); // lies ersten Datensatz
            $iNewsId = $oRow['id']; // lies id (DS mit selber guid wie Beitrag)
            $iTlDate = ($oRow['tstamp'] > $oRow['date']) ? $oRow['tstamp'] : $oRow['date']; // lies
            // update-Datum
            if ($iTlDate < $iItemDate)
            {
                // Beitrag ist aktueller?
                $this->_iStatsItemsUpdated += 1;
                // alte lokale Kopie fuer enclosures (images) loeschen, wenn vorhanden
                if (strlen($oRow['singleSRC']) > 1)
                {
                    unlink(TL_ROOT . '/' . $oRow['singleSRC']);
                }

                // lokale Kopie fuer enclosures (images) bereitstellen
                if (strlen($aSet['imageUrl']) > 1)
                {
                    $this->_makeLocal($aSet, $iNewsId, $aRssImportRow);
                }
                // update ausfuehren
                \Database::getInstance()->prepare("UPDATE $this->_sTable %s WHERE id=? ")->set($aSet)->execute($iNewsId);


                // Content Element aktualisieren
                if (isset($sContent) && $sContent != '')
                {
                    $_aContent = [
                        'pid'     => $iNewsId,
                        'ptable'  => $this->_sTable,
                        'sorting' => 128,
                        'tstamp'  => $aSet['tstamp'],
                        'type'    => 'text',
                        'text'    => $sContent,
                    ];
                    \Database::getInstance()->prepare("DELETE FROM tl_content WHERE ptable = $this->_sTable AND pid = ?")->execute($iNewsId);
                    \Database::getInstance()->prepare("INSERT INTO tl_content %s")->set($_aContent)->execute();
                }


            }
        }
    }

    /**
     * generate a unique alias
     *
     * @param string $sHeadline
     * @param int    $iId
     *
     * @return string
     */
    private function _generateNewAlias($sHeadline, $iId)
    {
        $sAlias = standardize($sHeadline);
        // Check if alias already exists
        $oResult = \Database::getInstance()->prepare("SELECT id FROM $this->_sTable WHERE alias=? ")->execute($sAlias);

        if ($oResult->numRows > 0)
        {
            $sAlias .= '-' . $iId;
        }

        return $sAlias;
    }

    /**
     * generate local Url for images and download links (affects the fields imageUrl and singleSrc
     * of tl_news)
     *
     * @param array $aSet
     * @param int   $iItemId
     * @param array $aArchiveRow
     */
    private function _makeLocal(&$aSet, $iItemId, $aArchiveRow)
    {
        if (strlen($aSet['imageUrl']) > 1)
        {
            if ($sNewpath = $this->_storeLocal($aSet['imageUrl'], $aArchiveRow['path'], $iItemId))
            {
                $aSet['imageUrl']  = ''; // put local link to singleSRC, delete imageUrl
                $aSet['singleSRC'] = $sNewpath;
            }
            else
            {
                \System::log(
                    'Warning, cannot make local copy of file(' . $aSet['imageUrl'] . ') reason: ' . $this->_sMakeLocalErrorWarning,
                    'RssImport->_makelocal',
                    TL_ERROR
                );
            }
        }
    }

    /**
     * provides archive with local copies of external download/image files
     *
     * @param string $sExtUrl
     * @param string $sLocalPath
     * @param int    $iId
     *
     * @return string
     */
    private function _storeLocal($sExtUrl, $sLocalPath, $iId)
    {
        // reset warning message
        $this->_sMakeLocalErrorWarning = '';

        // Positivliste für Datei-Extensions
        $sAllowedSuffixes = $GLOBALS['TL_CONFIG']['allowedDownload'];

        if (strlen($sExtUrl) == 0) // Leerstring als ext. URL ist sinnlos
        {
            $this->_sMakeLocalErrorWarning .= ' empty URL not allowed';
        }

        if (strlen($sLocalPath) < 2) // dulde keinen Leerstring als Basispfad
        {
            $this->_sMakeLocalErrorWarning .= ' empty basepath for downloads not allowed';
        }

        // setze lokalen Dateinamen: sLocalPath + filename + _ + id + extension
        $arInfo              = pathinfo($sExtUrl);
        $arInfo['extension'] = strtolower($arInfo['extension']); // hole suffix;
        $sFilename           = standardize(basename($sExtUrl, '.' . $arInfo['extension'])); // hole dateinamen
        // (ohne suffix)
        $sLocalfile = $sLocalPath . '/' . $sFilename . '_' . $iId . '.' . $arInfo['extension'];

        if (!in_array($arInfo['extension'], trimsplit(',', strtolower($sAllowedSuffixes))))
        {
            $this->_sMakeLocalErrorWarning .= ' Suffix not supported ';
        }

        if (strpos($sExtUrl, '?') !== false)
        {
            $this->_sMakeLocalErrorWarning .= ' special char in url not allowed (' . $sExtUrl . ')';
        }

        if (file_exists(TL_ROOT . '/' . $sLocalfile))
        {
            $this->_sMakeLocalErrorWarning .= ' output file alrady exists ';
        }

        if (strlen($this->_sMakeLocalErrorWarning) != 0)
        {
            return null; // Abbruch
        }

        // read
        try
        {
            $sData = @file_get_contents($sExtUrl);
        } catch (\Exception $oException)
        {
            $this->_sMakeLocalErrorWarning .= ' could not read from url(' . $oException->getMessage() . ')';

            return null; // Abbruch
        }

        if (strlen($sData) <= 0)
        {
            $this->_sMakeLocalErrorWarning .= ' no file data(' . $sExtUrl . ')';

            return null; // Abbruch
        }

        // write
        try
        {
            file_put_contents(TL_ROOT . '/' . $sLocalfile, $sData);
            $objModel = \Dbafs::addResource($sLocalfile);
        } catch (\Exception $oException)
        {
            $this->_sMakeLocalErrorWarning .= ' could not write file(' . $oException->getMessage() . ')';

            return null; // Abbruch
        }

        return $objModel->uuid; // Erfolg
    }

    /**
     * Callback-Function for updating a specific newsfeed
     *
     * @param \Datacontainer $dc
     */
    public function importNewFeeds(\Datacontainer $dc)
    {
        $this->_sTable = $dc->table;

        $sTable = ($this->_sTable == self::TL_NEWS) ? 'tl_news_archive' : 'tl_calendar';

        $sql = "SELECT $sTable.*, tl_files.path FROM $sTable";
        $sql .= " LEFT JOIN tl_files ON $sTable.rssimp_imgpath LIKE tl_files.uuid";
        $sql .= " WHERE $sTable.id = ? AND $sTable.rssimp_imp = ?";

        if (isset($sql))
        {
            $oResult = \Database::getInstance()->prepare($sql)->execute($dc->id, '1');
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
     * @param \Datacontainer $dc
     */
    public function deleteAttachments(\Datacontainer $dc)
    {
        $this->_sTable = $dc->table;

        switch ($this->_sTable)
        {
            case self::TL_NEWS_ARCHIVE:
                $sTable    = 'tl_news';
                $sIdColumn = 'pid';
                break;
            case self::TL_NEWS:
                $sTable    = 'tl_news';
                $sIdColumn = 'id';
                break;
            default:
                $sTable    = false;
                $sIdColumn = false;
                break;
        }

        $sql = "SELECT $sTable.id, tl_files.uuid AS uuid, tl_files.path FROM tl_files";
        $sql .= " LEFT JOIN $sTable ON tl_files.uuid = $sTable.singleSRC";
        $sql .= " WHERE $sTable.$sIdColumn = ?";
        $sql .= " GROUP BY tl_files.id";

        if ($sTable && $sIdColumn)
        {
            $oResult = \Database::getInstance()->prepare($sql)->execute($dc->id);

            if ($oResult->numRows > 0)
            {
                $aRows = $oResult->fetchAllAssoc();

                if (is_array($aRows))
                {
                    foreach ($aRows as $aRow)
                    {
                        if (file_exists(TL_ROOT . '/' . $aRow['path']) && $this->_checkIfAttachmentIsUnused($aRow['id'], $aRow['uuid'], $sTable))
                        {
                            unlink(TL_ROOT . '/' . $aRow['path']);
                            \Dbafs::deleteResource($aRow['path']);
                        }
                    }
                }
            }
        }
    }

    private function _checkIfAttachmentIsUnused($id, $uuid, $sTable)
    {
        $sql     = "SELECT id FROM $sTable WHERE id != ? AND HEX(singleSRC) = ?";
        $oResult = \Database::getInstance()->prepare($sql)->execute($id, bin2hex($uuid));

        return $oResult->numRows == 0;
    }
}
