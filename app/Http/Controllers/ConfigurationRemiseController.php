<?php

namespace App\Http\Controllers;



use App\Models\ConfigurationRemise;
use Illuminate\Http\Request;

class ConfigurationRemiseController extends Controller
{
    public function index()
    {
        return response()->json(ConfigurationRemise::first());
    }

    public function update(Request $request){

        try {
            $request->validate([
                'discount' => 'required|numeric|min:0|max:100'
            ]);
            $remise=ConfigurationRemise::first();
            if (!$remise) {
                $remise = new ConfigurationRemise();
            }
            $remise->discount=$request->discount;
            $remise->save();
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation des données. ','errors'=>$e->errors(),
                'success' => false,
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur interne s\'est produite: '. $e->getMessage(),
                'success' =>false ,
            ]);
        }
        return response()->json([
            'message' => 'Discount configuration successfully updated.',
            'success' =>true,
        ]);
    }
}
