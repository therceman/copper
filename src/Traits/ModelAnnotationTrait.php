<?php

namespace Copper\Traits;

trait ModelAnnotationTrait
{
    /**
     * Call Parent Method with Arguments as Array - Used By Annotation Helpers
     *
     * Using call_user_func_array instead of direct call to parent class to fix IDE error:
     * "Return value is expected to be '\App\Entity\ConfigEntity[]', '\Copper\Entity\AbstractEntity[]' returned"
     *
     * @param string $func
     * @param $args
     *
     * @return mixed
     */
    protected function cpm(string $func, $args)
    {
        return call_user_func_array('parent::' . $func, $args);
    }
}