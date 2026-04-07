<?php

namespace App\Services\Product;
use App\Models\Product;
use Illuminate\Support\Facades\DB;


class ProductService
{

    public function getTopProductsMonthly($limit)
    {
        //filtrer
        $topProducts = DB::table('invoice_lines')
            ->select('product_id', DB::raw('SUM(quantity) as total_sales'))
            ->whereNotNull('product_id')
            ->whereNotNull('invoice_id')
            ->whereYear('created_at',now()->year)
            ->whereMonth('created_at',now()->month)
            ->groupBy('product_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        //prendre les produits
        $products = Product::whereIn('id', $topProducts->pluck('product_id'))
            ->select('id', 'name', 'external_id')
            ->get();

        $result=$topProducts->map(function($topproduct) use ($products){
            $product=$products->where('id', $topproduct->product_id)->first();
            $product->top_sales=$topproduct->total_sales;
            return [
                'id' => $product->id,
                'name' => $product->name,
                'external_id' => $product->external_id,
                'total_sales' => $topproduct ? $topproduct->total_sales : 0
            ];
        });


        #var_dump($result);
        return $result;

    }
}