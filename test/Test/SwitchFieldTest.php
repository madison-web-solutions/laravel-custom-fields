<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\FieldOptionsValidationException;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCF\SwitchValue;
use MadisonSolutions\LCFTest\TestCase;

class SwitchFieldTest extends TestCase
{
    public function testCanConvertValidValuesToSwitchField()
    {
        $field = LCF::newSwitchField([
            'switch_fields' => [
                'num' => LCF::newIntegerField([]),
                'level' => LCF::newTextField([]),
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
        $field = LCF::newSwitchField([
            'switch_fields' => [
                'num' => LCF::newIntegerField([]),
                'level' => LCF::newTextField([]),
            ],
        ]);

        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, 10);
        $this->assertCoerceFails($field, ['num' => 10]);
        $this->assertCoerceFails($field, ['switch' => 'num', 'num' => 'foo'], ['switch' => 'num', 'num' => null]);
        $this->assertCoerceFails($field, ['switch' => 'foo', 'num' => 10]);
        $this->assertCoerceFails($field, new SwitchValue('num', 'foo'), ['switch' => 'num', 'num' => null]);
        $this->assertCoerceFails($field, new SwitchValue('foo', null));
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newSwitchField([
            'switch_fields' => [
                'num' => LCF::newIntegerField([]),
                'level' => LCF::newTextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, new SwitchValue('num', null));
        $this->assertValidationPasses($field, new SwitchValue('num'));
        $this->assertValidationPasses($field, new SwitchValue('num', 10));
        $this->assertValidationPasses($field, new SwitchValue('level', 'high'));
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 'foo');
        $this->assertValidationFails($field, ['switch' => 'num', 'num' => 10]);
        $this->assertValidationFails($field, new SwitchValue('foo', 10));
        $this->assertValidationFails($field, new SwitchValue('num', 'foo'));
    }

    public function testRequiredAttributeWorks()
    {
        // Outer switch required, inner value not required
        $field = LCF::newSwitchField([
            'required' => true,
            'switch_fields' => [
                'num' => LCF::newIntegerField([]),
                'level' => LCF::newTextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, new SwitchValue('num', 10));
        $this->assertValidationFails($field, null);
        $this->assertValidationPasses($field, new SwitchValue('num', null));
        $this->assertValidationPasses($field, new SwitchValue('num'));
        $this->assertValidationFailsWhenValueOmitted($field);

        // Outer switch required, inner value also required
        $field = LCF::newSwitchField([
            'required' => true,
            'switch_fields' => [
                'num' => LCF::newIntegerField([
                    'required' => true,
                ]),
                'level' => LCF::newTextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, new SwitchValue('num', 10));
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, new SwitchValue('num', null));
        $this->assertValidationFails($field, new SwitchValue('num'));
        $this->assertValidationFailsWhenValueOmitted($field);

        // Outer switch not required, inner value required if present
        $field = LCF::newSwitchField([
            'required' => false,
            'switch_fields' => [
                'num' => LCF::newIntegerField([
                    'required' => true,
                ]),
                'level' => LCF::newTextField([]),
            ],
        ]);

        $this->assertValidationPasses($field, new SwitchValue('num', 10));
        $this->assertValidationPasses($field, null);
        $this->assertValidationFails($field, new SwitchValue('num', null));
        $this->assertValidationFails($field, new SwitchValue('num'));
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
            $this->assertExceptionThrown(FieldOptionsValidationException::class, function () use ($invalid_key) {
                $field = LCF::newSwitchField([
                    'switch_fields' => [
                        $invalid_key => LCF::newIntegerField([]),
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
            $this->assertExceptionThrown(FieldOptionsValidationException::class, function () use ($options) {
                $field = LCF::newSwitchField($options);
            });
        }
    }
}
