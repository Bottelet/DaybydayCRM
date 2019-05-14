<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Repositories\Contact\ContactRepositoryContract;

class ContactHeaderComposer
{
    /**
     * The client repository implementation.
     *
     * @var ClientRepository
     */
    protected $contacts;

    /**
     * Create a new profile composer.
     *
     * @param ClientRepository|ClientRepositoryContract $contacts
     */
    public function __construct(ContactRepositoryContract $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     */
    public function compose(View $view)
    {
        $contacts = $this->contacts->find($view->getData()['contact']['id']);
        /**
         * [User assigned the client].
         *
         * @var contact
         */
        $contact = $contacts->user;

        $view->with('contact', $contact);
    }
}
