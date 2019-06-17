<?php

namespace MadisonSolutions\LCFTest\Test;

use DateTime;
use Carbon\Carbon;
use MadisonSolutions\JustDate\JustTime;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class TimeFieldTest extends TestCase
{
    public function testCanConvertValidValuesToTime()
    {
        $field = LCF::newTimeField([]);

        $this->assertCoerceOk($field, null, null);
        $this->assertCoerceOk($field, '', null);
        $this->assertCoerceOk($field, '00:00:00', '00:00:00');
        $this->assertCoerceOk($field, '14:32', '14:32:00');
        $this->assertCoerceOk($field, '14:32:56', '14:32:56');
        $this->assertCoerceOk($field, new JustTime(14, 32, 56), '14:32:56');
        $this->assertCoerceOk($field, new DateTime('2019-06-07 00:00:00'), '00:00:00');
        $this->assertCoerceOk($field, new DateTime('2019-06-07 14:32:56'), '14:32:56');
        $this->assertCoerceOk($field, Carbon::parse('2019-06-07 00:00:00'), '00:00:00');
        $this->assertCoerceOk($field, Carbon::parse('2019-06-07 14:32:56'), '14:32:56');
    }

    public function testCannotConvertInvalidValuesToTime()
    {
        $field = LCF::newTimeField([]);
        $this->assertCoerceFails($field, 'foo');
        $this->assertCoerceFails($field, false);
        $this->assertCoerceFails($field, 1432);
        $this->assertCoerceFails($field, '1432');
        $this->assertCoerceFails($field, '14:62');
        $this->assertCoerceFails($field, '14:62:56');
        $this->assertCoerceFails($field, '25:00');
        $this->assertCoerceFails($field, '14:32:56 14:32:56');
        $this->assertCoerceFails($field, new \stdClass());
    }

    public function testBasicValidationWorks()
    {
        $field = LCF::newTimeField([]);

        $this->assertValidationPasses($field, new JustTime(14, 32, 56));
        $this->assertValidationPasses($field, null);
        $this->assertValidationPassesWhenValueOmitted($field);

        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, false);
        $this->assertValidationFails($field, '14:32:56');
    }

    public function testRequiredAttributeWorks()
    {
        $field = LCF::newTimeField(['required' => true]);

        $this->assertValidationPasses($field, new JustTime(14, 32, 56));
        $this->assertValidationFails($field, null);
        $this->assertValidationFails($field, '');
        $this->assertValidationFailsWhenValueOmitted($field);
    }

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newTimeField([
            'min' => '14:32:56',
        ]);
        $this->assertValidationFails($field, new JustTime(14, 32, 55));
        $this->assertValidationPasses($field, new JustTime(14, 32, 56));
        $this->assertValidationPasses($field, new JustTime(14, 32, 57));

        $field = LCF::newTimeField([
            'max' => '14:32:56',
        ]);
        $this->assertValidationPasses($field, new JustTime(14, 32, 56));
        $this->assertValidationPasses($field, new JustTime(14, 32, 56));
        $this->assertValidationFails($field, new JustTime(14, 32, 57));
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newTimeField([
            'min' => '14:32:56',
        ]);
        $this->assertValidationPasses($field, null);

        $field = LCF::newTimeField([
            'required' => true,
            'min' => '14:32:56',
        ]);
        $this->assertValidationFails($field, null);
    }

    public function testInputTransformations()
    {
        $field = LCF::newTimeField([
            'step' => '00:00:10',
        ]);
        $this->assertCoerceOk($field, '14:47:56', '14:48:00');

        $field = LCF::newTimeField([
            'step' => '00:15',
        ]);
        $this->assertCoerceOk($field, '14:47:56', '14:45:00');

        $field = LCF::newTimeField([
            'step' => '01:00',
        ]);
        $this->assertCoerceOk($field, '14:47:56', '15:00:00');
    }
}
