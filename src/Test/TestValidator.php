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

        $this->testValidatorResponse($validatorRes, $res, 'string_fail', 'wrongValueType', ['string', 'integer']);

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
        $response = new FunctionResponse();

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
            $response->fail('integer should be valid');

        // ----- integer_negative ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_negative'))
            $response->fail('integer_negative should be valid');

        // --- integer_positive_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_positive_fail', 'wrongValueType');

        // --- integer_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_fail', 'wrongValueType');

        // --- integer_fail2 ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_fail2', 'wrongValueType');

        // ----- integer_with_tabs_and_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_with_tabs_and_spaces'))
            $response->fail('integer_with_tabs_and_spaces should be valid');

        // ----- integer_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_strict'))
            $response->fail('integer_strict should be valid');

        // --- integer_strict_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_strict_fail', 'wrongValueType');

        // ====================================================

        // ----- integer_only_negative ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative'))
            $response->fail('integer_only_negative should be valid');

        // ----- integer_only_negative_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative_spaces'))
            $response->fail('integer_only_negative_spaces should be valid');

        // ----- integer_only_negative_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative_strict'))
            $response->fail('integer_only_negative_strict should be valid');

        // --- integer_only_negative_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_negative_fail', 'valueTypeIsNotNegativeInteger');

        // --- integer_only_negative_strict_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_negative_strict_fail', 'valueTypeIsNotNegativeInteger');

        // ====================================================

        // ----- integer_only_positive ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive'))
            $response->fail('integer_only_positive should be valid');

        // ----- integer_only_positive_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive_spaces'))
            $response->fail('integer_only_positive_spaces should be valid');

        // ----- integer_only_positive_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive_strict'))
            $response->fail('integer_only_positive_strict should be valid');

        // --- integer_only_positive_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_positive_fail', 'valueTypeIsNotPositiveInteger');

        // --- integer_only_positive_strict_fail ---

        $this->testValidatorResponse($validatorRes, $res, 'integer_only_positive_strict_fail', 'valueTypeIsNotPositiveInteger');

        return ($response->hasError()) ? $response : $response->ok();
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

        $this->testValidatorResponse($validatorRes, $res, 'bool_fail', 'wrongValueType', ['boolean', 'string']);
        $this->testValidatorResponse($validatorRes, $res, 'bool_fail2', 'wrongValueType', ['boolean', 'integer']);
        $this->testValidatorResponse($validatorRes, $res, 'bool_fail3', 'wrongValueType');
        $this->testValidatorResponse($validatorRes, $res, 'bool_fail4', 'wrongValueType', ['boolean', 'string']);
        $this->testValidatorResponse($validatorRes, $res, 'bool_strict_fail', 'wrongValueType', ['boolean', 'string']);
        $this->testValidatorResponse($validatorRes, $res, 'bool_strict_fail2', 'wrongValueType', ['boolean', 'string']);
        $this->testValidatorResponse($validatorRes, $res, 'bool_strict_fail3', 'wrongValueType', ['boolean', 'integer']);

        return ($res->hasError()) ? $res : $res->ok();
    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['string, minLength, maxLength, length, regex', $this->stringAndBaseMethods()];
        $results[] = ['integer', $this->integer()];
        $results[] = ['boolean', $this->boolean()];

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