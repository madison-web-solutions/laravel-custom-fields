<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\Field;
use Illuminate\Support\MessageBag;
use Tests\TestCase as LaravelTestCase;
use TypeError;

class TestCase extends LaravelTestCase
{
    protected function assertCoerceOk(Field $field, $input, $expected_output)
    {
        $this->assertSame($expected_output, $field->toPrimitive($input));
    }

    protected function assertCoerceFails(Field $field, $value)
    {
        $error = null;
        try {
            $field->coerce($value);
        } catch (TypeError $e) {
            $error = $e;
        }
        $this->assertInstanceOf(TypeError::class, $error);
    }

    protected function assertValidationPasses(Field $field, $value)
    {
        $messages = new MessageBag();
        $field->validate(['dummy' => $value], 'dummy', $messages);
        $this->assertTrue($messages->isEmpty());
    }

    protected function assertValidationFails(Field $field, $value)
    {
        $messages = new MessageBag();
        $field->validate(['dummy' => $value], 'dummy', $messages);
        $this->assertFalse($messages->isEmpty());
    }

    protected function assertValidationPassesWhenValueOmitted(Field $field)
    {
        $messages = new MessageBag();
        $field->validate([], 'dummy', $messages);
        $this->assertTrue($messages->isEmpty());
    }

    protected function assertValidationFailsWhenValueOmitted(Field $field)
    {
        $messages = new MessageBag();
        $field->validate([], 'dummy', $messages);
        $this->assertFalse($messages->isEmpty());
    }
}
