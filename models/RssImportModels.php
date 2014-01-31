<?php

/**
 * xRssImport3
 *
 * Copyright (c) 2011, 2014 agentur fipps e.K
 *
 * @copyright 2011, 2014 agentur fipps e.K.
 * @author Arne Borchert
 * @package fipps\xRssImport
 * @license LGPL
 */
namespace fipps\xRssImport;


/**
 * Class ObjRssFeedItemFps
 */
class ObjFeedItemFps
{
    public $sLink;
    public $sTitle;
    public $sDescription;
    public $sContent;
    public $sGuid;
    public $iPublished;
    public $iUpdated;
    public $sContributorName;
    public $sAuthorName;
    public $sRights;
    public $arCategoryLabels;
    public $oImage;
    public $oDownload;
    public $sComments;
    public $sCopyright;
    public $sSource;
}


/**
 * Class ObjFeedEnclosureFps
 */
class ObjFeedEnclosureFps
{
    public $sLink;
    public $sTitle;
    public $sDescription;
    public $iLength;
    public $iWidth;
    public $iHeight;
    public $sType;
}


/**
 * Class FeedChannelFps
 * read a Rss/Atom feed channel
 *
 * @author Arne Borchert, Nikolaus Dulgeridis
 */
class FeedChannelFps
{
    public $sTitle;
    public $sDescription;
    public $iUpdated;
    public $sAuthorName;
    public $arItems = array();
    public $sError;

    /**
     * getFeed
     *
     * @param string $sUrl
     * @return boolean
     */
    public function getFeed($sUrl)
    {
        $oSimplePie = new \SimplePie();
        $oSimplePie->set_feed_url($sUrl);
        $oSimplePie->force_feed(true);

        // Simple Pie: Html-Attribut class erlauben
        $aAttributes = $oSimplePie->strip_attributes;
        $key = array_search('class', $aAttributes);
        unset($aAttributes[$key]);
        $oSimplePie->strip_attributes($aAttributes);

        // simplePie Konfiguration Contao spezifisch
        $oSimplePie->set_output_encoding($GLOBALS['TL_CONFIG']['characterSet']);
        $oSimplePie->set_cache_location(TL_ROOT . '/system/tmp');
        $oSimplePie->enable_cache(false);

        if (! $oSimplePie->init()) {
            $this->sError = 'fail:' . $oSimplePie->error(); // kein Ergebnis
            return false;
        }
        $oSimplePie->handle_content_type();

        $this->sTitle = $oSimplePie->get_title();
        $this->sDescription = $oSimplePie->get_description();

        // Add updated
        if ($arUpdated = $oSimplePie->get_feed_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated')) {
            $oDate = new \DateTime($arUpdated[0]['data']);
            $this->id = $oDate->format('U');
        }

        // Add author
        if ($oSimplePie->get_author()->name)
            $this->sAuthorName = $oSimplePie->get_author()->name;

        // parse items
        $arSimplePieItems = $oSimplePie->get_items();
        for ($i = 0; $i < count($arSimplePieItems); $i ++) {
            $oRssFeedItem = new ObjFeedItemFps();
            $oRssFeedItem->sLink = $arSimplePieItems[$i]->get_link();
            $oRssFeedItem->sTitle = $arSimplePieItems[$i]->get_title();
            $oRssFeedItem->sDescription = $arSimplePieItems[$i]->get_description();
            $oRssFeedItem->sContent = $arSimplePieItems[$i]->get_content();
            $oRssFeedItem->sGuid = $arSimplePieItems[$i]->get_id();

            $arCategories = $arSimplePieItems[$i]->get_categories();
            for ($j = 0; $j < count($arCategories); $j ++) {
                $oRssFeedItem->arCategoryLabels[$j] = $arCategories[$j]->get_label();
            }

            $oRssFeedItem->sCopyright = $arSimplePieItems[$i]->get_copyright();

            // get source tag
            if (($arSource = $arSimplePieItems[$i]->get_item_tags(
                                                                                                                                                            SIMPLEPIE_NAMESPACE_RSS_20,
                                                                                                                                                            'source')) ||
                 ($arSource = $arSimplePieItems[$i]->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'source')))
                $oRssFeedItem->sSource = $arSource[0]['data'];

            // Add date
            $oRssFeedItem->iPublished = $arSimplePieItems[$i]->get_date('U');
            if ($arUpdated = $arSimplePieItems[$i]->get_item_tags(SIMPLEPIE_NAMESPACE_ATOM_10, 'updated')) {
                $oDate = new \DateTime($arUpdated[0]['data']);
                $oRssFeedItem->iUpdated = (int) $oDate->format('U');
            } else
                $oRssFeedItem->iUpdated = $this->arItems[$i]->iPublished;

            // Add contributor
            if ($arSimplePieItems[$i]->get_contributor())
                $oRssFeedItem->sContributorName = $arSimplePieItems[$i]->get_contributor()->name;

            // Add author
            if ($arSimplePieItems[$i]->get_author())
                $oRssFeedItem->sAuthorName = $arSimplePieItems[$i]->get_author()->name;

            // Add enclosure
            $aItemsEnclosures = $arSimplePieItems[$i]->get_enclosures();
            if ($aItemsEnclosures) {
                $aEnclosures = array();

                foreach ($aItemsEnclosures as $oEnclosure) {
                    if ($oEnclosure->get_link() && $oEnclosure->get_type()) {

                        $sType = "oDownload";
                        if (strpos(strtolower($oEnclosure->get_type()), 'image') !== false) {
                            $sType = "oImage";
                        }
                        if (! isset($oRssFeedItem->$sType)) {
                            $oImgEnclosure = new ObjFeedEnclosureFps();
                            $oImgEnclosure->sLink = $oEnclosure->get_link();
                            $oImgEnclosure->sTitle = $oEnclosure->get_title();
                            $oImgEnclosure->sDescription = $oEnclosure->get_description();
                            $oImgEnclosure->iLength = $oEnclosure->get_length();
                            $oImgEnclosure->iWidth = $oEnclosure->get_width();
                            $oImgEnclosure->iHeight = $oEnclosure->get_height();
                            $oImgEnclosure->sType = $oEnclosure->get_type();

                            $oRssFeedItem->$sType = $oImgEnclosure;
                        }
                    } // endif
                } // endforeach

            }

            $this->arItems[$i] = $oRssFeedItem;
        } // endfor
        return true; // Feed erfolgreich gelesen
    }
}