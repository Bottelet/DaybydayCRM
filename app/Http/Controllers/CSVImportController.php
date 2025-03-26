<?php

namespace App\Http\Controllers;

use App\Enums\OfferStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Task;
use App\Models\User;
use App\Models\Offer;
use App\Models\InvoiceLine;
use Exception;
use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Project;
use App\Services\ClientNumber\ClientNumberService;


class CSVImportController extends Controller
{
    public function import(Request $request)
    {
        // Validation des données de l'input
        $request->validate([
            'file' => 'required|mimes:csv,txt',
            'file2' =>'required|mimes:csv,txt',
            'file3' =>'required|mimes:csv,txt',
            
        ]);
        DB::beginTransaction();

        try {
            // Ouvrir le fichier CSV
            $fichier1 = fopen($request->file('file'), 'r');
            $premiere_ligne = true;
            $colonnes = [];
            $compteur_ligne_fichier1 = 0;

            // Lire chaque ligne du fichier CSV
            while (($data = fgetcsv($fichier1, 1000, ',')) !== FALSE) {
                // Si c'est la première ligne, on récupère les colonnes
                $compteur_ligne_fichier1++;
                if ($premiere_ligne) {
                    $colonnes = $data; // Assigner les noms de colonnes
                    $premiere_ligne = false;
                    continue;
                }

                // Préparer les données pour insertion
                $row = array_combine($colonnes, $data);
                $this->project_client($row['project_title'],$row['client_name'],$compteur_ligne_fichier1);

            }

            $fichier2 = fopen($request->file('file2'), 'r');
            $premiere_ligne = true;
            $colonnes = [];
            $compteur_ligne_fichier2 = 0;

            // Lire chaque ligne du fichier CSV
            while (($data = fgetcsv($fichier2, 1000, ',')) !== FALSE) {
                // Si c'est la première ligne, on récupère les colonnes
                $compteur_ligne_fichier2++;
                if ($premiere_ligne) {
                    $colonnes = $data; // Assigner les noms de colonnes
                    $premiere_ligne = false;
                    continue;
                }

                // Préparer les données pour insertion
                $row = array_combine($colonnes, $data);
                $this->project_task($row['project_title'],$row['task_title'],$compteur_ligne_fichier2);

            }



            $fichier3 = fopen($request->file('file3'), 'r');
            $premiere_ligne = true;
            $colonnes = [];
            $compteur_ligne_fichier3 = 0;

            // Lire chaque ligne du fichier CSV
            while (($data = fgetcsv($fichier3, 1000, ',')) !== FALSE) {
                // Si c'est la première ligne, on récupère les colonnes
                $compteur_ligne_fichier3++;
                if ($premiere_ligne) {
                    $colonnes = $data; // Assigner les noms de colonnes
                    $premiere_ligne = false;
                    continue;
                }

                // Préparer les données pour insertion
                $row = array_combine($colonnes, $data);
                $this->feuille3($row['client_name'],$row['lead_title'],$row['type'],$row['produit'],
                $row['prix'],$row['quantite'],$compteur_ligne_fichier3);

            }

            // Fermer le fichier après l'import
            DB::commit();
            fclose($fichier1);
            fclose($fichier2);
            fclose($fichier3);

            // Message de succès

        } catch (\Exception $e) {
            // En cas d'exception, afficher un message d'erreur
            Session()->flash('flash_message_warning', __('Erreur :'.$e->getMessage()));
            DB::rollBack();
            // throw $e;
            return redirect()->back();
        }

        // Rediriger vers la page précédente
        Session()->flash('flash_message', __('Import de données pour la table : '.$request->input('table').' réussie'));

        return redirect()->back();
    }


    public function project_client($project_title, $client_name,$ligne)
    {

        $admin = User::find(1); // Récupérer l'admin
        if(empty(trim($client_name))||empty(trim($project_title))){
            throw new Exception("champ vide fichier 1 ligne : ".$ligne);
        }
    
        // Trouver le client avec son utilisateur
        $client = Client::whereHas('user', function ($query) use ($client_name) {
            $query->where('name', $client_name);
        })->first();
    
        if (!$client) {

            
            // Si le client n'existe pas, on le crée avec son utilisateur
            $user = factory(User::class)->create(['name' => $client_name]);
            // $user->save();
            $user->save();
            $client = factory(Client::class)->create(
                ['user_id'=>$user->id]
            );
            // $client->client_number = app(ClientNumberService::class)->setNextClientNumber();
            // $client->save();
            // $client->save();
    
           
        }
    
        // Créer un projet et l'associer au client existant
        $project = factory(Project::class)->make([
            'title' => $project_title,
            'status_id' => 11,
            'client_id'=> $client->id,
            'user_created_id' => $admin->id,
            'user_assigned_id' => $admin->id
        ]);
        
        
        $project->save(); // Sauvegarder le projet
    }

    public function project_task($project_title,$task_title,$ligne){

        $admin = User::find(1); // Récupérer l'admin

        if(empty(trim($task_title))||empty(trim($project_title))){
            throw new Exception("champ vide fichier 2 ligne : ".$ligne);
        }

        $project = Project::where("title",$project_title)->first();
        if(!$project){
            throw new Exception("Project n'existe pas fichier 2 ligne : ".$ligne);
        }
        $client = $project->client;
        
        $task = factory(Task::class)->create([
            'user_created_id' =>$admin->id,
            'user_assigned_id' =>$admin->id,
            'client_id' => $client->id,
            'status_id'=> 1,
            'project_id'=>$project->id,
            'title' => $task_title
            
        ]);
        // $task->save();

    }


    public function feuille3($client_name,$lead_title,$type,$produit,$prix,$quantite,$ligne){
        $admin = User::find(1);
        if(empty(trim($client_name))||
        empty(trim($lead_title)||
        empty(trim($prix)||
        empty(trim($quantite))||
        empty(trim($type))||
        empty(trim($produit))
        ))){
            throw new Exception("champ vide fichier 3 ligne : ".$ligne);
        }

        if($prix <= 0){
            throw new Exception("Montant incorrecte fichier 3 ligne : ".$ligne);
        }

        if($quantite<=0 ||is_float($quantite)){
            throw new Exception("Quantite incorrecte fichier 3 ligne : ".$ligne);
        }

        $client = Client::whereHas('user', function ($query) use ($client_name) {
            $query->where('name', $client_name);
        })->first();
        if(!$client){
            throw new Exception("Client inexistant fichier 3 ligne : ".$ligne);
        }

        $lead = Lead::where('title',$lead_title)->first();
        if(!$lead){
        $lead = factory(Lead::class)->create(
            [
                "client_id"=>$client->id,
                "status_id"=>7,
                "user_created_id"=>$admin->id,
                "user_assigned_id"=>$admin->id,
                "title" => $lead_title
                
            ]
            );
        }

        $product = Product::where("name",$produit)->first();
        if(!$product){
            $product = factory(Product::class)->create([
                "price"=>$prix,
                "name"=>$produit
            ]);
        }

        if($type=="offers"){
            $offer = factory(Offer::class)->create([
                "client_id"=>$client->id,
                "source_id"=>$lead->id
            ]);
            factory(InvoiceLine::class)->create([
                "invoice_id"=>null,
                "offer_id"=>$offer->id,
                "type"=>$product->default_type,
                "quantity"=>$quantite,
                "price"=>$prix * 100,
                "product_id"=>$product->id
            ]);
        }
        else{
            $offer = factory(Offer::class)->create([
                "client_id"=>$client->id,
                "source_id"=>$lead->id,
                "status"=>OfferStatus::won()->getStatus(),
            ]);
            factory(InvoiceLine::class)->create([
                "invoice_id"=>null,
                "offer_id"=>$offer->id,
                "type"=>$product->default_type,
                "quantity"=>$quantite,
                "price"=>$prix * 100,
                "product_id"=>$product->id
            ]);
            $invoice = factory(Invoice::class)->create([
                "client_id"=>$client->id,
                "offer_id"=>$offer->id,
                "source_type"=>Lead::class,
                "source_id"=>$lead->id
            ]);
            factory(InvoiceLine::class)->create([
                "invoice_id"=>$invoice->id,
                "offer_id"=>null,
                "type"=>$product->default_type,
                "quantity"=>$quantite,
                "price"=>$prix * 100,
                "product_id"=>$product->id
            ]);

        }


    }

    // private function offers()
    
}
