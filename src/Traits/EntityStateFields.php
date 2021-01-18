<?php

namespace Copper\Traits;

trait EntityStateFields
{
    /** @var string */
    public $created_at;
    /** @var string */
    public $updated_at;
    /** @var string */
    public $removed_at;
    /** @var bool */
    public $enabled;
}