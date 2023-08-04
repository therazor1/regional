<div>
    <div style="font-weight:bold;font-size:20px">
        Gracias por usar {$brand}, {$client_name}
    </div>
    <div style="margin-top:10px">
        Esperamos que hayas disfrutado de nuestro servicio.
    </div>

    <div style="margin-top:24px">
        {foreach $prices as $a}
            <div style="overflow:hidden;padding:4px 0;">
                <div style="float:left">{$a.id}</div>
                <div style="float:right;white-space:nowrap">{$a.name}</div>
            </div>
        {/foreach}
        <div style="overflow:hidden;padding:24px 0;margin-top:16px;border-top:1px solid #CCCCCC;
                    font-weight:bold;font-size:24px;">
            <div style="float:left">Total</div>
            <div style="float:right;white-space:nowrap">{$price_total|coin}</div>
        </div>
        <div style="overflow:hidden;padding:8px 0;border-top:1px solid #CCCCCC;
                    font-weight:bold;font-size:14px">
            <div style="float:left">
                <img style="width:28px;float:left" src="{$payment_icon}" alt="payment"/>
                <div style="float:left;margin-top:4px;margin-left:8px">{$payment_name}</div>
            </div>
            <div style="float:right;white-space:nowrap">{$pay_method|coin}</div>
        </div>
        {if $pay_wallet > 0}
            <div style="overflow:hidden;padding:8px 0;border-top:1px solid #CCCCCC;font-weight:bold;font-size:14px">
                <div style="float:left">
                    <img style="width:28px;float:left" src="{$icon_wallet}" alt="wallet"/>
                    <div style="float:left;margin-top:4px;margin-left:8px">Billetera</div>
                </div>
                <div style="float:right;white-space:nowrap">{$pay_wallet|coin}</div>
            </div>
        {/if}
    </div>

    <div style="background:#F8F8FA;padding:20px;border-radius:10px;margin-top:24px">
        <table style="width:100%;border-collapse: collapse;border-spacing:0;font-size:14px">
            <tbody>
            <tr>
                <td colspan="2" style="padding-bottom:16px">
                    Conductor
                </td>
            </tr>
            <tr>
                <td style="width:1%">
                    <img style="height:64px;border-radius:50%" src="{$driver_photo_url}" alt="driver"/>
                </td>
                <td style="padding-left:16px">
                    <div style="font-size:16px;font-weight:bold;margin-top:8px">
                        {$driver_name}
                    </div>
                    <div style="margin-top:4px">
                        <span style="font-weight:bold">{$driver_rating_average}</span> Calificación
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px">

        <span style="display: inline-block; background: black; color:white;height: 24px;line-height: 24px;
                     padding: 0 12px;border-radius: 12px">
            {$product_name}
        </span>

        <span style="margin-left:10px;color:#555">
            {$distance|string_format:"%.1f"} KM - {$duration} MIN
        </span>

    </div>

    <div style="margin-top:16px">

        {foreach key=$i item=$stop from=$stops}
            <div style="padding:10px 0 10px 20px;position:relative">
                <div style="display:inline-block;width: 8px;
                        height: 8px;border-radius:4px;margin-right: 10px;
                        position:absolute;top: 14px; left: 0;
                        background-color: {if $i==0}green{else}red{/if}"></div>
                {$stop->address}
            </div>
        {/foreach}

    </div>

    <div style="margin-top:10px">
        <img src="{$url_map}" alt="map" style="max-width:100%">
    </div>

</div>