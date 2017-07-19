<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'fipps',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'fipps\xRssImport\RssImport3'          => 'system/modules/rss_import/modules/RssImport3.php',

	// Classes
	'fipps\xRssImport\ObjFeedItemFps'      => 'system/modules/rss_import/classes/ObjFeedItemFps.php',
	'fipps\xRssImport\FeedChannelFps'      => 'system/modules/rss_import/classes/FeedChannelFps.php',
	'fipps\xRssImport\ObjFeedEnclosureFps' => 'system/modules/rss_import/classes/ObjFeedEnclosureFps.php',
));
