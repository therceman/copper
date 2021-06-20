<?php


namespace Copper\Component\AssetsManager;

class AssetsManagerConfigurator
{
    /** @var string|null $strict External media domain */
    public $external_media_domain;
    /** @var array|null $strict External media domain extension whitelist */
    public $external_media_whitelist;
    /** @var array|null $strict External media domain extension blacklist */
    public $external_media_blacklist;
}
