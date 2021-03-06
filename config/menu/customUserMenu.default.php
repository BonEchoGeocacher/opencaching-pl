<?php

use src\Controllers\MyRecommendationsController;
use src\Utils\Uri\SimpleRouter;
use src\Controllers\CacheNotesController;
use src\Utils\I18n\I18n;
use src\Controllers\UserIgnoredCachesController;
use src\Controllers\UserWatchedCachesController;

/**
 * This is simple configuration of links presented in sidebar of the page
 * for authorized users only.
 *
 * This is a DEFAULT configuration for ALL nodes.
 *
 * If you want to customize footer for your node
 * create config for your node by copied this file and changing its name.
 *
 * Every record of $menu table should be table record in form:
 *  '<translation-key-used-as-link-text>' => '<url>',
 *
 * or if link needs to be open in a new window (use php array)
 *  '<translation-key-used-as-link-text>' => ['<url>'],
 *
 */

$menu = [ // DON'T CHANGE $menu var name!
    /* 'translation key' => 'url' */
    'mnu_newCache'  => '/newcache.php',
    'mnu_myCaches'  => '/mycaches.php',
    'mnu_myStats'   => '/viewprofile.php',
    'mnu_myAccount' => '/myprofile.php',

    'mnu_myRoutes'      => '/myroutes.php',
    'mnu_myCacheNotes'  => SimpleRouter::getLink(CacheNotesController::class),
    'mnu_watchedCaches' => SimpleRouter::getLink(UserWatchedCachesController::class),
    'mnu_ignoredCaches' => SimpleRouter::getLink(UserIgnoredCachesController::class),
    'mnu_myRecommends'  => SimpleRouter::getLink(MyRecommendationsController::class, 'recommendations'),
    'mnu_savedQueries'  => '/query.php',
    'mnu_okapiExtApps'  => '/okapi/apps/?langpref=' . I18n::getCurrentLang(),
];
