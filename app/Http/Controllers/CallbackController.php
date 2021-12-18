<?php

namespace App\Http\Controllers;

use App\Services\Storage\Authentication\DropboxAuthenticator;
use App\Services\Storage\Authentication\GoogleDriveAuthenticator;
use App\Services\Storage\Dropbox;
use App\Services\Storage\GoogleDrive;
use Illuminate\Http\Request;
use App\Models\Integration;

class CallbackController extends Controller
{
    public function dropbox(Request $request)
    {
        
        $integration = Integration::whereApiType('file')->first();
        if ($integration) {
            session()->flash('flash_message_warning', __('File integration alredy exists'));
            return redirect()->route('integrations.index');
        }
        if ($request->error) {
            session()->flash('flash_message_warning', __('Access not given, try again'));
            return redirect()->route('integrations.index');
        }
        $res =  app(DropboxAuthenticator::class)->token($request->code);
        Integration::create(['name' => Dropbox::class, 'api_key' => $res->access_token, 'api_type' => 'file']);

        return redirect('/integrations');
    }

    public function googleDrive(Request $request)
    {
        $integration = Integration::whereApiType('file')->first();
        if ($integration) {
            session()->flash('flash_message_warning', __('File integration alredy exists'));
            return redirect()->route('integrations.index');
        }
        if ($request->error) {
            session()->flash('flash_message_warning', __('Access not given, try again'));
            return redirect()->route('integrations.index');
        }
        $res =  app(GoogleDriveAuthenticator::class)->token($request->code);

        if (!isset($res['refresh_token'])) {
            session()->flash('flash_message_warning', __('It seems you already have a connection to Daybyday. Please remove it in your Google console.'));
            return redirect()->route('integrations.index');
        }

        Integration::create(['name' => GoogleDrive::class, 'api_key' => $res['refresh_token'], 'api_type' => 'file']);
        return redirect('/integrations');
    }
}
