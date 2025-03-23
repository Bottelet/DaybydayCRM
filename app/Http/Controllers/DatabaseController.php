<?php

namespace App\Http\Controllers;

use App\Services\Database\DatabaseService;

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
        return view('database.import');
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
}