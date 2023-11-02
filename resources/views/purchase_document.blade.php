<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt; /* Menggunakan ukuran font dalam poin */
        }
        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            font-size: 16pt; /* Menggunakan ukuran font dalam poin */
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;    
        }
        .company-info {
            text-align: right;
        }
        .table-heading {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .main-content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .main-content-table, 
        .main-content-table th, 
        .main-content-table td {
            border: 1px solid #000;
        }
        .main-content-table th, 
        .main-content-table td {
            padding: 8pt; /* Menggunakan ukuran margin dalam poin */
            font-size: 10pt; /* Menggunakan ukuran font dalam poin */
        }
        .table-footer {
            width: 100%;
            margin-top: 1rem;
        }
        .signatures {
            margin-top: 80pt; /* Menggunakan ukuran margin dalam poin */
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 40%;
            text-align: center;
        }
        .signature p {
            border-top: 1px solid #000;
            margin-top: 8pt; /* Menggunakan ukuran margin dalam poin */
        }
        .vendor-name {
            margin-top: 8pt; /* Menggunakan ukuran margin dalam poin */
            font-size: 10pt; /* Menggunakan ukuran font dalam poin */
        }
        .po-number{
            margin-top: 8pt; /* Menggunakan ukuran margin dalam poin */
            font-size: 12pt; /* Menggunakan ukuran font dalam poin */
        }
        .dept-name{
            margin-top: 8pt; /* Menggunakan ukuran margin dalam poin */
            font-size: 12pt; /* Menggunakan ukuran font dalam poin */
        }
        .total-price {
            text-align: right;
            font-weight: bold;
            margin-top: 8pt; /* Menggunakan ukuran margin dalam poin */
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="table-heading">
            <tr>
                <td width="76%">
                    <table class="left-table-heading">
                        <tr>
                            <td>
                                <h2>Purchase Order</h2>
                            </td>
                        </tr>
                        <tr>
                            <td>PO Number: {{ $purchase['id'] }}</td>
                        </tr>
                        <tr>
                            <td>Vendor: {{$purchase['purchased_from']}}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="right-table-heading">
                        <tr>
                            <td>
                                <h2>CV. Sabar Maju</h2>
                            </td>
                        </tr>
                        <tr>
                            <td>Department IT</td>
                        </tr>
                        <tr>
                            <td>{{ $purchase['created_at']}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="main-content-table">
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama Barang</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 10%;">Jumlah</th>
                <th style="width: 15%;">Total</th>
            </tr>
            @foreach ($purchase['items'] as $key => $item )
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{$item['brand']}} - {{$item['model']}}</td>
                    <td>{{$item['price_ea']}}</td>
                    <td>{{$item['amount']}}</td>
                    <td>{{$item['total_price']}}</td>
                </tr>
            @endforeach
        </table>
        
        <div class="total-price">
            Total Harga: {{$purchase['total_price']}}
        </div>
        <table class="table-footer" cellspacing="10" cellpadding="0">
            <tr>
                <td width="70%">
                    <h4>Mengetahui</h4>
                </td>
                <td height="80"></td>
                <td>
                    <h4>Pembuat</h4>
                </td>
            </tr>
            <tr>
                <td>
                    <p></p>
                </td>
                <td></td>
                <td><p>Acung Salim</p></td>
            </tr>
        </table>
    </div>
</body>
</html>
