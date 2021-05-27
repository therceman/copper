<?php


namespace Copper\Test;


use Copper\Component\Validator\ValidatorHandler;
use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;

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

        if (is_array($result)) {
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

        $validatorRes = $validator->validate([
            'integer' => '1',
            'integer_negative' => '-5',
            'integer_positive_fail' => '+5',
            'integer_fail' => 'qwe',
            'integer_fail2' => 'qwe5',
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

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_negative_fail', 'valueTypeIsNotNegative');

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

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_positive_fail', 'valueTypeIsNotPositive');

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
            $validator->addFloatRule($key)->floatMaxDecimals(2);
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
            $validator->addFloatRule($key)->floatMaxDecimals(2)->strict(true);
        }

        $validator->addFloatRule('floatMaxDecimals_0')->floatMaxDecimals(0);

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
        $this->testValidatorResponse($validatorRes, $res, 'strict_float2_fail', 'valueTypeIsNotFloat',  'string');
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

        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos', 'valueTypeIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos2', 'valueTypeIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos3', 'valueTypeIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos4', 'valueTypeIsNotPositive');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_pos5', 'valueTypeIsNotPositive');

        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg', 'valueTypeIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg2', 'valueTypeIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg3', 'valueTypeIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg4', 'valueTypeIsNotNegative');
        $this->testValidatorResponse($validatorRes, $res, 'float_false_neg5', 'valueTypeIsNotNegative');

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