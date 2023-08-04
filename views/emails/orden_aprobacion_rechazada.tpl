{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a {$nombre},
    </div>
    <div style="margin-top:24px">
        La orden <b>{$orden->correlativo}</b> fue rechazada.
    </div>
    <div style="margin-top:24px">
        Motivo: {$motivo}
    </div>
    <div style="margin-top:24px">
        <a style="background:#039be5;padding:0 20px;color:#FFFFFF!important;border-radius:20px;text-decoration:none;
            display:inline-block; height:40px; font-size:14px; font-weight:bold; line-height:40px;"
           href="{$stg->url_web}">
            MÁS INFORMACIÓN
        </a>
    </div>
{/block}