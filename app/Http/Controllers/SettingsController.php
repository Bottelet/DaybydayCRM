<?php

namespace App\Http\Controllers;

use App\Enums\Country;
use App\Http\Requests\Setting\UpdateSettingOverallRequest;
use App\Models\BusinessHour;
use App\Models\Setting;
use App\Repositories\Currency\Currency;
use App\Repositories\Format\GetDateFormat;
use App\Repositories\Tax\Tax;
use App\Services\ClientNumber\ClientNumberService;
use App\Services\InvoiceNumber\InvoiceNumberService;
use App\Services\Setting\UpdateOverallSettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Throwable;

class SettingsController extends Controller
{
    /**
     * SettingsController constructor.
     */
    public function __construct()
    {
        $this->middleware('user.is.admin', ['only' => ['index', 'updateOverall', 'updateFirstStep']]);
        $this->middleware('is.demo', ['except' => ['index']]);
    }

    /**
     * @return mixed
     */
    public function index(Request $request)
    {
        $setting = Setting::first();
        if ( ! $setting) {
            $setting = Setting::query()->create([
                'company'        => 'Default Company',
                'currency'       => 'USD',
                'country'        => 'US',
                'language'       => 'en',
                'vat'            => 0,
                'client_number'  => 1,
                'invoice_number' => 1,
                'max_users'      => 10,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'settings'         => $setting,
                'business_hours'   => $this->businessHours(),
                'currencies'       => Currency::getAllCurrencies(),
                'current_currency' => $setting->currency,
                'client_number'    => app(ClientNumberService::class)->nextClientNumber(),
                'invoice_number'   => app(InvoiceNumberService::class)->nextInvoiceNumber(),
                'vat_percentage'   => app(Tax::class)->percentage(),
            ]);
        }

        return view('settings.index')
            ->withVatPercentage(app(Tax::class)->percentage())
            ->withClientNumber(app(ClientNumberService::class)->nextClientNumber())
            ->withInvoiceNumber(app(InvoiceNumberService::class)->nextInvoiceNumber())
            ->withCurrencies(Currency::getAllCurrencies())
            ->withCurrentCurrency($setting->currency)
            ->withSettings($setting)
            ->withBusinessHours($this->businessHours());
    }

    public function updateFirstStep(Request $request)
    {
        $start_time = Carbon::parse('2020-01-01 ' . $request->start_time . ':00');
        $end_time   = Carbon::parse('2020-01-01 ' . $request->end_time . ':00');
        $settings   = Setting::first();
        if ( ! $settings) {
            $settings = Setting::query()->create([
                'company'        => 'Default Company',
                'currency'       => 'USD',
                'country'        => 'US',
                'language'       => 'en',
                'vat'            => 0,
                'client_number'  => 1,
                'invoice_number' => 1,
                'max_users'      => 10,
            ]);
        }

        if ($start_time->gt($end_time)) {
            $end_tmp    = clone $end_time;
            $end_time   = $start_time;
            $start_time = $end_tmp;
        } elseif ($start_time->eq($end_time)) {
            $end_time->addHour();
        }
        $businessHours = BusinessHour::all();
        if ($businessHours->isNotEmpty()) {
            foreach (BusinessHour::all() as $businessHour) {
                $businessHour->update([
                    'open_time'  => $start_time->format('H:i:s'),
                    'close_time' => $end_time->format('H:i:s'),
                ]);
            }
        } else {
            for ($i = 1; $i < 8; $i++) {
                BusinessHour::query()->create([
                    'day'         => $this->integerToDay()[$i],
                    'open_time'   => '09:00',
                    'close_time'  => '18:00',
                    'settings_id' => $settings->id,
                ]);
            }
        }

        if ( ! $request->company_name) {
            $request->company_name = uniqid();
        }
        if ( ! $request->country) {
            $request->country = 'GB';
        }

        $country  = Country::fromCode($request->country);
        $currency = app(Currency::class, ['code' => $country->getCurrencyCode()]);

        $settings->country  = $request->country;
        $settings->company  = $request->company_name;
        $settings->vat      = $currency->getVatPercentage();
        $settings->currency = $currency->getCode();
        $settings->language = mb_strtolower($country->getLanguage()) === 'danish' ? 'dk' : 'en';
        $settings->save();

        $user           = auth()->user();
        $user->language = mb_strtolower($country->getLanguage()) === 'danish' ? 'dk' : 'en';
        $user->save();

        cache()->delete(GetDateFormat::CACHE_KEY);

        return redirect()->back();
    }

    /**
     * @return mixed
     */
    public function updateOverall(UpdateSettingOverallRequest $request, UpdateOverallSettingsService $service)
    {
        try {
            $result = $service->handle($request->validated());

            if ($result->status === 'client_number_invalid') {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('Client number invalid')], 400);
                }
                Session::flash('flash_message_warning', __('Client number invalid'));

                return redirect()->back();
            }

            if ($result->status === 'invoice_number_invalid') {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('Invoice number invalid')], 400);
                }
                Session::flash('flash_message_warning', __('Invoice number invalid'));

                return redirect()->back();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => __('Overall settings successfully updated')], 200);
            }

            Session::flash('flash_message', __('Overall settings successfully updated'));

            return redirect()->back();
        } catch (Throwable $exception) {
            report($exception);

            return $this->failureResponse(
                $request,
                __('Settings could not be updated. Please try again.'),
                'settings'
            );
        }
    }

    public function businessHours()
    {
        $openHour  = BusinessHour::orderBy('open_time', 'asc')->limit(1)->first();
        $closeHour = BusinessHour::orderBy('close_time', 'desc')->limit(1)->first();

        return [
            'open'  => $openHour ? $openHour->open_time : '09:00',
            'close' => $closeHour ? $closeHour->close_time : '17:00',
        ];
    }

    public function dateFormats()
    {
        return app(GetDateFormat::class)->getAllDateFormats();
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
            7 => 'sunday',
        ];
    }
}
