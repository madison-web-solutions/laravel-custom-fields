<?php
namespace MadisonSolutions\LCFTest;

use MadisonSolutions\LCF\RepeaterField;
use MadisonSolutions\LCF\SwitchField;
use MadisonSolutions\LCF\IntegerField;
use MadisonSolutions\LCF\SwitchValue;

class NestedFieldsTest extends TestCase
{
    public function testCanConvertValidValuesToNestedField()
    {
        $field = new RepeaterField([
            'sub_field' => new SwitchField([
                'switch_fields' => [
                    'num' => new IntegerField([]),
                    'scores' => new RepeaterField([
                        'sub_field' => new IntegerField([]),
                    ]),
                ],
            ]),
        ]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, [], null);
        $this->assertCoerceOk($field, [null], [null]);
        $this->assertCoerceOk($field, [new SwitchValue('num', null)], [['switch' => 'num', 'num' => null]]);
        $this->assertCoerceOk($field, [new SwitchValue('num', 10)], [['switch' => 'num', 'num' => 10]]);
        $this->assertCoerceOk($field, [new SwitchValue('scores', [])], [['switch' => 'scores', 'scores' => null]]);
        $this->assertCoerceOk($field, [new SwitchValue('scores', [null])], [['switch' => 'scores', 'scores' => [null]]]);
        $this->assertCoerceOk($field, [new SwitchValue('scores', [null, 10])], [['switch' => 'scores', 'scores' => [null, 10]]]);
        $this->assertCoerceOk($field, [new SwitchValue('scores', [2, 3, 4])], [['switch' => 'scores', 'scores' => [2, 3, 4]]]);

        $input = [new SwitchValue('num', 10), new SwitchValue('scores', [5, 3, 2])];
        $primitive = [['switch' => 'num', 'num' => 10], ['switch' => 'scores', 'scores' => [5, 3, 2]]];
        $this->assertCoerceOk($field, $input, $primitive);
    }

    public function testCannotConvertInvalidValuesToNestedField()
    {
        $field = new RepeaterField([
            'sub_field' => new SwitchField([
                'switch_fields' => [
                    'num' => new IntegerField([]),
                    'scores' => new RepeaterField([
                        'sub_field' => new IntegerField([]),
                    ]),
                ],
            ]),
        ]);

        $this->assertCoerceFails($field, 10);
        $this->assertCoerceFails($field, [10]);
        $this->assertCoerceFails($field, [['switch' => 'foo']]);
        $this->assertCoerceFails($field, [['switch' => 'num', 'num' => 'foo']]);
        $this->assertCoerceFails($field, [new SwitchValue('foo', 10)]);
        $this->assertCoerceFails($field, [new SwitchValue('num', 'foo')]);
        $this->assertCoerceFails($field, [new SwitchValue('num', 10), 'foo']);
        $this->assertCoerceFails($field, [new SwitchValue('num', 10), new SwitchValue('scores', 'foo')]);
    }
}
