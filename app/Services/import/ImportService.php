<?php

namespace App\Services\import;

use App\Enums\OfferStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Project;
use App\Models\Task;

class ImportService
{
    private $project_title_field="project_title";
    private $client_name_field="client_name";
    private $lead_title_field="lead_title";

    private $product_name_field="name";

    private $type_field="type";
    private $prix_field="prix";

    private $quantity_field="quantite";


    private $task_title_field="task_title";

    public function handlefile3($csvfile)
    {
        if(($handle=fopen($csvfile,"r"))!==false){
            $firstrow=fgetcsv($handle,1000,',');
            if(count($firstrow)!=6){
                throw new \Exception("CSV 3 doit avoir 6 en-têtes");
            }
            $client_name_id=0;
            $lead_title_id=0;
            $type_id=0;
            $produit_title_id=0;
            $prix_id=0;
            $quantity_id=0;

            for($i=0;$i<count($firstrow);$i++){
                if($firstrow[$i]==$this->client_name_field){
                   $client_name_id=$i;
                }
                if($firstrow[$i]==$this->lead_title_field){
                    $lead_title_id=$i;
                }
                if($firstrow[$i]==$this->type_field){
                    $type_id=$i;
                }
                if($firstrow[$i]==$this->product_name_field){
                    $produit_title_id=$i;
                }
                if($firstrow[$i]==$this->quantity_field){
                    $quantity_id=$i;
                }
                if($firstrow[$i]==$this->prix_field){
                    $prix_id=$i;
                }
            }
            $isa=0;
            while ($data = fgetcsv($handle,1000,','))
            {
                $isa++;
                try {
                    $client=Client::findByName($data[$client_name_id]);
                    $lead=Lead::findByTitle($data[$lead_title_id]);
                    $typevalue=$data[$type_id];
                    $product=Product::findByName($data[$produit_title_id]);

                    if(!$client){
                        $client=factory(Client::class)->create([
                            'company_name'=>$data[$client_name_id],
                        ]);
                    }
                    if(!$lead){
                        $lead=factory(Lead::class)->create([
                            'title'=>$data[$lead_title_id],
                            'client_id'=>$client->id,

                        ]);
                    }

                    if(!$product)
                    {
                        $product=factory(Product::class)->create([
                            'name'=>$data[$produit_title_id],
                        ]);
                    }


                    $offer=factory(Offer::class)->create([
                        'client_id'=>$client->id,
                        'source_id'=>$lead->id,
                        'status'=>OfferStatus::inProgress()->getStatus(),
                    ]);



                    if($typevalue=="invoice"){
                        $offer->status=OfferStatus::won()->getStatus();
                        $invoice=factory(Invoice::class)->create([
                            'client_id'=>$client->id,
                            'source_id'=>$lead->id,
                            'offer_id'=>$offer->id,
                        ]);
                        $invoiceline1=factory(InvoiceLine::class)->create([
                            'invoice_id'=>$invoice->id,
                            'type'=>$product->type,
                            'quantity'=>$data[$quantity_id],
                            'price'=>$data[$prix_id],
                            'title'=>$product->name,
                            'comment'=>'invoiceline result of the invoice '.$invoice->id,
                        ]);

                    }

                    $offer->save();
                    $invoiceline=factory(InvoiceLine::class)->create([
                        'type'=>$product->type,
                        'quantity'=>$data[$quantity_id],
                        'price'=>$data[$prix_id],
                        'title'=>$product->name,
                        'offer_id'=>$offer->id,
                        'comment'=>'invoiceline result of the offer '.$offer->id,
                    ]);
                }
                catch (\Exception $exception){
                    //echo $exception->getTraceAsString();
                    throw new \Exception( $csvfile->getClientOriginalName() ." line  ".$isa. ": " .$exception->getMessage());
                }
            }
        }
        else
        {
            throw new \Exception("Unable to open CSV 3 : ".$csvfile->getClientOriginalName());
        }
    }
    public function handlefile2($csvfile)
    {
        if(($handle=fopen($csvfile,"r"))!==false){
            $firstrow=fgetcsv($handle,1000,',');
            if(count($firstrow)!=2){
                throw new \Exception("CSV 2 doit avoir 2 en-têtes");
            }
            $project_title_id=0;
            $task_title_id=0;
            for($i=0;$i<count($firstrow);$i++){
                if($firstrow[$i]==$this->project_title_field){
                    $project_title_id=$i;
                }
                if($firstrow[$i]==$this->task_title_field){
                    $task_title_id=$i;
                }
            }
            $isa=0;
            while($data=fgetcsv($handle,1000,',')){
                $isa++;
                try {
                    $project=Project::getByTitle($data[$project_title_id]);
                    if(!$project){
                        $project=factory(Project::class)->create([
                            'title'=>$data[$project_title_id],
                        ]);
                    }
                    if(!Task::findByTitle($data[$task_title_id])){
                        factory(Task::class)->create([
                            'title'=>$data[$task_title_id],
                            'client_id'=>$project->client_id,
                        ]);
                    }
                }
               catch (\Exception $exception){
                   throw new \Exception( $csvfile->getClientOriginalName() ." line  ".$isa. ": " .$exception->getMessage());
               }

            }

        }
        else
        {
            throw new \Exception("Unable to open CSV 2 : ".$csvfile->getClientOriginalName());
        }
    }



    public function handlefile1($csvfile)
    {
        if(($handle=fopen($csvfile,"r"))!==false){
            $firstRow = fgetcsv($handle, 1000, ',');
           if(count($firstRow)!=2){
                throw  new \Exception("CSV 1 doit avoir 2 colonnes");
           }
           $project_title_id=0;
           $client_name_id=0;
           if($firstRow[0]==$this->project_title_field && $firstRow[1]==$this->client_name_field ){
               $client_name_id=1;
           }
           else if($firstRow[0]==$this->client_name_field && $firstRow[1]==$this->project_title_field ){
                $project_title_id=1;
           }
           else
           {
               throw  new \Exception("CSV 1 verifier les en-têtes");
           }
            $isa=0;
           while ($data = fgetcsv($handle, 1000, ',')) {
               $isa++;
               try {
                   $myclient=Client::findByName($data[$client_name_id]);
                   if(!$myclient){

                       $myclient=factory(Client::class)->create([
                           'company_name'=>$data[$client_name_id],
                       ]);
                   }

                   if(!Project::getByTitle($data[$project_title_id])){
                       $project=factory(Project::class)->create(
                           [
                               'client_id'=>$myclient->id,
                               'title'=>$data[$project_title_id],
                           ]
                       );
                   }
               }
               catch (\Exception $exception){
                   throw new \Exception( $csvfile->getClientOriginalName() ." line  ".$isa. ": " .$exception->getMessage());
               }

           }
        }
        else
        {
            throw new \Exception("Unable to open CSV 1 :" . $csvfile->getClientOriginalName());
        }
    }



}