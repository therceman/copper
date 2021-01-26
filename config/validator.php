<?php


use Copper\Component\Validator\ValidatorConfigurator;

return function (ValidatorConfigurator $validator) {
    /** @var string Only Digits */
    $validator->integer_regex = '/-?(\d+)/';
    /** @var string Only 0 or 1 */
    $validator->boolean_regex = '/(0|1)/';

    /** @var string Only Digits with one dot between */
    $validator->float_regex = '/-?(\d+\.\d+)|(0)/';

    /** @var string 4 Digits */
    $validator->year_regex = '/(\d{4})/';
    /** @var string Time range is -838:59:59 to 838:59:59 */
    $validator->time_regex = '/-?(\d{1,3}:\d{1,2}:\d{1,2})/';
    /** @var string Date range is 1000-01-01 to 9999-12-31 */
    $validator->date_regex = '/(\d{4}\W\d{1,2}\W\d{1,2})/';
    /** @var string Datetime range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59 */
    $validator->datetime_regex = '/(\d{4}\W\d{1,2}\W\d{1,2}) (\d{1,3}:\d{1,2}:\d{1,2})/';

};