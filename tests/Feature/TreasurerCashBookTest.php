<?php

namespace Tests\Feature;

use App\Models\CashBookEntry;
use App\Models\User;
use App\Services\CashBookReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreasurerCashBookTest extends TestCase
{
    use RefreshDatabase;

    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->treasurer = User::create([
            'fullname' => 'Florane D. Maru',
            'email' => 'treasurer@example.com',
            'password' => 'password',
            'role' => 'treasurer',
            'status' => 'active',
        ]);
        $this->actingAs($this->treasurer);
    }

    public function test_treasurer_can_record_an_expense(): void
    {
        $response = $this->post(route('treasurer.cashbook.store'), [
            'entry_date' => '2026-07-17',
            'type' => 'expense',
            'particulars' => '26pcs. 2x2 ft. signages tarpaulin',
            'reference_no' => '2275',
            'category' => 'Office Supplies/Equipments',
            'amount' => '3120',
        ]);

        $response->assertRedirect(route('treasurer.cashbook', ['month' => '2026-07']));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cash_book_entries', [
            'type' => 'expense',
            'particulars' => '26pcs. 2x2 ft. signages tarpaulin',
            'reference_no' => '2275',
            'category' => 'Office Supplies/Equipments',
            'recorded_by' => $this->treasurer->id,
        ]);
    }

    public function test_expenses_need_a_category_and_collections_drop_it(): void
    {
        $this->post(route('treasurer.cashbook.store'), [
            'entry_date' => '2026-07-17',
            'type' => 'expense',
            'particulars' => 'Rope',
            'amount' => '180',
        ])->assertSessionHasErrors('category');

        $this->post(route('treasurer.cashbook.store'), [
            'entry_date' => '2026-06-01',
            'type' => 'collection',
            'particulars' => 'SSC Collection Membership & Monthly Dues',
            'category' => 'Subsidy',
            'amount' => '5600',
        ])->assertSessionHasNoErrors();

        $this->assertNull(CashBookEntry::sole()->category);
    }

    public function test_monthly_balances_carry_forward(): void
    {
        $this->seedJuneAndJuly();

        $june = CashBookReport::forMonth('2026-06');
        $this->assertSame(275105.35, $june->beginningBalance);
        $this->assertSame(8200.0, $june->totalCollections);
        $this->assertSame(7270.0, $june->totalExpenses);
        $this->assertSame(276035.35, $june->endingBalance());
        $this->assertSame([
            'Office Supplies/Equipments' => 6670.0,
            'Office Meals/Snacks' => 500.0,
            'Traveling and Transportation' => 100.0,
        ], $june->categoryTotals);
        $this->assertSame(['SSC Collection Membership & Monthly Dues' => 8200.0], $june->collectionTotals);

        $july = CashBookReport::forMonth('2026-07');
        $this->assertSame(276035.35, $july->beginningBalance);
        $this->assertSame(3300.0, $july->totalExpenses);
        $this->assertSame(272735.35, $july->endingBalance());
        $this->assertSame('June', $july->previousMonthName());
    }

    public function test_a_beginning_balance_dated_inside_the_month_counts_as_that_months_opening_cash(): void
    {
        CashBookEntry::create(['entry_date' => '2026-07-01', 'type' => 'opening', 'particulars' => 'Cash on hand', 'amount' => 277069.35]);
        CashBookEntry::create(['entry_date' => '2026-07-22', 'type' => 'expense', 'particulars' => 'Office supplies', 'category' => 'Office Supplies/Equipments', 'amount' => 7655]);

        $july = CashBookReport::forMonth('2026-07');
        $this->assertSame(277069.35, $july->beginningBalance);
        $this->assertSame(269414.35, $july->endingBalance());
        $this->assertCount(1, $july->entries);

        $this->assertSame(0.0, CashBookReport::forMonth('2026-06')->beginningBalance);
    }

    public function test_cash_book_page_lists_the_months_entries(): void
    {
        $this->seedJuneAndJuly();

        $this->withViewErrors([])->get(route('treasurer.cashbook', ['month' => '2026-07']))
            ->assertOk()
            ->assertSee('Entries for July 2026')
            ->assertSee('26pcs. 2x2 ft. signages tarpaulin')
            ->assertSee('₱276,035.35')
            ->assertSee('₱272,735.35')
            ->assertDontSee('Fujidenzo Water Dispenser');
    }

    public function test_records_of_expenses_print(): void
    {
        $this->seedJuneAndJuly();

        $this->get(route('treasurer.cashbook.records', '2026-06'))
            ->assertOk()
            ->assertSee('Records of Expenses for the Month of June 2026')
            ->assertSee('Total Cash Balance from May')
            ->assertSee('Fujidenzo Water Dispenser Black')
            ->assertSee('SC547-000005450')
            ->assertSee('Office Meals/Snacks')
            ->assertDontSee('Subsidy')
            ->assertSee('283,305.35') // Cash DR / Fees CR total: 275,105.35 + 8,200
            ->assertSee('7,270.00')
            ->assertSee('FLORANE D. MARU')
            ->assertSee('ALTHEA MAE D. SALVAÑA')
            ->assertSee('JIREH JOY A. VILLACARLOS')
            ->assertSee(config('ssc.adviser'));
    }

    public function test_financial_report_print(): void
    {
        $this->seedJuneAndJuly();

        $this->get(route('treasurer.cashbook.financial', '2026-07'))
            ->assertOk()
            ->assertSee('for the Month Ending July 31, 2026')
            ->assertSeeInOrder([
                'Beginning Total Cash Balance', '276,035.35',
                'Expenses', 'Office Supplies/Equipments', '3,120.00', 'Event Supplies', '180.00',
                'Less: Total Expenses', '3,300.00',
                'Ending Total Cash Balance', '272,735.35',
            ])
            ->assertDontSee('Total Collections');
    }

    public function test_only_the_treasurer_can_use_the_cash_book(): void
    {
        $officer = User::create([
            'fullname' => 'Officer',
            'email' => 'officer@example.com',
            'password' => 'password',
            'role' => 'officer',
            'status' => 'active',
        ]);

        $this->actingAs($officer)->get(route('treasurer.cashbook'))->assertForbidden();
        $this->actingAs($officer)->post(route('treasurer.cashbook.store'), [])->assertForbidden();
    }

    public function test_entries_can_be_edited_and_deleted(): void
    {
        $entry = CashBookEntry::create(['entry_date' => '2026-07-22', 'type' => 'expense', 'particulars' => 'Rope', 'category' => 'Event Supplies', 'amount' => 180]);

        $this->put(route('treasurer.cashbook.update', $entry), [
            'entry_date' => '2026-07-22',
            'type' => 'expense',
            'particulars' => '1 roll Rope #5',
            'reference_no' => '214',
            'category' => 'Event Supplies',
            'amount' => '185',
        ])->assertSessionHasNoErrors();

        $entry->refresh();
        $this->assertSame('1 roll Rope #5', $entry->particulars);
        $this->assertEquals(185, (float) $entry->amount);

        $this->delete(route('treasurer.cashbook.destroy', $entry))
            ->assertRedirect(route('treasurer.cashbook', ['month' => '2026-07']));
        $this->assertDatabaseCount('cash_book_entries', 0);
    }

    private function seedJuneAndJuly(): void
    {
        $rows = [
            ['2026-05-31', 'opening', 'Cash on hand', null, null, 275105.35],
            ['2026-06-01', 'collection', 'SSC Collection Membership & Monthly Dues', null, null, 5600],
            ['2026-06-01', 'expense', 'SSC Officers Food- Lunch', '134-143', 'Office Meals/Snacks', 500],
            ['2026-06-01', 'expense', 'Gasoline', '36910', 'Traveling and Transportation', 100],
            ['2026-06-01', 'expense', 'Fujidenzo Water Dispenser Black', 'SC547-000005450', 'Office Supplies/Equipments', 6670],
            ['2026-06-02', 'collection', 'SSC Collection Membership & Monthly Dues', null, null, 2600],
            ['2026-07-17', 'expense', '26pcs. 2x2 ft. signages tarpaulin', '2275', 'Office Supplies/Equipments', 3120],
            ['2026-07-22', 'expense', '1 roll Rope #5', '214', 'Event Supplies', 180],
        ];

        foreach ($rows as [$date, $type, $particulars, $reference, $category, $amount]) {
            CashBookEntry::create([
                'entry_date' => $date,
                'type' => $type,
                'particulars' => $particulars,
                'reference_no' => $reference,
                'category' => $category,
                'amount' => $amount,
            ]);
        }
    }
}
