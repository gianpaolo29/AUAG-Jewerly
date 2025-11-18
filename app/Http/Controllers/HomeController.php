<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke()
    {
        // Fetch top-selling products based on total sold quantity
        $bestSellers = Product::query()
            ->select('products.*')
            ->join('transaction_items', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.type', 'buy') // use your transaction type for actual sales
            ->groupBy('products.id')
            ->addSelect([
                DB::raw('SUM(transaction_items.quantity) as sold_qty'),
                DB::raw('SUM(transaction_items.line_total) as gross_sales'),
            ])
            ->orderByDesc('sold_qty')
            ->with('pictureUrls') // optional relation if you have product images
            ->limit(12)
            ->get();

        // fallback if no sales yet — show latest products
        if ($bestSellers->isEmpty()) {
            $bestSellers = Product::query()->latest()->limit(8)->get();
        }

        // Pass to view
        return view('welcome', compact('bestSellers'));
    }
}
