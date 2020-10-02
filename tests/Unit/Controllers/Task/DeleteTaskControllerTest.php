<?php
namespace Tests\Unit\Controllers\Task;

use Tests\TestCase;
use App\Models\Task;
use App\Models\Client;
use App\Models\Invoice;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeleteTaskControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $task;
    private $invoice;

    public function setUp(): void
    {
        parent::setUp();

        $this->task = factory(Task::class)->create();
        $this->invoice = factory(Invoice::class)->create([
            'status' => 'Test',
            'client_id' => factory(Client::class)->create()->id,
            'integration_invoice_id' => $this->task->id,
            'integration_type' => Task::class,
        ]);

        $this->task->invoice_id = $this->invoice->id;
        $this->task->save();
        
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @test */
    public function deleteTask()
    {
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id));
        
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
    }

    /** @test */
    public function deleteInvoiceIfFlagGiven()
    {   
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id), [
            'delete_invoice' => "on"
        ]);
        
        $this->assertSoftDeleted('tasks', ['id' => $this->task->id]);
        $this->assertSoftDeleted('invoices', ['id' => $this->invoice->id]);
    }

    /** @test */
    public function doNotDeleteInvoiceIfFlagIsNotGivenButRemoveReference()
    {   
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id));
        
        $this->assertNull($this->task->invoice->refresh()->deleted_at);
        $this->assertNull($this->task->invoice->refresh()->integration_invoice_id);
        $this->assertNull($this->task->invoice->refresh()->integration_type);
    }


    /** @test */
    public function canDeleteTaskIfFlagIsGivenAndInvoiceDoesNotExists()
    {   
        $this->task->invoice_id = null;
        $this->task->save();
        
        $this->json('DELETE', route('tasks.destroy', $this->task->external_id), [
            'delete_invoice' => "on"
        ]);
        
        $this->assertNotNull($this->task->refresh()->deleted_at);
    }
}
