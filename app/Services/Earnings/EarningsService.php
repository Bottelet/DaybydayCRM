<?php

namespace App\Services\Earnings;

use App\Repositories\Money\Money;
use App\Repositories\Money\MoneyConverter;
use Illuminate\Support\Facades\DB;

class EarningsService
{
    public function getMonthlyEarnings($year,$month)
    {
        $earnings = DB::table('payments')
            ->select(
                DB::raw('SUM(amount) as total_earnings')
            )
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->where('deleted_at', null)
            ->first();
        $earning=$earnings && $earnings->total_earnings !== null ? $earnings->total_earnings : 0;

        return app(MoneyConverter::class, ['money' => new Money($earning)])->format();
    }

    public function getDaybyDayEarnings($year,$month)
    {
        $earnings = DB::table('payments')
            ->select(
                DB::raw('DATE(payment_date) as daty'), // Extraire le jour
                DB::raw('SUM(amount) as total_earnings') // Somme des montants
            )
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->where('deleted_at', null)
            ->groupBy(DB::raw('DATE(payment_date)')) // Regrouper par jour
            ->orderBy('daty')
            ->get();

        return $earnings;
    }

    public function getGlobalEarnings()
    {
        $earnings = DB::table('payments')
            ->select(
                DB::raw('SUM(amount) as total_earnings')
            )
            ->where('deleted_at', null)
            ->first();
        $earning=$earnings && $earnings->total_earnings !== null ? $earnings->total_earnings : 0;

        return app(MoneyConverter::class, ['money' => new Money($earning)])->format();
    }


    public function loadPayments($year,$month)
    {
        $earnings = DB::table('payments');
        if($year != null){
            $earnings = $earnings->whereYear('payment_date', $year);
        }
        if($month != null){
            $earnings = $earnings->whereMonth('payment_date', $month);
        }
        $earnings = $earnings->where('deleted_at', null);
        return $earnings->get();
    }

    public function getAnnualEarnings($year)
    {
        $earnings = DB::table('payments')
            ->select(
                DB::raw('SUM(amount) as total_earnings')
            )
            ->whereYear('payment_date', $year)
            ->where('deleted_at', null)
            ->first();
        $earning=$earnings && $earnings->total_earnings !== null ? $earnings->total_earnings : 0;

        return app(MoneyConverter::class, ['money' => new Money($earning)])->format();
    }
}