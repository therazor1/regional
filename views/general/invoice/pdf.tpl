<div style="font-size:14px">
    <table style="border-collapse: collapse;width: 100%;font-family: Arial, serif;">
        <tr>
            <td>
                <table style="border-collapse: collapse;width: 100%;font-family: Arial, serif;">
                    <tr>
                        <td style="font-size:18px;">
                            <span>FACTURA</span>
                            <strong>{$correlative|doc}</strong>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#999;font-size:13px;padding-top:6px">
                            {$date_created|human_datetime}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;padding-top:16px">{$us_name} {$us_surname}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#999">{$us_phone}</td>
                    </tr>
                    <tr>
                        <td style="font-size:13px;color:#999">{$us_email}</td>
                    </tr>
                </table>
            </td>
            <td style="background:#2D323E;width:360px;color:white;padding:8px" rowspan="5">
                <table style="border-collapse: collapse;width: 100%;font-family: Arial, serif;color:white;">
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

    <table style="border-collapse: collapse;width: 100%;font-family: Arial, serif;margin-top:36px">
        <thead>
        <tr>
            <th style="border-bottom: 1px solid #DDD;font-weight:normal;font-size:12px;padding:8px 0;text-align:left;
                       color:#999">
                DESCRIPCIÓN
            </th>
            <th style="border-bottom: 1px solid #DDD;font-weight:normal;font-size:12px;padding:8px 0;text-align:right;
                       color:#999">
                MONTO
            </th>
        </tr>
        </thead>
        <tbody>
        {foreach $items as $item}
            <tr>
                <td style="border-bottom: 1px solid #DDD;font-size:14px;padding:6px 0;">
                    {$item->name}
                </td>
                <td style="border-bottom: 1px solid #DDD;font-size:14px;text-align:right;">
                    {$item->total|coin}
                </td>
            </tr>
        {/foreach}
        <tr>
            <td style="font-size:24px;padding:16px 0;color:#999">TOTAL</td>
            <td style="font-size:24px;text-align:right;">
                {$total|coin}
            </td>
        </tr>
        </tbody>
    </table>
</div>