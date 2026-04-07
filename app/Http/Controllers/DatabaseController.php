<?php

namespace App\Http\Controllers;

use App\Services\Database\DatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DatabaseController extends Controller
{
    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function index()
    {
        return view('database.index');
    }

    public function import(){
        return view('database.import', [
            'errorImport' => session('errorImport')
        ]);
    }

    public function truncateAllExcept()
    {
        try{
            $this->databaseService->truncateAllExcept();
            Session()->flash('flash_message', __('Votre base de donnees a ete reinitialise'));
        }catch(\Exception $e){
            Session()->flash('flash_message_warning', __('Erreur lors de la reinitialisation'));
        }
        return redirect()->route('database.index');
            
    }

    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv1' => 'required|file|mimes:csv,txt',
            'csv2' => 'required|file|mimes:csv,txt',
            'csv3' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            $csv1 = $request->file('csv1');
            $csv2 = $request->file('csv2');
            $csv3 = $request->file('csv3');

            $errors = $this->databaseService->importIntoDatabase($csv1, $csv2, $csv3);

            return redirect()->back()->with('errorImport', $errors);
        }
        catch (\Exception $e) {
            Session()->flash('flash_message_warning', __('Erreur lors de l\'importation'));
        }
    }
}