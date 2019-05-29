<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\CompoundField;
use MadisonSolutions\LCF\TextField;
use MadisonSolutions\LCF\IntegerField;
use MadisonSolutions\LCF\RepeaterField;
use Illuminate\Validation\ValidationException;

class CompoundFieldTest extends TestCase
{
    public function testCanConvertValidValuesToCompoundField()
    {
        $field = new CompoundField([
            'sub_fields' => [
                'num' => new IntegerField([]),
                'level' => new TextField([]),
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
        $field = new CompoundField([
            'sub_fields' => [
                'num' => new IntegerField([]),
            ],
        ]);

        $this->assertCoerceFails($field, 10);
        $this->assertCoerceFails($field, ['num' => 'foo']);
    }

    public function testBasicValidationWorks()
    {
        $field = new CompoundField([
            'sub_fields' => [
                'num' => new IntegerField([]),
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
        $field = new CompoundField([
            'required' => true,
            'sub_fields' => [
                'num' => new IntegerField([]),
            ],
        ]);

        $this->assertValidationPasses($field, ['num' => 10]);
        $this->assertValidationFails($field, null);
        $this->assertValidationPasses($field, ['num' => null]);
        $this->assertValidationFailsWhenValueOmitted($field);

        // Outer compound required, inner value also required
        $field = new CompoundField([
            'required' => true,
            'sub_fields' => [
                'num' => new IntegerField([
                    'required' => true,
                ]),
            ],
        ]);

        $this->assertValidationPasses($field, ['num' => 10]);
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, ['num' => null]);
        $this->assertValidationFailsWhenValueOmitted($field);


        // Outer compound not required, inner value required when outer is present
        $field = new CompoundField([
            'required' => false,
            'sub_fields' => [
                'num' => new IntegerField([
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
            $this->assertExceptionThrown(ValidationException::class, function () use ($invalid_key) {
                $field = new CompoundField([
                    'sub_fields' => [
                        $invalid_key => new IntegerField([]),
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
            $this->assertExceptionThrown(ValidationException::class, function () use ($options) {
                $field = new CompoundField($options);
            });
        }
    }

    public function testPartialCoersion()
    {
        $field = new CompoundField([
            'sub_fields' => [
                'name' => new TextField([]),
                'age' => new IntegerField([]),
                'scores' => new RepeaterField([
                    'sub_field' => new IntegerField([]),
                ])
            ],
        ]);

        $input = ['name' => 'Dan', 'age' => 'foo', 'scores' => [1, 2, 3]];
        $expected_output = ['name' => 'Dan', 'age' => null, 'scores' => [1, 2, 3]];
        $output = $field->coerce($input, 0);
        $this->assertSame($expected_output, $output);

        $input = ['name' => null, 'age' => '37', 'scores' => [1, '2', 3, null]];
        $expected_output = ['name' => null, 'age' => 37, 'scores' => [1, 2, 3, null]];
        $output = $field->coerce($input, 0);
        $this->assertSame($expected_output, $output);

        $input = ['name' => 'Dan', 'scores' => [1, 2, 3, 'foo'], 'dummy' => 'dummy'];
        $expected_output = ['name' => 'Dan', 'age' => null, 'scores' => [1, 2, 3, null]];
        $output = $field->coerce($input, 0);
        $this->assertSame($expected_output, $output);

        $input = ['name' => ['Dan'], 'age' => 37, 'scores' => 'foo'];
        $expected_output = ['name' => null, 'age' => 37, 'scores' => [null]];
        $output = $field->coerce($input, 0);
        $this->assertSame($expected_output, $output);
    }
}
