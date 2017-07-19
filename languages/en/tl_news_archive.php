<?php if (!defined('TL_ROOT'))
{
    die('You cannot access this file directly!');
}

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
 *
 * @copyright  2011 agentur fipps e.K.
 * @author
 * @package    RssImport
 * @license    LGPL
 * @filesource
 */


/**
 * Fields
 */

$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imp']         = ['Import Feed', 'Import RSS-Feed into the news database.'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_impurl']      = ['Import Feed Url', 'Please choose the URL for the Feed to import.'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imgpath']     = ['Image/Attachment Path', 'Please choose a path to store images and attachments.'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_dlpath']      = ['Download Path', 'Please choose a path to store downloads.'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_published']   = ['Publish', 'Automatically assign imported news as published'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_teaserhtml']  = ['Allow HTML', 'Allow HTML-Tags inside the teaser.'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_subtitlesrc'] = ['Field for subtitles', 'Define field for subtitles from RSS/Atom-feed.'];
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_allowedTags'] = ['Allowed HTML Tags', 'Defines which html-tags are allowed in the content.'];

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_legend'] = 'RSS/Atom Feed (Import)';

// override corresponding text in tl_news_archive
$GLOBALS['TL_LANG']['tl_news_archive']['feed_legend'] = 'RSS/Atom Feed (Export)';