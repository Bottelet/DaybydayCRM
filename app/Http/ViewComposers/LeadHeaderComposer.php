<?php

namespace App\Http\ViewComposers;

use App\Models\Lead;
use Illuminate\Contracts\View\View;

class LeadHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData();

        $lead = $data['lead'] ?? null;

        if ($lead instanceof Lead) {
            $leadModel = $lead;
        } elseif (is_array($lead) && isset($lead['id'])) {
            $leadModel = Lead::find($lead['id']);
        } else {
            $leadModel = null;
        }

        $contact      = $leadModel?->user;
        $client       = $leadModel?->client;
        $contact_info = $client?->contacts()->first();

        $view->with('contact', $contact);
        $view->with('contact_info', $contact_info);
        $view->with('client', $client);
    }
}
