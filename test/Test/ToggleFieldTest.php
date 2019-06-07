<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class ToggleFieldTest extends TestCase
{
    public function testCanConvertValidValuesToInteger()
    {
        $field = LCF::newToggleField(['true_label' => 'Do it', 'false_label' => 'Nope']);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, '', null);
        $this->assertCoerceOk($field, true, true);
        $this->assertCoerceOk($field, false, false);
        $this->assertCoerceOk($field, 'on', true);
        $this->assertCoerceOk($field, 'false', false);
        $this->assertCoerceOk($field, 1, true);
        $this->assertCoerceOk($field, 0, false);
        $this->assertCoerceOk($field, 't', true);
        $this->assertCoerceOk($field, 'NO', false);
        $this->assertCoerceOk($field, 'Do it', true);
        $this->assertCoerceOk($field, 'Nope', false);
    }

    public function testCannotConvertInvalidValuesToInteger()
    {
        $field = LCF::newToggleField([]);

        $this->assertCoerceFails($field, []);
        $this->assertCoerceFails($field, new \stdClass());
        $this->assertCoerceFails($field, 2.5);
        $this->assertCoerceFails($field, INF);
        $this->assertCoerceFails($field, 'foo');
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newToggleField([]);

        $this->assertValidationPasses($field, true);
        $this->assertValidationPasses($field, false);
        $this->assertValidationPasses($field, null);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, 1);
        $this->assertValidationFails($field, 0);
        $this->assertValidationFails($field, 'true');
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newToggleField(['required' => true]);

        $this->assertValidationPasses($field, true);
        $this->assertValidationPasses($field, false);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }
}
