<?php
namespace App\Repositories\Client;

interface ClientRepositoryContract
{
    public function find($id);

    public function listAllClients();

    public function getInvoices($id);

    public function getAllClientsCount();

    public function listAllIndustries();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);

    public function vat($requestData);
}
