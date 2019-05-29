<?php

use App\Models\Permissions;
use Illuminate\Database\Migrations\Migration;

class AddContactPermissions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        /**
         * add contacts permissions.
         */
        $createContact               = new Permissions();
        $createContact->display_name = 'Create contact';
        $createContact->name         = 'contact-create';
        $createContact->description  = 'Permission to create contact';
        $createContact->save();

        $updateContact               = new Permissions();
        $updateContact->display_name = 'Update contact';
        $updateContact->name         = 'contact-update';
        $updateContact->description  = 'Permission to update contact';
        $updateContact->save();

        $deleteContact               = new Permissions();
        $deleteContact->display_name = 'Delete contact';
        $deleteContact->name         = 'contact-delete';
        $deleteContact->description  = 'Permission to delete contact';
        $deleteContact->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Permissions::where('name', 'contact-create')->delete();
        Permissions::where('name', 'contact-update')->delete();
        Permissions::where('name', 'contact-delete')->delete();
    }
}
