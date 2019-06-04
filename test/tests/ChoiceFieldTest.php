<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\LCF;

class ChoiceFieldTest extends TestCase
{
    public function testCanConvertValidValuesToChoice()
    {
        $field = LCF::newChoiceField([
            'choices' => ['red' => 'Red', 'blue' => 'Blue']
        ]);

        $this->assertCoerceOk($field, 'red', 'red');
        $this->assertCoerceOk($field, 'blue', 'blue');
        $this->assertCoerceOk($field, '', null);
        $this->assertCoerceOk($field, null, null);
    }

    public function testCannotConvertInvalidValuesToChoice()
    {
        $field = LCF::newChoiceField([
            'choices' => ['red' => 'Red', 'blue' => 'Blue']
        ]);

        $this->assertCoerceFails($field, 'green');
        $this->assertCoerceFails($field, 'Red');
        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, []);
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newChoiceField([
            'choices' => ['red' => 'Red', 'blue' => 'Blue']
        ]);
        
        $this->assertValidationPasses($field, 'red');
        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, '');
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 'green');
        $this->assertValidationFails($field, false);
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newChoiceField([
            'choices' => ['red' => 'Red', 'blue' => 'Blue'],
            'required' => true,
        ]);

        $this->assertValidationPasses($field, 'red');
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }
}
