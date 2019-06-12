<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\FieldOptionsValidationException;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class CompoundFieldTest extends TestCase
{
    public function testCanConvertValidValuesToCompoundField()
    {
        $field = LCF::newCompoundField([
            'sub_fields' => [
                'num' => LCF::newIntegerField([]),
                'level' => LCF::newTextField([]),
            ],
        ]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, [], ['num' => null, 'level' => null]);
        $this->assertCoerceOk($field, ['num' => null], ['num' => null, 'level' => null]);
        $this->assertCoerceOk($field, ['num' => 10], ['num' => 10, 'level' => null]);
        $this->assertCoerceOk($field, ['num' => 10, 'foo' => 'bar'], ['num' => 10, 'level' => null]);
        $this->assertCoerceOk($field, ['num' => 10, 'level' => 'high'], ['num' => 10, 'level' => 'high']);
    }

    public function testCannotConvertInvalidValuesToCompoundField()
    {
        $field = LCF::newCompoundField([
            'sub_fields' => [
                'num' => LCF::newIntegerField([]),
            ],
        ]);

        $this->assertCoerceFails($field, 10);
        $this->assertCoerceFails($field, ['num' => 'foo'], ['num' => null]);
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newCompoundField([
            'sub_fields' => [
                'num' => LCF::newIntegerField([]),
            ],
        ]);

        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, []);
        $this->assertValidationPasses($field, ['num' => null]);
        $this->assertValidationPasses($field, ['num' => 10]);
        $this->assertValidationPasses($field, ['num' => 10, 'foo' => 'bar']);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 10);
        $this->assertValidationFails($field, ['num' => 'foo']);
    }

    public function testRequiredAttributeWorks()
    {
        // Outer compound required, inner values not required
        $field = LCF::newCompoundField([
            'required' => true,
            'sub_fields' => [
                'num' => LCF::newIntegerField([]),
            ],
        ]);

        $this->assertValidationPasses($field, ['num' => 10]);
        $this->assertValidationFails($field, null);
        $this->assertValidationPasses($field, ['num' => null]);
        $this->assertValidationFailsWhenValueOmitted($field);

        // Outer compound required, inner value also required
        $field = LCF::newCompoundField([
            'required' => true,
            'sub_fields' => [
                'num' => LCF::newIntegerField([
                    'required' => true,
                ]),
            ],
        ]);

        $this->assertValidationPasses($field, ['num' => 10]);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, ['num' => null]);
        $this->assertValidationFailsWhenValueOmitted($field);


        // Outer compound not required, inner value required when outer is present
        $field = LCF::newCompoundField([
            'required' => false,
            'sub_fields' => [
                'num' => LCF::newIntegerField([
                    'required' => true,
                ]),
            ],
        ]);

        $this->assertValidationPasses($field, ['num' => 10]);
        $this->assertValidationPasses($field, null);
        $this->assertValidationFails($field, ['num' => null]);
        $this->assertValidationPassesWhenValueOmitted($field);
    }

    public function testInvalidKeysNotAllowedInCompound()
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
        ];
        foreach ($invalid_keys as $invalid_key) {
            $this->assertExceptionThrown(FieldOptionsValidationException::class, function () use ($invalid_key) {
                $field = LCF::newCompoundField([
                    'sub_fields' => [
                        $invalid_key => LCF::newIntegerField([]),
                    ],
                ]);
            });
        }
    }

    public function testAtLeastOneSubFieldRequired()
    {
        $invalid_options = [
            ['sub_fields' => []],
            ['sub_fields' => null],
            [],
        ];
        foreach ($invalid_options as $options) {
            $this->assertExceptionThrown(FieldOptionsValidationException::class, function () use ($options) {
                $field = LCF::newCompoundField($options);
            });
        }
    }

    public function testPartialCoersion()
    {
        $field = LCF::newCompoundField([
            'sub_fields' => [
                'name' => LCF::newTextField([]),
                'age' => LCF::newIntegerField([]),
                'scores' => LCF::newRepeaterField([
                    'sub_field' => LCF::newIntegerField([]),
                ])
            ],
        ]);

        $input = ['name' => 'Dan', 'age' => 'foo', 'scores' => [1, 2, 3]];
        $expected_output = ['name' => 'Dan', 'age' => null, 'scores' => [1, 2, 3]];
        $this->assertCoerceFails($field, $input, $expected_output);

        $input = ['name' => null, 'age' => '37', 'scores' => [1, '2', 3, null]];
        $expected_output = ['name' => null, 'age' => 37, 'scores' => [1, 2, 3, null]];
        $this->assertCoerceOk($field, $input, $expected_output);

        $input = ['name' => 'Dan', 'scores' => [1, 2, 3, 'foo'], 'dummy' => 'dummy'];
        $expected_output = ['name' => 'Dan', 'age' => null, 'scores' => [1, 2, 3, null]];
        $this->assertCoerceFails($field, $input, $expected_output);

        $input = ['name' => ['Dan'], 'age' => 37, 'scores' => 'foo'];
        $expected_output = ['name' => null, 'age' => 37, 'scores' => null];
        $this->assertCoerceFails($field, $input, $expected_output);
    }
}
