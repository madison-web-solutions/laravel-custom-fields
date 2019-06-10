<?php

namespace MadisonSolutions\LCFTest\Test;

use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class TextFieldsTest extends TestCase
{
    public function testCanConvertValidValuesToText()
    {
        $fields = [LCF::newTextField([]), LCF::newTextAreaField([])];

        foreach ($fields as $field) {
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
    }

    public function testCannotConvertInvalidValuesToText()
    {
        $fields = [LCF::newTextField([]), LCF::newTextAreaField([])];

        foreach ($fields as $field) {
            $this->assertCoerceFails($field, []);
            $this->assertCoerceFails($field, new \stdClass());
        }
    }

    public function testBasicValidationWorks()
    {
        $fields = [LCF::newTextField([]), LCF::newTextAreaField([])];

        foreach ($fields as $field) {
            $this->assertValidationPasses($field, 'cheese');
            $this->assertValidationPasses($field, null);
            $this->assertValidationPasses($field, '');
            $this->assertValidationPassesWhenValueOmitted($field);

            $this->assertValidationFails($field, 10);
            $this->assertValidationFails($field, ['cheese']);
        }
    }

    public function testRequiredAttributeWorks()
    {
        $fields = [LCF::newTextField(['required' => true]), LCF::newTextAreaField(['required' => true])];

        foreach ($fields as $field) {
            $this->assertValidationPasses($field, 'cheese');
            $this->assertValidationFails($field, null);
            $this->assertValidationFails($field, '');
            $this->assertValidationFailsWhenValueOmitted($field);
        }
    }

    public function testOTherValidationRulesWork()
    {
        $field = LCF::newTextField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, '12345');
        $this->assertValidationFails($field, '1234');

        $field = LCF::newTextAreaField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, '12345');
        $this->assertValidationFails($field, '1234');

        $field = LCF::newTextField([
            'max' => 5,
        ]);
        $this->assertValidationPasses($field, '12345');
        $this->assertValidationFails($field, '123456');

        $field = LCF::newTextAreaField([
            'max' => 5,
        ]);
        $this->assertValidationPasses($field, '12345');
        $this->assertValidationFails($field, '123456');

        $field = LCF::newTextField([
            'regex' => '/B\d\d\d(foo|bar)/',
        ]);
        $this->assertValidationPasses($field, 'B123foo');
        $this->assertValidationFails($field, 'B123nope');
        $field = LCF::newTextField([
            'regex' => '/B\d\d\d(foo|bar)/i',
        ]);
        $this->assertValidationPasses($field, 'b123foo');
        $this->assertValidationFails($field, 'b123nope');

        $field = LCF::newTextField([
            'content' => 'url',
        ]);
        $this->assertValidationPasses($field, 'http://www.example.com');
        $this->assertValidationFails($field, 'not-a-url');

        $field = LCF::newTextField([
            'content' => 'uuid',
        ]);
        $this->assertValidationPasses($field, '6a5d66bf-8a8d-4209-8a19-b0043bf37ae0');
        $this->assertValidationFails($field, 'not-a-uuid');

        $field = LCF::newTextField([
            'content' => 'email',
        ]);
        $this->assertValidationPasses($field, 'foo@hotmail.com');
        $this->assertValidationFails($field, 'not-an-email');

        $field = LCF::newTextField([
            'content' => 'ip',
        ]);
        $this->assertValidationPasses($field, '192.168.0.100');
        $this->assertValidationPasses($field, '2001:db8::ff00:42:8329');
        $this->assertValidationFails($field, 'not-an-ip');

        $field = LCF::newTextField([
            'content' => 'ipv4',
        ]);
        $this->assertValidationPasses($field, '192.168.0.100');
        $this->assertValidationFails($field, '2001:db8::ff00:42:8329');
        $this->assertValidationFails($field, 'not-an-ip');

        $field = LCF::newTextField([
            'content' => 'ipv6',
        ]);
        $this->assertValidationFails($field, '192.168.0.100');
        $this->assertValidationPasses($field, '2001:db8::ff00:42:8329');
        $this->assertValidationFails($field, 'not-an-ip');
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $field = LCF::newTextField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, '');
        $this->assertValidationPasses($field, null);

        $field = LCF::newTextField([
            'required' => true,
            'min' => 5,
        ]);
        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, null);

        $field = LCF::newTextAreaField([
            'min' => 5,
        ]);
        $this->assertValidationPasses($field, '');
        $this->assertValidationPasses($field, null);

        $field = LCF::newTextAreaField([
            'required' => true,
            'min' => 5,
        ]);
        $this->assertValidationFails($field, '');
        $this->assertValidationFails($field, null);
    }

    public function testInputTransformations()
    {
        $field = LCF::newTextField([
            'case' => 'lower',
        ]);
        $this->assertCoerceOk($field, 'FöoBÁR deRP', 'föobár derp');

        $field = LCF::newTextField([
            'case' => 'upper',
        ]);
        $this->assertCoerceOk($field, 'FöoBÁR deRP', 'FÖOBÁR DERP');

        $field = LCF::newTextField([
            'case' => 'title',
        ]);
        $this->assertCoerceOk($field, 'FöoBÁR deRP', 'Föobár Derp');

        $field = LCF::newTextField([
            'case' => 'slug',
        ]);
        $this->assertCoerceOk($field, 'FöoBÁR deRP', 'foobar-derp');
    }
}
