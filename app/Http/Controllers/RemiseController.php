<?php
// namespace App\Api\v1\Controllers\DashBoardApi;
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\Remise;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RemiseController extends Controller {

    public function addRemise(Request $request){
        // Validation de l'objet "remise" dans la requête
        $request->validate([
            'remise.nom' => 'required|string|max:255',
            'remise.type' => 'required|string|max:50',
            'remise.valeur' => 'required|numeric',
            'remise.montant_min' => 'required|numeric',
            'remise.dateDebut' => 'required|date',
            'remise.dateFin' => 'required|date|after_or_equal:remise.dateDebut',
            'remise.isActive' => 'required|boolean'
        ]);
        
        // Création de l'objet remise à partir des données de la requête
        $remiseData = $request->input('remise');  // Récupérer l'objet "remise" directement
        
        // Création de la remise
        $remise = new Remise();
        $remise->nom = $remiseData['nom'];
        $remise->type = $remiseData['type'];
        $remise->valeur = $remiseData['valeur'];
        $remise->montant_min = $remiseData['montant_min'];
        $remise->dateDebut = Carbon::parse($remiseData['dateDebut']);
        $remise->dateFin = Carbon::parse($remiseData['dateFin']);
        $remise->isActive = $remiseData['isActive'];
        
        // Sauvegarde de la remise
        $remise->save();
        
        // Retourner la réponse avec l'objet remis
        return response()->json([
            'message' => 'Remise ajoutée avec succès',
            'remise' => $remise
        ], 201);
    }
    

    public function getRemise(){
        $remises = Remise::all();
        return response()->json(['remises'=>$remises]);
    }

    public function addRemiseToInvoice(Request $request){
        $remise_id = $request->input('remise_id');
        $invoice_id = $request->input('facture_id');
        $remise = new Remise();
        $remise->setRemise($invoice_id,$remise_id);
        Session()->flash('flash_message', __('Remise applique avec succès !'));
            return redirect()->back();
    }
    
}