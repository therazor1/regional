{extends './layouts/base.tpl'}
{block content}
    <div>
        Hola <span style="font-weight:bold">{$name}</span>,
    </div>
    <div style="margin-top:24px">
        Se ha procedido a crear una cuenta en el SICAM, ingresa al siguiente link para que puedas generar tu contraseña
        de acceso personal:
    </div>
    <div style="margin-top:24px">
        <a style="background:{$stg->color_accent};padding:0 20px;color:#FFFFFF!important;border-radius:20px;
                text-decoration:none;display:inline-block; height:40px; font-size:14px; font-weight:bold;
                line-height:40px;"
           href="{$url}">
            GENERAR CONTRASEÑA
        </a>
    </div>
{/block}