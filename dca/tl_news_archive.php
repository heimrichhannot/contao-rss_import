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

// Load tl_content language file
$this->loadLanguageFile('tl_content');
$this->loadLanguageFile('tl_news');

// Table tl_news_archive
$GLOBALS['TL_DCA']['tl_news_archive']['config']['ondelete_callback'][] = array(
                                                                            'RssImport3',
                                                                            'deleteAttachments'
);

// Palettes
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace(
                                                                        '{protected_legend:hide}',
                                                                        '{rssimp_legend:hide},rssimp_imp; {protected_legend:hide}',
                                                                        $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);

// Selectors
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'] = array('rssimp_imp', 'rssimp_source');

// Subpalettes
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes'] =array( 
		'rssimp_imp' 		=> 'rssimp_impurl, rssimp_imgpath, rssimp_published, rssimp_teaserhtml, rssimp_allowedTags, rssimp_subtitlesrc, rssimp_author, rssimp_source, rssimp_target',
);
/*
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['rssimp_imp'] = 'rssimp_impurl, rssimp_imgpath, rssimp_published, rssimp_teaserhtml,
						    rssimp_allowedTags, rssimp_subtitlesrc, rssimp_author, rssimp_source';
							
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['rssimp_source'] = 'rssimp_jumpTo';
*/



// Fields
$tmpfields = array(
                'rssimp_imp' => array(
                                    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imp'],
                                    'inputType' => 'checkbox',
                                    'exclude' => true,
                                    'default' => '',
                                    'eval' => array(
                                                    'mandatory' => false,
                                                    'isBoolean' => true,
                                                    'submitOnChange' => true
                                    ),
                                    'sql' => "char(1) NOT NULL default ''"
                ),

                'rssimp_impurl' => array(
                                        'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_impurl'],
                                        'exclude' => true,
                                        'inputType' => 'text',
                                        'eval' => array(
                                                        'rgxp' => 'url',
                                                        'mandatory' => true,
                                                        'tl_class' => 'long',
                                                        'decodeEntities' => true
                                        ),
                                        'sql' => "varchar(255) NOT NULL default ''"
                ),

                'rssimp_imgpath' => array(
                                        'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imgpath'],
                                        'exclude' => true,
                                        'inputType' => 'fileTree',
                                        'eval' => array(
                                                        'mandatory' => true,
                                                        'fieldType' => 'radio',
                                                        'path' => 'files'
                                        ),
                                        'sql' => "binary(16) NULL"
                ),

                'rssimp_published' => array(
                                            'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_published'],
                                            'exclude' => true,
                                            'filter' => true,
                                            'flag' => 1,
                                            'inputType' => 'checkbox',
                                            'eval' => array(
                                                            'doNotCopy' => true,
                                                            'tl_class' => 'w50'
                                            ),
                                            'sql' => "char(1) NOT NULL default ''"
                ),

                'rssimp_subtitlesrc' => array(
                                            'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_subtitlesrc'],
                                            'sorting' => true,
                                            'inputType' => 'select',
                                            'exclude' => true,
                                            'options' => array(
                                                            'category' => 'category (rss, atom)',
                                                            'contributor' => 'contributor (atom)',
                                                            'rights' => 'rights (atom)'
                                            ),
                                            'eval' => array(
                                                            'mandatory' => false,
                                                            'tl_class' => 'w50',
                                                            'includeBlankOption' => true
                                            ),
                                            'sql' => "varchar(64) NOT NULL default ''"
                ),

                'rssimp_teaserhtml' => array(
                                            'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_teaserhtml'],
                                            'exclude' => true,
                                            'filter' => true,
                                            'flag' => 1,
                                            'inputType' => 'checkbox',
                                            'eval' => array(
                                                            'doNotCopy' => true,
                                                            'tl_class' => 'w50'
                                            ),
                                            'sql' => "varchar(1) NOT NULL default ''"
                ),

                'rssimp_allowedTags' => array(
                                            'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_allowedTags'],
                                            'exclude' => true,
                                            'inputType' => 'text',
                                            'default' => &$GLOBALS['TL_CONFIG']['allowedTags'],
                                            'eval' => array(
                                                            'preserveTags' => true,
                                                            'tl_class' => 'clr',
                                                            'mandatory' => false,
                                                            'tl_class' => 'long'
                                            ),
                                            'sql' => "text NOT NULL"
                ),

                'rssimp_author' => array(
                                        'label' => &$GLOBALS['TL_LANG']['tl_news']['author'],
                                        'default' => $this->User->id,
                                        'exclude' => true,
                                        'filter' => true,
                                        'sorting' => true,
                                        'flag' => 11,
                                        'inputType' => 'select',
                                        'foreignKey' => 'tl_user.name',
                                        'eval' => array(
                                                        'doNotCopy' => true,
                                                        'mandatory' => true,
                                                        'includeBlankOption' => true,
                                                        'tl_class' => 'w50'
                                        ),
                                        'sql' => "int(10) unsigned NOT NULL default '0'"
                ),
				
//Weiterleitungsziel

				'rssimp_source' => array
				(
					'label'                   => &$GLOBALS['TL_LANG']['tl_news']['source'],
					'default'                 => 'external',
					'exclude'                 => true,
					'filter'                  => true,
					'inputType'               => 'radio',
					'options_callback'        => array('tl_news_archive', 'getSourceOptions'),
					'reference'               => &$GLOBALS['TL_LANG']['tl_news'],
					'eval'                    => array('submitOnChange'=>true, 'helpwizard'=>true,  'tl_class'=>'long, clr'),
					'sql'                     => "varchar(12) NOT NULL default ''"
				),

				
				'rssimp_target' => array
				(
					'label'                   => &$GLOBALS['TL_LANG']['MSC']['target'],
					'default'                 => '1',
					'exclude'                 => true,
					'inputType'               => 'checkbox',
					'eval'                    => array('tl_class'=>'w50 m12'),
					'sql'                     => "char(1) NOT NULL default ''"
				)
)
;

$GLOBALS['TL_DCA']['tl_news_archive']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_news_archive']['fields'], $tmpfields);