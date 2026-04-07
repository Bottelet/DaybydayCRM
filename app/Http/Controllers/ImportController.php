<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use App\Services\import\ImportService;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function index()
    {
        return view('upload.index');
    }
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file1'=>'required|file|mimes:csv,txt',
                'file2'=>'required|file|mimes:csv,txt',
                'file3'=>'required|file|mimes:csv,txt',
            ]);
            $csvfile1=$request->file('file1');
            $csvfile2=$request->file('file2');
            $csvfile3=$request->file('file3');

            DB::beginTransaction();

            $myimportservice=new ImportService();
            $myimportservice->handlefile1($csvfile1);
            $myimportservice->handlefile2($csvfile2);
            $myimportservice->handlefile3($csvfile3);

            DB::commit();
            session()->flash('flash_message', __('Files successfully imported'));
        }
        catch (ValidationException $e)
        {
            DB::rollback();
            session()->flash('flash_message_warning', $e->getMessage());
        }
        catch (\Exception $e)
        {
            DB::rollback();
            #echo $e->getMessage();
            session()->flash('flash_message_warning', $e->getMessage());

        }
        #var_dump(Status::typeOfLead());
        return redirect()->back();
    }
}