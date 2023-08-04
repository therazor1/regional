<div class="b-all">
    <table>
        <tr>
            <td class="ctr" style="width:140px">
                <img src="{$stg->pic_logo|pic}" style="max-width:80px">
            </td>
            <td class="b-l bold ctr py-16" style="font-size:18px">CONDICIONES GENERALES DEL CONTRATO</td>
            <td class="b-l bold ctr" style="width:190px">
                F-ICOM03/CO-PE-010000-01 (Revisión 1)
            </td>
        </tr>
    </table>
</div>

<div style="height:16px"></div>

<div class="b-all">

    <table>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">OBRA:</td>
            <td class="b-l px-4 py-2">{$obra->nombre}</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">ÁREA:</td>
            <td class="b-l px-4 py-2">{$area->nombre}</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">Nº CONTRATO:</td>
            <td class="b-l px-4 py-2"></td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">OBJETO DE CONTRATO:</td>
            <td class="b-l px-4 py-2"></td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">PROVEEDOR:</td>
            <td class="b-l px-4 py-2">{$proveedor->nombre}</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">CÓDIGO PROVEEDOR:</td>
            <td class="b-l px-4 py-2">{$proveedor->id}</td>
            <td class="ctt-head px-4 py-2" style="width:140px;">RUC:</td>
            <td class="b-l px-4 py-2">{$proveedor->ruc}</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">FECHA:</td>
            <td class="b-l px-4 py-2">{$orden->fecha_oc|human_date}</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:180px;">NOTAS:</td>
            <td class="b-l px-4 py-2">{$orden->notas}</td>
        </tr>
    </table>

    <div class="b-t"></div>

    <table>
        <tr>
            <td class="px-4 py-2 bold" style="width:220px">PREVISIÓN EN MASTER:</td>
            <td class="b-l px-4 py-2"></td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2 b-t" style="width:220px">MONTO TOTAL A CONTRATAR:</td>
            <td class="b-l px-4 py-2"></td>
        </tr>
    </table>

    <div class="b-t"></div>

    <table class="table-styled">
        <tr>
            <th class="b-b">DESCRIPCIÓN</th>
            <th class="b-b b-l" style="width:100px">UNIDAD DE MEDIDA</th>
            <th class="b-b b-l" style="width:100px">PRECIO UNITARIO</th>
            <th class="b-b b-l" style="width:100px">MEDICIÓN</th>
            <th class="b-b b-l" style="width:100px">IMPORTE</th>
        </tr>
        {foreach $orden_materiales as $i => $om}
            <tr>
                <td>{$om->ma_nombre}</td>
                <td class="b-l ctr">{$om->un_nombre}</td>
                <td class="b-l ctr">{$om->precio_unidad|coin_format}</td>
                <td class="b-l ctr">{$om->sm_cantidad}</td>
                <td class="b-l ctr">{$om->precio_total|coin_format}</td>
            </tr>
        {/foreach}
        <tr>
            <td class="bold" style="text-align:right">SUMA CONTRATO PRINCIPAL</td>
            <td class="b-l"></td>
            <td class="b-l"></td>
            <td class="b-l"></td>
            <td class="b-l ctr">{$orden->monto_total|coin_format}</td>
        </tr>
    </table>

    <div class="b-t"></div>

    <table>
        <tr>
            <td class="ctt-head px-4 py-2 b-t" style="width:220px">TOTAL DE CONTRATO + ADENDAS</td>
            <td class="b-l px-4 py-2 ctr">% SOBRE CONTRATO PRINCIPAL</td>
        </tr>
    </table>

    <div class="b-t"></div>

    <table class="table-styled">
        {foreach $orden_materiales as $i => $om}
            <tr>
                <td>{$om->ma_nombre}</td>
                <td class="b-l ctr">{$om->un_nombre}</td>
                <td class="b-l ctr">{$om->precio_unidad|coin_format}</td>
                <td class="b-l ctr">{$om->sm_cantidad}</td>
                <td class="b-l ctr">{$om->precio_total|coin_format}</td>
            </tr>
        {/foreach}
        <tr>
            <td class="bold" style="text-align:right">SUMA CONTRATO MAS ADENDAS</td>
            <td class="b-l"></td>
            <td class="b-l"></td>
            <td class="b-l"></td>
            <td class="b-l ctr">{$orden->monto_total|coin_format}</td>
        </tr>
    </table>

    <div class="b-t"></div>

    <table>
        <tr>
            <td class="ctt-head px-4 py-2" style="width:220px;">DESCRIPCIÓN DE LOS TRABAJOS:</td>
            <td class="b-l px-4 py-2"></td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2 b-t" style="width:220px;" rowspan="2">POR CUENTA DE OHL:</td>
            <td class="b-l px-4 py-2">COMBUSTIBLE</td>
        </tr>
        <tr>
            <td class="b-l px-4 py-2">OPERADOR</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2 b-t" style="width:220px;" rowspan="3">POR CUENTA DEL PROVEEDOR:</td>
            <td class="b-l px-4 py-2">REPUESTOS</td>
        </tr>
        <tr>
            <td class="b-l px-4 py-2">MANTENIMIENTO</td>
        </tr>
        <tr>
            <td class="b-l px-4 py-2">POLIZA VEHICULAR Y RC</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2 b-t" style="width:220px;">FORMA DE MEDICIÓN:</td>
            <td class="b-l px-4 py-2">DÍA</td>
        </tr>
        <tr>
            <td class="ctt-head px-4 py-2 b-t" style="width:220px;">SE ADJUNTA:</td>
            <td class="b-l px-4 py-2">DOCUMENTOS EMPRESA Y UNIDAD</td>
        </tr>
    </table>
</div>

<div style="height:16px"></div>

<div class="b-all">
    <table class="table-styled">
        <tr>
            <td class="bold" rowspan="2" style="width:260px;background:#DDD">FECHA DE CONTRATO + ADENDAS:</td>
            <td class="b-l bold">FECHA DE INICIO</td>
            <td></td>
            <td class="bold">DURACIÓN</td>
            <td>   MESES</td>
        </tr>
        <tr>
            <td class="b-l bold">FECHA DE TÉRMINO</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</div>

<div style="height:16px"></div>

<div class="b-all">
    <table class="table-styled">
        <tr>
            <td class="ctt-head" rowspan="3">ANTICIPO:</td>
            <td class="bold">SI</td>
            <td class="b-g-l"></td>
            <td class="b-g-l bold">PORCENTAJE</td>
            <td class="b-g-l text-right">%</td>
        </tr>
        <tr>
            <td class="b-g-t bold">NO</td>
            <td class="b-g-l b-g-t"></td>
            <td class="b-g-l b-g-t"></td>
            <td class="b-g-l b-g-t"></td>
        </tr>
        <tr>
            <td class="b-g-t bold">AMORTIZACIÓN</td>
            <td class="b-g-l b-g-t text-right">%</td>
            <td class="b-g-l b-g-t bold">CARTA FIANZA</td>
            <td class="b-g-l b-g-t">NO</td>
        </tr>
        <tr>
            <td class="ctt-head b-g-t">FORMA DE PAGO:</td>
            <td class="b-g-t" colspan="2">{$orden->forma_pago_txt}</td>
            <td class="ctt-head">PLAZO DE PAGO:</td>
            <td class="b-g-l b-g-t"> DIAS</td>
        </tr>
        <tr>
            <td class="ctt-head b-g-t">PENALIZACIONES:</td>
            <td class="b-g-t" colspan="4"></td>
        </tr>
        <tr>
            <td class="ctt-head b-g-t" rowspan="2">RETENCIÓN:</td>
            <td class="b-g-t">NO</td>
            <td class="b-g-l b-g-t"></td>
            <td class="b-g-l b-g-t"></td>
            <td class="b-g-l b-g-t"></td>
        </tr>
        <tr>
            <td class="b-g-t">SI</td>
            <td class="b-g-l b-g-t"></td>
            <td class="b-g-l b-g-t">PORCENTAJE</td>
            <td class="b-g-l b-g-t text-right">%</td>
        </tr>
        <tr>
            <td class="ctt-head b-g-t">DEVOLUCIÓN DE LA RETENCIÓN:</td>
            <td class="b-g-t" colspan="4">Finiquito</td>
        </tr>
    </table>
    <table class="table-styled">
        {foreach $firmas_bloques as $firmas}
            <tr>
                {foreach $firmas as $i => $firma}
                    <td class="b-t ctr {if $i != 0}b-l{/if}" style="width:33.333%;height:80px">
                        {if $firma->aprobado && $firma->us_pic_firma}
                            <img src="{$firma->us_pic_firma|pic}" style="height:50px">
                        {/if}
                    </td>
                {/foreach}
                {for $i = count($firmas) to 2}
                    <td class="b-t b-l"></td>
                {/for}
            </tr>
            <tr>
                {foreach $firmas as $i => $firma}
                    <td class="{if $i != 0}b-l{/if}">
                        Fecha: {if $firma->aprobado}{$firma->fecha_aprobado|human_date}{/if}
                    </td>
                {/foreach}
                {for $i = count($firmas) to 2}
                    <td class="b-l"></td>
                {/for}
            </tr>
            <tr>
                {foreach $firmas as $i => $firma}
                    <td class="b-t {if $i != 0}b-l{/if} bold" style="text-transform:uppercase">
                        {$firma->ro_name}
                    </td>
                {/foreach}
                {for $i = count($firmas) to 2}
                    <td class="b-t b-l"></td>
                {/for}
            </tr>
        {/foreach}
    </table>
</div>