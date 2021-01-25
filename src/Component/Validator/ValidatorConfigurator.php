<?php


namespace Copper\Component\Validator;

class ValidatorConfigurator
{
    /** @var string 4 Digits */
    public $year_regex = '/(\d{4})/m';
    /** @var string Time range is -838:59:59 to 838:59:59 */
    public $time_regex = '/-?(\d{1,3}:\d{1,2}:\d{1,2})/m';
    /** @var string Date range is 1000-01-01 to 9999-12-31 */
    public $date_regex = '/(\d{4}\W\d{1,2}\W\d{1,2})/m';
}