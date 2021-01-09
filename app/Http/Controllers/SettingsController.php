<?php
namespace App\Http\Controllers;

use App\Enums\Country;
use App\Models\BusinessHour;
use App\Repositories\Currency\Currency;
use App\Repositories\Format\GetDateFormat;
use App\Repositories\Setting\GenerateSetting;
use App\Repositories\Tax\Tax;
use App\Services\ClientNumber\ClientNumberService;
use App\Services\ClientNumber\ClientNumberValidator;
use App\Services\InvoiceNumber\InvoiceNumberService;
use App\Services\InvoiceNumber\InvoiceNumberValidator;
use Auth;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Session;
use App\Http\Requests;
use App\Models\Setting;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Http\Requests\Setting\UpdateSettingOverallRequest;

class SettingsController extends Controller
{
    /**
     * SettingsController constructor.
     */
    public function __construct()
    {
        $this->middleware('user.is.admin', ['only' => ['index']]);
        $this->middleware('is.demo', ['except' => ['index']]);
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return view('settings.index')
            ->withVatPercentage(app(Tax::class)->percentage())
            ->withClientNumber(app(ClientNumberService::class)->nextClientNumber())
            ->withInvoiceNumber(app(InvoiceNumberService::class)->nextInvoiceNumber())
            ->withCurrencies(Currency::getAllCurrencies())
            ->withCurrentCurrency(Setting::select("currency")->first()->currency)
            ->withSettings(Setting::first())
            ->withBusinessHours($this->businessHours());
    }

    public function updateFirstStep(Request $request)
    {
        $start_time = Carbon::parse('2020-01-01 ' . $request->start_time . ':00');
        $end_time = Carbon::parse('2020-01-01 ' . $request->end_time . ':00');
        $settings = Setting::first();

        if ($start_time->gt($end_time)) {
            $end_tmp = clone $end_time;
            $end_time = $start_time;
            $start_time = $end_tmp;
        } elseif ($start_time->eq($end_time)) {
            $end_time->addHour();
        }
        $businessHours = BusinessHour::all();
        if($businessHours->isNotEmpty()) {
            foreach (BusinessHour::all() as $businessHour) {
                $businessHour->update([
                    'open_time' => $start_time->format('H:i:s'),
                    'close_time' => $end_time->format('H:i:s'),
                ]);
            }
        } else {
            for ($i=1; $i < 8; $i++) {
                \App\Models\BusinessHour::create([
                    'day' => $this->integerToDay()[$i],
                    'open_time' => '09:00',
                    'close_time' => '18:00',
                    'settings_id' => $settings->id,
                ]);
            }
        }

        if (!$request->company_name) {
            $request->company_name = uniqid();
        }
        if (!$request->country) {
            $request->country = "GB";
        }

        $country = Country::fromCode($request->country);
        $currency = app(Currency::class, ["code" => $country->getCurrencyCode()]);

        $settings->country = $request->country;
        $settings->company = $request->company_name;
        $settings->vat = $currency->getVatPercentage();
        $settings->currency = $currency->getCode();
        $settings->language = strtolower($country->getLanguage()) === "danish" ? "dk" : "en";
        $settings->save();

        $user = auth()->user();
        $user->language = strtolower($country->getLanguage()) === "danish" ? "dk" : "en";
        $user->save();

        cache()->delete(GetDateFormat::CACHE_KEY);

        return redirect()->back();
    }

    /**
     * @param UpdateSettingOverallRequest $request
     * @return mixed
     */
    public function updateOverall(UpdateSettingOverallRequest $request)
    {
        $setting = Setting::first();

        if (!app(ClientNumberValidator::class)->validateClientNumber((int)$request->client_number)) {
            Session::flash('flash_message_warning', __('Client number invalid'));
            return redirect()->back();
        }


        if (!app(InvoiceNumberValidator::class)->validateInvoiceNumber((int)$request->invoice_number)) {
            Session::flash('flash_message_warning', __('Invoice number invalid'));
            return redirect()->back();
        }
        if ($request->currency == $setting->currency && !empty($request->vat)) {
            $setting->vat = $request->vat * 100;
        } elseif (empty($request->vat)) {
            $request->vat = $setting->vat;
        } else {
            if (app(Currency::class, ["code" => $request->currency])->hasCurrency($request->currency)) {
                $setting->currency = $request->currency;
                if ($request->vat == $setting->vat / 100) {
                    $setting->vat = app(Currency::class, ["code" => $request->currency])->getCurrency($request->currency)["vatPercentage"];
                } else {
                    $setting->vat = $request->vat * 100;
                }
            };
        }
        $start_time = Carbon::parse('2020-01-01 ' . $request->start_time . ':00');
        $end_time = Carbon::parse('2020-01-01 ' . $request->end_time . ':00');
        if ($start_time->gt($end_time)) {
            $end_tmp = clone $end_time;
            $end_time = $start_time;
            $start_time = $end_tmp;
        } elseif ($start_time->eq($end_time)) {
            $end_time->addHour();
        }

        foreach (BusinessHour::all() as $businessHour) {
            $businessHour->update([
                'open_time' => $start_time->format('H:i:s'),
                'close_time' => $end_time->format('H:i:s'),
            ]);
        }

        $setting->client_number = $request->client_number;
        $setting->invoice_number = $request->invoice_number;
        isset($request->company) ? $setting->company = $request->company: null;
        $setting->country = $request->country;
        $setting->language = $request->language;
        $setting->save();

        cache()->delete(GetDateFormat::CACHE_KEY);

        Session::flash('flash_message', __('Overall settings successfully updated'));
        return redirect()->back();
    }

    public function businessHours()
    {
        return [
            'open' => BusinessHour::orderBy('open_time', 'asc')->limit(1)->first()->open_time,
            'close' => BusinessHour::orderBy('close_time', 'desc')->limit(1)->first()->close_time
        ];
    }

    private function integerToDay()
    {
        return [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday'
        ];
    }

    public function dateFormats()
    {
        return app(GetDateFormat::class)->getAllDateFormats();
    }
}
