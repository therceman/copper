<?php


use Copper\Component\AssetsManager\AssetsManagerConfigurator;

return function (AssetsManagerConfigurator $assets) {
    $assets->external_media_domain = null;
    $assets->external_media_whitelist = null;
    $assets->external_media_blacklist = null;
};
