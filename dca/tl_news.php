<?php

/**
 * xRssImport3
 *
 * Language file for modules (de)
 *
 * Copyright (c) 2011, 2014 agentur fipps e.K
 *
 * @copyright 2011, 2014 agentur fipps e.K.
 * @author Arne Borchert
 * @package fipps\xRssImport
 * @license LGPL
 */

// Table tl_news
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = array(
                                                                    'RssImport3',
                                                                    'deleteAttachments'
);
$GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = array(
                                                                    'RssImport3',
                                                                    'importNewFeeds'
);

// Fields
$tmpfields = array(
                'rssimp_guid' => array(
                                    'sql' => "mediumtext NULL"
                ),
                'rssimp_link' => array(
                                    'sql' => "mediumtext NULL"
                )
);

$GLOBALS['TL_DCA']['tl_news']['fields'] = array_merge($GLOBALS['TL_DCA']['tl_news']['fields'], $tmpfields);