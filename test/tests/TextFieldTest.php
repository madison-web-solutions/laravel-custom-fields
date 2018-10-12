<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\TextField;

class TextFieldTest extends TestCase
{
    public function testCanConvertValidValuesToText()
    {
        $field = new TextField([]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, 'foo', 'foo');
        $this->assertCoerceOk($field, false, 'false');
        $this->assertCoerceOk($field, true, 'true');
        $this->assertCoerceOk($field, 10, '10');
        $this->assertCoerceOk($field, 0.5, '0.5');
        $this->assertCoerceOk($field, '', null);

        // Create an object with a __toString() method
        $fooObj = new class {
            public function __toString()
            {
                return 'foo';
            }
        };
        $this->assertCoerceOk($field, $fooObj, 'foo');
    }

    public function testCannotConvertInvalidValuesToText()
    {
        $field = new TextField([]);

        $this->assertCoerceFails($field, []);
        $this->assertCoerceFails($field, new \stdClass());
    }

    public function testBasicValidationWorks()
    {
        $field = new TextField([]);

        $this->assertValidationPasses($field, 'cheese');
        $this->assertValidationPasses($field, null);
        $this->assertValidationPasses($field, '');
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, 10);
        $this->assertValidationFails($field, ['cheese']);
    }

    public function testRequiredAttributeWorks()
    {
        $field = new TextField(['required' => true]);

        $this->assertValidationPasses($field, 'cheese');
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }
}
