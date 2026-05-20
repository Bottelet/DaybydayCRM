<?php

namespace Tests\Unit\ViewComposers;

use App\Http\ViewComposers\InvoiceHeaderComposer;
use App\Http\ViewComposers\LeadHeaderComposer;
use App\Http\ViewComposers\TaskHeaderComposer;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Setting;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Tests\Support\FakeView;

/**
 * Verifies that all three header view composers degrade gracefully when
 * related models (client, contact, assignee) are null instead of crashing.
 *
 * Uses FakeView (a state-based fake) instead of Mockery mocks so that
 * assertions verify actual outcomes (shared data) rather than call counts.
 */
#[Group('view-composers')]
class ViewComposerNullSafetyTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
        Setting::factory()->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── TaskHeaderComposer ──────────────────────────────────────────────────

    #[Test]
    public function it_handles_task_without_client()
    {
        /* Arrange */
        $task = Task::factory()->make([
            'client_id'        => null,
            'user_assigned_id' => $this->user->id,
        ]);
        $view = new FakeView(['tasks' => $task]);

        /* Act – must not throw */
        (new TaskHeaderComposer())->compose($view);

        /* Assert exact shared values */
        $this->assertNull($view->getShared('client'));
        $this->assertNull($view->getShared('contact_info'));
        $view->assertShared('contact'); // key must be present even when null
    }

    #[Test]
    public function it_handles_task_without_assigned_user()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $task   = Task::factory()->make([
            'client_id'        => $client->id,
            'user_assigned_id' => null,
        ]);
        $view = new FakeView(['tasks' => $task]);

        /* Act */
        (new TaskHeaderComposer())->compose($view);

        /* Assert */
        $this->assertNull($view->getShared('contact'));
        $this->assertSame($client->id, $view->getShared('client')->id);
        $view->assertShared('contact_info');
    }

    #[Test]
    public function it_handles_missing_task_in_view_data()
    {
        /* Arrange – no 'tasks' key at all */
        $view = new FakeView([]);

        /* Act – must not throw */
        (new TaskHeaderComposer())->compose($view);

        /* Assert all three keys are pushed, all null */
        $this->assertNull($view->getShared('contact'));
        $this->assertNull($view->getShared('client'));
        $this->assertNull($view->getShared('contact_info'));
    }

    #[Test]
    public function it_populates_contact_client_and_contact_info_when_task_is_complete()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $task   = Task::factory()->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $this->user->id,
        ]);
        $view = new FakeView(['tasks' => $task]);

        /* Act */
        (new TaskHeaderComposer())->compose($view);

        /* Assert that assigned user IS shared as contact */
        $shared = $view->getShared();
        $this->assertArrayHasKey('contact', $shared);
        $this->assertArrayHasKey('client', $shared);
        $this->assertArrayHasKey('contact_info', $shared);
        $this->assertSame($this->user->id, $shared['contact']->id);
        $this->assertSame($client->id, $shared['client']->id);
    }

    // ─── LeadHeaderComposer ──────────────────────────────────────────────────

    #[Test]
    public function it_handles_lead_without_client()
    {
        /* Arrange */
        $lead = Lead::factory()->make([
            'client_id'        => null,
            'user_assigned_id' => $this->user->id,
        ]);
        $view = new FakeView(['lead' => $lead]);

        /* Act */
        (new LeadHeaderComposer())->compose($view);

        /* Assert */
        $this->assertNull($view->getShared('client'));
        $this->assertNull($view->getShared('contact_info'));
        $view->assertShared('contact');
    }

    #[Test]
    public function it_handles_missing_lead_in_view_data()
    {
        /* Arrange */
        $view = new FakeView([]);

        /* Act */
        (new LeadHeaderComposer())->compose($view);

        /* Assert all three keys present, all null */
        $this->assertNull($view->getShared('contact'));
        $this->assertNull($view->getShared('client'));
        $this->assertNull($view->getShared('contact_info'));
    }

    #[Test]
    public function it_populates_contact_when_lead_has_assigned_user()
    {
        /* Arrange */
        $client = Client::factory()->create();
        $lead   = Lead::factory()->create([
            'client_id'        => $client->id,
            'user_assigned_id' => $this->user->id,
        ]);
        $view = new FakeView(['lead' => $lead]);

        /* Act */
        (new LeadHeaderComposer())->compose($view);

        /* Assert */
        $shared = $view->getShared();
        $this->assertSame($this->user->id, $shared['contact']->id);
        $this->assertSame($client->id, $shared['client']->id);
    }

    // ─── InvoiceHeaderComposer ───────────────────────────────────────────────

    #[Test]
    public function it_handles_invoice_without_client()
    {
        /* Arrange */
        $invoice = Invoice::factory()->make(['client_id' => null]);
        $view    = new FakeView(['invoice' => $invoice]);

        /* Act */
        (new InvoiceHeaderComposer())->compose($view);

        /* Assert */
        $this->assertNull($view->getShared('client'));
        $this->assertNull($view->getShared('contact_info'));
        $view->assertShared('client');
        $view->assertShared('contact_info');
    }

    #[Test]
    public function it_handles_missing_invoice_in_view_data()
    {
        /* Arrange */
        $view = new FakeView([]);

        /* Act */
        (new InvoiceHeaderComposer())->compose($view);

        /* Assert both keys present and null */
        $this->assertNull($view->getShared('client'));
        $this->assertNull($view->getShared('contact_info'));
    }

    #[Test]
    public function it_populates_client_when_invoice_has_a_client()
    {
        /* Arrange */
        $client  = Client::factory()->create();
        $invoice = Invoice::factory()->create(['client_id' => $client->id]);
        $view    = new FakeView(['invoice' => $invoice]);

        /* Act */
        (new InvoiceHeaderComposer())->compose($view);

        /* Assert */
        $this->assertSame($client->id, $view->getShared('client')->id);
    }
}
