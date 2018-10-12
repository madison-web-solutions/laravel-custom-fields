<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\SwitchField;
use MadisonSolutions\LCF\TextField;
use MadisonSolutions\LCF\IntegerField;
use MadisonSolutions\LCF\SwitchValue;
use Illuminate\Validation\ValidationException;

class SwitchFieldTest extends TestCase
{
    public function testCanConvertValidValuesToSwitchField()
    {
        $field = new SwitchField([
            'switch_fields' => [
                'num' => new IntegerField([]),
                'level' => new TextField([]),
            ],
        ]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, ['switch' => 'num', 'num' => 10], ['switch' => 'num', 'num' => 10]);
        $this->assertCoerceOk($field, ['switch' => 'num', 'num' => null], ['switch' => 'num', 'num' => null]);
        $this->assertCoerceOk($field, ['switch' => 'num'], ['switch' => 'num', 'num' => null]);
        $this->assertCoerceOk($field, ['switch' => 'num', 'level' => 'high'], ['switch' => 'num', 'num' => null]);
        $this->assertCoerceOk($field, ['switch' => 'num', 'num' => 10, 'dummy' => 'derp'], ['switch' => 'num', 'num' => 10]);
        $this->assertCoerceOk($field, ['switch' => 'num', 'num' => '10'], ['switch' => 'num', 'num' => 10]);
        $this->assertCoerceOk($field, ['switch' => 'level', 'level' => 'high'], ['switch' => 'level', 'level' => 'high']);
        $this->assertCoerceOk($field, new SwitchValue('num', 10), ['switch' => 'num', 'num' => 10]);
        $this->assertCoerceOk($field, new SwitchValue('num', '10'), ['switch' => 'num', 'num' => 10]);
        $this->assertCoerceOk($field, new SwitchValue('level', 'low'), ['switch' => 'level', 'level' => 'low']);
        $this->assertCoerceOk($field, new SwitchValue('num', null), ['switch' => 'num', 'num' => null]);
    }

    public function testCannotConvertInvalidValuesToSwitchField()
    {
        $field = new SwitchField([
            'switch_fields' => [
                'num' => new IntegerField([]),
                'level' => new TextField([]),
            ],
        ]);

        $this->assertCoerceFails($field, 10);
        $this->assertCoerceFails($field, ['num' => 10]);
        $this->assertCoerceFails($field, ['switch' => 'num', 'num' => 'foo']);
        $this->assertCoerceFails($field, ['switch' => 'foo', 'num' => 10]);
        $this->assertCoerceFails($field, new SwitchValue('num', 'foo'));
        $this->assertCoerceFails($field, new SwitchValue('foo', null));
    }

    public function testBasicValidationWorks()
    {
        $field = new SwitchField([
            'switch_fields' => [
                'num' => new IntegerField([]),
                'level' => new TextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, ['switch' => 'num', 'num' => null]);
        $this->assertValidationPasses($field, ['switch' => 'num']);
        $this->assertValidationPasses($field, ['switch' => 'num', 'num' => 10]);
        $this->assertValidationPasses($field, ['switch' => 'level', 'level' => 'high']);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 'foo');
        $this->assertValidationFails($field, ['num' => 10]);
        $this->assertValidationFails($field, ['switch' => 'foo', 'num' => 10]);
        $this->assertValidationFails($field, ['switch' => 'num', 'num' => 'foo']);
    }

    public function testRequiredAttributeWorks()
    {
        // Outer switch required, inner value not required
        $field = new SwitchField([
            'required' => true,
            'switch_fields' => [
                'num' => new IntegerField([]),
                'level' => new TextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, ['switch' => 'num', 'num' => 10]);
        $this->assertValidationFails($field, null);
        $this->assertValidationPasses($field, ['switch' => 'num', 'num' => null]);
        $this->assertValidationPasses($field, ['switch' => 'num']);
        $this->assertValidationFailsWhenValueOmitted($field);

        // Outer switch required, inner value also required
        $field = new SwitchField([
            'required' => true,
            'switch_fields' => [
                'num' => new IntegerField([
                    'required' => true,
                ]),
                'level' => new TextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, ['switch' => 'num', 'num' => 10]);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, ['switch' => 'num', 'num' => null]);
        $this->assertValidationFails($field, ['switch' => 'num']);
        $this->assertValidationFailsWhenValueOmitted($field);

        // Outer switch not required, inner value required if present
        $field = new SwitchField([
            'required' => false,
            'switch_fields' => [
                'num' => new IntegerField([
                    'required' => true,
                ]),
                'level' => new TextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, ['switch' => 'num', 'num' => 10]);
        $this->assertValidationPasses($field, null);
        $this->assertValidationFails($field, ['switch' => 'num', 'num' => null]);
        $this->assertValidationFails($field, ['switch' => 'num']);
        $this->assertValidationPassesWhenValueOmitted($field);
    }

    public function testInvalidKeysNotAllowedInSwitch()
    {
        $invalid_keys = [
            null,
            '',
            0,
            '3',
            '3b',
            'ABC',
            'hello world',
            'hello-world',
            'h.w',
            'switch' // Cannot use 'switch' as the name of a switch field
        ];
        foreach ($invalid_keys as $invalid_key) {
            $this->assertExceptionThrown(ValidationException::class, function () use ($invalid_key) {
                $field = new SwitchField([
                    'switch_fields' => [
                        $invalid_key => new IntegerField([]),
                    ],
                ]);
            });
        }
    }

    public function testAtLeastOneSwitchFieldRequired()
    {
        $invalid_options = [
            ['switch_fields' => []],
            ['switch_fields' => null],
            [],
        ];
        foreach ($invalid_options as $options) {
            $this->assertExceptionThrown(ValidationException::class, function () use ($options) {
                $field = new SwitchField($options);
            });
        }
    }
}
