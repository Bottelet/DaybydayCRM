<?php
namespace App\Http\Controllers;
use App\Models\Absence;
use App\Models\Activity;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Client;
use App\Models\Comment;
use App\Models\ConfigurationRemise;
use App\Models\Contact;
use App\Models\CreditLine;
use App\Models\CreditNote;
use App\Models\Department;
use App\Models\Document;
use App\Models\Industry;
use App\Models\Integration;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Mail;
use App\Models\Offer;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Product;
use App\Models\Project;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class ReinitialiseController extends Controller
{
    public function index()
    {
        return view('reinitialise.index');
    }


    public function reset()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Absence::truncate();
        Activity::truncate();
        Appointment::truncate();
        #BusinessHour::truncate();
        Client::truncate();
        Comment::truncate();
        Contact::truncate();
        //CreditLine::truncate();
        //CreditNote::truncate();
        #Department::truncate();
        Document::truncate();
        #Industry::truncate();
        Integration::truncate();
        Invoice::truncate();
        InvoiceLine::truncate();
        Lead::truncate();
        Mail::truncate();
        Offer::truncate();
        Payment::truncate();
        #Permission::truncate();
        #PermissionRole::truncate();
        ConfigurationRemise::truncate();
        Product::truncate();
        Project::truncate();
        #Role::truncate();
        #RoleUser::truncate();
        #Setting::truncate();
        #Status::truncate();
        Task::truncate();
        #User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return redirect()->route('dashboard');
        /*$projects=factory(Project::class)->create([
            'description' => 'Description spécifique de ce projet',
        ]);
        return response()->json($projects);*/
    }

}

?>
