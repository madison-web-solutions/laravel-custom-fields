<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class OptionsFieldTest extends TestCase
{
    public function testCanConvertValidValuesToOptions()
    {
        $field = LCF::newOptionsField(['choices' => ['a' => 'A', 'b' => 'B']]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, [], ['a' => false, 'b' => false]);
        $this->assertCoerceOk($field, ['foo' => 'bar'], ['a' => false, 'b' => false]);
        $this->assertCoerceOk($field, ['a' => true], ['a' => true, 'b' => false]);
        $this->assertCoerceOk($field, ['a' => 'true'], ['a' => true, 'b' => false]);
        $this->assertCoerceOk($field, ['b' => 1], ['a' => false, 'b' => true]);
        $this->assertCoerceOk($field, ['b' => null], ['a' => false, 'b' => false]);
        $this->assertCoerceOk($field, ['a' => true, 'b' => true], ['a' => true, 'b' => true]);
    }

    public function testCannotConvertInvalidValuesToOptions()
    {
        $field = LCF::newOptionsField(['choices' => ['a' => 'A', 'b' => 'B']]);

        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, 'a');
        $this->assertCoerceFails($field, ['a' => 'foo'], ['a' => false, 'b' => false]);
        $this->assertCoerceFails($field, ['a' => 'foo', 'b' => true], ['a' => false, 'b' => true]);
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newOptionsField(['choices' => ['a' => 'A', 'b' => 'B']]);

        $this->assertValidationPasses($field, ['a' => true, 'b' => true]);
        $this->assertValidationPasses($field, ['a' => true, 'b' => false]);
        $this->assertValidationPasses($field, ['a' => true, 'b' => true, 'foo' => 'bar']);
        $this->assertValidationPasses($field, null);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, false);
        $this->assertValidationFails($field, ['a' => 'true', 'b' => true]);
        $this->assertValidationFails($field, ['a' => true]);
        $this->assertValidationFails($field, ['a' => null, 'b' => true]);
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newOptionsField(['choices' => ['a' => 'A', 'b' => 'B'], 'required' => true]);

        $this->assertValidationPasses($field, ['a' => true, 'b' => false]);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }
}
