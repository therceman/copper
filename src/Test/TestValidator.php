<?php


namespace Copper\Test;


use Copper\Component\Validator\ValidatorHandler;
use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;

class TestValidator
{

    private function stringAndBaseMethods()
    {
        $response = new FunctionResponse();

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
            $response->fail('string should be valid');

        if (ArrayHandler::hasKey($validatorRes->result, 'string_fail') === false)
            $response->fail('string_fail should be invalid');

        if ($validatorRes->result['string_fail']->status !== false)
            $response->fail('string_fail should have status: false');

        if ($validatorRes->result['string_fail']->msg !== 'wrongValueType')
            $response->fail('string_fail should have msg: wrongValueType');

        if ($validatorRes->result['string_fail']->result[0] !== 'string')
            $response->fail('string_fail should have result[0]: string');

        if ($validatorRes->result['string_fail']->result[1] !== 'integer')
            $response->fail('string_fail should have result[1]: integer');

        // ----- string_required ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_required'))
            $response->fail('string_required should be valid');

        // --- string_required_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'string_required_fail') === false)
            $response->fail('string_required_fail should be invalid');

        if ($validatorRes->result['string_required_fail']->status !== false)
            $response->fail('string_required_fail should have status: false');

        if ($validatorRes->result['string_required_fail']->msg !== 'valueCannotBeEmpty')
            $response->fail('string_required_fail should have msg: valueCannotBeEmpty');

        // --- string_required_fail2 ---

        if (ArrayHandler::hasKey($validatorRes->result, 'string_required_fail2') === false)
            $response->fail('string_required_fail2 should be invalid');

        if ($validatorRes->result['string_required_fail2']->status !== false)
            $response->fail('string_required_fail2 should have status: false');

        if ($validatorRes->result['string_required_fail2']->msg !== 'valueCannotBeEmpty')
            $response->fail('string_required_fail2 should have msg: valueCannotBeEmpty');

        // ----- string_min_len_5 ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_min_len_5'))
            $response->fail('string_min_len_5 should be valid');

        // --- string_min_len_5_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'string_min_len_5_fail') === false)
            $response->fail('string_min_len_5_fail should be invalid');

        if ($validatorRes->result['string_min_len_5_fail']->status !== false)
            $response->fail('string_min_len_5_fail should have status: false');

        if ($validatorRes->result['string_min_len_5_fail']->msg !== 'minLengthRequired')
            $response->fail('string_min_len_5_fail should have msg: minLengthRequired');

        if ($validatorRes->result['string_min_len_5_fail']->result !== 5)
            $response->fail('string_min_len_5_fail should have result: 5');

        // ----- string_max_len_10 ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_max_len_10'))
            $response->fail('string_max_len_10 should be valid');

        // --- string_max_len_10_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'string_max_len_10_fail') === false)
            $response->fail('string_max_len_10_fail should be invalid');

        if ($validatorRes->result['string_max_len_10_fail']->status !== false)
            $response->fail('string_max_len_10_fail should have status: false');

        if ($validatorRes->result['string_max_len_10_fail']->msg !== 'maxLengthReached')
            $response->fail('string_max_len_10_fail should have msg: maxLengthReached');

        if ($validatorRes->result['string_max_len_10_fail']->result !== 10)
            $response->fail('string_max_len_10_fail should have result: 10');

        // ----- string_len_11 ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_len_11'))
            $response->fail('string_len_11 should be valid');

        // --- string_len_11_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'string_len_11_fail') === false)
            $response->fail('string_len_11_fail should be invalid');

        if ($validatorRes->result['string_len_11_fail']->status !== false)
            $response->fail('string_len_11_fail should have status: false');

        if ($validatorRes->result['string_len_11_fail']->msg !== 'wrongLength')
            $response->fail('string_len_11_fail should have msg: wrongLength');

        if ($validatorRes->result['string_len_11_fail']->result !== 11)
            $response->fail('string_len_11_fail should have result: 11');

        // ----- string_regex ----

        if (ArrayHandler::hasKey($validatorRes->result, 'string_regex'))
            $response->fail('string_regex should be valid');

        // --- string_regex_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'string_regex_fail') === false)
            $response->fail('string_regex_fail should be invalid');

        if ($validatorRes->result['string_regex_fail']->status !== false)
            $response->fail('string_regex_fail should have status: false');

        if ($validatorRes->result['string_regex_fail']->msg !== 'invalidValueFormat')
            $response->fail('string_regex_fail should have msg: invalidValueFormat');

        if ($validatorRes->result['string_regex_fail']->result['example'] !== '1337')
            $response->fail('string_regex_fail should have result example: 1337');

        return ($response->hasError()) ? $response : $response->ok();
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

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_positive_fail') === false)
            $response->fail('integer_positive_fail should be invalid');

        if ($validatorRes->result['integer_positive_fail']->status !== false)
            $response->fail('integer_positive_fail should have status: false');

        if ($validatorRes->result['integer_positive_fail']->msg !== 'wrongValueType')
            $response->fail('integer_positive_fail should have msg: wrongValueType');

        // --- integer_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_fail') === false)
            $response->fail('integer_fail should be invalid');

        if ($validatorRes->result['integer_fail']->status !== false)
            $response->fail('integer_fail should have status: false');

        if ($validatorRes->result['integer_fail']->msg !== 'wrongValueType')
            $response->fail('integer_fail should have msg: wrongValueType');

        // --- integer_fail2 ---

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_fail2') === false)
            $response->fail('integer_fail2 should be invalid');

        if ($validatorRes->result['integer_fail2']->status !== false)
            $response->fail('integer_fail2 should have status: false');

        if ($validatorRes->result['integer_fail2']->msg !== 'wrongValueType')
            $response->fail('integer_fail2 should have msg: wrongValueType');

        // ----- integer_with_tabs_and_spaces ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_with_tabs_and_spaces'))
            $response->fail('integer_with_tabs_and_spaces should be valid');

        // ----- integer_strict ----

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_strict'))
            $response->fail('integer_strict should be valid');

        // --- integer_strict_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_strict_fail') === false)
            $response->fail('integer_strict_fail should be invalid');

        if ($validatorRes->result['integer_strict_fail']->status !== false)
            $response->fail('integer_strict_fail should have status: false');

        if ($validatorRes->result['integer_strict_fail']->msg !== 'wrongValueType')
            $response->fail('integer_strict_fail should have msg: wrongValueType');

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

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative_fail') === false)
            $response->fail('integer_only_negative_fail should be invalid');

        if ($validatorRes->result['integer_only_negative_fail']->status !== false)
            $response->fail('integer_only_negative_fail should have status: false');

        if ($validatorRes->result['integer_only_negative_fail']->msg !== 'valueTypeIsNotNegativeInteger')
            $response->fail('integer_only_negative_fail should have msg: valueTypeIsNotNegativeInteger');

        // --- integer_only_negative_strict_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_negative_strict_fail') === false)
            $response->fail('integer_only_negative_strict_fail should be invalid');

        if ($validatorRes->result['integer_only_negative_strict_fail']->status !== false)
            $response->fail('integer_only_negative_strict_fail should have status: false');

        if ($validatorRes->result['integer_only_negative_strict_fail']->msg !== 'valueTypeIsNotNegativeInteger')
            $response->fail('integer_only_negative_strict_fail should have msg: valueTypeIsNotNegativeInteger');

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

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive_fail') === false)
            $response->fail('integer_only_positive_fail should be invalid');

        if ($validatorRes->result['integer_only_positive_fail']->status !== false)
            $response->fail('integer_only_positive_fail should have status: false');

        if ($validatorRes->result['integer_only_positive_fail']->msg !== 'valueTypeIsNotPositiveInteger')
            $response->fail('integer_only_positive_fail should have msg: valueTypeIsNotPositiveInteger');

        // --- integer_only_positive_strict_fail ---

        if (ArrayHandler::hasKey($validatorRes->result, 'integer_only_positive_strict_fail') === false)
            $response->fail('integer_only_positive_strict_fail should be invalid');

        if ($validatorRes->result['integer_only_positive_strict_fail']->status !== false)
            $response->fail('integer_only_positive_strict_fail should have status: false');

        if ($validatorRes->result['integer_only_positive_strict_fail']->msg !== 'valueTypeIsNotPositiveInteger')
            $response->fail('integer_only_positive_strict_fail should have msg: valueTypeIsNotPositiveInteger');

        return ($response->hasError()) ? $response : $response->ok();
    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['string, minLength, maxLength, length, regex', $this->stringAndBaseMethods()];
        $results[] = ['integer', $this->integer()];

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