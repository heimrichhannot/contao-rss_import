<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package XRssImport3
 * @link https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array(
                                'fipps\xRssImport'
));

/**
 * Register the classes
 */
ClassLoader::addClasses(
                        array(
                            // Modules
                            'fipps\xRssImport\RssImport3' => 'system/modules/xRssImport3/modules/RssImport3.php',
                            // Models
                            'fipps\xRssImport\ObjFeedItemFps' => 'system/modules/xRssImport3/models/RssImportModels.php',
                            'fipps\xRssImport\ObjFeedEnclosureFps' => 'system/modules/xRssImport3/models/RssImportModels.php',
                            'fipps\xRssImport\FeedChannelFps' => 'system/modules/xRssImport3/models/RssImportModels.php'
                        ));

