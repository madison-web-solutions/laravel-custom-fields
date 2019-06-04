<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\LCF;

class RepeaterFieldTest extends TestCase
{
    public function testCanConvertValidValuesToRepeater()
    {
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newTextField([]),
        ]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, [], null);
        $this->assertCoerceOk($field, ['foo'], ['foo']);
        $this->assertCoerceOk($field, ['foo', 'bar'], ['foo', 'bar']);
        $this->assertCoerceOk($field, [1, 5], ['1', '5']);
        $this->assertCoerceOk($field, ['a' => 'foo', 'b' => 'bar'], ['foo', 'bar']);
    }

    public function testCannotConvertInvalidValuesToRepeater()
    {
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newTextField([]),
        ]);

        $this->assertCoerceFails($field, 'foo');
        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, new \stdClass());
        $this->assertCoerceFails($field, [new \stdClass()], [null]);
        $this->assertCoerceFails($field, [[]], [null]);
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newTextField([]),
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
        $field = LCF::newRepeaterField([
            'required' => true,
            'sub_field' => LCF::newTextField([]),
        ]);

        // Repeater required, but inner Text not required
        $this->assertValidationPasses($field, ['foo']);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, []);
        $this->assertValidationPasses($field, [null]);
        $this->assertValidationFailsWhenValueOmitted($field);

        $field = LCF::newRepeaterField([
            'required' => true,
            'sub_field' => LCF::newTextField([
                'required' => true,
            ]),
        ]);

        // Repeater required, and inner Text also required
        $this->assertValidationPasses($field, ['foo']);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, []);
        $this->assertValidationFails($field, [null]);
        $this->assertValidationFailsWhenValueOmitted($field);

        $field = LCF::newRepeaterField([
            'required' => false,
            'sub_field' => LCF::newTextField([
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

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newTextField([]),
            'min' => 2,
        ]);
        $this->assertValidationPasses($field, ['foo', 'bar']);
        $this->assertValidationFails($field, ['foo']);

        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newTextField([]),
            'max' => 2,
        ]);
        $this->assertValidationPasses($field, ['foo', 'bar']);
        $this->assertValidationFails($field, ['foo', 'bar', 'derp']);
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newTextField([]),
            'min' => 2,
        ]);
        $this->assertValidationPasses($field, []);
        $this->assertValidationPasses($field, null);

        $field = LCF::newRepeaterField([
            'required' => true,
            'sub_field' => LCF::newTextField([]),
            'min' => 2,
        ]);
        $this->assertValidationFails($field, []);
        $this->assertValidationFails($field, null);
    }
}
