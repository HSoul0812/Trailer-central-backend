<!DOCTYPE html>
<html>

<head>
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 14px;
            font: inherit;
            vertical-align: baseline;
            font-family: Arial, Helvetica, sans-serif;
        }

        .printTable {
            z-index: -55;
            position: absolute;
        }

        .print-inventory {
            padding: 10px;
        }

        .print-inventory-image-container {
            display: -webkit-box;
            -webkit-box-pack: justify;
        }

        .print-inventory>div>div>div {
            max-width: 70%;
        }

        .print-inventory>div>div>img {
            max-width: 300px;
            max-height: 300px;
            margin: 10px;
        }

        .dealership {
            margin-top: 20px;
        }

        .inventory-info {
            margin: 20px 0;
        }

        .clear {
            clear: both;
        }

        p {
            line-height: 25px;
        }

        .print-inventory p {
            margin: 4px 0;
        }

        .print-inventory .dealership {
            margin-bottom: 20px;
        }

        .print-inventory .inventory-info .label,
        .print-inventory .inventory-info .data {
            font-size: 14px;
        }

        .inventory-title {
            border-top: 2px solid black;
            border-bottom: 2px solid black;
            margin: 10px 0px;
            padding: 10px 0px;
        }

        h2 {
            font-size: 1.5em;
            font-weight: bold;
        }

        table.inventory-info {
            width: 100%;
        }

        .inventory-info td {
            border-bottom: 1px dashed black;
        }

        .inventory-info .label {
            display: inline-block;
        }

        .footer-table {
            width: 757px;
            margin: 38px auto;
            border-top: 1px solid black;
            border-left: 1px solid black;
        }

        .footer-table td {
            border: 1px solid black;
            border-width: 0 1px 1px 0;
            padding: 0 5px;
        }

        .footer-table .label {
            font-weight: bold;
        }

        .footer-table .data {
            text-align: right;
            display: block;
        }

        .dealer-email {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
    </style>
    <meta charset="UTF-8" />
</head>

<body>
    @php
        $columns = [['label' => 'Stock#', 'data' => $inventory['stock']]];
        if ($inventory['vin']) {
            $columns[] = ['label' => 'VIN#', 'data' => $inventory['vin']];
        }
        $columns[] = ['label' => 'Year', 'data' => $inventory['year']];
        $columns[] = ['label' => 'Manufacturer', 'data' => $inventory['manufacturer']];

        if ($inventory['width_inches']) {
            $width = $inventory['width_inches'] . '" or ' . ($inventory['width_second'] . "'" ?? '') . ($inventory['width_inches_second'] . '"' ?? '');
            $columns[] = ['label' => 'Width', 'data' => $width];
        }
        if ($inventory['length_inches']) {
            $length = $inventory['length_inches'] . '" or ' . ($inventory['length_second'] . "'" ?? '') . ($inventory['length_inches_second'] . '"' ?? '');
            $columns[] = ['label' => 'Length', 'data' => $length];
        }
        if ($inventory['height_inches']) {
            $height = $inventory['height_inches'] . '" or ' . ($inventory['height_second'] . "'" ?? '') . ($inventory['height_inches_second'] . '"' ?? '');
            $columns[] = ['label' => 'Height', 'data' => $height];
        }
        if ($inventory['weight']) {
            $columns[] = ['label' => 'Weight', 'data' => $inventory['weight']];
        }
        if ($inventory['gvwr']) {
            $columns[] = ['label' => 'GVWR', 'data' => $inventory['gvwr']];
        }
        if ($inventory['payload_capacity']) {
            $columns[] = ['label' => 'Payload', 'data' => $inventory['payload_capacity']];
        }
        if ($color = optional($inventory['attributes']->where('attribute.code', 'color')->first())->value) {
            $columns[] = ['label' => 'Color', 'data' => strtoupper($color)];
        }
        if ($axles = optional($inventory['attributes']->where('attribute.code', 'axles')->first())->value) {
            $columns[] = ['label' => '#Axles', 'data' => $axles];
        }
        if ($inventory['axle_capacity']) {
            $columns[] = ['label' => 'Axle Capacity', 'data' => $inventory['axle_capacity']];
        }
        if ($mileage = optional($inventory['attributes']->where('attribute.code', 'mileage')->first())->value) {
            $columns[] = ['label' => 'Mileage', 'data' => $mileage];
        }
    @endphp
    <div id="singlePrint" style="max-width: 100%" class="d-none d-sm-block printTable print-inventory">
        <div style="border-top: 2px solid #000; border-bottom: 2px solid #000">
            <div class="print-inventory-image-container">
                <div>
                    <div>
                        <div class="dealership">
                            <p class="dealer-name">
                                <strong>{{ $inventory['dealer_location']['name'] }}</strong>
                            </p>
                            <p class="dealer-phone">
                                Phone: {{ $inventory['dealer_location']['phone'] }}
                            </p>
                            <div class="clear"></div>
                            <p class="dealer-address">
                                {{ $inventory['dealer_location']['address'] }}<br>{{ $inventory['dealer_location']['city'] }},
                                {{ $inventory['dealer_location']['region'] }}
                                {{ $inventory['dealer_location']['postal'] }}
                            </p>
                            <p class="dealer-email">
                                Email: {{ $inventory['dealer_location']['email'] }}
                            </p>
                        </div>
                    </div>
                    <div class="clear"></div>
                    @if (count($inventory['images']))
                        <div class="inventory-image">
                            <img src="{{ $inventory['images'][0]['url'] }}" style="max-width: 250px; max-height: 250px">
                        </div>
                    @endif
                </div>
                @if ($logo = $inventory['dealer_logo'])
                    <img src="{{ $logo }}" />
                @endif
            </div>
            <h2 style="border: none" class="inventory-title">
                {!! $inventory['title'] !!}
            </h2>
        </div>
        <div class="clear"></div>
        <table class="inventory-info">
            @foreach (collect($columns)->chunk(3) as $items)
                <tr>
                    @foreach ($items as $item)
                        <td>
                            <span class="label">{{ $item['label'] }}:</span>
                            <span class="data">{{ $item['data'] }}</span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
            <tr>
                <td colspan="3">
                    <span class="label">URL:</span>
                    <span
                        class="data">https://{{ $inventory['dealer']['website']['domain'] }}{{ $inventory['url'] }}</span>
                </td>
            </tr>
        </table>
        <div class="clear"></div>
        <table class="footer-table" cellspacing="0" cellpadding="0">
            @if ($inventory['msrp'])
                <tr>
                    <td><span class="label">MSRP</span></td>
                    <td>
                        <span class="data">${{ number_format($inventory['msrp'], 2) }}</span>
                    </td>
                </tr>
            @endif
            @if ($inventory['price'])
                <tr>
                    <td><span class="label">Price</span></td>
                    <td>
                        <span class="data">${{ number_format($inventory['price'], 2) }}</span>
                    </td>
                </tr>
            @endif
            @if ($inventory['sales_price'])
                <tr>
                    <td><span class="label">Sale Price</span></td>
                    <td>
                        <span class="data">${{ number_format($inventory['sales_price'], 2) }}</span>
                    </td>
                </tr>
            @endif
        </table>
        <div class="clear"></div>
        @if ($inventory['features_count'])
            <h3 class="inventory-title">
                Features
            </h3>
        @endif
        <table class="inventory-info" style="width: 100%;">
            @foreach ($inventory['features'] as $features)
                <tr>
                    @foreach($features as $feature)
                        <td>
                            - <span class="data">{{ $feature->value }}</span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
        <div class="clear"></div>
        <h2>Description</h2>
        @if ($inventory['description_html'])
            <div>{!! $inventory['description_html'] !!}</div>
        @endif
    </div>
</body>

</html>
