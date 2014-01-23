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
 * @author     Arne Borchert 
 * @package    RssImport
 * @license    LGPL 
 * @filesource
 */

 /**
 * Load tl_content language file
 */
$this->loadLanguageFile('tl_content');
$this->loadLanguageFile('tl_news');

/**
 * Table tl_news_archive 
 */

$GLOBALS['TL_DCA']['tl_news_archive']['config']['ondelete_callback'][] = array('RssImport', 'deleteAttachments');

// Palettes 

$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = 
	str_replace('{protected_legend:hide}', '{rssimp_legend:hide},rssimp_imp;'.'{protected_legend:hide}', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);




// Selectors
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][]= 'rssimp_imp';

// Subpalettes 
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['rssimp_imp']= 
						    'rssimp_impurl, rssimp_imgpath, rssimp_published, rssimp_teaserhtml, 
						    rssimp_allowedTags, rssimp_subtitlesrc, rssimp_author';

// Fields
$tmpfields= array
	(
       'rssimp_imp' => array 
        ( 
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imp'],
            'inputType'             => 'checkbox',
			'exclude'               => true,         
            'default'               => '', 
            'eval'                  => array('mandatory'=>false, 'isBoolean' => true, 'submitOnChange' => true), 
			'sql'					=> "char(1) NOT NULL default ''",
        ),		

        'rssimp_impurl' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_impurl'],
			'exclude'               => true,
			'inputType'             => 'text',
			'eval'                  => array('rgxp'=>'url', 'mandatory'=>true, 'tl_class'=>'long', 'decodeEntities'=>true),
			'sql'					=> "varchar(255) NOT NULL default ''",
		),

		'rssimp_imgpath' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imgpath'],
			'exclude'               => true,
			'inputType'             => 'fileTree',
			'eval'                  => array('mandatory'=>true, 'fieldType'=>'radio', 'path'=>'files'),
			'sql'                     => "binary(16) NULL"
		), 

		'rssimp_published' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_published'],
			'exclude'               => true,
			'filter'                => true,
			'flag'                  => 1,
			'inputType'             => 'checkbox',
			'eval'                  => array('doNotCopy'=>true, 'tl_class'=>'w50'),
			'sql'					=> "char(1) NOT NULL default ''",
		),

		'rssimp_subtitlesrc' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_subtitlesrc'],
			'sorting'               => true, 
			'inputType' 			=> 'select',
			'exclude'               => true,		
			'options' 				=> array('category'=>'category (rss, atom)', 'contributor'=>'contributor (atom)', 'rights'=>'rights (atom)'),
			'eval'                  => array('mandatory'=>false, 'tl_class'=>'w50', 'includeBlankOption' => true),
			'sql'					=> "varchar(64) NOT NULL default ''",
		),

		'rssimp_teaserhtml' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_teaserhtml'],
			'exclude'               => true,
			'filter'                => true,
			'flag'                  => 1,
			'inputType'             => 'checkbox',
			'eval'                  => array('doNotCopy'=>true, 'tl_class'=>'w50'),
			'sql'					=> "varchar(1) NOT NULL default ''",
		),

		'rssimp_allowedTags' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_allowedTags'],
			'exclude'               => true,
			'inputType'             => 'text',
			'default'				=> &$GLOBALS['TL_CONFIG']['allowedTags'],
			'eval'                  => array('preserveTags'=>true,'tl_class'=>'clr','mandatory'=>false,'tl_class'=>'long'),
			'sql'					=> "text NOT NULL",
		),

		'rssimp_author' => array
		(
			'label'                 => &$GLOBALS['TL_LANG']['tl_news']['author'],
			'default'               => $this->User->id,
			'exclude'               => true,
			'filter'                => true,
			'sorting'               => true,
			'flag'                  => 11,
			'inputType'             => 'select',
			'foreignKey'            => 'tl_user.name',
			'eval'                  => array('doNotCopy'=>true, 'mandatory'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
			'sql'					=> "int(10) unsigned NOT NULL default '0'",
		),
		
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']= array_merge($GLOBALS['TL_DCA']['tl_news_archive']['fields'],$tmpfields);