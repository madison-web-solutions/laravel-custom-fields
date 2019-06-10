<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class NumberFieldTest extends TestCase
{
    public function testCanConvertValidValuesToNumber()
    {
        $field = LCF::newNumberField([]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, 10, 10);
        $this->assertCoerceOk($field, 0, 0);
        $this->assertCoerceOk($field, -5, -5);
        $this->assertCoerceOk($field, '10', 10);
        $this->assertCoerceOk($field, 2.0, 2);
        $this->assertCoerceOk($field, '2.0', 2);
        $this->assertCoerceOk($field, 2.1, 2.1);
        $this->assertCoerceOk($field, '2.1', 2.1);
        $this->assertCoerceOk($field, true, 1);
        $this->assertCoerceOk($field, false, 0);
    }

    public function testCannotConvertInvalidValuesToNumber()
    {
        $field = LCF::newNumberField([]);

        $this->assertCoerceFails($field, []);
        $this->assertCoerceFails($field, new \stdClass());
        $this->assertCoerceFails($field, INF);
        $this->assertCoerceFails($field, 'foo');
        $this->assertCoerceFails($field, '£2.50');
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newNumberField([]);

        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, 10);
        $this->assertValidationPasses($field, 0);
        $this->assertValidationPasses($field, -5);
        $this->assertValidationPasses($field, 2.5);
        $this->assertValidationPassesWhenValueOmitted($field);

        // strings '' and '10' fail because coercion should be done before validation
        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, '10');
        $this->assertValidationFails($field, 'cheese');
        $this->assertValidationFails($field, ['cheese']);
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newNumberField(['required' => true]);

        $this->assertValidationPasses($field, 10);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newNumberField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, 5.1);
        $this->assertValidationPasses($field, 5);
        $this->assertValidationFails($field, 4.9);

        $field = LCF::newNumberField([
            'min' => 0,
        ]);
        $this->assertValidationPasses($field, 5);
        $this->assertValidationPasses($field, 0);
        $this->assertValidationFails($field, -5);

        $field = LCF::newNumberField([
            'max' => 10,
        ]);
        $this->assertValidationPasses($field, 9.9);
        $this->assertValidationPasses($field, 10);
        $this->assertValidationFails($field, 10.1);

        $field = LCF::newNumberField([
            'max' => 0,
        ]);
        $this->assertValidationPasses($field, -5);
        $this->assertValidationPasses($field, 0);
        $this->assertValidationFails($field, 10);
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newNumberField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, null);

        $field = LCF::newNumberField([
            'required' => true,
            'min' => 5,
        ]);
        $this->assertValidationFails($field, null);
    }

    public function testInputTransformations()
    {
        $field = LCF::newNumberField([
            'decimals' => 2,
        ]);
        $this->assertCoerceOk($field, 383.549, 383.55);
        $this->assertCoerceOk($field, -103.777, -103.78);

        $field = LCF::newNumberField([
            'decimals' => 0,
        ]);
        $this->assertCoerceOk($field, 383.549, 384);
        $this->assertCoerceOk($field, -103.777, -104);

        $field = LCF::newNumberField([
            'decimals' => -2,
        ]);
        $this->assertCoerceOk($field, 383.549, 400);
        $this->assertCoerceOk($field, -103.777, -100);
    }
}
