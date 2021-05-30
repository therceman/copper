<?php


namespace Copper\Test;


use Copper\Component\Validator\ValidatorHandler;
use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;

class TestValidator
{
    private function testValidatorResponse($vRes, &$res, $key, $msg, $result = null)
    {
        if (ArrayHandler::hasKey($vRes->result, $key) === false)
            $res->fail($key . ' should be invalid');

        if ($vRes->result[$key]->status !== false)
            $res->fail($key . ' should have status: false');

        if ($vRes->result[$key]->msg !== $msg)
            $res->fail($key . ' should have msg: ' . $msg);

        if ($result === null)
            return $res;

        if (VarHandler::isArray($result)) {
            foreach ($result as $rKey => $rValue) {
                if ($vRes->result[$key]->result[$rKey] !== $rValue)
                    $res->fail($key . ' should have result[' . $rKey . ']: ' . $rValue);
            }
        } else {
            if ($vRes->result[$key]->result !== $result)
                $res->fail($key . ' should have result: ' . $result);
        }
    }

    private function stringAndBaseMethods()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $validator->addStringRule('string');
        $validator->addStringRule('string_fail');

        $validator->addStringRule('string_required', true);
        $validator->addStringRule('string_required_fail', true);
        $validator->addStringRule('string_required_fail2', true);

        $validator->addStringRule('string_min_len_5')->minLength(5);
        $validator->addStringRule('string_min_len_5_fail')->minLength(5);

        $validator->addStringRule('string_max_len_10')->maxLength(10);
        $validator->addStringRule('string_max_len_10_fail')->maxLength(10);

        $validator->addStringRule('string_len_11')->length(11);
        $validator->addStringRule('string_len_11_fail')->length(11);

        $validator->addStringRule('string_regex')->regex('(^[\pN]+$)');
        $validator->addStringRule('string_regex_fail')->regex('(^[\pN]+$)', '1337');

        $validatorRes = $validator->validate([
            'string' => 'hello',
            'string_fail' => 123,

            'string_required' => 'hello',
            'string_required_fail' => '',
            'string_required_fail2' => ' ',

            'string_min_len_5' => 'hello',
            'string_min_len_5_fail' => 'qwe',

            'string_max_len_10' => 'hello',
            'string_max_len_10_fail' => 'qweqweqweqwe',

            'string_len_11' => 'qweqweqwe12',
            'string_len_11_fail' => 'hello',

            'string_regex' => '1337',
            'string_regex_fail' => 'l33t'
        ]);

        if (ArrayHandler::hasKey($validatorRes->result, 'string'))
            $res->fail('string should be valid');

        $this->testValidatorResponse($validatorRes, $res, 'string_fail', 'valueTypeIsNotString', 'integer');

        // ----- string_required ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_required'))
            $res->fail('string_required should be valid');

        // --- string_required_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'string_required_fail', 'valueCannotBeEmpty');

        // --- string_required_fail2 ---

        $this->testValidatorResponse($validatorRes, $res, 'string_required_fail2', 'valueCannotBeEmpty');

        // ----- string_min_len_5 ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_min_len_5'))
            $res->fail('string_min_len_5 should be valid');

        // --- string_min_len_5_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'string_min_len_5_fail', 'minLengthRequired', 5);

        // ----- string_max_len_10 ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_max_len_10'))
            $res->fail('string_max_len_10 should be valid');

        // --- string_max_len_10_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'string_max_len_10_fail', 'maxLengthReached', 10);

        // ----- string_len_11 ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_len_11'))
            $res->fail('string_len_11 should be valid');

        // --- string_len_11_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'string_len_11_fail', 'wrongLength', 11);

        // ----- string_regex ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_regex'))
            $res->fail('string_regex should be valid');

        // --- string_regex_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'string_regex_fail', 'invalidValueFormat', ['example' => '1337']);

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function integer()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $validator->addIntegerRule('integer');
        $validator->addIntegerRule('integer_negative');
        $validator->addIntegerRule('integer_positive_fail');
        $validator->addIntegerRule('integer_fail');
        $validator->addIntegerRule('integer_fail2');
        $validator->addIntegerRule('integer_fail3');
        $validator->addIntegerRule('integer_fail4');
        $validator->addIntegerRule('integer_ok');
        $validator->addIntegerRule('integer_with_tabs_and_spaces');
        $validator->addIntegerRule('integer_strict')->strict(true);
        $validator->addIntegerRule('integer_strict_fail')->strict(true);

        $validator->addIntegerNegativeRule('integer_only_negative');
        $validator->addIntegerNegativeRule('integer_only_negative_fail');
        $validator->addIntegerNegativeRule('integer_only_negative_spaces');
        $validator->addIntegerNegativeRule('integer_only_negative_strict')->strict(true);
        $validator->addIntegerNegativeRule('integer_only_negative_strict_fail')->strict(true);

        $validator->addIntegerPositiveRule('integer_only_positive');
        $validator->addIntegerPositiveRule('integer_only_positive_fail');
        $validator->addIntegerPositiveRule('integer_only_positive_spaces');
        $validator->addIntegerPositiveRule('integer_only_positive_strict')->strict(true);
        $validator->addIntegerPositiveRule('integer_only_positive_strict_fail')->strict(true);

        $validator->addIntegerRule('int_min_10')->min(10);
        $validator->addIntegerRule('int_min_10_exact')->min(10);
        $validator->addIntegerRule('int_min_10_fail')->min(10);

        $validator->addIntegerRule('int_max_20')->max(20);
        $validator->addIntegerRule('int_max_20_exact')->max(20);
        $validator->addIntegerRule('int_max_20_fail')->max(20);

        $validator->addIntegerRule('int_min_5_max_10')->min(5)->max(10);
        $validator->addIntegerRule('int_min_5_max_10_exact_min')->min(5)->max(10);
        $validator->addIntegerRule('int_min_5_max_10_exact_max')->min(5)->max(10);
        $validator->addIntegerRule('int_min_5_max_10_fail_min')->min(5)->max(10);
        $validator->addIntegerRule('int_min_5_max_10_fail_max')->min(5)->max(10);

        $validatorRes = $validator->validate([
            'int_min_10' => '11',
            'int_min_10_exact' => 10,
            'int_min_10_fail' => 9,

            'int_max_20' => 19,
            'int_max_20_exact' => 20,
            'int_max_20_fail' => 21,

            'int_min_5_max_10' => ' 6   ',
            'int_min_5_max_10_exact_min' => 5,
            'int_min_5_max_10_exact_max' => 10,
            'int_min_5_max_10_fail_min' => 4,
            'int_min_5_max_10_fail_max' => 11,

            'integer' => '1',
            'integer_negative' => '-5',
            'integer_positive_fail' => '+5',
            'integer_fail' => 'qwe',
            'integer_fail2' => 'qwe5',
            'integer_fail3' => '1.1',
            'integer_fail4' => 1.1,
            'integer_ok' => 1.0,
            'integer_with_tabs_and_spaces' => '    5 ',
            'integer_strict' => 1338,
            'integer_strict_fail' => '1337',

            'integer_only_negative' => -1,
            'integer_only_negative_spaces' => ' -1  ',
            'integer_only_negative_strict' => -1,
            'integer_only_negative_fail' => '1',
            'integer_only_negative_strict_fail' => '-1',

            'integer_only_positive' => 1,
            'integer_only_positive_spaces' => ' 1  ',
            'integer_only_positive_strict' => 1,
            'integer_only_positive_fail' => '-1',
            'integer_only_positive_strict_fail' => '1',
        ]);

        // ---------------------------------- Min / Max ----------------------------------

        $trueMinMax = [
            'int_min_10' => '11',
            'int_min_10_exact' => 10,
            'int_max_20' => 19,
            'int_max_20_exact' => 20,
            'int_min_5_max_10' => ' 6   ',
            'int_min_5_max_10_exact_min' => 5,
            'int_min_5_max_10_exact_max' => 10
        ];

        foreach ($trueMinMax as $key => $value) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        $this->testValidatorResponse($validatorRes, $res, 'int_min_10_fail', ValidatorHandler::VALUE_IS_LESS_THAN_MINIMUM);
        $this->testValidatorResponse($validatorRes, $res, 'int_max_20_fail', ValidatorHandler::VALUE_IS_GREATER_THAN_MAXIMUM);
        $this->testValidatorResponse($validatorRes, $res, 'int_min_5_max_10_fail_min', ValidatorHandler::VALUE_IS_LESS_THAN_MINIMUM);
        $this->testValidatorResponse($validatorRes, $res, 'int_min_5_max_10_fail_max', ValidatorHandler::VALUE_IS_GREATER_THAN_MAXIMUM);

        // ----- integer ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer'))
            $res->fail('integer should be valid');

        // ----- integer_negative ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_negative'))
            $res->fail('integer_negative should be valid');

        // --- integer_positive_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_positive_fail', 'valueTypeIsNotInteger');

        // --- integer_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_fail', 'valueTypeIsNotInteger');

        // --- integer_fail2 ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_fail2', 'valueTypeIsNotInteger');

        // --- integer_fail3 ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_fail3', 'valueTypeIsNotInteger');

        // --- integer_fail4 ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_fail4', 'valueTypeIsNotInteger');

        // ----- integer_ok ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_ok'))
            $res->fail('integer_ok should be valid');

        // ----- integer_with_tabs_and_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_with_tabs_and_spaces'))
            $res->fail('integer_with_tabs_and_spaces should be valid');

        // ----- integer_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_strict'))
            $res->fail('integer_strict should be valid');

        // --- integer_strict_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_strict_fail', 'valueTypeIsNotInteger');

        // ====================================================

        // ----- integer_only_negative ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative'))
            $res->fail('integer_only_negative should be valid');

        // ----- integer_only_negative_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative_spaces'))
            $res->fail('integer_only_negative_spaces should be valid');

        // ----- integer_only_negative_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative_strict'))
            $res->fail('integer_only_negative_strict should be valid');

        // --- integer_only_negative_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_negative_fail', 'valueIsNotNegative');

        // --- integer_only_negative_strict_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_negative_strict_fail', 'valueTypeIsNotInteger', 'string');

        // ====================================================

        // ----- integer_only_positive ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive'))
            $res->fail('integer_only_positive should be valid');

        // ----- integer_only_positive_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive_spaces'))
            $res->fail('integer_only_positive_spaces should be valid');

        // ----- integer_only_positive_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive_strict'))
            $res->fail('integer_only_positive_strict should be valid');

        // --- integer_only_positive_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_positive_fail', 'valueIsNotPositive');

        // --- integer_only_positive_strict_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_positive_strict_fail', 'valueTypeIsNotInteger', 'string');

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function boolean()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $validator->addBooleanRule('bool');
        $validator->addBooleanRule('bool2');
        $validator->addBooleanRule('bool3');
        $validator->addBooleanRule('bool4');
        $validator->addBooleanRule('bool5');
        $validator->addBooleanRule('bool6');
        $validator->addBooleanRule('bool7');
        $validator->addBooleanRule('bool8');
        $validator->addBooleanRule('bool_fail');
        $validator->addBooleanRule('bool_fail2');
        $validator->addBooleanRule('bool_fail3');
        $validator->addBooleanRule('bool_fail4');
        $validator->addBooleanRule('bool_strict')->strict(true);
        $validator->addBooleanRule('bool_strict_fail')->strict(true);
        $validator->addBooleanRule('bool_strict_fail2')->strict(true);
        $validator->addBooleanRule('bool_strict_fail3')->strict(true);

        $validatorRes = $validator->validate([
            'bool' => true,
            'bool2' => false,
            'bool3' => 1,
            'bool4' => 0,
            'bool5' => '1',
            'bool6' => '0',
            'bool7' => 'true',
            'bool8' => 'false',
            'bool_fail' => '2',
            'bool_fail2' => 2,
            'bool_fail3' => null,
            'bool_fail4' => 'qwe',
            'bool_strict' => true,
            'bool_strict_fail' => 'true',
            'bool_strict_fail2' => '1',
            'bool_strict_fail3' => 1,
        ]);

        if (ArrayHandler::hasKey($validatorRes->result, 'bool'))
            $res->fail('bool should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool2'))
            $res->fail('bool2 should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool3'))
            $res->fail('bool3 should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool4'))
            $res->fail('bool4 should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool5'))
            $res->fail('bool5 should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool6'))
            $res->fail('bool6 should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool7'))
            $res->fail('bool7 should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'bool8'))
            $res->fail('bool8 should be valid');

        $this->testValidatorResponse($validatorRes, $res, 'bool_fail', 'valueTypeIsNotBoolean', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'bool_fail2', 'valueTypeIsNotBoolean', 'integer');
        $this->testValidatorResponse($validatorRes, $res, 'bool_fail3', 'valueTypeIsNotBoolean', 'NULL');
        $this->testValidatorResponse($validatorRes, $res, 'bool_fail4', 'valueTypeIsNotBoolean', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'bool_strict_fail', 'valueTypeIsNotBoolean', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'bool_strict_fail2', 'valueTypeIsNotBoolean', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'bool_strict_fail3', 'valueTypeIsNotBoolean', 'integer');

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function float()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $trueVars = [
            'float' => 1.55,
            'float2' => -1.55,
            'float3' => '1.55',
            'float4' => '-1.55',
            'float_max_3_dec' => '1.555',
            'float_max_3_dec2' => 1.001,
            'float_5' => '1.00',
            'float_6' => 1.00,
            'float_7' => '  1.05',
            'float_8' => ' 1.010 ',
            'float_9' => 1,
            'float_10' => 0,
            'float_11' => -1,
            'float_12' => .55,
            'float_13' => '.55',
            'float_14' => '-.55',
        ];

        $falseVars = [
            'float_fail' => '1.55a',
            'float_fail_2' => '1e3',
            'float_fail_3' => null,
            'float_fail_4' => 'qwe',
            'float_fail_5' => false,
            'float_fail_6' => true,
            'float_fail_7' => [],
        ];

        foreach (ArrayHandler::merge($trueVars, $falseVars) as $key => $var) {
            $validator->addFloatRule($key);
        }

        $trueVarsStrict = [
            'strict_float' => 1.55,
            'strict_float2' => 1.00,
            'strict_float3' => -1.123
        ];

        $falseVarsStrict = [
            'strict_float_fail' => '1.55',
            'strict_float2_fail' => '1.00',
            'strict_float3_fail' => '-1.123'
        ];

        foreach (ArrayHandler::merge($trueVarsStrict, $falseVarsStrict) as $key => $var) {
            $validator->addFloatRule($key)->strict(true);
        }

        // decimals check

        $decTrueVars = [
            'dec_float' => 1.22,
            'dec_float2' => -1.22,
            'dec_float3' => '1.22',
            'dec_float4' => '-1.22',
            'dec_float5' => '.22',
            'dec_float6' => '-.23',
            'dec_float7' => 123456789011.001000006066046, // will be turned to 123456789011 @see validateFloat
            'dec_float8' => '123456789011.01',
            'dec_float9' => '123456.0100',
            'dec_float10' => 123456.0100,
            'dec_float11' => 123456789012.0112,            // will be turned to 12345678901.01 @see validateFloat
        ];

        $decFalseVars = [
            'dec_float_fail' => 1.223,
            'dec_float2_fail' => -1.223,
            'dec_float3_fail' => '1.223',
            'dec_float4_fail' => '-1.223',
            'dec_float5_fail' => '123456789011.001000006066046',
            'dec_float6_fail' => '123456789011.01000006066046',
            'dec_float7_fail' => 12345678901.001000006066046,
            'dec_float8_fail' => '123456789011.001',
            'dec_float9_fail' => '12345678901.001',
        ];

        foreach (ArrayHandler::merge($decTrueVars, $decFalseVars) as $key => $var) {
            $validator->addFloatRule($key)->maxDecimals(2);
        }

        $decTrueVarsStrict = [
            'dec_float_strict' => 1.22,
            'dec_float_strict2' => -1.22,
            'dec_float_strict3' => -.22,
            'dec_float_strict4' => 0.22,
            'dec_float_strict5' => .22,
        ];

        $decFalseVarsStrict = [
            'dec_float_fail_strict' => 1.223,
            'dec_float_fail_strict2' => -1.223,
            'dec_float_fail_strict3' => -.223,
            'dec_float_fail_strict4' => 0.223,
            'dec_float_fail_strict5' => .223,
        ];

        $otherTrue = [
            'floatMaxDecimals_0' => 1.23
        ];

        foreach (ArrayHandler::merge($decTrueVarsStrict, $decFalseVarsStrict) as $key => $var) {
            $validator->addFloatRule($key)->maxDecimals(2)->strict(true);
        }

        $validator->addFloatRule('floatMaxDecimals_0')->maxDecimals(0);

        // positive

        $positiveTrue = [
            'float_pos' => 1,
            'float_pos2' => 1.1,
            'float_pos3' => '1',
            'float_pos4' => '1.1',
            'float_pos5' => '.55',
        ];

        $positiveFalse = [
            'float_false_pos' => -1,
            'float_false_pos2' => -1.1,
            'float_false_pos3' => '-1',
            'float_false_pos4' => '-1.1',
            'float_false_pos5' => '-.55',
        ];

        foreach (ArrayHandler::merge($positiveTrue, $positiveFalse) as $key => $var) {
            $validator->addFloatPositiveRule($key);
        }

        // negative

        $negativeTrue = [
            'float_neg' => -1,
            'float_neg2' => -1.1,
            'float_neg3' => '-1',
            'float_neg4' => '-1.1',
            'float_neg5' => '-.55',
        ];

        $negativeFalse = [
            'float_false_neg' => 1,
            'float_false_neg2' => 1.1,
            'float_false_neg3' => '1',
            'float_false_neg4' => '1.1',
            'float_false_neg5' => '.55',
        ];

        foreach (ArrayHandler::merge($negativeTrue, $negativeFalse) as $key => $var) {
            $validator->addFloatNegativeRule($key);
        }

        $vars = ArrayHandler::merge($trueVars, $falseVars);
        $vars = ArrayHandler::merge($vars, $trueVarsStrict);
        $vars = ArrayHandler::merge($vars, $falseVarsStrict);
        $vars = ArrayHandler::merge($vars, $decTrueVars);
        $vars = ArrayHandler::merge($vars, $decFalseVars);
        $vars = ArrayHandler::merge($vars, $decTrueVarsStrict);
        $vars = ArrayHandler::merge($vars, $decFalseVarsStrict);
        $vars = ArrayHandler::merge($vars, $otherTrue);
        $vars = ArrayHandler::merge($vars, $positiveTrue);
        $vars = ArrayHandler::merge($vars, $positiveFalse);
        $vars = ArrayHandler::merge($vars, $negativeTrue);
        $vars = ArrayHandler::merge($vars, $negativeFalse);

        $validatorRes = $validator->validate($vars);

        $trueVars = ArrayHandler::merge($trueVars, $trueVarsStrict);
        $trueVars = ArrayHandler::merge($trueVars, $decTrueVars);
        $trueVars = ArrayHandler::merge($trueVars, $decTrueVarsStrict);
        $trueVars = ArrayHandler::merge($trueVars, $otherTrue);
        $trueVars = ArrayHandler::merge($trueVars, $positiveTrue);
        $trueVars = ArrayHandler::merge($trueVars, $negativeTrue);

        foreach ($trueVars as $key => $var) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        $this->testValidatorResponse($validatorRes, $res, 'float_fail', 'valueTypeIsNotFloat', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'float_fail_2', 'valueTypeIsNotFloat', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'float_fail_3', 'valueTypeIsNotFloat', 'NULL');
        $this->testValidatorResponse($validatorRes, $res, 'float_fail_4', 'valueTypeIsNotFloat', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'float_fail_5', 'valueTypeIsNotFloat', 'boolean');
        $this->testValidatorResponse($validatorRes, $res, 'float_fail_6', 'valueTypeIsNotFloat', 'boolean');
        $this->testValidatorResponse($validatorRes, $res, 'float_fail_7', 'valueTypeIsNotFloat', 'array');

        $this->testValidatorResponse($validatorRes, $res, 'strict_float_fail', 'valueTypeIsNotFloat', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'strict_float2_fail', 'valueTypeIsNotFloat', 'string');
        $this->testValidatorResponse($validatorRes, $res, 'strict_float3_fail', 'valueTypeIsNotFloat', 'string');

        $this->testValidatorResponse($validatorRes, $res, 'dec_float_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float2_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float3_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float4_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float5_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float6_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float7_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float8_fail', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float9_fail', 'tooManyDecimalDigits', 2);

        $this->testValidatorResponse($validatorRes, $res, 'dec_float_fail_strict', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float_fail_strict2', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float_fail_strict3', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float_fail_strict4', 'tooManyDecimalDigits', 2);
        $this->testValidatorResponse($validatorRes, $res, 'dec_float_fail_strict5', 'tooManyDecimalDigits', 2);

        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos', 'valueIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos2', 'valueIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos3', 'valueIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos4', 'valueIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos5', 'valueIsNotPositive');

        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg', 'valueIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg2', 'valueIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg3', 'valueIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg4', 'valueIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg5', 'valueIsNotNegative');

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function email()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $trueVars = [
            'test1' => 'test1+extra@qwe.com',
            'test2' => 'test2.2ndPart+extra@qwe.com',
            'test3' => 'test2.2ndPart+extra@sub.qwe.com',
            'test4' => 'test3_normal@qwe.com',
            'test5' => 'test4-normal@qwe.com',
            'test6' => 'test5.min2chars.top.level@qwe.co',
        ];

        $falseVars = [
            'test1_fail' => 'test6_noatsign.com',
            'test2_fail' => 'test7_wrongchar_=qwe@qwe.com',
            'test3_fail' => 'test8_min1char.top.level@qwe.a',
            'test4_fail' => 'test9_wrongchar_domain@qwe.c!om',
            'test5_fail' => 'test10_dot_after_atsign@.qwe.com',
            'test6_fail' => 'test11_minus_after_atsign@-qwe.com',
            'test7_fail' => '-test12_minus_sign_at_start@qwe.com',
            'test8_fail' => '.test13_dot_sign_at_start@qwe.com',
            'test9_fail' => ',test13_dot_sign_at_start@qwe.com',
        ];

        foreach (ArrayHandler::merge($trueVars, $falseVars) as $key => $var) {
            $validator->addEmailRule($key);
        }


        $vars = ArrayHandler::merge($trueVars, $falseVars);

        $validatorRes = $validator->validate($vars);

        foreach ($trueVars as $key => $var) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        foreach ($falseVars as $key => $var) {
            $this->testValidatorResponse($validatorRes, $res, $key, 'invalidEmailFormat');
        }

        return ($res->hasError()) ? $res : $res->ok();
    }


    private function phone()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $vars = [
            ' +371 12345678        ' => true,
            ' 371 12345678         ' => true,
            ' 12345678             ' => true,
            ' +(371) 12345678      ' => true,
            ' (371) 12345678       ' => true,
            ' -371 12345678        ' => false,
            ' +371 1234567         ' => false,
            ' +317 hello 12345678  ' => false,
            ' +371 12345678+       ' => false,
            ' +((371) 12345678+    ' => false,
        ];

        $trueVars = [];
        $falseVars = [];

        foreach ($vars as $key => $bool) {
            $field = StringHandler::replace($key, ' ', '_');
            $value = StringHandler::trim($key);

            if ($bool)
                $trueVars[$field] = $value;
            else
                $falseVars[$field] = $value;
        }

        foreach (ArrayHandler::merge($trueVars, $falseVars) as $key => $var) {
            $validator->addPhoneRule($key);
        }

        $vars = ArrayHandler::merge($trueVars, $falseVars);

        $validatorRes = $validator->validate($vars);

        foreach ($trueVars as $key => $var) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        foreach ($falseVars as $key => $var) {
            $this->testValidatorResponse($validatorRes, $res, $key, ValidatorHandler::INVALID_PHONE_FORMAT);
        }

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function enum()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        $array = ['1', 2, '3 ', '   4   ', '   5   '];

        $validator->addEnumRule('test1', $array);
        $validator->addEnumRule('test2', $array);
        $validator->addEnumRule('test3', $array);
        $validator->addEnumRule('test4', $array);
        $validator->addEnumRule('test5', $array);
        $validator->addEnumRule('test6', $array);

        $validator->addEnumRule('test7', $array)->strict();
        $validator->addEnumRule('test8', $array)->strict();
        $validator->addEnumRule('test9', $array)->strict();
        $validator->addEnumRule('test10', $array)->strict();

        $validator->addEnumRule('test11', $array);
        $validator->addEnumRule('test12', $array);
        $validator->addEnumRule('test13', $array);

        $validator->addEnumRule('test14', $array)->strict();
        $validator->addEnumRule('test15', $array)->strict();
        $validator->addEnumRule('test16', $array)->strict();

        $trueVars = [
            'test1' => 1,
            'test2' => '2',
            'test3' => 3,
            'test4' => 4,
            'test5' => ' 4  ',
            'test6' => '  5       '
        ];

        $strictTrueVars = [
            'test7' => '1',
            'test8' => 2,
            'test9' => '3 ',
            'test10' => '   4   ',
        ];

        $true = ArrayHandler::merge($trueVars, $strictTrueVars);

        $falseVars = [
            'test11' => 6,
            'test12' => '1a',
            'test13' => '2   2',
        ];

        $strictFalseVars = [
            'test14' => 1,
            'test15' => '2',
            'test16' => '3',
        ];

        $false = ArrayHandler::merge($falseVars, $strictFalseVars);

        $vars = ArrayHandler::merge($true, $false);

        $validatorRes = $validator->validate($vars);

        foreach ($true as $key => $var) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        foreach ($false as $key => $var) {
            $this->testValidatorResponse($validatorRes, $res, $key, ValidatorHandler::WRONG_ENUM_VALUE);
        }

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function date()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        // ---------------------- App Config Date Format (Y-m-d) ----------------------

        $trueVarsAppConfigDate = [
            'date1' => '2020-01-31',
            'date2' => '1000-12-31',
            'date3' => '9999-01-31',
        ];

        $badVarsAppConfigDate = [
            'date0_bad' => '2020-1-31', // false, because app format is 'Y-m-d', 'm' - month with leading zeros (not 'n')
            'date1_bad' => '2020-13-31',
            'date2_bad' => '1000-01-32',
            'date3_bad' => '999-01-31',
            'date4_bad' => '10000-01-31',
            'date5_bad' => '2e0-12-12',
            'date6_bad' => 'qwe',
            'date7_bad' => '2020-01-31a',
        ];

        $appConfigVars = ArrayHandler::merge($trueVarsAppConfigDate, $badVarsAppConfigDate);

        foreach ($appConfigVars as $key => $value) {
            $validator->addDateRule($key);
        }

        // ---------------------- Custom Date Format (Y:n:j) ----------------------

        $customFormat = 'Y:n:j';

        $trueVarsCustomFormat = [
            'date1b' => '2020:1:11',
            'date2b' => '2020:1:3',
            'date3b' => '9999:1:3',
            'date4b' => '1000:1:3',
        ];

        $badVarsCustomFormat = [
            'date0b_bad' => '2020:01:31',
            'date1b_bad' => '2020:13:31',
            'date2b_bad' => '1000:1:32',
            'date3b_bad' => '999:01:31',
            'date4b_bad' => '2020-01-31',
        ];

        $customVars = ArrayHandler::merge($trueVarsCustomFormat, $badVarsCustomFormat);

        foreach ($customVars as $key => $value) {
            $validator->addDateRule($key)->dateFormat($customFormat);
        }

        // ------------------------------------------------------------------

        $vars = ArrayHandler::merge($appConfigVars, $customVars);

        $validatorRes = $validator->validate($vars);

        foreach (ArrayHandler::merge($trueVarsAppConfigDate, $trueVarsCustomFormat) as $key => $var) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        foreach (ArrayHandler::merge($badVarsAppConfigDate, $badVarsCustomFormat) as $key => $var) {
            $this->testValidatorResponse($validatorRes, $res, $key, ValidatorHandler::INVALID_DATE_FORMAT);
        }

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function time()
    {
        $res = new FunctionResponse();

        $validator = new ValidatorHandler();

        // ---------------------- App Config Time Format (H:i:s) ----------------------

        $trueVarsAppConfigTime = [
            'time1' => '20:03:59',
            'time2' => '00:00:00',
            'time3' => '23:59:59',
        ];

        $badVarsAppConfigTime = [
            'time0_bad' => '1:03:45', // false, because app format is 'H:i:s', 'H' - hour with leading zeros (not 'G')
            'time1_bad' => '23:58:61',
            'time2_bad' => '23:61:57',
            'time3_bad' => '23:31:5e',
            'time4_bad' => '23:33:52a',
            'time6_bad' => 'z23:61:53',
            'time7_bad' => '23:23:5',
        ];

        $appConfigVars = ArrayHandler::merge($trueVarsAppConfigTime, $badVarsAppConfigTime);

        foreach ($appConfigVars as $key => $value) {
            $validator->addTimeRule($key);
        }

        // ---------------------- Custom Time Format (g/i/s) ----------------------
        // g - 1 through 12

        $customFormat = 'g/i/s';

        $trueVarsCustomFormat = [
            'time1b' => '1/03/43',
            'time2b' => '12/43/03',
            'time3b' => '1/00/00',
        ];

        $badVarsCustomFormat = [
            'time0b_bad' => '13/03/23',
            'time1b_bad' => '12/61/30',
            'time2b_bad' => '12/30/62',
            'time3b_bad' => 'a12/30/33',
            'time4b_bad' => '1e/30/33',
            'time5b_bad' => '11/30/3',
            'time6b_bad' => '01/30/30',
            'time7b_bad' => '5/30/30a',
            'time8b_bad' => '0/00/00',
        ];

        $customVars = ArrayHandler::merge($trueVarsCustomFormat, $badVarsCustomFormat);

        foreach ($customVars as $key => $value) {
            $validator->addTimeRule($key)->timeFormat($customFormat);
        }

        // ------------------------------------------------------------------

        $vars = ArrayHandler::merge($appConfigVars, $customVars);

        $validatorRes = $validator->validate($vars);

        foreach (ArrayHandler::merge($trueVarsAppConfigTime, $trueVarsCustomFormat) as $key => $var) {
            if (ArrayHandler::hasKey($validatorRes->result, $key))
                $res->fail($key . ' should be valid');
        }

        foreach (ArrayHandler::merge($badVarsAppConfigTime, $badVarsCustomFormat) as $key => $var) {
            $this->testValidatorResponse($validatorRes, $res, $key, ValidatorHandler::INVALID_TIME_FORMAT);
        }

        return ($res->hasError()) ? $res : $res->ok();
    }

    private function datetime()
    {
        $res = new FunctionResponse();


        return ($res->hasError()) ? $res : $res->ok();
    }

    private function year()
    {
        $res = new FunctionResponse();


        return ($res->hasError()) ? $res : $res->ok();
    }

    private function numeric()
    {
        $res = new FunctionResponse();


        return ($res->hasError()) ? $res : $res->ok();
    }

    private function alpha()
    {
        $res = new FunctionResponse();


        return ($res->hasError()) ? $res : $res->ok();
    }

    private function alphaNumeric()
    {
        $res = new FunctionResponse();


        return ($res->hasError()) ? $res : $res->ok();
    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['string, minLength, maxLength, length, regex', $this->stringAndBaseMethods()];
        $results[] = ['integer', $this->integer()];
        $results[] = ['boolean', $this->boolean()];
        $results[] = ['float', $this->float()];
        $results[] = ['email', $this->email()];
        $results[] = ['phone', $this->phone()];
        $results[] = ['enum', $this->enum()];
        // $results[] = ['decimal', $this->decimal()]; the same test as $this->float()
        $results[] = ['date', $this->date()];
        $results[] = ['time', $this->time()];
        $results[] = ['datetime', $this->datetime()];
        $results[] = ['year', $this->year()];
        $results[] = ['numeric', $this->numeric()];
        $results[] = ['alpha', $this->alpha()];
        $results[] = ['alphaNumeric', $this->alphaNumeric()];

        $failedTests = [];

        foreach ($results as $result) {
            if ($result[1]->hasError())
                $failedTests[] = $result[0];
        }

        if (count($failedTests) > 0)
            return $response->fail('Failed Tests: ' . join(', ', $failedTests), $results);

        return $response->result($results);
    }
}