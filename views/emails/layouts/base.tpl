{*Este es la plantilla de CORREO*}
<div style="background:#f2f2f2;font-family:Helvetica,Arial,sans-serif;font-size:12px;">

    <div style="margin:0 auto; max-width:600px;">

        <div style="padding:4px 4px;text-align:center;background-color:{$stg->color_primary}">
            <img style="max-height:50px" src="{$stg->pic_logo|pic}" alt="logo"/>
        </div>

        <div style="background:white; padding:40px;font-size:14px;line-height:20px">
            {block content}{/block}
        </div>

        <div style="padding:24px;background-color:{$stg->color_primary};color:rgba(255,255,255,.8)">
            <div style="font-weight:bold;line-height:18px; font-size:14px">
                No responder
            </div>
            <div style="margin-top:8px;line-height:18px; font-size:14px">
                Este es un correo automático, por favor no responder
            </div>
            <div style="text-align:center;font-size:12px;border-top:1px solid rgba(255,255,255,.1);
                    margin-top:24px;padding-top:24px">
                El equipo de {$stg->brand}
            </div>
        </div>

    </div>

</div>