<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCF\SwitchValue;
use MadisonSolutions\LCFTest\TestCase;

class NestedFieldsTest extends TestCase
{
    public function testCanConvertValidValuesToNestedField()
    {
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newSwitchField([
                'switch_fields' => [
                    'num' => LCF::newIntegerField([]),
                    'scores' => LCF::newRepeaterField([
                        'sub_field' => LCF::newIntegerField([]),
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
        $field = LCF::newRepeaterField([
            'sub_field' => LCF::newSwitchField([
                'switch_fields' => [
                    'num' => LCF::newIntegerField([]),
                    'scores' => LCF::newRepeaterField([
                        'sub_field' => LCF::newIntegerField([]),
                    ]),
                ],
            ]),
        ]);

        $this->assertCoerceFails($field, 10);
        $this->assertCoerceFails($field, [10], [null]);
        $this->assertCoerceFails($field, [['switch' => 'foo']], [null]);
        $this->assertCoerceFails($field, [['switch' => 'num', 'num' => 'foo']], [new SwitchValue('num', null)]);
        $this->assertCoerceFails($field, [new SwitchValue('foo', 10)], [null]);
        $this->assertCoerceFails($field, [new SwitchValue('num', 'foo')], [new SwitchValue('num', null)]);
        $this->assertCoerceFails($field, [new SwitchValue('num', 10), 'foo'], [new SwitchValue('num', 10), null]);
        $this->assertCoerceFails($field, [new SwitchValue('num', 10), new SwitchValue('scores', 'foo')], [new SwitchValue('num', 10), new SwitchValue('scores', null)]);
        $this->assertCoerceFails($field, [new SwitchValue('num', 10), new SwitchValue('scores', [10, 'foo'])], [new SwitchValue('num', 10), new SwitchValue('scores', [10, null])]);
    }
}
