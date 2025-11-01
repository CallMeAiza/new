<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f0f0f0;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-approved {
            background-color: #17a2b8;
            color: #fff;
        }
        .status-delivered {
            background-color: #28a745;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PURCHASE ORDER RECEIPT</h1>
        <p>{{ config('app.name', 'PNPH System') }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Order Number:</span>
            <span>{{ $purchaseOrder->order_number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Order Date:</span>
            <span>{{ $purchaseOrder->order_date->format('F d, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Expected Delivery:</span>
            <span>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('F d, Y') : 'Not specified' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Supplier Name:</span>
            <span>{{ $purchaseOrder->supplier_name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ordered By:</span>
            <span>Cook</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="status-badge status-{{ $purchaseOrder->status }}">
                {{ $purchaseOrder->status === 'approved' ? 'Ordered' : ucfirst($purchaseOrder->status) }}
            </span>
        </div>
    </div>

    @if($purchaseOrder->notes)
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <p>{{ $purchaseOrder->notes }}</p>
        </div>
    </div>
    @endif

    <h3>Order Items</h3>
    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="35%">Item Name</th>
                <th width="15%">Quantity</th>
                <th width="10%">Unit</th>
                <th width="17.5%">Unit Price</th>
                <th width="17.5%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->quantity_ordered }}</td>
                <td>{{ $item->unit }}</td>
                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                <td>₱{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-right">Grand Total:</td>
                <td>₱{{ number_format($purchaseOrder->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
        <p>This is a computer-generated document. No signature is required.</p>
    </div>
</body>
</html>
