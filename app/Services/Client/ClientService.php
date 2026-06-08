<?php

namespace App\Services\Client;

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ClientService
{
    /**
     * Create a new client with its primary contact.
     *
     * @param array $data Validated data from StoreClientRequest
     *
     * @return array [$client, $contact] tuple of newly created models
     */
    public function createClientWithContact(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Create the client
            $client = Client::create([
                'external_id'  => Uuid::uuid4()->toString(),
                'company_name' => $data['company_name'],
                'vat'          => $data['vat'] ?? null,
                'address'      => $data['address'] ?? null,
                'zipcode'      => $data['zipcode'] ?? null,
                'city'         => $data['city'] ?? null,
                'company_type' => $data['company_type'] ?? null,
                'industry_id'  => $data['industry_id'],
                'user_id'      => $data['user_id'],
            ]);

            // Create the primary contact for the client
            $contact = Contact::create([
                'external_id'      => Uuid::uuid4()->toString(),
                'client_id'        => $client->id,
                'name'             => $data['name'],
                'email'            => $data['email'],
                'primary_number'   => $data['primary_number'] ?? null,
                'secondary_number' => $data['secondary_number'] ?? null,
                'is_primary'       => true,
            ]);

            return [$client, $contact];
        });
    }

    /**
     * Get clients with optimized eager loading for datatables.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getClientsForDataTable()
    {
        // For the main client list, we only need specific columns
        // No relationships needed as the datatable only shows basic client info
        return Client::query()->select(['external_id', 'company_name', 'vat', 'address']);
    }

    /**
     * Get client with all necessary relationships for detail view.
     *
     * @param string $external_id
     *
     * @return Client
     */
    public function getClientWithRelations(string $external_id): Client
    {
        return Client::with([
            'user',              // Assigned user
            'primaryContact',    // Primary contact
            'industry',          // Industry relationship
            'documents',         // Documents
            'appointments',      // Appointments
        ])
            ->where('external_id', $external_id)
            ->firstOrFail();
    }

    /**
     * Get tasks for client with optimized eager loading.
     *
     * @param Client $client
     *
     * @return Collection
     */
    public function getTasksWithRelations(Client $client)
    {
        return $client->tasks()
            ->leftJoin('statuses', 'tasks.status_id', '=', 'statuses.id')
            ->leftJoin('users', 'tasks.user_assigned_id', '=', 'users.id')
            ->leftJoin('clients', 'tasks.client_id', '=', 'clients.id')
            ->with([
                'status',        // Task status for color/title
                'user',          // User assigned to task (fixes N+1 on assigned_user->name)
            ])
            ->select([
                'tasks.id',
                'tasks.external_id',
                'tasks.title',
                'tasks.created_at',
                'tasks.deadline',
                'tasks.user_assigned_id',
                'tasks.client_id',
                'tasks.status_id',
                'statuses.title as status_title',
                'users.name as user_name',
                'clients.company_name as client_name',
            ]);
    }

    /**
     * Get projects for client with optimized eager loading.
     *
     * @param Client $client
     *
     * @return Collection
     */
    public function getProjectsWithRelations(Client $client)
    {
        return $client->projects()
            ->leftJoin('statuses', 'projects.status_id', '=', 'statuses.id')
            ->leftJoin('users', 'projects.user_assigned_id', '=', 'users.id')
            ->with([
                'status',        // Project status for color/title
                'assignee',      // User assigned to project (fixes N+1 on assignee->name)
            ])
            ->select([
                'projects.id',
                'projects.external_id',
                'projects.title',
                'projects.created_at',
                'projects.deadline',
                'projects.user_assigned_id',
                'projects.client_id',
                'projects.status_id',
                'statuses.title as status_title',
                'users.name as user_name',
            ]);
    }

    /**
     * Get leads for client with optimized eager loading.
     *
     * @param Client $client
     *
     * @return Collection
     */
    public function getLeadsWithRelations(Client $client)
    {
        return $client->leads()
            ->leftJoin('statuses', 'leads.status_id', '=', 'statuses.id')
            ->leftJoin('users', 'leads.user_assigned_id', '=', 'users.id')
            ->with([
                'status',        // Lead status for color/title
                'user',          // User assigned to lead (fixes N+1 on assigned_user->name)
            ])
            ->select([
                'leads.id',
                'leads.external_id',
                'leads.title',
                'leads.created_at',
                'leads.deadline',
                'leads.user_assigned_id',
                'leads.client_id',
                'leads.status_id',
                'statuses.title as status_title',
                'users.name as user_name',
            ]);
    }

    /**
     * Get invoices query for client with optimized eager loading.
     * Returns the HasMany relation (which extends Builder) for DataTables server-side processing.
     *
     * @param Client $client
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
                'client_id',
            ]);
    }

    /**
     * Get all invoices for a client (for show page).
     *
     * @param Client $client
     *
     * @return Collection
     */
    public function getInvoices(Client $client): Collection
    {
        return $client->invoices()->with('invoiceLines')->get();
    }

    /**
     * Get client by external ID.
     *
     * @param string $external_id
     *
     * @return Client
     */
    public function findByExternalId(string $external_id): Client
    {
        return Client::query()->where('external_id', $external_id)->firstOrFail();
    }
}
