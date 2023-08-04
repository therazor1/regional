{extends './layouts/base.tpl'}
{block content}
    <div>
        Hola <span style="font-weight:bold">{$name}</span>,
    </div>
    <div style="margin-top:24px">
        {$stg->brand} lo invita a formar parte de su red de proveedores. Haga clic en el siguiente botón para continuar.
    </div>
    <div style="margin-top:24px">
        <a style="background:{$stg->color_accent};padding:0 20px;color:#FFFFFF!important;border-radius:20px;
                text-decoration:none;display:inline-block; height:40px; font-size:14px; font-weight:bold;
                line-height:40px;"
           href="{$url}">
            INGRESAR
        </a>
    </div>
    <div style="margin-top:24px">
        <p>Datos de acceso</p>
        <p>
            <b>Email:</b> {$email}
            <br>
            <b>Clave:</b> {$password}
        </p>
    </div>
{/block}