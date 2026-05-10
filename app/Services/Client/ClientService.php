<?php

namespace App\Services\Client;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    /**
     * Get clients with optimized eager loading for datatables
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getClientsForDataTable()
    {
        // For the main client list, we only need specific columns
        // No relationships needed as the datatable only shows basic client info
        return Client::select(['external_id', 'company_name', 'vat', 'address']);
    }

    /**
     * Get client with all necessary relationships for detail view
     *
     * @param string $external_id
     * @return Client
     */
    public function getClientWithRelations(string $external_id): Client
    {
        return Client::with([
            'user',              // Assigned user
            'primaryContact',    // Primary contact
            'industry',          // Industry relationship
            'documents',         // Documents
            'appointments'       // Appointments
        ])
        ->where('external_id', $external_id)
        ->firstOrFail();
    }

    /**
     * Get tasks for client with optimized eager loading
     *
     * @param Client $client
     * @return Collection
     */
    public function getTasksWithRelations(Client $client): Collection
    {
        return $client->tasks()
            ->with([
                'status',        // Task status for color/title
                'user'           // User assigned to task (fixes N+1 on assigned_user->name)
            ])
            ->select([
                'id', 
                'external_id', 
                'title', 
                'created_at', 
                'deadline', 
                'user_assigned_id', 
                'client_id', 
                'status_id'
            ])
            ->get();
    }

    /**
     * Get projects for client with optimized eager loading
     *
     * @param Client $client
     * @return Collection
     */
    public function getProjectsWithRelations(Client $client): Collection
    {
        return $client->projects()
            ->with([
                'status',        // Project status for color/title
                'assignee'       // User assigned to project (fixes N+1 on assignee->name)
            ])
            ->select([
                'id', 
                'external_id', 
                'title', 
                'created_at', 
                'deadline', 
                'user_assigned_id', 
                'client_id', 
                'status_id'
            ])
            ->get();
    }

    /**
     * Get leads for client with optimized eager loading
     *
     * @param Client $client
     * @return Collection
     */
    public function getLeadsWithRelations(Client $client): Collection
    {
        return $client->leads()
            ->with([
                'status',        // Lead status for color/title
                'user'           // User assigned to lead (fixes N+1 on assigned_user->name)
            ])
            ->select([
                'id', 
                'external_id', 
                'title', 
                'created_at', 
                'deadline', 
                'user_assigned_id', 
                'client_id', 
                'status_id'
            ])
            ->get();
    }

    /**
     * Get invoices query for client with optimized eager loading
     * Returns a Builder for DataTables server-side processing
     *
     * @param Client $client
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getInvoicesWithRelations(Client $client)
    {
        return $client->invoices()
            ->with(['invoiceLines'])  // Eager load invoice lines for calculations
            ->select([
                'id', 
                'external_id', 
                'sent_at', 
                'status', 
                'invoice_number',
                'client_id'
            ]);
    }

    /**
     * Get all invoices for a client (for show page)
     *
     * @param Client $client
     * @return Collection
     */
    public function getInvoices(Client $client): Collection
    {
        return $client->invoices()->with('invoiceLines')->get();
    }

    /**
     * Get client by external ID
     *
     * @param string $external_id
     * @return Client
     */
    public function findByExternalId(string $external_id): Client
    {
        return Client::where('external_id', $external_id)->firstOrFail();
    }
}
