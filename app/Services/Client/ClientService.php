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
    public function getTasksWithRelations(Client $client): Collection
    {
        return $client->tasks()
            ->with([
                'status',        // Task status for color/title
                'user',          // User assigned to task (fixes N+1 on assigned_user->name)
            ])
            ->select([
                'id',
                'external_id',
                'title',
                'created_at',
                'deadline',
                'user_assigned_id',
                'client_id',
                'status_id',
            ])
            ->get();
    }

    /**
     * Get projects for client with optimized eager loading.
     *
     * @param Client $client
     *
     * @return Collection
     */
    public function getProjectsWithRelations(Client $client): Collection
    {
        return $client->projects()
            ->with([
                'status',        // Project status for color/title
                'assignee',      // User assigned to project (fixes N+1 on assignee->name)
            ])
            ->select([
                'id',
                'external_id',
                'title',
                'created_at',
                'deadline',
                'user_assigned_id',
                'client_id',
                'status_id',
            ])
            ->get();
    }

    /**
     * Get leads for client with optimized eager loading.
     *
     * @param Client $client
     *
     * @return Collection
     */
    public function getLeadsWithRelations(Client $client): Collection
    {
        return $client->leads()
            ->with([
                'status',        // Lead status for color/title
                'user',          // User assigned to lead (fixes N+1 on assigned_user->name)
            ])
            ->select([
                'id',
                'external_id',
                'title',
                'created_at',
                'deadline',
                'user_assigned_id',
                'client_id',
                'status_id',
            ])
            ->get();
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
