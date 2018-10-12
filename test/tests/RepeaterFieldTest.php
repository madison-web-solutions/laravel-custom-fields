<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\RepeaterField;
use MadisonSolutions\LCF\TextField;

class RepeaterFieldTest extends TestCase
{
    public function testCanConvertValidValuesToRepeater()
    {
        $field = new RepeaterField([
            'sub_field' => new TextField([]),
        ]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, [], null);
        $this->assertCoerceOk($field, ['foo'], ['foo']);
        $this->assertCoerceOk($field, ['foo', 'bar'], ['foo', 'bar']);
        $this->assertCoerceOk($field, [1, 5], ['1', '5']);
        $this->assertCoerceOk($field, ['a' => 'foo', 'b' => 'bar'], ['foo', 'bar']);
        $this->assertCoerceOk($field, 'foo', ['foo']);
        $this->assertCoerceOk($field, false, ['false']);
    }

    public function testCannotConvertInvalidValuesToRepeater()
    {
        $field = new RepeaterField([
            'sub_field' => new TextField([]),
        ]);

        $this->assertCoerceFails($field, new \stdClass());
        $this->assertCoerceFails($field, [new \stdClass()]);
        $this->assertCoerceFails($field, [[]]);
    }

    public function testBasicValidationWorks()
    {
        $field = new RepeaterField([
            'sub_field' => new TextField([]),
        ]);

        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, [null]);
        $this->assertValidationPasses($field, ['foo']);
        $this->assertValidationPasses($field, ['foo', 'bar']);
        $this->assertValidationPasses($field, ['a' => 'foo', 'b' => 'bar']);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 'foo');
        $this->assertValidationFails($field, [10]);
        $this->assertValidationFails($field, ['foo', 10]);
    }

    public function testRequiredAttributeWorks()
    {
        // Repeater required, but inner Text not required
        $field = new RepeaterField([
            'required' => true,
            'sub_field' => new TextField([]),
        ]);

        $this->assertValidationPasses($field, ['foo']);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, []);
        $this->assertValidationPasses($field, [null]);
        $this->assertValidationFailsWhenValueOmitted($field);

        $field = new RepeaterField([
            'required' => true,
            'sub_field' => new TextField([
                'required' => true,
            ]),
        ]);

        // Repeater required, and inner Text also required
        $this->assertValidationPasses($field, ['foo']);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, []);
        $this->assertValidationFails($field, [null]);
        $this->assertValidationFailsWhenValueOmitted($field);

        $field = new RepeaterField([
            'required' => false,
            'sub_field' => new TextField([
                'required' => true,
            ]),
        ]);

        // Repeater not required, but inner Text required when present
        $this->assertValidationPasses($field, ['foo']);
        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, []);
        $this->assertValidationFails($field, [null]);
        $this->assertValidationPassesWhenValueOmitted($field);
    }
}
