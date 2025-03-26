<?php

namespace App\Models\Dashboard;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Remise extends Model
{
    protected $table = 'remises'; // Nom de la table dans la base de données

    protected $fillable = [
        'nom',
        'type',
        'valeur',
        'montant_min',
        'dateDebut',
        'dateFin',
        'isActive',
    ];

    protected $casts = [
        'valeur' => 'double',
        'montant_min' => 'double',
        'dateDebut' => 'date',
        'dateFin' => 'date',
        'isActive' => 'boolean',
    ];

    public $timestamps = false;


    public function setRemise($invoice_id,$remise_id){
        $invoice = Invoice::where("id",$invoice_id)->first();
        $remise = Remise::where("id",$remise_id)->first();
        $invoice_lines = InvoiceLine::where("invoice_id",$invoice_id)->get();
        foreach($invoice_lines as $invoice_line){
            $invoice_line->price -= $invoice_line->price*$remise->valeur/100;
            $invoice_line->save(); 
        }
        $invoice->remise_id= $remise_id;
        $invoice->save();
    }
    public function getRemiseValide($invoice_id){
        $invoice = Invoice::where("id",$invoice_id)->first();
        $remises = Remise::where("montant_min","<=",$invoice->getTotalPriceAttribute()->getBigDecimalAmount())
        ->where('dateDebut','<=',Carbon::now())
        ->where ('dateFin','>=',Carbon::now())
        ->where('isActive',true)->get();
        return $remises;
    }


}
