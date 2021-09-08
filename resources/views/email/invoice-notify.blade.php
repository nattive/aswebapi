<html>

<body
    style="background-color:#e2e1e0;font-family: Open Sans, sans-serif;font-size:100%;font-weight:400;line-height:1.4;color:#000;">
    <table
        style="max-width:670px;margin:50px auto 10px;background-color:#fff;padding:25px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;-webkit-box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);-moz-box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24); border-top: solid 10px green;">
        <thead>
            <tr>
                <th style="text-align:left;">
                    {{ $invoiceEmail['body'] }}
                </th>
                <th style="text-align:right;font-weight:400;">
                    {{ date('dS F Y', strtotime($invoiceEmail['invoice']->created_at)) }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="height:35px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: rgb(236, 235, 235); padding:10px 20px;">
                    <p style="font-size:12px;margin:0 0 6px 0;"><span
                            style="font-weight:bold;display:inline-block;min-width:150px">Generated By</span>
                        {{ $invoiceEmail['invoice']->generated_by?->name }}</p>
                    <p style="font-size:12px;margin:0 0 6px 0;"><span
                            style="font-weight:bold;display:inline-block;min-width:146px">Invoice Code</span>
                        {{ $invoiceEmail['invoice']->code }}</p>
                    <p style="font-size:12px;margin:0 0 0 0;"><span
                            style="font-weight:bold;display:inline-block;min-width:146px">Total amount</span>
                        ₦{{ number_format($invoiceEmail['invoice']->total_amount) }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:14px;padding:30px 15px 0 15px;">Customer Details</td>
            </tr>
            <tr>
                <td style="width:50%;padding:20px;vertical-align:top">
                    <p style="margin:0 0 10px 0;padding:0;font-size:12px;"><span
                            style="display:block;font-weight:bold;font-size:13px">Name</span>
                        {{ $invoiceEmail['invoice']->customer->name }}</p>
                    <p style="margin:0 0 10px 0;padding:0;font-size:12px;"><span
                            style="display:block;font-weight:bold;font-size:13px;">Phone</span>
                        {{ $invoiceEmail['invoice']->customer->phonenumer }}</p>
                </td>
                <td style="width:50%;padding:20px;vertical-align:top">
                    <p style="margin:0 0 10px 0;padding:0;font-size:12px;"><span
                            style="display:block;font-weight:bold;font-size:13px;">Address</span>
                        {{ $invoiceEmail['invoice']->customer->address }}</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:14px;padding:15px 15px 0 15px;">Products</td>
            </tr>
            @foreach ($invoiceEmail['invoiceItems'] as $invoiceItems)
                <tr>
                    <td colspan="2" style="padding:15px;">
                        <p style="font-size:14px;margin:0;padding:10px;background-color: rgba(238, 236, 236, 0.651);font-weight:bold;">
                            <span
                                style="display:block;font-size:13px;font-weight:normal;">{{ $invoiceItems->product->name }}</span>{{ $invoiceItems->amount }}
                            <b style="font-size:12px;font-weight:300;"> x{{ $invoiceItems->qty }}</b>
                        </p>
                    </td>

                </tr>
            @endforeach
        </tbody>
        <footer>
            <tr>
                <td colspan="2" style="font-size:14px;padding:50px 15px 0 15px;">
                    <a style="text-align: center; color: rgb(53, 53, 53)" href="#">Login To view Invoice</a>
                </td>
            </tr>
        </footer>
    </table>
</body>

</html>