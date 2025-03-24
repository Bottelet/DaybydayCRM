<?php

namespace App\Http\Controllers;

use App\Services\Database\DatabaseService;
use Illuminate\Support\Facades\DB;

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
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . env('DB_DATABASE');

        return view('database.import', [
            'tables' => $tables,
            'tableKey' => $tableKey
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
            'csv' => 'required|file|mimes:csv,txt',
            'table' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        try {
            $file = $request->file('csv');
            $table = $request->input('table');
            
            $data = $this->databaseService->readCsv($file->getRealPath());
            
            foreach ($data as $record) {
                logger()->info('Record: ', $record);
            }

            // $this->databaseService->importData($table, $data);
            Session()->flash('flash_message', __('Importation réussie'));
            return redirect()->route('database.index');
        }
        catch (\Exception $e) {
            Session()->flash('flash_message_warning', __('Erreur lors de l\'importation'));
        }
    }
}