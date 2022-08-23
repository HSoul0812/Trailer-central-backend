<style>
    html,
    body {
        margin: 0;
        padding: 0;
        border: 0;
        font-size: 100%;
        font: inherit;
        vertical-align: baseline;
    }

    .printTable {
        z-index: -55;
        position: absolute;
    }

    .print-inventory {
        padding: 10px;
        font-size: 20px;
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

    .inventory-description {
        font-size: 22px;
    }

    .print-inventory p {
        margin: 4px 0;
    }

    .print-inventory .dealership {
        margin-bottom: 20px;
    }

    .print-inventory .inventory-info .label,
    .print-inventory .inventory-info .data {
        word-wrap: break-word;
        font-size: 20px;
    }

    .inventory-title {
        border-top: 2px solid black;
        border-bottom: 2px solid black;
        margin: 10px 0px;
        padding: 10px 0px;
    }

    h2 {
        font-size: 21px;
        font-weight: 700;
    }

    table.inventory-info {
        width: 100%;
    }

    .inventory-info td {
        border-bottom: 1px dashed black;
    }

    .inventory-info .label {
        font-weight: bold;
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
    }
</style>

<div
    id="singlePrint"
    class="d-none d-sm-block printTable print-inventory"
>
    <div style="border-top: 2px solid #000; border-bottom: 2px solid #000">
        <div class="dealership">
            <p class="dealer-name">
                <strong>{{ $inventory['dealer_location']['name'] }}</strong>
            </p>
            <p class="dealer-phone">
                Phone: {{ $inventory['dealer_location']['phone'] }}
            </p>
            <div class="clear"></div>
            <p class="dealer-address">
                {{ $inventory['dealer_location']['address'] }}<br>{{
            $inventory['dealer_location']['city']
          }}, {{ $inventory['dealer_location']['region'] }}
                {{ $inventory['dealer_location']['postal'] }}
            </p>
            <p class="dealer-email">
                Email: {{ $inventory['dealer_location']['email'] }}
            </p>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
        @if(count($inventory['images']))
            <div class="inventory-image">
                <img
                    src="{{$inventory['images'][0]['url']}}"
                    style="max-width: 250px; max-height: 250px"
                >
            </div>
        @endif
        <h2 style="border: none" class="inventory-title">
            {{ $inventory['title'] }}
        </h2>
        <div class="clear"></div>
    </div>
    <table class="inventory-info">
        <tr>
            <td>
                <span class="label">Stock #:</span>
                <span class="data">{{ $inventory['stock'] }}</span>
            </td>
            <td>
                <span class="label">VIN #:</span>
                <span class="data">{{ $inventory['vin'] }}</span>
            </td>
            <td>
                <span class="label">Year:</span>
                <span class="data">{{ $inventory['year'] }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Height:</span>
                <span class="data">
                    @if($inventory['height_inches'])
                        {{$inventory['height_inches']}} or
                        @if($inventory['height_second'])
                            {{$inventory['height_second']}}
                        @endif
                        @if($inventory['height_inches_second'])
                            {{$inventory['height_inches_second']}}
                        @endif
                    @else
                        0
                    @endif
                </span>
            </td>
            <td>
                <span class="label">Width:</span>
                <span class="data">
                    @if($inventory['width_inches'])
                        {{$inventory['width_inches']}} or
                        @if($inventory['width_second'])
                            {{$inventory['width_second']}}
                        @endif
                        @if($inventory['width_inches_second'])
                            {{$inventory['width_inches_second']}}
                        @endif
                    @else
                        0
                    @endif
                </span>
            </td>
            <td>
                <span class="label">Length:</span>
                <span class="data">@if($inventory['length_inches'])
                        {{$inventory['length_inches']}} or
                        @if($inventory['length_second'])
                            {{$inventory['length_second']}}
                        @endif
                        @if($inventory['length_inches_second'])
                            {{$inventory['length_inches_second']}}
                        @endif
                    @else
                        0
                    @endif
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Manufacturer:</span>
                <span class="data">{{
            $inventory['manufacturer']
          }}</span>
            </td>
            <td>
                <span class="label">Weight:</span>
                <span class="data">{{
            $inventory['weight']
          }}</span>
            </td>
            <td>
                <span class="label">GVWR:</span>
                <span class="data">{{
            $inventory['gvwr']
          }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="label">URL:</span>
                <span class="data">https://{{ $inventory['dealer']['website']['domain'] }}{{ $inventory['url'] }}</span>
            </td>
        </tr>
    </table>
    <div class="clear"></div>
    <table class="footer-table" cellspacing="0" cellpadding="0">
        @if($inventory['msrp'])
            <tr>
                <td><span class="label">MSRP</span></td>
                <td>
                    <span class="data">$ {{ $inventory['msrp'] }}</span>
                </td>
            </tr>
        @endif
        @if($inventory['price'])
            <tr>
                <td><span class="label">Original Price</span></td>
                <td>
                    <span class="data">$ {{ $inventory['price'] }}</span>
                </td>
            </tr>
        @endif
        @if($inventory['sales_price'])
            <tr>
                <td><span class="label">Sale Price</span></td>
                <td>
                    <span class="data">$ {{ $inventory['sales_price'] }}</span>
                </td>
            </tr>
        @endif
    </table>
    <div class="clear"></div>
    @if(count($inventory['features']))
        <h3 class="inventory-title">
            Features
        </h3>
    @endif
    <table class="inventory-info" style="width: 100%; font-size: 80%">
        @for($i = 0; $i < ceil(count($inventory['features'])/3); $i++)
            <tr>
                @isset($inventory['features'][($i-1) * 3])
                    <td>
                        - <span class="data">{{ $inventory['features'][($i-1) * 3]['value'] }}</span>
                    </td>
                @endisset
                @isset($inventory['features'][($i-1) * 3 + 1])
                    <td>
                        - <span class="data">{{ $inventory['features'][($i-1) * 3 + 1]['value'] }}</span>
                    </td>
                @endisset
                @isset($inventory['features'][($i-1) * 3 + 2])
                    <td>
                        - <span class="data">{{ $inventory['features'][($i-1) * 3 + 2]['value'] }}</span>
                    </td>
                @endisset
            </tr>
        @endfor
    </table>
    <div class="clear"></div>
    <h2>Description</h2>
    @if($inventory['description'])
        <p class="inventory-description">{!! $inventory['description'] !!}</p>
    @endif
</div>
