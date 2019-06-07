<?php

namespace MadisonSolutions\LCFTest\Test;

use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class TimestampFieldTest extends TestCase
{
    public function testCanConvertValidValuesToTimestamp()
    {
        $field = LCF::newTimestampField([]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, '', null);
        $this->assertCoerceOk($field, '1970-01-01T00:00:00.000000Z', '1970-01-01T00:00:00.000000Z');
        $this->assertCoerceOk($field, '2019-06-07T14:32:56.000000Z', '2019-06-07T14:32:56.000000Z');
        $this->assertCoerceOk($field, gmmktime(14, 32, 56, 6, 7, 2019), '2019-06-07T14:32:56.000000Z');
        $this->assertCoerceOk($field, new DateTime('2019-06-07 14:32:56', new DateTimeZone('UTC')), '2019-06-07T14:32:56.000000Z');
        $this->assertCoerceOk($field, Carbon::parse('2019-06-07 14:32:56', 'UTC'), '2019-06-07T14:32:56.000000Z');
    }

    public function testCannotConvertInvalidValuesToTimestamp()
    {
        $field = LCF::newTimestampField([]);
        $this->assertCoerceFails($field, 'foo');
        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, '2019-06-07 14:32:56');
        $this->assertCoerceFails($field, '2019-06-32T14:32:56.000000Z');
        $this->assertCoerceFails($field, '2019-06-32T14:32:66.000000Z');
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newTimestampField([]);

        $this->assertValidationPasses($field, Carbon::now());
        $this->assertValidationPasses($field, null);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, false);
        $this->assertValidationFails($field, '1970-01-01T00:00:00.000000Z');
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newTimestampField(['required' => true]);

        $this->assertValidationPasses($field, Carbon::now());
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newTimestampField([
            'min' => '2019-06-07 14:32:56',
        ]);
        $this->assertValidationFails($field, Carbon::parse('2019-06-07 14:32:55'));
        $this->assertValidationPasses($field, Carbon::parse('2019-06-07 14:32:56'));
        $this->assertValidationPasses($field, Carbon::parse('2019-06-07 14:32:57'));

        $field = LCF::newTimestampField([
            'max' => '2019-06-07 14:32:56',
        ]);
        $this->assertValidationPasses($field, Carbon::parse('2019-06-07 14:32:55'));
        $this->assertValidationPasses($field, Carbon::parse('2019-06-07 14:32:56'));
        $this->assertValidationFails($field, Carbon::parse('2019-06-07 14:32:57'));
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newTimestampField([
            'min' => '2019-06-07 14:32:56',
        ]);
        $this->assertValidationPasses($field, null);

        $field = LCF::newTimestampField([
            'required' => true,
            'min' => '2019-06-07 14:32:56',
        ]);
        $this->assertValidationFails($field, null);
    }
}
