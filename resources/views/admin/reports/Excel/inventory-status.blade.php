
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Stock Quantity</th>
            <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inventoryData as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->stock_quantity }}</td>
            <td>{{ $item->price }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
