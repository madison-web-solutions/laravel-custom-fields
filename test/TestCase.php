<?php

namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\Validator as LCFValidator;
use Tests\TestCase as LaravelTestCase;
use TypeError;

class TestCase extends LaravelTestCase
{
    protected function assertExceptionThrown(string $expected_exception_class, callable $callback)
    {
        $actual_exception = null;
        try {
            $callback();
        } catch (\Exception $e) {
            $actual_exception = $e;
        }
        if (!($actual_exception instanceof $expected_exception_class)) {
            error_log($actual_exception->getMessage());
        }
        $this->assertInstanceOf($expected_exception_class, $actual_exception);
        return $actual_exception;
    }

    protected function assertCoerceOk(Field $field, $input, $expected_output)
    {
        $result = $field->coerce($input, $output);
        $this->assertTrue($result);
        $this->assertSame(json_encode($expected_output), json_encode($output));
    }

    protected function assertCoerceFails(Field $field, $input, $expected_output = null)
    {
        $result = $field->coerce($input, $output);
        $this->assertFalse($result);
        $this->assertSame(json_encode($expected_output), json_encode($output));
    }

    protected function assertValidationPasses(Field $field, $value)
    {
        $v = new LCFValidator(['dummy' => $value], ['dummy' => $field]);
        $this->assertSame([], $v->messages()->all());
        $this->assertTrue($v->passes());
    }

    protected function assertValidationFails(Field $field, $value)
    {
        $v = new LCFValidator(['dummy' => $value], ['dummy' => $field]);
        $this->assertFalse($v->passes());
    }

    protected function assertValidationPassesWhenValueOmitted(Field $field)
    {
        $v = new LCFValidator([], ['dummy' => $field]);
        $this->assertSame([], $v->messages()->all());
        $this->assertTrue($v->passes());
    }

    protected function assertValidationFailsWhenValueOmitted(Field $field)
    {
        $v = new LCFValidator([], ['dummy' => $field]);
        $this->assertFalse($v->passes());
    }
}
