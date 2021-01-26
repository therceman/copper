<?php


namespace Copper\Component\Validator;

class ValidatorConfigurator
{
    /** @var string Only Digits */
    public $integer_regex;
    /** @var string Only 0 or 1 */
    public $boolean_regex;

    /** @var string Only Digits with dots */
    public $float_regex;

    /** @var string 4 Digits */
    public $year_regex;
    /** @var string Time range is -838:59:59 to 838:59:59 */
    public $time_regex;
    /** @var string Date range is 1000-01-01 to 9999-12-31 */
    public $date_regex;
    /** @var string Datetime range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59 */
    public $datetime_regex;

}