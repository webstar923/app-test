<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function loginPage(): View
    {
        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::attempt($request->except(['_token']))) {
            return redirect()->route('admin.products');
        }

        return redirect()->back()->with('error', 'Invalid login credentials');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function products(): View
    {
        $products = $this->productService->getAllProducts();
        return view('admin.products', compact('products'));
    }

    public function editProduct(int $id): View
    {
        $product = $this->productService->findProduct($id);
        return view('admin.edit_product', compact('product'));
    }

    public function updateProduct(ProductRequest $request, int $id): RedirectResponse
    {
        $product = $this->productService->findProduct($id);
        $this->productService->updateProduct($product, $request->validated(), $request->file('image'));

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }

    public function deleteProduct(int $id): RedirectResponse
    {
        $product = $this->productService->findProduct($id);
        $this->productService->deleteProduct($product);

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully');
    }

    public function addProductForm(): View
    {
        return view('admin.add_product');
    }

    public function addProduct(ProductRequest $request): RedirectResponse
    {
        $this->productService->createProduct($request->validated(), $request->file('image'));

        return redirect()->route('admin.products')->with('success', 'Product added successfully');
    }
}
