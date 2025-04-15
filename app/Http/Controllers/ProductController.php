<?php

namespace App\Http\Controllers;

use App\Services\ExchangeRateService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ExchangeRateService $exchangeRateService
    ) {}

    public function index(): View
    {
        $products = $this->productService->getAllProducts();
        $exchangeRate = $this->exchangeRateService->getUsdToEurRate();

        return view('products.list', compact('products', 'exchangeRate'));
    }

    public function show(Request $request): View
    {
        $product = $this->productService->findProduct($request->route('product_id'));
        $exchangeRate = $this->exchangeRateService->getUsdToEurRate();

        return view('products.show', compact('product', 'exchangeRate'));
    }
}
