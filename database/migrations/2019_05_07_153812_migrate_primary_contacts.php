<?php

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Migrations\Migration;

class MigratePrimaryContacts extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // move the primary contact information from the Client into a Contact record
        $clients = Client::get();

        foreach ($clients as $c) {
            if ('' != trim($c->primary_contact_name)) {
                $contact                   = new Contact();
                $contact->name             = $c->primary_contact_name;
                $contact->email            = $c->email;
                $contact->address          = $c->address;
                $contact->zipcode          = $c->zipcode;
                $contact->city             = $c->city;
                $contact->primary_number   = $c->primary_number;
                $contact->secondary_number = $c->secondary_number;
                $contact->client_id        = $c->id;
                $contact->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // delete all contacts
        DB::table('contacts')->delete();
    }
}
