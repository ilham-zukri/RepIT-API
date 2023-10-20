<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order</title>
    <style>
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
            justify-content: space-between;
        }
        .company-info {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8pt; /* Menggunakan ukuran margin dalam poin */
            font-size: 10pt; /* Menggunakan ukuran font dalam poin */
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="po-title">
                Purchase Order
                <div class="po-number">No: 258</div>
                <div class="vendor-name">Vendor: Mandiri Jaya Komputer</div>
            </div>
            <div class="company-info">
                CV Sabar Maju
                <div class="dept-name">Departemen IT</div>
                <div class="vendor-name">12-11-22</div>
            </div>
        </div>
        <table>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama Barang</th>
                <th style="width: 15%;">Harga</th>
                <th style="width: 10%;">Jumlah</th>
                <th style="width: 15%;">Total</th>
            </tr>
            <tr>
                <td>1</td>
                <td>Product A</td>
                <td>5.000.000</td>
                <td>2</td>
                <td>10.000.000</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Product B</td>
                <td>2.000.000</td>
                <td>2</td>
                <td>4.000.00</td>
            </tr>
        </table>
        <div class="total-price">
            Total Harga: 14.000.000
        </div>
        <div class="signatures">
            <div class="signature">
                <p>Mengetahui</p>
            </div>
            <div class="signature">
                <p>Pembuat</p>
            </div>
        </div>
    </div>
</body>
</html>
