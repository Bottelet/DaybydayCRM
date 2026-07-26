<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AppointmentsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\InvoiceLinesController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\OffersController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::auth();
Route::get('/logout', [LoginController::class, 'logout']);
Route::group(['middleware' => ['auth']], static function () {
    /*
     * Main
     */
    Route::get('/', [PagesController::class, 'dashboard']);
    Route::get('dashboard', [PagesController::class, 'dashboard'])->name('dashboard');

    /*
     * Users
     */
    Route::group(['prefix' => 'users'], static function () {
        Route::get('/data', [UsersController::class, 'anyData'])->name('users.data');
        Route::get('/taskdata/{id}', [UsersController::class, 'taskData'])->name('users.taskdata');
        Route::get('/projectdata/{id}', [UsersController::class, 'projectData'])->name('users.projectdata');
        Route::get('/leaddata/{id}', [UsersController::class, 'leadData'])->name('users.leaddata');
        Route::get('/clientdata/{id}', [UsersController::class, 'clientData'])->name('users.clientdata');
        Route::get('/users', [UsersController::class, 'users'])->name('users.users');
        Route::get('/calendar-users', [UsersController::class, 'calendarUsers'])->name('users.calendar');
    });
    Route::get('users', [UsersController::class, 'index'])->name('users.index');
    Route::get('users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('users', [UsersController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [UsersController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    /*
     * Roles
     */
    Route::group(['prefix' => 'roles'], static function () {
        Route::get('/data', [RolesController::class, 'indexData'])->name('roles.data');
        Route::patch('/update/{external_id}', [RolesController::class, 'update']);
    });
    Route::get('roles', [RolesController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}', [RolesController::class, 'show'])->name('roles.show');
    Route::get('roles/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::delete('roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
    /*
     * Clients
     */
    Route::group(['prefix' => 'clients'], static function () {
        Route::get('/data', [ClientsController::class, 'anyData'])->name('clients.data');
        Route::get('/taskdata/{external_id}', [ClientsController::class, 'taskDataTable'])->name('clients.taskDataTable');
        Route::get('/projectdata/{external_id}', [ClientsController::class, 'projectDataTable'])->name('clients.projectDataTable');
        Route::get('/leaddata/{external_id}', [ClientsController::class, 'leadDataTable'])->name('clients.leadDataTable');
        Route::get('/invoicedata/{external_id}', [ClientsController::class, 'invoiceDataTable'])->name('clients.invoiceDataTable');
        Route::post('/create/cvrapi', [ClientsController::class, 'cvrapiStart']);
        Route::patch('/updateassignee/{external_id}', [ClientsController::class, 'updateAssignee'])->name('clients.updateAssignee');
        Route::post('/upload/{external_id}', [DocumentsController::class, 'upload'])->name('document.upload');
        Route::patch('/updateassign/{external_id}', [ClientsController::class, 'updateAssign']);
        Route::post('/updateassign/{external_id}', [ClientsController::class, 'updateAssign']);
    });
    Route::get('clients', [ClientsController::class, 'index'])->name('clients.index');
    Route::get('clients/create', [ClientsController::class, 'create'])->name('clients.create');
    Route::post('clients', [ClientsController::class, 'store'])->name('clients.store');
    Route::get('clients/{client}', [ClientsController::class, 'show'])->name('clients.show');
    Route::get('clients/{client}/edit', [ClientsController::class, 'edit'])->name('clients.edit');
    Route::match(['put', 'patch'], 'clients/{client}', [ClientsController::class, 'update'])->name('clients.update');
    Route::delete('clients/{client}', [ClientsController::class, 'destroy'])->name('clients.destroy');
    Route::get('document/{external_id}', [DocumentsController::class, 'view'])->name('document.view');
    Route::get('document/download/{external_id}', [DocumentsController::class, 'download'])->name('document.download');
    Route::delete('document/{external_id}', [DocumentsController::class, 'destroy'])->name('document.destroy');
    //Route::resource('documents', 'DocumentsController');

    /*
     * Tasks
     */
    Route::group(['prefix' => 'tasks'], static function () {
        Route::get('/data', [TasksController::class, 'anyData'])->name('tasks.data');
        Route::patch('/updatestatus/{external_id}', [TasksController::class, 'updateStatus'])->name('task.update.status');
        Route::patch('/updateassign/{external_id}', [TasksController::class, 'updateAssign'])->name('task.update.assignee');
        Route::post('/updatestatus/{external_id}', [TasksController::class, 'updateStatus']);
        Route::post('/updateassign/{external_id}', [TasksController::class, 'updateAssign']);
        Route::post('/invoice/{external_id}', [TasksController::class, 'invoice'])->name('task.invoice');
        Route::patch('/update-deadline/{external_id}', [TasksController::class, 'updateDeadline'])->name('task.update.deadline');
        Route::get('/create/{client_external_id}', [TasksController::class, 'create'])->name('client.task.create');
        Route::get('/create/{client_external_id}/{project_external_id}', [TasksController::class, 'create'])->name('client.project.task.create');
        Route::post('/updateproject/{external_id}', [TasksController::class, 'updateProject'])->name('tasks.update.project');
        Route::patch('/updateproject/{external_id}', [TasksController::class, 'updateProject'])->name('tasks.updateProject'); // Alias
    });
    Route::get('tasks', [TasksController::class, 'index'])->name('tasks.index');
    Route::get('tasks/create', [TasksController::class, 'create'])->name('tasks.create');
    Route::post('tasks', [TasksController::class, 'store'])->name('tasks.store');
    Route::get('tasks/{task}', [TasksController::class, 'show'])->name('tasks.show');
    Route::get('tasks/{task}/edit', [TasksController::class, 'edit'])->name('tasks.edit');
    Route::match(['put', 'patch'], 'tasks/{task}', [TasksController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TasksController::class, 'destroy'])->name('tasks.destroy');

    /*
     * Leads
     */
    Route::group(['prefix' => 'leads'], static function () {
        //Route::get('/all-leads-data', [LeadsController::class, 'allLeads'])->name('leads.all');
        Route::get('/data', [LeadsController::class, 'leadsJson'])->name('leads.data');
        Route::patch('/updateassign/{external_id}', [LeadsController::class, 'updateAssign'])->name('leads.updateAssign');
        Route::post('/updateassign/{external_id}', [LeadsController::class, 'updateAssign']);
        Route::patch('/updatestatus/{external_id}', [LeadsController::class, 'updateStatus'])->name('lead.update.status');
        Route::post('/updatestatus/{external_id}', [LeadsController::class, 'updateStatus']);
        Route::patch('/update-deadline/{external_id}', [LeadsController::class, 'updateDeadline'])->name('lead.update.deadline');
        Route::patch('/updatefollowup/{external_id}', [LeadsController::class, 'updateFollowup'])->name('lead.followup')
            ->middleware('permission:lead-update-deadline');
        Route::get('/create/{client_external_id}', [LeadsController::class, 'create'])->name('client.lead.create');
        Route::delete('/{lead}/json', [LeadsController::class, 'destroyJson'])->name('leads.destroy.json');
    });
    Route::get('leads', [LeadsController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadsController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadsController::class, 'store'])->name('leads.store');
    Route::get('leads/{lead}', [LeadsController::class, 'show'])->name('leads.show');
    Route::get('leads/{lead}/edit', [LeadsController::class, 'edit'])->name('leads.edit');
    Route::match(['put', 'patch'], 'leads/{lead}', [LeadsController::class, 'update'])->name('leads.update');
    Route::delete('leads/{lead}', [LeadsController::class, 'destroy'])->name('leads.destroy');
    Route::post('/comments/{type}/{external_id}', [CommentController::class, 'store'])->name('comments.create');

    /*
     * Products
     */
    Route::group(['prefix' => 'products'], static function () {
        Route::get('/', [ProductsController::class, 'index'])->name('products.index');
        Route::delete('/{product}', [ProductsController::class, 'destroy'])->name('products.destroy');
        Route::get('/creator/{external_id?}', [ProductsController::class, 'productCreator'])->name('products.creator');
        Route::post('/{external_id?}', [ProductsController::class, 'update'])->name('products.update');
        Route::get('/data', [ProductsController::class, 'allProducts'])->name('products.data');
    });

    /*
     * Projects
     */
    Route::group(['prefix' => 'projects'], static function () {
        Route::get('/data', [ProjectsController::class, 'indexData'])->name('projects.index.data');
        Route::patch('/updatestatus/{external_id}', [ProjectsController::class, 'updateStatus'])->name('project.update.status');
        Route::patch('/updateassign/{external_id}', [ProjectsController::class, 'updateAssign'])->name('project.update.assignee');
        Route::post('/updatestatus/{external_id}', [ProjectsController::class, 'updateStatus']);
        Route::post('/updateassign/{external_id}', [ProjectsController::class, 'updateAssign']);
        Route::patch('/update-deadline/{external_id}', [ProjectsController::class, 'updateDeadline'])->name('project.update.deadline');
        Route::get('/create/{client_external_id}', [ProjectsController::class, 'create'])->name('project.client.create');
    });
    Route::get('projects', [ProjectsController::class, 'index'])->name('projects.index');
    Route::get('projects/create', [ProjectsController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectsController::class, 'store'])->name('projects.store');
    Route::get('projects/{project}', [ProjectsController::class, 'show'])->name('projects.show');
    Route::get('projects/{project}/edit', [ProjectsController::class, 'edit'])->name('projects.edit');
    Route::match(['put', 'patch'], 'projects/{project}', [ProjectsController::class, 'update'])->name('projects.update');
    Route::delete('projects/{project}', [ProjectsController::class, 'destroy'])->name('projects.destroy');
    /*
     * Settings
     */
    Route::group(['prefix' => 'settings'], static function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
        Route::patch('/overall', [SettingsController::class, 'updateOverall'])->name('settings.updateOverall');
        Route::patch('/', [SettingsController::class, 'updateOverall'])->name('settings.update'); // Alias for backwards compatibility
        Route::post('/first-steps', [SettingsController::class, 'updateFirstStep'])->name('settings.updateFirstStep');
        Route::get('/business-hours', [SettingsController::class, 'businessHours'])->name('settings.business_hours');
        Route::get('/date-formats', [SettingsController::class, 'dateFormats'])->name('settings.date_formats');
    });

    /*
     * Departments
     */
    Route::group(['prefix' => 'departments'], static function () {
        Route::get('/indexData', [DepartmentsController::class, 'indexData'])->name('departments.indexDataTable');
    });
    Route::get('departments', [DepartmentsController::class, 'index'])->name('departments.index');
    Route::get('departments/create', [DepartmentsController::class, 'create'])->name('departments.create');
    Route::post('departments', [DepartmentsController::class, 'store'])->name('departments.store');
    Route::get('departments/{department}', [DepartmentsController::class, 'show'])->name('departments.show');
    Route::get('departments/{department}/edit', [DepartmentsController::class, 'edit'])->name('departments.edit');
    Route::match(['put', 'patch'], 'departments/{department}', [DepartmentsController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentsController::class, 'destroy'])->name('departments.destroy');

    /*
     * Integrations
     */
    Route::group(['prefix' => 'integrations'], static function () {
        Route::post('/revokeAccess', [IntegrationsController::class, 'revokeAccess'])->name('integration.revoke-access');
        Route::post('/sync/dinero', [IntegrationsController::class, 'dineroSync'])->name('sync.dinero');
    });
    Route::get('integrations', [IntegrationsController::class, 'index'])->name('integrations.index');
    Route::post('integrations', [IntegrationsController::class, 'store'])->name('integrations.store');

    /*
     * Notifications
     */
    Route::group(['prefix' => 'notifications'], static function () {
        Route::post('/markread', [NotificationsController::class, 'markRead'])->name('notification.read');
        Route::get('/markall', [NotificationsController::class, 'markAll']);
        Route::get('/{id}', [NotificationsController::class, 'markRead']);
    });

    /*
     * Invoices
     */
    Route::group(['prefix' => 'invoices'], static function () {
        Route::post('/sentinvoice/{external_id}', [InvoicesController::class, 'updateSentStatus'])->name('invoice.sent');
        Route::post('/newitem/{external_id}', [InvoicesController::class, 'newItem'])->name('invoice.new.item');
        Route::get('/overdue', [InvoicesController::class, 'overdue'])->name('invoices.overdue');
        Route::get('/{invoice}', [InvoicesController::class, 'show'])->name('invoices.show');
        Route::get('/payments-data/{invoice}', [InvoicesController::class, 'paymentsDataTable'])->name('invoice.paymentsDataTable');
    });

    Route::get('/money-format', [InvoicesController::class, 'moneyFormat'])->name('money.format');
    Route::post('/invoice/create/offer/{lead}', [OffersController::class, 'create'])->name('create.offer');
    Route::post('/offers/create/{lead}', [OffersController::class, 'create'])->name('offers.create');
    Route::post('/invoice/create/invoiceLine/{invoice}', [InvoicesController::class, 'newItems'])->name('create.invoiceLine');

    /*
     * Invoice Lines
     */
    Route::delete('/invoice-lines/{invoiceLine}', [InvoiceLinesController::class, 'destroy'])->name('invoiceLine.destroy');

    /*
     * Payment
     */
    Route::group(['prefix' => 'payment'], static function () {
        Route::delete('/{payment}', [PaymentsController::class, 'destroy'])->name('payment.destroy');
        Route::post('/add-payment/{invoice}', [PaymentsController::class, 'addPayment'])->name('payment.add');
    });

    /*
     * Offers
     */
    Route::group(['prefix' => 'offer'], static function () {
        Route::post('/won', [OffersController::class, 'won'])->name('offer.won');
        Route::post('/lost', [OffersController::class, 'lost'])->name('offer.lost');
        Route::post('/{offer}/update', [OffersController::class, 'update'])->name('offer.update');
        Route::get('/{offer}/invoice-lines/json', [OffersController::class, 'getOfferInvoiceLinesJson']);
    });

    // Additional route aliases for backward compatibility
    Route::post('/offers/won', [OffersController::class, 'won'])->name('offers.won');
    Route::post('/offers/lost', [OffersController::class, 'lost'])->name('offers.lost');
    Route::post('/offers/{offer}/update', [OffersController::class, 'update'])->name('offers.update');

    /*
     * Documents
     */
    Route::get('/add-documents/{external_id}/{type}', [DocumentsController::class, 'uploadFilesModalView']);
    Route::post('/uploadToTask/{external_id}', [DocumentsController::class, 'uploadToTask'])->name('document.task.upload');
    Route::post('/uploadToProject/{external_id}', [DocumentsController::class, 'uploadToProject'])->name('document.project.upload');
    Route::get('/search/{query}/{type?}', [SearchController::class, 'search'])->name('search');

    /*
     * Appointments
     */
    Route::group(['prefix' => 'appointments'], static function () {
        Route::get('/calendar', [AppointmentsController::class, 'calendar'])->name('appointments.calendar');
        Route::get('/data', [AppointmentsController::class, 'appointmentsJson'])->name('appointments.data.json');
        Route::post('/update/{appointment}', [AppointmentsController::class, 'update'])->name('appointments.update');
        Route::delete('/{appointment}', [AppointmentsController::class, 'destroy'])->name('appointments.destroy');
    });

    /*
     * Absence
     */
    Route::group(['prefix' => 'absences'], static function () {
        Route::get('/data', [AbsenceController::class, 'indexData'])->name('absence.data');
        Route::get('/', [AbsenceController::class, 'index'])->name('absence.index');
        Route::get('/create', [AbsenceController::class, 'create'])->name('absence.create');
        Route::post('/', [AbsenceController::class, 'store'])->name('absence.store');
        Route::delete('/{absence}', [AbsenceController::class, 'destroy'])->name('absence.destroy');
    });
});

Route::group(['middleware' => ['auth']], static function () {
    Route::get('/dropbox-token', [CallbackController::class, 'dropbox'])->name('dropbox.callback');
    Route::get('/googledrive-token', [CallbackController::class, 'googleDrive'])->name('googleDrive.callback');
});
