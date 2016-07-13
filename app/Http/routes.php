<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/
Route::group(['middleware' => 'web'], function () {
    Route::auth();
    
});

Route::group(['middleware' => ['web', 'auth']], function () {
	    Route::get('/', 'PagesController@dashboard');
	    Route::get('users/data', 'UsersController@anyData')->name('users.data');
	    Route::get('users/taskdata/{id}', 'UsersController@taskData')->name('users.taskdata');
	    Route::get('users/closedtaskdata/{id}', 'UsersController@closedtaskData')->name('users.closedtaskdata');
	    Route::get('users/clientdata/{id}', 'UsersController@clientData')->name('users.clientdata');
	    Route::get('clients/data', 'ClientsController@anyData')->name('clients.data');
	    Route::get('tasks/data', 'TasksController@anyData')->name('tasks.data');
	    Route::get('leads/data', 'LeadsController@anyData')->name('leads.data');
	    Route::resource('users', 'UsersController');
    	Route::post('clients/create/cvrapi', 'ClientsController@cvrapistart');
    	Route::post('clients/upload/{id}', 'DocumentsController@upload');
    	
		Route::resource('clients', 'ClientsController');
		Route::get('settings', 'SettingsController@index')->name('settings.index');
		Route::patch('settings/permissionsUpdate', 'SettingsController@permissionsUpdate');
		Route::post('settings/stripe', 'SettingsController@stripe');
		Route::patch('settings/overall', 'SettingsController@updateoverall');
		Route::patch('tasks/updatestatus/{id}', 'TasksController@updatestatus');
		Route::patch('tasks/updateassign/{id}', 'TasksController@updateassign');
		Route::post('tasks/updatetime/{id}', 	'TasksController@updatetime');
		Route::post('tasks/invoice/{id}', 'TasksController@invoice');
		Route::patch('leads/updateassign/{id}', 'LeadsController@updateassign');
		Route::resource('tasks', 'TasksController');
		Route::resource('leads', 'LeadsController');
		Route::post('tasks/comments/{id}', 'CommentController@store');
		Route::post('leads/notes/{id}', 'NotesController@store');
		Route::patch('leads/updatestatus/{id}', 'LeadsController@updatestatus');
		Route::patch('leads/updatefollowup/{id}', 'LeadsController@updatefollowup')->name('leads.followup');
		
		Route::resource('departments', 'DepartmentsController');
		Route::resource('roles', 'RolesController');
		
		Route::get('dashboard', 'PagesController@dashboard')->name('dashboard');
		Route::resource('integrations', 'IntegrationsController');
		
		//Notifications
		Route::get('notifications/getall', 'NotificationsController@getAll')->name('notifications.get');
		Route::post('notifications/markread', 'NotificationsController@markRead');
		Route::get('notifications/markall', 'NotificationsController@markAll');


		Route::get('documents/import', 'DocumentsController@import');

});

