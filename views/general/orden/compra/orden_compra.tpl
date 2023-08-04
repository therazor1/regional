<table class="table-styled">
    <tr>
        <td class="bold py-16" style="font-size:18px">
            <div>ORDEN</div>
            <div>DE COMPRA</div>
            <div>N°: {$orden->correlativo}</div>
        </td>
        <td>
            <table>
                <tr>
                    <td class="bold">Facturar a</td>
                    <td> : {$empresa->nombre}</td>
                </tr>
                <tr>
                    <td class="bold">Rut</td>
                    <td> : {$empresa->rut}</td>
                </tr>
                <tr>
                    <td class="bold">Dirección</td>
                    <td> : {$empresa->direccion}</td>
                </tr>
                <tr>
                    <td class="bold" colspan="3">DESPACHAR A</td>
                </tr>
                <tr>
                    <td class="bold">Obra</td>
                    <td> : {$obra->nombre}</td>
                </tr>
                <tr>
                    <td class="bold">Dirección</td>
                    <td> : {$obra->direccion}</td>
                </tr>
                <tr>
                    <td class="bold">Área</td>
                    <td> :</td>
                </tr>
                <tr>
                    <td class="bold">Contacto</td>
                    <td> : {$obra->contacto_nombre}</td>
                </tr>
                <tr>
                    <td class="bold">Fono</td>
                    <td> : {$obra->contacto_telefono}</td>
                </tr>
            </table>
        </td>
        <td style="width:140px">
            <img src="assets/img/logo_formato_orden.png" style="max-width:140px;">
            {*<img src="{$stg->pic_logo|pic}" style="max-width:140px">*}
        </td>
    </tr>
</table>

<table class="b-t table-styled">
    <tr>
        <td class="bold">Señores</td>
        <td> : {$proveedor->nombre}</td>
        <td class="bold">Fecha O/C</td>
        <td> : {$orden->fecha_oc|human_date}</td>
    </tr>
    <tr>
        <td class="bold">R.U.T.</td>
        <td> : {$proveedor->ruc}</td>
        <td class="bold">Fecha Entrega</td>
        <td> : {$orden->fecha_entrega|human_date}</td>
    </tr>
    <tr>
        <td class="bold">Dirección</td>
        <td> : {$proveedor->direccion}</td>
        <td class="bold">Lugar de entrega</td>
        <td> : {$orden->lugar_entrega}</td>
    </tr>
    <tr>
        <td class="bold">Atención</td>
        <td> : {$contacto_proveedor->name} {$contacto_proveedor->surname}</td>
        <td class="bold">Moneda</td>
        <td> : {$tipo_moneda->nombre}</td>
    </tr>
    <tr>
        <td class="bold">Fono:</td>
        <td> : {$contacto_proveedor->phone}</td>
        <td class="bold">Tipo cambio</td>
        <td> : {$tipo_cambio}</td>
    </tr>
    <tr>
        <td class="bold"></td>
        <td></td>
        <td class="bold">Correo:</td>
        <td> : {$contacto_proveedor->email}</td>
    </tr>
</table>

<div class="b-t">
    <table class="table-styled">
        <tr>
            <th class="b-b" style="width:4.0%">Li</th>
            <th class="b-b" style="width:11.0%">Item</th>
            <th class="b-b">UM</th>
            <th class="b-b">Descripción</th>
            <th class="b-b">P. Uni. ({$tipo_moneda_principal->nombre})</th>
            <th class="b-b">P. Unitario</th>
            <th class="b-b">Cantidad</th>
            <th class="b-b">Total</th>
        </tr>
        {foreach $orden_materiales as $i => $om}
            <tr style="{($i%2==1) ? '' : 'background:#DDD'}">
                <td class="ctr">{$i+1}</td>
                <td class="ctr">{$om->ma_codigo}</td>
                <td class="ctr">{$om->un_nombre}</td>
                <td>{$om->ma_nombre|htmlspecialchars}</td>
                <td class="text-right">{$om->precio_unidad_local|coin_format:$tipo_moneda_principal->decimales}</td>
                <td class="text-right">{$om->precio_unidad|coin_format:$tipo_moneda->decimales}</td>
                <td class="ctr">{$om->cantidad}</td>
                <td class="text-right">{$om->precio_total|coin_format:$tipo_moneda->decimales}</td>
            </tr>
        {/foreach}
    </table>
</div>

<div class="b-t div pt-48">
    <div class="b-all p-4" style="width:360px;margin:auto">
        <div>Notas:</div>
        <div>{$orden->notas|nl2br}</div>
    </div>
</div>

{* Alto del FOOTER (totales) para no meter contenido por debajo *}
<div style="height:295px"></div>