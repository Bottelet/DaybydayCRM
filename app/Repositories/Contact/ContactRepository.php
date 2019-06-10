<?php

namespace App\Repositories\Contact;

use App\Models\Contact;

/**
 * Class ContactRepository.
 */
class ContactRepository implements ContactRepositoryContract
{
    const CREATED        = 'created';
    const UPDATED_ASSIGN = 'updated_assign';

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id)
    {
        return Contact::findOrFail($id);
    }

    /**
     * @return mixed
     */
    public function listAllContacts()
    {
        return Contact::pluck('name', 'id');
    }

    /**
     * @return int
     */
    public function getAllContactsCount()
    {
        return Contact::count();
    }

    /**
     * @param $requestData
     */
    public function create($requestData)
    {
        $contact = Contact::create($requestData);
        Session()->flash('flash_message', 'Contact successfully added');
        event(new \App\Events\ContactAction($contact, self::CREATED));
    }

    /**
     * @param $id
     * @param $requestData
     */
    public function update($id, $requestData)
    {
        $contact = Contact::findOrFail($id);
        $contact->fill($requestData->all())->save();
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        try {
            $contact = Contact::findorFail($id);
            $contact->delete();
            Session()->flash('flash_message', 'Contact successfully deleted');
        } catch (\Illuminate\Database\QueryException $e) {
            Session()->flash('flash_message_warning', 'Contact can NOT have, leads, or tasks assigned when deleted');
        }
    }
}
