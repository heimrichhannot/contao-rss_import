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
// Load tl_content language file
$this->loadLanguageFile('tl_content');
$this->loadLanguageFile('tl_news');
$this->import('BackendUser', 'User');

// Table tl_news_archive
$GLOBALS['TL_DCA']['tl_news_archive']['config']['ondelete_callback'][] = [
    'fipps\xRssImport\RssImport3',
    'deleteAttachments',
];

// Palettes
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace(
    '{protected_legend:hide}',
    '{rssimp_legend:hide},rssimp_imp; {protected_legend:hide}',
    $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']
);

// Selectors
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'] = [
    'rssimp_imp',
    'rssimp_source',
];

// Subpalettes
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes'] = [
    'rssimp_imp' => 'rssimp_impurl,
                                                                                rssimp_imgpath,
                                                                                rssimp_size,
                                                                                rssimp_imagemargin,
                                                                                rssimp_fullsize,
                                                                                rssimp_floating,
                                                                                rssimp_published,
                                                                                rssimp_teaserhtml,
                                                                                rssimp_allowedTags,
                                                                                rssimp_subtitlesrc,
                                                                                rssimp_author,
                                                                                rssimp_source,
                                                                                rssimp_target',
];

// Fields
$tmpfields = [
    'rssimp_imp' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imp'],
        'inputType' => 'checkbox',
        'exclude'   => true,
        'eval'      => [
            'mandatory'      => false,
            'isBoolean'      => true,
            'submitOnChange' => true,
        ],
        'sql'       => "char(1) NOT NULL default ''",
    ],

    'rssimp_impurl' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_impurl'],
        'exclude'   => true,
        'inputType' => 'text',
        'eval'      => [
            'rgxp'           => 'url',
            'mandatory'      => true,
            'tl_class'       => 'long',
            'decodeEntities' => true,
        ],
        'sql'       => "varchar(255) NOT NULL default ''",
    ],

    'rssimp_imgpath' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_imgpath'],
        'exclude'   => true,
        'inputType' => 'fileTree',
        'eval'      => [
            'mandatory' => true,
            'fieldType' => 'radio',
            'path'      => 'files',
        ],
        'sql'       => "binary(16) NULL",
    ],

    'rssimp_size'        => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['size'],
        'exclude'   => true,
        'inputType' => 'imageSize',
        'options'   => $GLOBALS['TL_CROP'],
        'reference' => &$GLOBALS['TL_LANG']['MSC'],
        'eval'      => [
            'rgxp'       => 'digit',
            'nospace'    => true,
            'helpwizard' => true,
            'tl_class'   => 'w50',
        ],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],
    'rssimp_imagemargin' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
        'exclude'   => true,
        'inputType' => 'trbl',
        'options'   => [
            'px',
            '%',
            'em',
            'rem',
            'ex',
            'pt',
            'pc',
            'in',
            'cm',
            'mm',
        ],
        'eval'      => [
            'includeBlankOption' => true,
            'tl_class'           => 'w50',
        ],
        'sql'       => "varchar(128) NOT NULL default ''",
    ],
    'rssimp_fullsize'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['fullsize'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => [
            'tl_class' => 'w50 m12',
        ],
        'sql'       => "char(1) NOT NULL default ''",
    ],
    'rssimp_floating'    => [
        'label'     => &$GLOBALS['TL_LANG']['tl_content']['floating'],
        'default'   => 'above',
        'exclude'   => true,
        'inputType' => 'radioTable',
        'options'   => [
            'above',
            'left',
            'right',
            'below',
        ],
        'eval'      => [
            'cols'     => 4,
            'tl_class' => 'w50',
        ],
        'reference' => &$GLOBALS['TL_LANG']['MSC'],
        'sql'       => "varchar(12) NOT NULL default ''",
    ],
    'rssimp_published'   => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_published'],
        'exclude'   => true,
        'filter'    => true,
        'flag'      => 1,
        'inputType' => 'checkbox',
        'eval'      => [
            'doNotCopy' => true,
            'tl_class'  => 'long',
        ],
        'sql'       => "char(1) NOT NULL default ''",
    ],

    'rssimp_subtitlesrc' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_subtitlesrc'],
        'sorting'   => true,
        'inputType' => 'select',
        'exclude'   => true,
        'options'   => [
            'category'    => 'category (rss, atom)',
            'contributor' => 'contributor (atom)',
            'rights'      => 'rights (atom)',
        ],
        'eval'      => [
            'mandatory'          => false,
            'tl_class'           => 'w50',
            'includeBlankOption' => true,
        ],
        'sql'       => "varchar(64) NOT NULL default ''",
    ],

    'rssimp_teaserhtml' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_teaserhtml'],
        'exclude'   => true,
        'filter'    => true,
        'flag'      => 1,
        'inputType' => 'checkbox',
        'eval'      => [
            'doNotCopy' => true,
            'tl_class'  => 'long',
        ],
        'sql'       => "varchar(1) NOT NULL default ''",
    ],

    'rssimp_allowedTags' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news_archive']['rssimp_allowedTags'],
        'exclude'   => true,
        'inputType' => 'text',
        'default'   => &$GLOBALS['TL_CONFIG']['allowedTags'],
        'eval'      => [
            'preserveTags' => true,
            'tl_class'     => 'clr long',
            'mandatory'    => false,
        ],
        'sql'       => "text NOT NULL",
    ],

    'rssimp_author' => [
        'label'      => &$GLOBALS['TL_LANG']['tl_news']['author'],
        'default'    => $this->User->id,
        'exclude'    => true,
        'filter'     => true,
        'sorting'    => true,
        'flag'       => 11,
        'inputType'  => 'select',
        'foreignKey' => 'tl_user.name',
        'eval'       => [
            'doNotCopy'          => true,
            'mandatory'          => true,
            'includeBlankOption' => true,
            'tl_class'           => 'w50',
        ],
        'sql'        => "int(10) unsigned NOT NULL default '0'",
        'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
    ],

    'rssimp_source' => [
        'label'     => &$GLOBALS['TL_LANG']['tl_news']['source'],
        'default'   => 'external',
        'exclude'   => true,
        'filter'    => true,
        'inputType' => 'radio',
        'options'   => [
            'default',
            'external',
        ],
        'reference' => &$GLOBALS['TL_LANG']['tl_news'],
        'eval'      => [
            'tl_class' => 'long, clr',
        ],
        'sql'       => "varchar(12) NOT NULL default 'external'",
    ],

    'rssimp_target' => [
        'label'     => &$GLOBALS['TL_LANG']['MSC']['target'],
        'default'   => '1',
        'exclude'   => true,
        'inputType' => 'checkbox',
        'eval'      => [
            'tl_class' => 'w50 m12',
        ],
        'sql'       => "char(1) NOT NULL default ''",
    ],
];

$GLOBALS['TL_DCA']['tl_news_archive']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_news_archive']['fields'], $tmpfields);
