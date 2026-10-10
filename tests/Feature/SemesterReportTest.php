<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\BudgetRelease;
use App\Models\CashBookEntry;
use App\Models\EnrollmentPayment;
use App\Models\Expense;
use App\Models\Liquidation;
use App\Models\Proposal;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\SemesterReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemesterReportTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->officer = $this->user('officer', 'Officer Cruz', 'officer@example.com');
    }

    public function test_term_dates_fall_back_to_the_academic_calendar(): void
    {
        $first = SchoolYear::create(['label' => '2026-2027', 'semester' => SchoolYear::SEMESTER_FIRST]);
        $second = new SchoolYear(['label' => '2026-2027', 'semester' => SchoolYear::SEMESTER_SECOND]);

        $this->assertSame('2026-08-01', $first->termStart()->toDateString());
        $this->assertSame('2026-12-31', $first->termEnd()->toDateString());
        $this->assertSame('2027-01-01', $second->termStart()->toDateString());
        $this->assertSame('2027-07-31', $second->termEnd()->toDateString());
        $this->assertSame('August 1, 2026 - December 31, 2026', $first->termRangeLabel());
    }

    public function test_dates_set_by_an_admin_win_over_the_calendar(): void
    {
        $term = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'starts_on' => '2026-07-15',
            'ends_on' => '2027-01-20',
        ]);

        $this->assertSame('2026-07-15', $term->termStart()->toDateString());
        $this->assertSame('2027-01-20', $term->termEnd()->toDateString());
        $this->assertTrue($term->hasTermWindow());
    }

    public function test_a_malformed_label_leaves_the_term_without_a_window(): void
    {
        $term = new SchoolYear(['label' => 'not-a-year', 'semester' => SchoolYear::SEMESTER_FIRST]);

        $this->assertNull($term->termStart());
        $this->assertFalse($term->hasTermWindow());
        $this->assertSame('Dates not set', $term->termRangeLabel());
    }

    public function test_the_report_only_counts_the_terms_own_records(): void
    {
        [$first, $second] = $this->seedTwoTerms();

        $report = SemesterReport::forTerm($first);

        // Funds and contributions are matched on the academic term string.
        $this->assertSame(30000.0, $report->totalAllocated);
        $this->assertSame(4500.0, $report->totalSpent);
        $this->assertSame(25500.0, $report->totalRemaining);
        $this->assertSame(2500.0, $report->contributionsCollected);
        $this->assertSame(2, $report->contributionPayers);

        // Projects, releases, liquidations and cash entries are matched on dates.
        $this->assertSame(1, $report->projectsProposed);
        $this->assertSame('Leadership Seminar', $report->projects->first()->project_title);
        $this->assertSame(1, $report->projectsApproved);
        $this->assertSame(12000.0, $report->projectsApprovedBudget);
        $this->assertSame(8000.0, $report->totalReleased);
        $this->assertSame(1, $report->releaseCount);
        $this->assertSame(1, $report->liquidationsFiled);
        $this->assertSame(3000.0, $report->cashCollections);
        $this->assertSame(1200.0, $report->cashExpenses);
        $this->assertSame(['Office Supplies/Equipments' => 1200.0], $report->cashCategoryTotals);

        // The second term sees only its own figures.
        $secondReport = SemesterReport::forTerm($second);
        $this->assertSame(10000.0, $secondReport->totalAllocated);
        $this->assertSame(0.0, $secondReport->totalSpent);
        $this->assertSame(800.0, $secondReport->contributionsCollected);
        $this->assertSame(1, $secondReport->projectsProposed);
        $this->assertSame('Sports Fest', $secondReport->projects->first()->project_title);
        $this->assertSame(0.0, $secondReport->totalReleased);
        $this->assertSame(0.0, $secondReport->cashCollections);
    }

    public function test_pending_funds_and_unpaid_contributions_are_left_out(): void
    {
        $term = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'is_active' => true,
        ]);

        $approved = $this->budget($term, 5000, 'Approved');
        $this->budget($term, 9000, 'Pending');
        $this->expense($approved, 1000, 'Approved', '2026-09-05');
        $this->expense($approved, 400, 'Pending', '2026-09-06');

        $student = $this->user('student', 'Paid Student', 'paid@example.com');
        $unpaid = $this->user('student', 'Unpaid Student', 'unpaid@example.com');
        $this->payment($student, $term, 50, 'paid');
        $this->payment($unpaid, $term, 50, 'pending');

        $report = SemesterReport::forTerm($term);

        $this->assertSame(5000.0, $report->totalAllocated);
        $this->assertSame(1000.0, $report->totalSpent);
        $this->assertSame(50.0, $report->contributionsCollected);
        $this->assertSame(1, $report->contributionPayers);
        $this->assertSame(20, $report->spentPercent());
    }

    public function test_an_undated_term_reports_funds_but_no_dated_records(): void
    {
        $term = SchoolYear::create(['label' => 'bad-label', 'semester' => SchoolYear::SEMESTER_FIRST]);
        $this->budget($term, 1000, 'Approved');
        $this->proposal('Orphan Project', '2026-09-10');

        $report = SemesterReport::forTerm($term);

        $this->assertNull($report->start);
        $this->assertSame(1000.0, $report->totalAllocated);
        $this->assertSame(0, $report->projectsProposed);
    }

    public function test_a_term_with_nothing_recorded_reads_as_empty(): void
    {
        $term = SchoolYear::create(['label' => '2026-2027', 'semester' => SchoolYear::SEMESTER_FIRST]);

        $this->assertTrue(SemesterReport::forTerm($term)->isEmpty());
    }

    public function test_the_active_term_is_reported_on_by_default(): void
    {
        SchoolYear::create(['label' => '2024-2025', 'semester' => SchoolYear::SEMESTER_FIRST]);
        $active = SchoolYear::create(['label' => '2025-2026', 'semester' => SchoolYear::SEMESTER_SECOND, 'is_active' => true]);
        SchoolYear::create(['label' => '2026-2027', 'semester' => SchoolYear::SEMESTER_FIRST]);

        $this->assertSame($active->id, SemesterReport::resolveTerm(null)->id);
        $this->assertSame('2026-2027', SemesterReport::reportableTerms()->first()->label);
    }

    public function test_a_student_can_read_the_semester_report(): void
    {
        [$first] = $this->seedTwoTerms();
        $student = $this->user('student', 'Reader', 'reader@example.com');

        $this->actingAs($student)
            ->get(route('student.semester_reports', ['term' => $first->id]))
            ->assertOk()
            ->assertSee('Semester Reports')
            ->assertSee('2026-2027 - First Semester')
            ->assertSee('Leadership Seminar')
            ->assertSee('₱30,000.00')
            ->assertSee('Tarpaulin printing')
            ->assertDontSee('Sports Fest');
    }

    public function test_the_treasurer_and_admin_read_the_same_figures(): void
    {
        [$first] = $this->seedTwoTerms();

        foreach ([['treasurer', 'treasurer.semester_reports'], ['admin', 'admin.semester_reports']] as [$role, $routeName]) {
            $this->actingAs($this->user($role, ucfirst($role), "{$role}@example.com"))
                ->get(route($routeName, ['term' => $first->id]))
                ->assertOk()
                ->assertSee('₱30,000.00')
                ->assertSee('Leadership Seminar');
        }
    }

    public function test_the_printable_sheet_lists_the_terms_totals(): void
    {
        [$first] = $this->seedTwoTerms();

        $this->actingAs($this->user('treasurer', 'Treasurer', 'treasurer@example.com'))
            ->get(route('treasurer.semester_reports.print', $first))
            ->assertOk()
            ->assertSee('Semester Transparency Report')
            ->assertSee('2026-2027 - First Semester')
            ->assertSeeInOrder([
                'Funds Received', '2,500.00', '3,000.00',
                'Funds Allocated', '30,000.00',
                'Expenditures', 'Office Supplies/Equipments', '1,200.00',
                'Projects and Accountability', '8,000.00',
            ]);
    }

    public function test_the_term_is_split_into_its_months_with_balances_carried_forward(): void
    {
        [$first] = $this->seedTwoTerms();

        $report = SemesterReport::forTerm($first);
        $months = $report->months;

        $this->assertSame(
            ['August 2026', 'September 2026', 'October 2026', 'November 2026', 'December 2026'],
            $months->map(fn ($month) => $month->label())->all()
        );

        // The 30,000 fund is approved on the term's first day, so August opens with it.
        $august = $months->firstWhere('monthStart.month', 8);
        $this->assertSame(30000.0, $august->beginningBalance);
        $this->assertSame(0.0, $august->totalCollections);
        $this->assertSame(30000.0, $august->endingBalance());

        // September collected 3,000 and spent nothing.
        $september = $months[1];
        $this->assertSame(30000.0, $september->beginningBalance);
        $this->assertSame(3000.0, $september->totalCollections);
        $this->assertSame(0.0, $september->totalExpenses);
        $this->assertSame(33000.0, $september->endingBalance());
        $this->assertSame(33000.0, $september->cashDebitTotal());
        $this->assertSame('August', $september->previousMonthName());

        // December's 1,200 expense closes the term out.
        $december = $months->last();
        $this->assertSame(33000.0, $december->beginningBalance);
        $this->assertSame(1200.0, $december->totalExpenses);
        $this->assertSame(['Office Supplies/Equipments' => 1200.0], $december->categoryTotals);
        $this->assertSame(31800.0, $december->endingBalance());
        $this->assertSame(31800.0, $report->closingBalance());

        // Month subtotals add up to the semester figures.
        $this->assertSame($report->cashCollections, round($months->sum('totalCollections'), 2));
        $this->assertSame($report->cashExpenses, round($months->sum('totalExpenses'), 2));
        $this->assertFalse($months->contains(fn ($month) => $month->isPartial()));
    }

    public function test_a_term_starting_mid_month_only_counts_its_own_days(): void
    {
        $term = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'starts_on' => '2026-08-15',
            'ends_on' => '2026-09-30',
        ]);

        CashBookEntry::create(['entry_date' => '2026-08-10', 'type' => 'expense', 'particulars' => 'Before the term', 'category' => 'Event Supplies', 'amount' => 500]);
        CashBookEntry::create(['entry_date' => '2026-08-20', 'type' => 'expense', 'particulars' => 'Inside the term', 'category' => 'Event Supplies', 'amount' => 700]);

        $report = SemesterReport::forTerm($term);
        $august = $report->months->first();

        $this->assertTrue($august->isPartial());
        $this->assertSame('August 15 - August 31, 2026', $august->coverageLabel());
        $this->assertSame(700.0, $august->totalExpenses);
        $this->assertSame(700.0, $report->cashExpenses);
        // Spending before the term lowers the balance it opens with.
        $this->assertSame(-500.0, $report->openingBalance);
    }

    public function test_the_records_of_expenses_sheet_lists_every_month(): void
    {
        [$first] = $this->seedTwoTerms();

        $this->actingAs($this->user('treasurer', 'Treasurer', 'treasurer@example.com'))
            ->get(route('treasurer.semester_reports.records', $first))
            ->assertOk()
            ->assertSee('Records of Expenses for 2026-2027 - First Semester')
            ->assertSeeInOrder([
                'August 2026',
                'September 2026', 'Membership dues', '3,000.00',
                'October 2026',
                'November 2026',
                'December 2026', 'Bond paper', '1,200.00',
                'Semester Total', 'Whole Semester',
            ])
            ->assertSee('Office Supplies/Equipments')
            ->assertSee('Total Cash Balance from July');
    }

    public function test_a_student_can_open_the_records_of_expenses_sheet(): void
    {
        [$first] = $this->seedTwoTerms();

        $this->actingAs($this->user('student', 'Reader', 'reader@example.com'))
            ->get(route('student.semester_reports.records', $first))
            ->assertOk()
            ->assertSee('Bond paper');
    }

    public function test_long_tables_are_paged_eight_rows_at_a_time(): void
    {
        $term = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $this->proposal('Project ' . $i, '2026-09-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT));
        }

        $student = $this->user('student', 'Reader', 'reader@example.com');

        // Newest first, so page one holds projects 10 down to 3.
        $this->actingAs($student)
            ->get(route('student.semester_reports', ['term' => $term->id]))
            ->assertOk()
            ->assertSee('Project 10')
            ->assertSee('Project 3')
            ->assertDontSee('Project 2<')
            ->assertSee('10</strong> records', false);

        $this->actingAs($student)
            ->get(route('student.semester_reports', ['term' => $term->id, 'projects' => 2]))
            ->assertOk()
            ->assertSee('Project 2')
            ->assertSee('Project 1')
            ->assertDontSee('Project 10');
    }

    public function test_officers_cannot_open_the_semester_report(): void
    {
        $this->actingAs($this->officer)->get(route('student.semester_reports'))->assertForbidden();
        $this->actingAs($this->officer)->get(route('treasurer.semester_reports'))->assertForbidden();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get(route('student.semester_reports'))->assertRedirect();
    }

    public function test_an_admin_can_correct_the_report_coverage(): void
    {
        $term = SchoolYear::create(['label' => '2026-2027', 'semester' => SchoolYear::SEMESTER_FIRST]);
        $admin = $this->user('admin', 'Admin', 'admin@example.com');

        $this->actingAs($admin)
            ->patch(route('admin.settings.sy.dates', $term), ['starts_on' => '2026-07-15', 'ends_on' => '2027-01-20'])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasNoErrors();

        $term->refresh();
        $this->assertSame('2026-07-15', $term->starts_on->toDateString());
        $this->assertSame('2027-01-20', $term->ends_on->toDateString());
    }

    public function test_a_term_cannot_end_before_it_starts(): void
    {
        $term = SchoolYear::create(['label' => '2026-2027', 'semester' => SchoolYear::SEMESTER_FIRST]);

        $this->actingAs($this->user('admin', 'Admin', 'admin@example.com'))
            ->patch(route('admin.settings.sy.dates', $term), ['starts_on' => '2026-08-01', 'ends_on' => '2026-07-01'])
            ->assertSessionHasErrors('ends_on');

        $this->assertNull($term->refresh()->starts_on);
    }

    public function test_a_new_term_can_be_added_with_its_coverage_dates(): void
    {
        $this->actingAs($this->user('admin', 'Admin', 'admin@example.com'))
            ->post(route('admin.settings.sy.add'), [
                'sy_label' => '2027-2028',
                'semester' => SchoolYear::SEMESTER_FIRST,
                'starts_on' => '2027-08-15',
                'ends_on' => '2027-12-20',
            ])
            ->assertSessionHasNoErrors();

        $added = SchoolYear::where('label', '2027-2028')->sole();
        $this->assertSame('2027-08-15', $added->starts_on->toDateString());
        $this->assertSame('2027-12-20', $added->ends_on->toDateString());
    }

    public function test_a_term_added_without_dates_still_gets_a_window(): void
    {
        $this->actingAs($this->user('admin', 'Admin', 'admin@example.com'))
            ->post(route('admin.settings.sy.add'), [
                'sy_label' => '2027-2028',
                'semester' => SchoolYear::SEMESTER_SECOND,
            ])
            ->assertSessionHasNoErrors();

        $added = SchoolYear::where('label', '2027-2028')->sole();
        $this->assertSame('2028-01-01', $added->starts_on->toDateString());
        $this->assertSame('2028-07-31', $added->ends_on->toDateString());
    }

    /**
     * Two terms of the same school year, each with its own funds, contributions,
     * project, disbursement and cash book activity.
     *
     * @return array{0: SchoolYear, 1: SchoolYear}
     */
    private function seedTwoTerms(): array
    {
        $first = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_FIRST,
            'is_active' => true,
        ]);
        $second = SchoolYear::create([
            'label' => '2026-2027',
            'semester' => SchoolYear::SEMESTER_SECOND,
        ]);

        // ── First semester: 1 Aug - 31 Dec 2026 ──
        $firstFund = $this->budget($first, 30000, 'Approved');
        $this->expense($firstFund, 4500, 'Approved', '2026-09-12', 'Tarpaulin printing');

        $this->payment($this->user('student', 'Ana Reyes', 'ana@example.com'), $first, 1500, 'paid');
        $this->payment($this->user('student', 'Ben Lim', 'ben@example.com'), $first, 1000, 'paid');

        $seminar = $this->proposal('Leadership Seminar', '2026-09-01', 'Approved', 15000, 12000);
        $this->release($seminar, 8000, '2026-09-20');
        $this->liquidation($seminar, 'Seminar liquidation', '2026-10-05');

        CashBookEntry::create(['entry_date' => '2026-09-02', 'type' => 'collection', 'particulars' => 'Membership dues', 'amount' => 3000]);
        CashBookEntry::create(['entry_date' => '2026-12-31', 'type' => 'expense', 'particulars' => 'Bond paper', 'category' => 'Office Supplies/Equipments', 'amount' => 1200]);

        // ── Second semester: 1 Jan - 31 Jul 2027 ──
        $this->budget($second, 10000, 'Approved');
        $this->payment($this->user('student', 'Cris Dy', 'cris@example.com'), $second, 800, 'paid');
        $this->proposal('Sports Fest', '2027-02-10', 'Pending', 9000);

        return [$first, $second];
    }

    private function user(string $role, string $fullname, string $email): User
    {
        return User::create([
            'fullname' => $fullname,
            'email' => $email,
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }

    private function budget(SchoolYear $term, float $amount, string $status): Budget
    {
        return Budget::forceCreate([
            'title' => 'SSC Fund ' . $term->id . ' ' . $status,
            'department' => Budget::ALL_DEPARTMENTS,
            'allocated_amount' => $amount,
            'remaining_balance' => $amount,
            'school_year' => $term->academic_term,
            'status' => $status,
            'created_at' => $term->termStart()?->toDateTimeString() ?? '2026-08-01 08:00:00',
        ]);
    }

    private function expense(Budget $budget, float $amount, string $status, string $date, string $title = 'Supplies'): Expense
    {
        return Expense::forceCreate([
            'budget_id' => $budget->id,
            'officer_id' => $this->officer->id,
            'expense_title' => $title,
            'amount' => $amount,
            'status' => $status,
            'created_at' => $date . ' 09:00:00',
        ]);
    }

    private function payment(User $student, SchoolYear $term, float $amount, string $status): EnrollmentPayment
    {
        return EnrollmentPayment::create([
            'user_id' => $student->id,
            'amount' => $amount,
            'semester' => $term->academic_term,
            'status' => $status,
        ]);
    }

    private function proposal(
        string $title,
        string $date,
        string $status = 'Pending',
        float $requested = 1000,
        ?float $approved = null
    ): Proposal {
        return Proposal::forceCreate([
            'officer_id' => $this->officer->id,
            'project_title' => $title,
            'requested_budget' => $requested,
            'approved_budget' => $approved,
            'status' => $status,
            'created_at' => $date . ' 10:00:00',
        ]);
    }

    private function release(Proposal $proposal, float $amount, string $date): BudgetRelease
    {
        return BudgetRelease::forceCreate([
            'proposal_id' => $proposal->id,
            'released_by' => $this->officer->id,
            'amount_released' => $amount,
            'release_method' => 'Cash',
            'release_status' => 'Released',
            'created_at' => $date . ' 11:00:00',
            'updated_at' => $date . ' 11:00:00',
        ]);
    }

    private function liquidation(Proposal $proposal, string $title, string $date): Liquidation
    {
        return Liquidation::forceCreate([
            'proposal_id' => $proposal->id,
            'officer_id' => $this->officer->id,
            'title' => $title,
            'status' => 'Pending',
            'created_at' => $date . ' 12:00:00',
        ]);
    }
}
