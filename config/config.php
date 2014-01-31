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

$GLOBALS['TL_CRON']['hourly'][] = array(
                                        'fipps\xRssImport\RssImport3',
                                        'importAllNewsFeeds'
);