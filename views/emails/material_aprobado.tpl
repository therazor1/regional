{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a: {$name} {$surname}
    </div>
    <div style="margin-top:24px">
        El material requerido se ha ingresado al catálogo de materiales con el siguiente detalle :
    </div>
    <div style="margin-top:24px">
        <div>CÓDIGO ALMACÉN: -</div>
        <div>DESCRIPCIÓN MATERIAL: {$descripcion}</div>
        <br>
        <div>FAMILIA</div>
    </div>
    <div style="margin-top:24px">
        <div>Atentamente,</div>
        <div>Administrador del Catálogo de materiales</div>
    </div>
{/block}