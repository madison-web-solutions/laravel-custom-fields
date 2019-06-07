<?php

namespace MadisonSolutions\LCFTest\Test;

use DateTime;
use Carbon\Carbon;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class DateFieldTest extends TestCase
{
    public function testCanConvertValidValuesToDate()
    {
        $field = LCF::newDateField([]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, '', null);
        $this->assertCoerceOk($field, '1970-01-01', '1970-01-01');
        $this->assertCoerceOk($field, '2019-06-07', '2019-06-07');
        $this->assertCoerceOk($field, new JustDate(2019, 6, 7), '2019-06-07');
        $this->assertCoerceOk($field, gmmktime(0, 0, 0, 6, 7, 2019), '2019-06-07');
        $this->assertCoerceOk($field, gmmktime(11, 35, 17, 6, 7, 2019), '2019-06-07');
        $this->assertCoerceOk($field, new DateTime('2019-06-07 00:00:00'), '2019-06-07');
        $this->assertCoerceOk($field, new DateTime('2019-06-07 11:35:17'), '2019-06-07');
        $this->assertCoerceOk($field, Carbon::parse('2019-06-07 00:00:00'), '2019-06-07');
        $this->assertCoerceOk($field, Carbon::parse('2019-06-07 11:35:17'), '2019-06-07');
    }

    public function testCannotConvertInvalidValuesToDate()
    {
        $field = LCF::newDateField([]);
        $this->assertCoerceFails($field, 'foo');
        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, '20/10/1981');
        $this->assertCoerceFails($field, '81-20-10');
        $this->assertCoerceFails($field, '2019-06-32');
        $this->assertCoerceFails($field, new \stdClass());
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newDateField([]);

        $this->assertValidationPasses($field, new JustDate(2019, 6, 7));
        $this->assertValidationPasses($field, null);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, false);
        $this->assertValidationFails($field, '2019-06-07');
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newDateField(['required' => true]);

        $this->assertValidationPasses($field, new JustDate(2019, 6, 7));
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newDateField([
            'min' => '2019-06-07',
        ]);
        $this->assertValidationFails($field, new JustDate(2019, 6, 6));
        $this->assertValidationPasses($field, new JustDate(2019, 6, 7));
        $this->assertValidationPasses($field, new JustDate(2019, 6, 8));

        $field = LCF::newDateField([
            'max' => '2019-06-07',
        ]);
        $this->assertValidationPasses($field, new JustDate(2019, 6, 6));
        $this->assertValidationPasses($field, new JustDate(2019, 6, 7));
        $this->assertValidationFails($field, new JustDate(2019, 6, 8));
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newDateField([
            'min' => '2019-06-07',
        ]);
        $this->assertValidationPasses($field, null);

        $field = LCF::newDateField([
            'required' => true,
            'min' => '2019-06-07',
        ]);
        $this->assertValidationFails($field, null);
    }
}
