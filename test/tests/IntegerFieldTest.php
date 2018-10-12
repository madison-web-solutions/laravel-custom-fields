<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\IntegerField;

class IntegerFieldTest extends TestCase
{
    public function testCanConvertValidValuesToInteger()
    {
        $field = new IntegerField([]);

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
        $field = new IntegerField([]);

        $this->assertCoerceFails($field, []);
        $this->assertCoerceFails($field, new \stdClass());
        $this->assertCoerceFails($field, 2.5);
        $this->assertCoerceFails($field, INF);
        $this->assertCoerceFails($field, 'foo');
    }

    public function testBasicValidationWorks()
    {
        $field = new IntegerField([]);

        $this->assertValidationPasses($field, 10);
        $this->assertValidationPasses($field, '10');
        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, '');
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 'cheese');
        $this->assertValidationFails($field, ['cheese']);
    }

    public function testRequiredAttributeWorks()
    {
        $field = new IntegerField(['required' => true]);

        $this->assertValidationPasses($field, 10);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }
}
