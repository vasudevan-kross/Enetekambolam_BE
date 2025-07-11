<!DOCTYPE html>
<html>

<head>
    <title>Invoice </title>
</head>
<style type="text/css">
    @font-face {
        font-family: 'meera-regular-unicode';
        src: url('{{ storage_path('fonts/meera-regular-unicode-font.ttf') }}') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    .malayalam-text {
        font-family: 'meera-regular-unicode';
    }
    body {
        font-family: 'Roboto Condensed', sans-serif;
    }

    .rupee-symbol {
        font-family: 'DejaVu Sans', sans-serif !important;
        /* A font that supports the Rupee symbol */
    }

    .add-detail {
        display: flex;
        align-items: flex-start;
    }

    .m-0 {
        margin: 0px;
    }

    .p-0 {
        padding: 0px;
    }

    .pt-5 {
        padding-top: 5px;
    }

    .mt-10 {
        margin-top: 10px;
    }

    .text-center {
        text-align: center !important;
    }

    .w-100 {
        width: 100%;
    }

    .w-50 {
        width: 50%;
    }

    .w-85 {
        width: 85%;
    }

    .w-25 {
        width: 25%;
    }

    .w-75 {
        width: 75%;
    }

    .w-15 {
        width: 15%;
    }

    .logo img {
        width: 60px;
        height: 60px;
    }

    .gray-color {
        color: #5D5D5D;
    }

    .text-bold {
        font-weight: bold;
    }

    .border {
        border: 1px solid black;
    }

    table tr,
    th,
    td {
        border: 1px solid #d2d2d2;
        border-collapse: collapse;
        padding: 7px 8px;
    }

    table tr th {
        background: #F4F4F4;
        font-size: 15px;
    }

    table tr td {
        font-size: 13px;
    }

    table {
        border-collapse: collapse;
    }

    .box-text p {
        line-height: 10px;
    }

    .float-left {
        float: left;
    }

    .text-right {
        text-align: right;
    }

    .total-part {
        font-size: 16px;
        line-height: 12px;
    }

    .total-right p {
        padding-right: 20px;
    }

    .signature-section {
        margin-top: 160px;
        /* Adjust space from other content */
        width: 100%;
    }

    .signature-text {
        /* width: 30%; */
        /* Adjust as needed */
        /* float: right; */
        text-align: right;
        margin-right: 20px;
        /* Space from the right edge */
    }

    .signature-text p {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 5px;
        /* Space between text and line */
    }

    .fotter-line {
        border-top: 1px solid #000;
        /* Black line for signature */
        width: 100%;
        height: 2px;
        margin-top: 70px;
        position: relative;
        top: 5px;
    }


    .signature-underline {
        text-decoration: underline;
        text-decoration-color: #000;
        text-decoration-thickness: 2px;
        text-underline-offset: 2px;
    }

    .gst-footer {
        margin-top: 10px;
        position: absolute;
        bottom: 0;
        width: 100%;
        text-align: center;
        padding: 10px;
        font-size: 14px;
        font-weight: bold;
    }
</style>

<body>
    <div class="head-title">
        <h1 class="text-center m-0 p-0">Invoice</h1>
    </div>
    <table class="table w-100 mt-10" style="border: none;">
        <tr style="border: none;">
            <td class="w-50" style="border: none;">
                <!-- <p class="m-0 pt-5 text-bold w-100">Invoice Id - <span class="gray-color">#1</span></p> -->
                <p class="m-0 pt-5 text-bold w-100">Order Id - <span class="gray-color">{{$order->order_number}}</span>
                </p>
                <!-- <p class="m-0 pt-5 text-bold w-100">Order Date - <span class="gray-color">{{$order->created_at}}</span></p> -->
                <p class="m-0 pt-5 text-bold w-100">
                    Order Date -
                    <span class="gray-color">
                        {{ \Carbon\Carbon::parse($order->created_at)->setTimezone('Asia/Kolkata')->format('d/m/Y h:i A') }}
                    </span>
                </p>
                <p class="m-0 pt-5 text-bold w-100">Invoice Date - <span
                        class="gray-color">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span></p>

            </td>
            <td class="w-50 text-right logo" style="border: none;">
                <img src="{{$logo_url}}" alt="Logo">
            </td>
        </tr>
    </table>
    <div class="table-section bill-tbl w-100 mt-10">
        <table class="table w-100 mt-10">
            <tr>
                <th class="w-50">Sold By</th>
                <th class="w-50">Billing Address</th>
            </tr>
            <tr>
                <td>
                    <div class="box-text">
                        <p>{{$lp_1}}</p>
                        <p>{{$lp_2}}</p>
                        <p>{{$lp_3}}</p>
                        <p>{{$lp_4}}</p>
                    </div>
                </td>
                <td>
                    <div class="box-text">
                        <p>{{$order->name}}</p>
                        <p>{{$order->flat_no}} {{$order->apartment_name}} {{$order->area}} {{$order->landmark}}</p>
                        <p>{{$order->city}} {{$order->pincode}}</p>
                        <p>Contact: {{$order->s_phone}}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <!-- <div class="table-section bill-tbl w-100 mt-10">
    <table class="table w-100 mt-10">
        <tr>
            <th class="w-50">Payment Method</th>
            <th class="w-50">Shipping Method</th>
        </tr>
        <tr>
            <td>Cash On Delivery</td>
            <td>Free Shipping - Free Shipping</td>
        </tr>
    </table>
</div> -->
    <div class="table-section bill-tbl w-100 mt-10">
        <table class="table w-100 mt-10">
            <tr>
                <th>Product Name</th>
                <th>Sub scription</th>
                <th>Day</th>
                <th>Qty</th>
                <th class=" rupee-symbol">MRP per qty(₹)</th>
                <th class=" rupee-symbol">MRP(₹)</th>
                <th class=" rupee-symbol">Disc(₹)</th>
                <th class=" rupee-symbol">Price(₹)</th>
                <th class=" rupee-symbol">Tax(₹)</th>
                <th class=" rupee-symbol">Total(₹)</th>

            </tr>
            <tr align="center">
                <td class="malayalam-text">{{$order->title}} - {{$order->qty_text}}</td>
                <td>{{$subName}}</td>
                <td>{{$deliveryDays}}</td>
                <td>{{$order->qty}}</td>
                <td>{{ number_format($order->mrp, 2) }}</td>
                <td>{{ number_format($order->totalAmount, 2) }}</td>
                <td>{{ number_format($order->discount, 2) }}</td>
                <td>{{ number_format($order->totalPrice, 2) }}</td>
                <td>{{ number_format($order->totalTax, 2) }}</td>
                <td>{{ number_format($order->netAmount, 2) }}</td>
            </tr>

            <tr>
                <td colspan="10">
                    <div class="total-part">
                        <div class="total-left w-75 float-left" align="right">
                            <p>Sub Total</p>
                            <p>Delivery Charge {{ $deliveryDays }} day</p>
                            <p>Total Payable (incl. of delivery charge)</p>
                        </div>
                        <div class="total-right w-25 float-left text-bold" align="right">
                            <!-- Subtotal (order amount minus delivery charge) -->
                            <p class="rupee-symbol">{{ '₹' . number_format($order->netAmount, 2) }}</p>
                            <!-- Delivery Charge -->
                            <p class="rupee-symbol">{{ '₹' . number_format($order->delivery_charge, 2) }}</p>
                            <!-- Total Amount (order amount) -->
                            <p class="rupee-symbol">{{ '₹' . number_format($order->order_amount, 2) }}</p>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="signature-section">
        <div class="signature-text" align="right">
            <p class="signature-underline">Authorized Signatory</p>
        </div>
    </div>
    <div class="fotter-line"></div>
    <div class="gst-footer">
        <p>{{$bp}}</p>
    </div>

</html>