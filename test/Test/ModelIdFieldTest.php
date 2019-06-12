<?php

namespace MadisonSolutions\LCFTest\Test;

use App\Guest;
use App\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCFTest\TestCase;

class ModelIdFieldTest extends TestCase
{
    use RefreshDatabase;

    protected function guestIdField(array $opts = [])
    {
        $opts = $opts + [
            'model_class' => Guest::class,
            'search_fields' => ['first_name', 'last_name'],
            'label_attribute' => 'full_name',
        ];
        return LCF::newModelIdField($opts);
    }

    protected function productSkuField(array $opts = [])
    {
        $opts = $opts + [
            'model_class' => Product::class,
            'search_fields' => ['sku', 'name'],
            'label_attribute' => 'name',
        ];
        return LCF::newModelIdField($opts);
    }

    public function testCanConvertValidValuesToModelId()
    {
        $guest = factory(Guest::class)->create();
        $prod = factory(Product::class)->create();
        $guest_field = $this->guestIdField();
        $prod_field = $this->productSkuField();

        $this->assertCoerceOk($guest_field, null, null);
        $this->assertCoerceOk($guest_field, 10, 10);
        $this->assertCoerceOk($guest_field, $guest, $guest->id);
        $this->assertCoerceOk($prod_field, null, null);
        $this->assertCoerceOk($prod_field, 'BC01', 'BC01');
        $this->assertCoerceOk($prod_field, 101, '101');
        $this->assertCoerceOk($prod_field, $prod, $prod->sku);
    }

    public function testCannotConvertInvalidValuesToModelId()
    {
        $guest_field = $this->guestIdField();
        $prod_field = $this->productSkuField();

        foreach ([$guest_field, $prod_field] as $field) {
            $this->assertCoerceFails($field, []);
            $this->assertCoerceFails($field, new \stdClass());
            $this->assertCoerceFails($field, false);
            $this->assertCoerceFails($field, 2.5);
        }
        $this->assertCoerceFails($guest_field, 'BC01');
    }

    public function testBasicValidationWorks()
    {
        $guest = factory(Guest::class)->create();
        $prod = factory(Product::class)->create();
        $guest_field = $this->guestIdField();
        $prod_field = $this->productSkuField();

        $this->assertValidationPasses($guest_field, $guest->id);
        $this->assertValidationPasses($guest_field, null);
        $this->assertValidationPassesWhenValueOmitted($guest_field);

        $this->assertValidationFails($guest_field, '');
        $this->assertValidationFails($guest_field, false);
        $this->assertValidationFails($guest_field, 999);

        $this->assertValidationPasses($prod_field, $prod->sku);
        $this->assertValidationPasses($prod_field, null);
        $this->assertValidationPassesWhenValueOmitted($prod_field);

        $this->assertValidationFails($prod_field, '');
        $this->assertValidationFails($prod_field, false);
        $this->assertValidationFails($prod_field, 'AB999');
    }

    public function testRequiredAttributeWorks()
    {
        $guest = factory(Guest::class)->create();
        $guest_field = $this->guestIdField(['required' => true]);

        $this->assertValidationPasses($guest_field, $guest->id);
        $this->assertValidationFails($guest_field, null);
        $this->assertValidationFails($guest_field, '');
        $this->assertValidationFailsWhenValueOmitted($guest_field);
    }

    public function testOTherValidationRulesWork()
    {
        $g1 = factory(Guest::class)->create(['first_name' => 'Daniel']);
        $g2 = factory(Guest::class)->create(['first_name' => 'Robert']);
        $g3 = factory(Guest::class)->create(['first_name' => 'Danielle']);

        $guest_field = $this->guestIdField(['criteria' => [
            ['first_name', 'like', '%dan%'],
        ]]);

        $this->assertValidationPasses($guest_field, $g1->id);
        $this->assertValidationFails($guest_field, $g2->id);
        $this->assertValidationPasses($guest_field, $g3->id);

        $guest_field = $this->guestIdField(['criteria' => [
            ['first_name', 'like', '%dan%'],
            ['first_name', '<>', 'Danielle'],
        ]]);

        $this->assertValidationPasses($guest_field, $g1->id);
        $this->assertValidationFails($guest_field, $g2->id);
        $this->assertValidationFails($guest_field, $g3->id);
    }

    public function testFetchingSuggestions()
    {
        $guest_field = $this->guestIdField();
        $mf = app(LCF::class)->makeModelFinder($guest_field->model_class, $guest_field->criteria, $guest_field->search_fields, $guest_field->label_attribute);

        $g1 = factory(Guest::class)->create(['first_name' => 'Daniel', 'last_name' => 'Howard']);
        $g2 = factory(Guest::class)->create(['first_name' => 'Hank', 'last_name' => 'Grantham']);
        $g3 = factory(Guest::class)->create(['first_name' => 'Pierre', 'last_name' => 'Chang']);

        $sugg = $mf->getSuggestions('howar');
        $this->assertIsArray($sugg);
        $this->assertCount(1, $sugg);
        $this->assertContains(['id' => $g1->id, 'display_name' => 'Daniel Howard'], $sugg);

        $sugg = $mf->getSuggestions('HAN');
        $this->assertIsArray($sugg);
        $this->assertCount(2, $sugg);
        $this->assertContains(['id' => $g2->id, 'display_name' => 'Hank Grantham'], $sugg);
        $this->assertContains(['id' => $g3->id, 'display_name' => 'Pierre Chang'], $sugg);

        $sugg = $mf->getSuggestions('foo');
        $this->assertIsArray($sugg);
        $this->assertCount(0, $sugg);
    }

    public function testOtherValidationRulesInteractionWithRequiredAttriute()
    {
        $guest = factory(Guest::class)->create(['first_name' => 'Daniel']);

        $guest_field = $this->guestIdField([
            'criteria' => [['first_name', '=', 'Daniel']],
        ]);
        $this->assertValidationPasses($guest_field, null);

        $guest_field = $this->guestIdField([
            'required' => true,
            'criteria' => [['first_name', '=', 'Daniel']],
        ]);
        $this->assertValidationFails($guest_field, null);
    }
}
