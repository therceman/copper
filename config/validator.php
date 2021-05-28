<?php


use Copper\Component\Validator\ValidatorConfigurator;

return function (ValidatorConfigurator $validator) {
    // email
    $validator->email_minLength = 7;
    $validator->email_maxLength = 50;
    $validator->email_regex = '/(^[a-zA-Z0-9][a-zA-Z0-9\+\.\-\_]*@[a-zA-Z0-9][a-zA-Z0-9\-\.]*\.[a-zA-Z0-9]{2,}$)/';

    $validator->email_regex_format_example = [
        'en' => 'john.wick@gmail.com',
        'ru' => 'ivan.pavlov@mail.ru',
        'lv' => 'ivars.ozols@inbox.lv',
    ];

    // year
    $validator->year_regex = '/(\d{4})/';
    // time
    $validator->time_regex = '/-?(\d{1,3}:\d{1,2}:\d{1,2})/';
    // date
    $validator->date_regex = '/(\d{4}\W\d{1,2}\W\d{1,2})/';
    // datetime
    $validator->datetime_regex = '/(\d{4}\W\d{1,2}\W\d{1,2}) (\d{1,3}:\d{1,2}:\d{1,2})/';

    $validator->strict = false;
};