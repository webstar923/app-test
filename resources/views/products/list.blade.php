<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <style>
        .price-container {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
        }
        .price-usd {
            font-size: 1.5rem;
            font-weight: bold;
            color: #e74c3c;
        }
        .price-eur {
            font-size: 1.2rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Products</h1>

        <div class="products-grid">
            @forelse ($products as $product)
                <div class="product-card">
                    @if ($product->image)
                        <img src="{{ env('APP_URL') }}/{{ $product->image }}" class="product-image" alt="{{ $product->name }}">
                    @endif
                    <div class="product-info">
                        <h2 class="product-title">{{ $product->name }}</h2>
                        <p class="product-description">{{ Str::limit($product->description, 100) }}</p>
                        <div class="price-container">
                            <span class="price-usd">${{ number_format($product->price, 2) }}</span>
                            <span class="price-eur">€{{ number_format($product->price * $exchangeRate, 2) }}</span>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            @empty
                <div class="empty-message">
                    <p>No products found.</p>
                </div>
            @endforelse
        </div>

        <div style="margin-top: 20px; text-align: center; font-size: 0.9rem; color: #7f8c8d;">
            <p>Exchange Rate: 1 USD = {{ number_format($exchangeRate, 4) }} EUR</p>
        </div>
    </div>
</body>
</html>
