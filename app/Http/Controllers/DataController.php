<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    /**
     * Réinitialiser les données et exécuter un seeder.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetAndImportData()
    {
        // Désactiver les vérifications de contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Récupérer la liste des tables à exclure depuis .env
        $excludedTables = explode(',', env('EXCLUDED_TABLES', ''));

        // Récupérer toutes les tables de la base de données
        $tables = DB::select('SHOW TABLES');

        // Parcourir les tables et les vider (sauf celles exclues)
        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . config('database.connections.mysql.database')};

            // Vérifier si la table doit être exclue
            if (!in_array($tableName, $excludedTables)) {
                DB::table($tableName)->truncate();
            }
        }

        // Réactiver les vérifications de contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Exécuter un seeder pour importer des données
        // Artisan::call('db:seed', ['--class' => 'DummyDatabaseSeeder']);

        // Rediriger avec un message de succès
        return redirect()->back()->with('success', 'Données réinitialisées et importées avec succès.');
    }
}