<div class="b-all">

    <table>
        <tr>
            <td class="ctr" style="width:140px">
                <img src="{$stg->pic_logo|pic}" style="max-width:80px">
            </td>
            <td class="b-l bold ctr py-16" style="font-size:18px">ORDEN SERVICIO</td>
            <td class="b-l bold ctr" style="width:190px">
                F-ICOM03/CO-PE-010000-01 (Revisión 1)
            </td>
        </tr>
    </table>

    <div class="b-t">
        <table>
            <tr>
                <td>
                    <div class="bold">ORDEN SERVICIO</div>
                    <div>{$orden->correlativo}</div>
                </td>
                <td>
                    <table>
                        <tr>
                            <td class="bold">Facturar a:</td>
                            <td>OBRASCON HUARTE LAIN S.A. SUC. DEL PERU</td>
                        </tr>
                        <tr>
                            <td class="bold">RUC:</td>
                            <td>20425123115</td>
                        </tr>
                        <tr>
                            <td class="bold">Dirección:</td>
                            <td>AV. 28 DE JULIO NRO. 150 DPTO. 7MO URB. MIRAFLORES LIMA - LIMA - MIRAFLORES</td>
                        </tr>
                        <tr>
                            <td class="bold">Obra:</td>
                            <td>{$obra->nombre}</td>
                        </tr>
                        <tr>
                            <td class="bold">Dirección Obra:</td>
                            <td>{$obra->direccion}</td>
                        </tr>
                        <tr>
                            <td class="bold">Área:</td>
                            <td>{$area->nombre}</td>
                        </tr>
                        <tr>
                            <td class="bold">Requerimiento:</td>
                            <td>
                                {foreach $solicitudes as $so}
                                    <div>{$so->codigo_requerimiento}</div>
                                {/foreach}
                            </td>
                        </tr>
                        <tr>
                            <td class="bold">Centro de Costo:</td>
                            <td>{$area->nombre}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="b-t">
        <table>
            <tr>
                <td class="bold">Señores</td>
                <td>{$proveedor->nombre}</td>
                <td class="bold">R.U.C</td>
                <td>{$proveedor->ruc}</td>
            </tr>
            <tr>
                <td class="bold">Dirección</td>
                <td>{$proveedor->direccion}</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="bold">Atención</td>
                <td>
                    {foreach $obra_usuarios as $o}
                        <div>{$o->name} {$o->surname}</div>
                    {/foreach}
                </td>
                <td class="bold">Fecha O/C</td>
                <td>{$orden->fecha_oc|human_date}</td>
            </tr>
            <tr>
                <td class="bold">Teléfono</td>
                <td>
                    {foreach $obra_usuarios as $o}
                        <div>{$o->phone}</div>
                    {/foreach}
                </td>
                <td class="bold">Fecha Entrega:</td>
                <td>{$orden->fecha_entrega|human_date}</td>
            </tr>
            <tr>
                <td class="bold">E-mail</td>
                <td>
                    {foreach $obra_usuarios as $o}
                        <div>{$o->email}</div>
                    {/foreach}
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td class="bold">Notas</td>
                <td>{$orden->notas}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="b-t" style="height:14px"></div>

    <div class="b-t">
        <table class="table-styled">
            <tr>
                <th class="b-b">Item</th>
                <th class="b-b">Cantidad</th>
                <th class="b-b">Unidad</th>
                <th class="b-b">Descripción</th>
                <th class="b-b" style="width:80px">T. Entrega</th>
                <th class="b-b">P. Unitario</th>
                <th class="b-b">Descto.</th>
                <th class="b-b">P. Total</th>
            </tr>
            <tr>
                <td class="ctr"></td>
                <td class="b-l ctr"></td>
                <td class="b-l ctr"></td>
                <td class="b-l"></td>
                <td class="ctr">(DIAS UTILES)</td>
                <td class="b-l ctr">{$tipo_moneda->codigo}</td>
                <td class="b-l ctr"></td>
                <td class="b-l ctr">{$tipo_moneda->codigo}</td>
            </tr>
            {foreach $orden_materiales as $i => $om}
                <tr>
                    <td class="ctr">{$i+1}</td>
                    <td class="b-l ctr">{$om->sm_cantidad}</td>
                    <td class="b-l ctr">{$om->un_nombre}</td>
                    <td class="b-l">
                        <div>{$om->so_codigo_requerimiento}</div>
                        <div>{$om->ma_nombre}</div>
                    </td>
                    <td class="ctr">{$om->cm_plazo_entrega}</td>
                    <td class="b-l ctr">{$om->precio_unidad|coin_format}</td>
                    <td class="b-l ctr">{'0'|coin_format}</td>
                    <td class="b-l ctr">{$om->precio_total|coin_format}</td>
                </tr>
            {/foreach}
            <tr>
                <td></td>
                <td class="b-l"></td>
                <td class="b-l"></td>
                <td class="b-l" colspan="2">
                    <div style="height:100px"></div>
                    <div>Nota: Solo se valorizará lo realmente suministrado / efectuado.</div>
                    {foreach $solicitudes as $so}
                        <div class="ctr">{$so->detalle_trabajo}</div>
                    {/foreach}
                    <div class="bold pt-8">OHL ES AGENTE DE RETENCIÓN A PARTIR DEL 1º DE SETIEMBRE DEL 2010.</div>
                </td>
                <td class="b-l"></td>
                <td class="b-l"></td>
                <td class="b-l"></td>
            </tr>
            <tr>
                <td></td>
                <td class="b-l"></td>
                <td class="b-l">Notas:</td>
                <td class="b-l b-t pt-48" colspan="2">
                    <div>1.- Indicar Nº de orden de compra en factura.</div>
                    <div>2.- Entregar Factura y Guía de remisión.</div>
                    <div>3.- Sellar y firmar orden de compra en el recuadro de conformidad del proveedor.</div>
                    <div>4.- Comprador: {$user->name} {$user->surname}</div>
                </td>
                <td class="b-l"></td>
                <td class="b-l"></td>
                <td class="b-l"></td>
            </tr>
            <tr>
                <td></td>
                <td class="b-l"></td>
                <td class="b-l"></td>
                <td class="b-l b-t pt-48" colspan="2">
                    <div class="bold">CONDICIONES DE LA COMPRA:</div>
                    <div>A.- Pago de factura -60 días fecha de factura</div>
                    <div>B.- El precio incluye entrega en Lima en punto donde indique OHL</div>
                    <div>C.- Plazo de entrega: 3 semanas desde recepción OC</div>
                    <div>Nota: Queda establecido que el pago efectivo de la factura por parte de OHL, se efecturará solo
                        los
                        días 10 o 25 de cada mes, posteriores al cumplimiento del plazo pactado. En ese sentido, será
                        aplicable
                        la fecha más próxima.
                    </div>
                </td>
                <td class="b-l"></td>
                <td class="b-l"></td>
                <td class="b-l"></td>
            </tr>
            <tr>
                <td class="b-t" colspan="5" style="padding:unset">
                    <table>
                        <tr>
                            <td>
                                <div>No se cancelará la factura que no cumpla con los siguientes requisitos:</div>
                                <div>1.- Recepción del 100% conforme de los itemes estipulados en la factura</div>
                                <div>2.- Remitir factura a la oficina según la dirección se indica en la parte
                                    superior.
                                </div>
                                <div>- Copia de la Orden Compra.</div>
                                <div>- Guía de Despacho y firma recepción de materiales y/o aprobación de los trabajos
                                    por parte de OHL
                                </div>
                                <div>3.- El suministro de lo solicitado en la presente O/C, se entiende como la
                                    aceptación por parte del
                                    proveedor de todas las condiciones de esta O/C
                                </div>
                                <div class="ctr">Se considera de cumplimiento obligatorio para los trámites oportunos,
                                    el cumplimiento por parte del
                                    proveedor de las indicaciones dadas en el documento INSTRUCCIONES DE ÓRDENES el cual
                                    forma parte de la
                                    presente orden.
                                </div>
                            </td>
                            <td class="b-l">
                                <div class="bold">CONFORMIDAD DEL PROVEEDOR</div>
                                <br><br><br><br><br><br><br><br>
                                <div class="bold">FECHA:</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="b-l b-t"></td>
                <td class="b-l b-t">
                    <div>Neto {$tipo_moneda->codigo}</div>
                    <br>
                    <div>{$tipo_impuesto->porcentaje}% {$tipo_impuesto->codigo}</div>
                    <br>
                    <div>Total {$tipo_moneda->codigo}</div>
                </td>
                <td class="b-l b-t ctr">
                    <div>{$orden->monto_base|coin_format}</div>
                    <br>
                    <div>{$orden->monto_impuesto|coin_format}</div>
                    <br>
                    <div>{$orden->monto_total|coin_format}</div>
                </td>
            </tr>
        </table>
    </div>

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

    <div class="b-t">
        <table class="table-styled">
            <tr>
                <td>
                    <div>Lugar de Entrega:</div>
                    <div>Forma de Pago:</div>
                    <div>No de cuenta:</div>
                </td>
                <td>
                    Atención:
                </td>
            </tr>
        </table>
    </div>

</div>