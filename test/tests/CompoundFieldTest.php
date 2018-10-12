<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\CompoundField;
use MadisonSolutions\LCF\TextField;
use MadisonSolutions\LCF\IntegerField;
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
}
