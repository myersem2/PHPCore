<?php

// TEST-1: Functioning as expected - PASS
$subj = $unset_variable ?? null;

$result = match ($subj) {
    'Some-Text' => 'Incorect-Match-1',
    'Other-Text' => 'Incorect-Match-2',
    default => 'Expected-Result',
};

var_dump($result); // string 'Expected-Result' (length=15)


// TEST-2: Incorrectly matches first expression - FAILS
function testFunction1()
{
    $subj = $unset_variable ?? null;

    $result = match ($subj) {
        'Some-Text' => 'Incorect-Match-1',
        'Other-Text' => 'Incorect-Match-2',
        default => 'Expected-Result',
    };

    var_dump($result); // string 'Incorect-Match-1' (length=16)
}
testFunction1();


// TEST-3: Incorrectly matches first expression - FAILS
class TestClass1
{
    public function __construct()
    {
        $subj = $unset_variable ?? null;

        $result = match ($subj) {
            'Some-Text' => 'Incorect-Match-1',
            'Other-Text' => 'Incorect-Match-2',
            default => 'Expected-Result',
        };

        var_dump($result); // string 'Incorect-Match-1' (length=16)
    }
}
$test_class = new TestClass1();


// TEST-4: Functioning as expected - PASS
function testFunction2()
{
    $subj = $unset_variable ?? null;

    $result = match ($subj) {
        'Some-Text' => 'Incorect-Match-1',
        //'Other-Text' => 'Incorect-Match-2',
        default => 'Expected-Result',
    };

    var_dump($result); // string 'Expected-Result' (length=15)
}
testFunction2();


// TEST-5: Functioning as expected - PASS
class TestClass2
{
    public function __construct()
    {
        $subj = $unset_variable ?? null;

        $result = match ($subj) {
            'Some-Text' => 'Incorect-Match-1',
            //'Other-Text' => 'Incorect-Match-2',
            default => 'Expected-Result',
        };

        var_dump($result); // string 'Expected-Result' (length=15)
    }
}
$test_class = new TestClass2();


// TEST-6: Functioning as expected - PASS
function testFunction3()
{
    $subj = null;

    $result = match ($subj) {
        'Some-Text' => 'Incorect-Match-1',
        'Other-Text' => 'Incorect-Match-2',
        default => 'Expected-Result',
    };

    var_dump($result); // string 'Expected-Result' (length=15)
}
testFunction3();


// TEST-7: Functioning as expected - PASS
class TestClass3
{
    public function __construct()
    {
        $subj = null;

        $result = match ($subj) {
            'Some-Text' => 'Incorect-Match-1',
            'Other-Text' => 'Incorect-Match-2',
            default => 'Expected-Result',
        };

        var_dump($result); // string 'Expected-Result' (length=15)
    }
}
$test_class = new TestClass3();