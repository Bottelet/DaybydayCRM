<?php

namespace App\Repositories\Contact;

interface ContactRepositoryContract
{
    public function find($id);

    public function listAllContacts();

    public function getAllContactsCount();

    public function create($requestData);

    public function update($id, $requestData);

    public function destroy($id);
}
