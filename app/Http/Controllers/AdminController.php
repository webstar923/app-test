<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Show login page
     */
    public function loginPage(): View
    {
        return view('login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/products');
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials'])
            ->withInput($request->except('password'));
    }

    /**
     * Display all products
     */
    public function products(): View
    {
        $products = $this->productService->getAllProducts();
        return view('admin.products', compact('products'));
    }

    /**
     * Show add product form
     */
    public function addProductForm(): View
    {
        return view('admin.add_product');
    }

    /**
     * Add a new product
     */
    public function addProduct(ProductRequest $request): RedirectResponse
    {
        $this->productService->createProduct(
            $request->validated(),
            $request->file('image')
        );

        return redirect('/admin/products')->with('success', 'Product created successfully');
    }

    /**
     * Show edit product form
     */
    public function editProductForm(string $id): View|RedirectResponse
    {
        $product = $this->productService->getAllProducts()->find($id);
        if (!$product) {
            return redirect('/admin/products')->with('error', 'Product not found');
        }
        return view('admin.edit_product', compact('product'));
    }

    /**
     * Update an existing product
     */
    public function updateProduct(ProductRequest $request, string $id): RedirectResponse
    {
        if (!$this->productService->updateProduct(
            $id,
            $request->validated(),
            $request->file('image')
        )) {
            return back()->with('error', 'Product not found');
        }

        return redirect('/admin/products')->with('success', 'Product updated successfully');
    }

    /**
     * Delete a product
     */
    public function deleteProduct(string $id): RedirectResponse
    {
        if (!$this->productService->deleteProduct($id)) {
            return back()->with('error', 'Product not found');
        }

        return redirect('/admin/products')->with('success', 'Product deleted successfully');
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
