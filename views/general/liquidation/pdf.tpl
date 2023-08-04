<table>
    <tr>
        <td>
            <table>
                <tr>
                    <td style="font-size:18px;">
                        <span>LIQUIDACIÓN</span>
                        <strong>{$liq->id|doc}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="color:#999;font-size:13px;padding-top:6px">
                        {$liq->date_created|human_datetime}
                    </td>
                </tr>
                <tr>
                    <td style="font-size:13px;padding-top:16px">{$liq->us_name} {$liq->us_surname}</td>
                </tr>
                <tr>
                    <td style="font-size:13px;color:#999">{$liq->us_phone}</td>
                </tr>
                <tr>
                    <td style="font-size:13px;color:#999">{$liq->us_email}</td>
                </tr>
            </table>
        </td>
        <td style="background:#2D323E;width:360px;color:white;padding:8px" rowspan="5">
            <table style="color:white">
                <tr>
                    <td rowspan="5">
                        <img src="{$stg->pic_logo|pic}" style="max-width:80px">
                    </td>
                    <td>{$stg->name}</td>
                </tr>
                <tr>
                    <td style="font-size:13px;padding-top:4px">{$stg->address}</td>
                </tr>
                <tr>
                    <td style="font-size:13px;padding-top:4px">{$stg->phone}</td>
                </tr>
                <tr>
                    <td style="font-size:13px;padding-top:4px">{$stg->email}</td>
                </tr>
                <tr>
                    <td style="font-size:13px;padding-top:4px">{$stg->website}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="table-border" style="margin-top:36px">
    <thead>
    <tr>
        <th style="font-weight:normal;font-size:12px;padding:8px 0;text-align:left;color:#999">
            DESCRIPCIÓN
        </th>
        <th style="font-weight:normal;font-size:12px;padding:8px 0;text-align:right;color:#999">
            MONTO
        </th>
    </tr>
    </thead>
    <tbody>
    {foreach $liq->items as $item}
        <tr>
            <td style="font-size:14px;padding:6px 0;">{$item->name}</td>
            <td style="font-size:14px;text-align:right;">
                {$item->amount|coin}
            </td>
        </tr>
    {/foreach}
    <tr>
        <td style="font-size:24px;padding:16px 0;color:#999">TOTAL</td>
        <td style="font-size:24px;text-align:right;">
            {$liq->amount|coin}
        </td>
    </tr>
    </tbody>
</table>