<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\LCF;

class IntegerFieldTest extends TestCase
{
    public function testCanConvertValidValuesToInteger()
    {
        $field = LCF::newIntegerField([]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, 10, 10);
        $this->assertCoerceOk($field, 0, 0);
        $this->assertCoerceOk($field, -5, -5);
        $this->assertCoerceOk($field, '10', 10);
        $this->assertCoerceOk($field, 2.0, 2);
        $this->assertCoerceOk($field, '2.0', 2);
        $this->assertCoerceOk($field, true, 1);
        $this->assertCoerceOk($field, false, 0);
    }

    public function testCannotConvertInvalidValuesToInteger()
    {
        $field = LCF::newIntegerField([]);

        $this->assertCoerceFails($field, []);
        $this->assertCoerceFails($field, new \stdClass());
        $this->assertCoerceFails($field, 2.5);
        $this->assertCoerceFails($field, INF);
        $this->assertCoerceFails($field, 'foo');
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newIntegerField([]);

        $this->assertValidationPasses($field, 10);
        $this->assertValidationPasses($field, 0);
        $this->assertValidationPasses($field, -5);
        $this->assertValidationPasses($field, null);
        $this->assertValidationPassesWhenValueOmitted($field);

        // strings '' and '10' fail because coercion should be done before validation
        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, '10');
        $this->assertCoerceFails($field, 2.5);
        $this->assertValidationFails($field, 'cheese');
        $this->assertValidationFails($field, ['cheese']);
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newIntegerField(['required' => true]);

        $this->assertValidationPasses($field, 10);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newIntegerField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, 5);
        $this->assertValidationFails($field, 4);

        $field = LCF::newIntegerField([
            'min' => 0,
        ]);
        $this->assertValidationPasses($field, 5);
        $this->assertValidationPasses($field, 0);
        $this->assertValidationFails($field, -5);

        $field = LCF::newIntegerField([
            'max' => 10,
        ]);
        $this->assertValidationPasses($field, 10);
        $this->assertValidationFails($field, 11);

        $field = LCF::newIntegerField([
            'max' => 0,
        ]);
        $this->assertValidationPasses($field, -5);
        $this->assertValidationPasses($field, 0);
        $this->assertValidationFails($field, 10);
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newTextField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, null);

        $field = LCF::newTextField([
            'required' => true,
            'min' => 5,
        ]);
        $this->assertValidationFails($field, null);
    }
}
