<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Product PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; }
        h3 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>

<h3>Product: {{ $product->name }}</h3>
<p>Description: {{ $product->description }}</p>

<table>
    <thead>
    <tr>
        <th>Brand Name</th>
        <th>Image</th>
        <th>Price</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($product->brands as $brand)
        <tr>
            <td>{{ $brand->name }}</td>
           <td>
                @if($brand->image)
                    <img src="{{ public_path('storage/'.$brand->image) }}" width="60">
                @endif
            </td>

            <td>{{ number_format($brand->price, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p><strong>Total Price:</strong> {{ number_format($total_price, 2) }}</p>

</body>
</html>
