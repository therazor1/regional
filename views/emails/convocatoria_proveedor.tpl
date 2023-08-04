{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a: {$nombre}
    </div>
    <div style="margin-top:24px">
        Su representada ha sido invitada a formar parte de la base de datos de proveedores y subcontratistas de OHL.
        Su usuario y la clave de ingreso son las siguientes:
    </div>
    <div style="margin-top:24px">
        <a href="{$stg->url_proveedores}">Ver invitación</a>
    </div>
    <div style="margin-top:24px">
        <div>Atentamente,</div>
        <div>OHL</div>
    </div>
{/block}