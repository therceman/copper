<?php


namespace Copper\Component\Validator;

class ValidatorConfigurator
{
    /** @var int Email min length. <hr> Default: 7 */
    public $email_minLength;
    /** @var int Email max length. <hr> Default: 50 */
    public $email_maxLength;
    /**
     * Email regex check.
     * <hr>
     * <code>
     * Default: (^[a-zA-Z0-9][a-zA-Z0-9\+\.\-\_]*@[a-zA-Z0-9][a-zA-Z0-9\-\.]*\.[a-zA-Z0-9]{2,}$)
     *
     * test1+extra@qwe.com                  - true
     * test2.2ndPart+extra@qwe.com          - true
     * test2.2ndPart+extra@sub.qwe.com      - true
     * test3_normal@qwe.com                 - true
     * test4-normal@qwe.com                 - true
     * test5.min2chars.top.level@qwe.co     - true
     * test6_noatsign.com                   - false
     * test7_wrongchar_=qwe@qwe.com         - false
     * test8_min1char.top.level@qwe.a       - false
     * test9_wrongchar_domain@qwe.c!om      - false
     * test10_dot_after_atsign@.qwe.com     - false
     * test11_minus_after_atsign@-qwe.com   - false
     * -test12_minus_sign_at_start@qwe.com  - false
     * .test13_dot_sign_at_start@qwe.com    - false
     * </code>
     * @var string
     */
    public $email_regex;
    /** @var string Email format example. Default: jonh.wick@gmail.com */
    public $email_regex_format_example;

    /**
     * Phone regex check.
     * <hr>
     * <code>
     * Default: (^\+?\(?\d{0,3}\)?\s(?:\d\s*){8,18}$)
     *
     * +371 12345678        - true
     * 371 12345678         - true
     * 12345678             - true
     * +(371) 12345678      - true
     * (371) 12345678       - true
     * -371 12345678        - false
     * +371 1234567         - false
     * +317 hello 12345678  - false
     * +371 12345678+       - false
     * +((371) 12345678+    - false
     * </code>
     * @var string
     */
    public $phone_regex;
    /** @var string */
    public $phone_regex_format_example;

    /** @var string 4 Digits */
    public $year_regex;
    /** @var string Time range is -838:59:59 to 838:59:59 */
    public $time_regex;
    /** @var string Date range is 1000-01-01 to 9999-12-31 */
    public $date_regex;
    /** @var string Datetime range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59 */
    public $datetime_regex;

    /** @var bool $strict Allow only strict validation */
    public $strict;

}