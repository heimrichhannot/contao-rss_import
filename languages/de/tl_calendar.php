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
 * @author      
 * @package    RssImport 
 * @license    LGPL 
 * @filesource
 */

/**
 * Fields
 */

$GLOBALS['TL_LANG']['tl_calendar']['rssimp_imp']= array('Feed importieren','RSS/Atom Feed Import aktivieren.');
$GLOBALS['TL_LANG']['tl_calendar']['rssimp_impurl']= array('Feed Url','Geben Sie die URL für den Feed an, der importiert werden soll.');
$GLOBALS['TL_LANG']['tl_calendar']['rssimp_imgpath']= array('Pfad für Bilder/Anlagen','Bitte wählen Sie den Ablagepfad für Bilder und Anlagen aus.');
$GLOBALS['TL_LANG']['tl_calendar']['rssimp_published']= array('Veröffentlichen','Gelesene Beiträge automatisch zur Veröffentlichung freigeben');
$GLOBALS['TL_LANG']['tl_calendar']['rssimp_teaserhtml']= array('Erlaube HTML im Teaser', 'HTML-Tags im Teaser erlauben.');
//$GLOBALS['TL_LANG']['tl_calendar']['rssimp_subtitlesrc']= array('Feld für Untertitel', 'Bestimme Feld für Untertitel in der RSS/Atom-Datei.');
$GLOBALS['TL_LANG']['tl_calendar']['rssimp_allowedTags']= array('Erlaubte HTML Tags', 'Bestimmt welche Html-Tags im Beitrag erlaubt sind.');
/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_calendar']['rssimp_legend']    = 'RSS/Atom Feed (Import)';

// override corresponding text in tl_calendar
$GLOBALS['TL_LANG']['tl_calendar']['feed_legend']      = 'RSS/Atom Feed (Export)';