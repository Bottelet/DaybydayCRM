<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    public function truncateDatabase()
    {
        try {
            // Désactiver les contraintes de clé étrangère
            Schema::disableForeignKeyConstraints();

            // Récupérer toutes les tables
            $tables = DB::select("SELECT table_name 
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name NOT IN ('users', 'roles', 'role_user',
'permissions','permission_role',
'department_user','departments','settings','industries','business_hours',
'clients','statuses','contacts')");

            // Parcourir chaque table et supprimer son contenu
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                 // Récupération du nom de la table
                DB::table($tableName)->truncate(); // DELETE FROM table_name
            }

            // Réactiver les contraintes de clé étrangère
            Schema::enableForeignKeyConstraints();

            Session()->flash('flash_message', __('Toutes les données ont été supprimées avec succès !'));
            return redirect()->back();
        } catch (\Exception $e) {
            Session()->flash('flash_message_warning', __('Erreur lors de la suppression des données : ' . $e->getMessage()));
            return redirect()->back();
            
        }
    }

    public function page_import(){
        return view("database.import");
    }
}
