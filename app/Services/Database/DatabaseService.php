<?php
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use Faker\Generator as Faker;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use App\Models\Task;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Offer;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Contact;
use App\Enums\OfferStatus;
use App\Enums\InvoiceStatus;

class DatabaseService
{

    private $faker;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function truncateAllExcept()
    {
        $excludedTables = explode(',', env('EXCLUDED_TABLES', ''));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . env('DB_DATABASE');

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            if (!in_array($tableName, $excludedTables)) {
                DB::table($tableName)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function readCsv(string $filename): array
    {
        $defaultResponse = [
            'headers' => [],
            'records' => [],
            'lines' => []
        ];

        if (($handle = fopen($filename, 'r')) === false) {
            return $defaultResponse;
        }

        $header = fgetcsv($handle, 0, ',');
        
        if ($header === false || empty($header)) {
            fclose($handle);
            return $defaultResponse;
        }

        $records = [];
        $lines = [];
        $lineNumber = 1;
        
        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            
            if (count($header) !== count($data)) {
                throw new \Exception("Nombre de colonnes incorrect à la ligne $lineNumber");
            }
            
            $records[] = array_combine($header, $data);
            $lines[] = rtrim(implode(',', $data), "\r\n");
        }

        fclose($handle);
        
        return [
            'headers' => $header,
            'records' => $records,
            'lines' => $lines
        ];
    }

    public function tableNameMapping(string $tableName, $key, $value): array
    {
        $records = DB::table($tableName)->select($value, $key)->get();
        
        $mapping = $records->pluck($value, $key)->toArray();
        
        return $mapping;
    }

    public function importIntoDatabase($csv1, $csv2, $csv3){
        $error = array();
        try{
            \DB::beginTransaction();

            $data1 = $this->readCsv($csv1->getPathname());
            $data2 = $this->readCsv($csv2->getPathname()); 
            $data3 = $this->readCsv($csv3->getPathname());
            
            info("CSV Import file 1 : ", ['headers' => $data1['headers']]);
            foreach ($data1['records'] as $index => $record) {
                /*$doublon = $this->findDoublon($data1['records'], $index, 1);
                if($doublon != null){
                    $error[] = $doublon;
                    continue;
                }*/

                if($record['project_title']=="" || $record['client_name']==""){
                    $error[] = "Erreur dans le fichier 2 sur la ligne ".($index+2)." cause: Colonne vide dans la ligne";
                    continue;
                }
                
                try{

                    $client = Client::firstOrCreate(
                        ['company_name' => $record['client_name']],
                        [
                            'external_id' => $this->faker->uuid,
                            'address' => $this->faker->city,
                            'zipcode' => $this->faker->postcode,
                            'city' => $this->faker->city,
                            'company_type' => 'Aps',
                            'industry_id' => $this->faker->numberBetween(1, 25),
                            'user_id' => $this->faker->randomElement([1]),
                        ]
                    );

                    $contact = Contact::firstOrCreate(
                        ['client_id' => $client->id],
                        [
                            'external_id' => $this->faker->uuid,
                            'name' => $this->faker->name,
                            'email' => $this->faker->email,
                            'primary_number' => $this->faker->randomNumber(8),
                            'secondary_number' => $this->faker->randomNumber(8),
                            'is_primary' => 1
                        ]
                    );
    
                    $userAssignedId = User::inRandomOrder()->value('id');
                    $userCreatedId = User::inRandomOrder()->value('id');
    
                    $project = Project::create([
                        'external_id' => $this->faker->uuid,
                        'title' => $record['project_title'],
                        'description' => $record['project_title'],
                        'status_id' => 11,
                        'user_assigned_id' => $userAssignedId,
                        'user_created_id' => $userCreatedId,
                        'client_id' => $client->id,
                        'deadline' => now()->addDays(7)
                    ]);
                }catch(\Exception $e){
                    $error[] = "Erreur dans le fichier 1 sur la ligne ".($index+2)." cause: ".$e->getMessage();
                }
                
            }

            info('CSV Import file 2 :', ['headers' => $data2['headers']]);
            $projectMap = $this->tableNameMapping('projects', 'title', 'id');
            foreach($data2  ['records'] as $index => $record){
                /*$doublon = $this->findDoublon($data2['records'], $index, 2);
                if($doublon != null){
                    $error[] = $doublon;
                    continue;
                }*/

                if($record['project_title']=="" || $record['task_title']==""){
                    $error[] = "Erreur dans le fichier 2 sur la ligne ".($index+2)." cause: Colonne vide dans la ligne";
                    continue;
                }

                try{

                    if($projectMap[$record['project_title']]==null){
                        $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: Le client est inexistant";
                        continue;
                    }

                    $userAssignedId = User::inRandomOrder()->value('id');
                    $userCreatedId = User::inRandomOrder()->value('id');
                    $clientId = Client::inRandomOrder()->value('id');

                    $task = Task::create([
                        'external_id' => $this->faker->uuid,
                        'title' => $record['task_title'],
                        'description' => $record['task_title'],
                        'status_id' => 1,
                        'user_assigned_id' => $userAssignedId,
                        'user_created_id' => $userCreatedId,
                        'client_id' => $clientId,
                        'deadline' => now()->addDays(7),
                        'project_id' => $projectMap[$record['project_title']]
                    ]);
                }catch(\Exception $e){
                    $error[] = "Erreur dans le fichier 2 sur la ligne ".($index+2)." cause: ".$e->getMessage();
                }
            }

            info('CSV Import file 3 :', ['headers' => $data2['headers']]);
            $clientMap = $this->tableNameMapping('clients', 'company_name', 'id');
            foreach ($data3['records'] as $index => $record) {

                /*$doublon = $this->findDoublon($data3['records'], $index, 3);
                if($doublon != null){
                    $error[] = $doublon;
                    continue;
                }*/

                if($record['client_name']=="" || $record['lead_title']=="" || $record['type']=="" || $record['produit']=="" || $record['prix']=="" || $record['quantite']==""){
                    $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: Colonne vide dans la ligne";
                    continue;
                }

                try{
                    if(!is_numeric($record['prix']) || !is_numeric($record['quantite'])){
                        $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: Colonne numerique non valide, prix: ".$record['prix']." - quantite: ".$record['quantite'];
                        continue;
                    }
                    if($record['prix'] <= 0){
                        $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: Prix inferieur ou egal a 0";
                    }elseif($record['quantite'] <= 0){
                        $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: Quantite inferieur ou egal a 0";
                    }else{
                        try{

                            if($clientMap[$record['client_name']]==null){
                                $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: Le client est inexistant";
                                continue;
                            }

                            $userAssignedId = User::inRandomOrder()->value('id');
                            $userCreatedId = User::inRandomOrder()->value('id');

                            $lead = Lead::firstOrCreate(
                                ['title' => $record['lead_title']],
                                [
                                    'external_id' => $this->faker->uuid,
                                    'description' => $record['lead_title'],
                                    'status_id' => 7,
                                    'user_assigned_id' => $userAssignedId,
                                    'user_created_id' => $userCreatedId,
                                    'client_id' => $clientMap[$record['client_name']],
                                    'deadline' => now()->addDays(7)
                                ]
                            );

                            $produit = Product::firstOrCreate(
                                ['name' => $record['produit']],
                                [
                                    'external_id' => $this->faker->uuid,
                                    'description' => $record['produit'],
                                    'number' => 10000,
                                    'price' => $record['prix'],
                                    'archived' => 0,
                                    'default_type' => 'pieces'
                                ]
                            );

                            if($record['type']=="invoice"){
                                $invoice = Invoice::create([
                                    'external_id' => $this->faker->uuid,
                                    'status' => InvoiceStatus::draft()->getStatus(),
                                    'client_id' => $clientMap[$record['client_name']],
                                    'source_id' => $lead->id,
                                    'source_type' => Lead::class,
                                    // 'offer_id' => $offer->id,
                                    'due_at' => now()->addDays(7)
                                ]);

                                $invoice_line2 = InvoiceLine::create([
                                    'external_id' => $this->faker->uuid,
                                    'title' => $produit->name,
                                    'comment' => $this->faker->sentence,
                                    'price' => $record['prix'],
                                    'invoice_id' => $invoice->id,
                                    'quantity' => $record['quantite'],
                                    'product_id' => $produit->id
                                ]);
                            }else{
                                $offer = Offer::create([
                                    'external_id' => $this->faker->uuid,
                                    'client_id' => $clientMap[$record['client_name']],
                                    'status' => $record['type']=== "invoice" ? OfferStatus::won()->getStatus(): OfferStatus::inProgress()->getStatus(),
                                    'source_id' => $lead->id,
                                    'source_type' => Lead::class
                                ]);

                                $invoice_line1 = InvoiceLine::create([
                                    'external_id' => $this->faker->uuid,
                                    'title' => $produit->name,
                                    'comment' => $this->faker->sentence,
                                    'price' => $record['prix'],
                                    'type' => 'piece',
                                    'offer_id' => $offer->id,
                                    'quantity' => $record['quantite'],
                                    'product_id' => $produit->id
                                ]);
                            }
                        }catch(\Exception $e){
                            $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: ".$e->getMessage();
                        }
                    }
                }catch(\Exception $e){
                    $error[] = "Erreur dans le fichier 3 sur la ligne ".($index+2)." cause: ".$e->getMessage();
                }
            }

            if(count($error)>0){
                \DB::rollBack();
                return $error;
            }

            \DB::commit();
        }catch(\Exception $e){
            \DB::rollBack();
            $error[] = "Erreur lors de l'importation des fichiers cause: ".$e->getMessage();
        }
        return $error;
    }

    public function findDoublon($lines, $indice, $file){
        $total = count($lines);
        if($total>$indice){
            $total = $indice;
        }

        for($i=0; $i<$total; $i++){
            if($lines[$i] === $lines[$indice]){
                return "Erreur dans le fichier ".$file." sur la ligne ".($indice+1)." cause: la ligne est le doublon de la ligne ".($i+1);
            }
        }
        return null;
    }

    public function exportCsv($request){
        $client = Client::where('external_id', $request->id)->firstOrFail();
        $client_name = $client->company_name." copy";

        $projects = Project::where('client_id', $client->id)->get();
        $invoices = Invoice::where('client_id', $client->id)->get();
        
        $filename = 'clients.csv';
        $file = fopen($filename,"w");

        fputcsv($file,array("client"));
        fputcsv($file, $client->toArray());

        fputcsv($file,array("projects"));
        foreach ($projects as $project){
            fputcsv($file,$project->toArray());
        }

        fputcsv($file,array("invoices"));
        foreach ($invoices as $invoice){
            $invoice_lines = InvoiceLine::where('invoice_id', $invoice->id)->get();
            foreach($invoice_lines as $invoice_line){
                fputcsv($file,$invoice_line->toArray());
            }
        }
        fclose($file);
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Content-Type: application/csv; "); 
        readfile($filename);
        
        unlink($filename);
    }

    public function importCsvDuplicate($lines){
        try{
            \DB::beginTransaction();  
            $id = 0;
            for($i=0; $i<count($lines); $i++){
                $colonne = explode(',', $lines[$i]);
                if($colonne[0]=="client"){
                    $client = Client::create(
                        [
                            'external_id' => $this->faker->uuid,
                            'address' => $colonne[1],
                            'zipcode' => $colonne[2],
                            'city' => $colonne[3],
                            'company_name' => $colonne[4]."_copy",
                            'company_type' => $colonne[5],
                            'user_id' => $colonne[6],
                            'industry_id' => $colonne[7],
                        ]
                    );

                    $id = $client->id;
    
                    $contact = Contact::create(
                        [
                            'external_id' => $this->faker->uuid,
                            'client_id' => $client->id,
                            'name' => $client->company_name,
                            'email' => $this->faker->email,
                            'primary_number' => $this->faker->randomNumber(8),
                            'secondary_number' => $this->faker->randomNumber(8),
                            'is_primary' => 1
                        ]
                    );
                }elseif($colonne[0]=="projects"){
                    $project = Project::create([
                        'external_id' => $this->faker->uuid,
                        'title' => $colonne[1]."_copy",
                        'description' => $colonne[2],
                        'status_id' => $colonne[3],
                        'user_assigned_id' => $colonne[4],
                        'user_created_id' => $colonne[5],
                        'client_id' => $id,
                        'deadline' => $colonne[7]
                    ]);
                }else{
                    $invoice = Invoice::create([
                        'external_id' => $this->faker->uuid,
                        'status' => InvoiceStatus::draft()->getStatus(),
                        'client_id' => $id,
                        'due_at' => now()->addDays(7)
                    ]);
    
                    $invoice_line2 = InvoiceLine::create([
                        'external_id' => $this->faker->uuid,
                        'title' => $colonne[1],
                        'comment' => $colonne[2],
                        'price' => $colonne[3],
                        'invoice_id' => $invoice->id,
                        'quantity' => $colonne[4],
                        'product_id' => $colonne[5]
                    ]); 
                }
            }
            \DB::commit();
        }catch(\Exception $e){
            \DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}