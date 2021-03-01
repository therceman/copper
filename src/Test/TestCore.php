<?php


namespace Copper\Test;


use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;
use Copper\Handler\CollectionHandler;

class TestCore
{

    private function array_handler()
    {
        $response = new FunctionResponse();

        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $array2 = ['a' => 2, 'b' => 2, 'e' => 5];

        $assocArrayOfObjects = [
            (object)['id' => 1, 'title' => 'a', 'tag' => 'qwe', 'mark' => 11],
            (object)['id' => 2, 'title' => 'b', 'tag' => 'qwe2', 'mark' => 11],
            (object)['id' => 3, 'title' => 'c', 'tag' => 'qwe', 'mark' => 10],
            (object)['id' => 4, 'title' => 'd', 'tag' => 'qwe3', 'mark' => 12]
        ];

        $assocArray = [
            ['id' => 1, 'title' => 'a', 'tag' => 'qwe', 'mark' => 11],
            ['id' => 2, 'title' => 'b', 'tag' => 'qwe2', 'mark' => 11],
            ['id' => 3, 'title' => 'c', 'tag' => 'qwe', 'mark' => 10],
            ['id' => 4, 'title' => 'd', 'tag' => 'qwe3', 'mark' => 12]
        ];

        $res = ArrayHandler::lastKey($array);
        if ($res !== 'd')
            return $response->fail('Last key should be: d', $res);

        $res = ArrayHandler::lastValue($array);
        if ($res !== 4)
            return $response->fail('Last value should be: 4', $res);

        $res = ArrayHandler::hasValue($array, 3);
        if ($res !== true)
            return $response->fail('Should have value: 3', $res);

        $res = ArrayHandler::hasValue($array, 5);
        if ($res !== false)
            return $response->fail('Should not have value: 5', $res);

        $res = ArrayHandler::merge($array, $array2);
        if ($res !== ['a' => 2, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5])
            return $response->fail("Merge Should be exact, like: ['a' => 2, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]", $res);

        $res = ArrayHandler::merge_reindexKeys($array, $array2);
        if ($res !== [2, 2, 3, 4, 5])
            return $response->fail("Merge Should be exact, like: [2, 2, 3, 4, 5]", $res);

        $res = ArrayHandler::merge_reindexKeys($array, $array2, true);
        if ($res !== [2, 3, 4, 5])
            return $response->fail("Merge Should be exact, like: [2, 3, 4, 5]", $res);

        $res = ArrayHandler::merge_uniqueValues($array, $array2);
        if ($res !== ['a' => 2, 'c' => 3, 'd' => 4, 'e' => 5])
            return $response->fail("Merge Should be exact, like: ['a' => 2, 'c' => 3, 'd' => 4, 'e' => 5]", $res);

        $res = ArrayHandler::merge_uniqueValues($array, $array2, true);
        if ($res !== [2, 3, 4, 5])
            return $response->fail("Merge Should be exact, like: [2, 3, 4, 5]", $res);

        $res = ArrayHandler::switch('banana', ['apple', 'banana', 'milk'], ['good', 'great', 'bad']);
        if ($res !== 'great')
            return $response->fail("Banana should be: great", $res);

        $res = ArrayHandler::assocMatch($assocArray[1], ['id' => 2]);
        if ($res !== true)
            return $response->fail("Item should be matched", $res);

        $res = ArrayHandler::assocMatch($assocArrayOfObjects[2], ['tag' => 'qwe', 'mark' => 10]);
        if ($res !== true)
            return $response->fail("Item should be matched", $res);

        $res = ArrayHandler::assocMatch($assocArrayOfObjects[2], ['tag' => 'qwe', 'mark' => 11]);
        if ($res !== false)
            return $response->fail("Item should not be matched", $res);

        $res = ArrayHandler::assocFind($assocArray, ['id' => 2]);
        if ($res[0]['title'] !== 'b')
            return $response->fail("Matched entry title should be: b", $res);

        $res = ArrayHandler::assocFind($assocArrayOfObjects, ['id' => 2]);
        if ($res[0]->title !== 'b')
            return $response->fail("Matched entry title should be: b", $res);

        $res = ArrayHandler::assocFind($assocArrayOfObjects, ['tag' => 'qwe']);
        if (count($res) !== 2 && ($res[1]->mark !== 10))
            return $response->fail("Matched entry count should be 2 and last matched entry mark should be 10", $res);

        $res = ArrayHandler::assocFind($assocArrayOfObjects, ['tag' => 'qwe', 'mark' => 10]);
        if (count($res) !== 1 && ($res[0]->title !== 'c'))
            return $response->fail("Matched entry count should be 1 and last matched entry title should be c", $res);

        $res = ArrayHandler::assocValueList($assocArray, 'title');
        if ($res !== ['a', 'b', 'c', 'd'])
            return $response->fail("Result should be ['a','b','c','d']", $res);

        $res = ArrayHandler::assocDelete($assocArrayOfObjects, ['tag' => 'qwe', 'mark' => 12]);
        if ($res !== $assocArrayOfObjects)
            return $response->fail("Nothing should be changed", $res);

        $res = ArrayHandler::assocDelete($assocArrayOfObjects, ['tag' => 'qwe', 'mark' => 10]);
        if (json_encode($res) !== json_encode([
                (object)['id' => 1, 'title' => 'a', 'tag' => 'qwe', 'mark' => 11],
                (object)['id' => 2, 'title' => 'b', 'tag' => 'qwe2', 'mark' => 11],
                (object)['id' => 4, 'title' => 'd', 'tag' => 'qwe3', 'mark' => 12]
            ]))
            return $response->fail("Result should match.", $res);

        // TODO test clean()

        return $response->ok();
    }

    private function collection_handler()
    {
        $response = new FunctionResponse();

        $collection = [
            (object)['id' => 1, 'title' => 'a', 'tag' => 'qwe', 'mark' => 11],
            (object)['id' => 2, 'title' => 'b', 'tag' => 'qwe2', 'mark' => 11],
            (object)['id' => 3, 'title' => 'c', 'tag' => 'qwe', 'mark' => 10],
            (object)['id' => 4, 'title' => 'd', 'tag' => 'qwe3', 'mark' => 12]
        ];

        $res = CollectionHandler::valueList($collection, 'title');
        if ($res !== ['a', 'b', 'c', 'd'])
            return $response->fail("Result should be ['a','b','c','d']", $res);

        $res = CollectionHandler::find($collection, ['tag' => 'qwe']);
        if (count($res) !== 2 && ($res[1]->mark !== 10))
            return $response->fail("Matched entry count should be 2 and last matched entry mark should be 10", $res);

        $res = CollectionHandler::findFirst($collection, ['tag' => 'qwe']);
        if ($res->title !== 'a')
            return $response->fail("Matched entry title should be: a", $res);

        $res = CollectionHandler::delete($collection, ['tag' => 'qwe', 'mark' => 10]);
        if (json_encode($res) !== json_encode([
                (object)['id' => 1, 'title' => 'a', 'tag' => 'qwe', 'mark' => 11],
                (object)['id' => 2, 'title' => 'b', 'tag' => 'qwe2', 'mark' => 11],
                (object)['id' => 4, 'title' => 'd', 'tag' => 'qwe3', 'mark' => 12]
            ]))
            return $response->fail("Result should match.", $res);

        return $response->ok();
    }

    public function run()
    {
        $response = new FunctionResponse();

        $results = [];

        $results[] = ['array_handler', $this->array_handler()];
        $results[] = ['collection_handler', $this->collection_handler()];

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