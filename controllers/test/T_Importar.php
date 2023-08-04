<?php namespace Controllers\test;

use Models\Equipo;
use Models\Familia;
use Models\RubroEquipo;
use Models\Subfamilia;

class T_Importar extends _controller
{

    public function familias()
    {
        return rsp('anulado');

        $familias = [
            ['tipo' => 'S', 'codigo' => '00', 'nombre' => 'SERVICIOS OFICINA'],
            ['tipo' => 'S', 'codigo' => '01', 'nombre' => 'REPARACIÓN Y MANTENCIÓN'],
            ['tipo' => 'S', 'codigo' => '02', 'nombre' => 'ARRIENDOS'],
            ['tipo' => 'S', 'codigo' => '03', 'nombre' => 'SUBCONTRATOS, SERVICIOS E INSTALADORES'],
            ['tipo' => 'S', 'codigo' => '04', 'nombre' => 'ESTUDIOS Y PROYECTOS'],
            ['tipo' => 'S', 'codigo' => '05', 'nombre' => 'CERTIFICACIONES'],
            ['tipo' => 'M', 'codigo' => '00', 'nombre' => 'MATERIALES OFICINA'],
            ['tipo' => 'M', 'codigo' => '01', 'nombre' => 'MAQUINARIA Y EQUIPOS'],
            ['tipo' => 'M', 'codigo' => '02', 'nombre' => 'REPUESTOS, LUBRICANTES Y COMBUSTIBLES'],
            ['tipo' => 'M', 'codigo' => '03', 'nombre' => 'SEGURIDAD PERSONAL'],
            ['tipo' => 'M', 'codigo' => '04', 'nombre' => 'INSTALACIÓN DE FAENAS Y CIERRES'],
            ['tipo' => 'M', 'codigo' => '05', 'nombre' => 'FERRETERIA Y FIJACIONES'],
            ['tipo' => 'M', 'codigo' => '06', 'nombre' => 'ELECTRICIDAD Y CLIMATIZACIÓN'],
            ['tipo' => 'M', 'codigo' => '07', 'nombre' => 'SANITARIOS'],
            ['tipo' => 'M', 'codigo' => '08', 'nombre' => 'MADERAS'],
            ['tipo' => 'M', 'codigo' => '09', 'nombre' => 'PINTURAS'],
            ['tipo' => 'M', 'codigo' => '10', 'nombre' => 'HORMIGONES'],
            ['tipo' => 'M', 'codigo' => '11', 'nombre' => 'PREFABRICADOS DE HORMIGÓN'],
            ['tipo' => 'M', 'codigo' => '12', 'nombre' => 'ACEROS'],
            ['tipo' => 'M', 'codigo' => '13', 'nombre' => 'ÁRIDOS'],
            ['tipo' => 'M', 'codigo' => '14', 'nombre' => 'PAVIMENTOS'],
            ['tipo' => 'M', 'codigo' => '15', 'nombre' => 'RECUBRIMIENTOS Y GEOTEXTILES'],
            ['tipo' => 'M', 'codigo' => '16', 'nombre' => 'PUERTAS Y VENTANAS'],
            ['tipo' => 'M', 'codigo' => '17', 'nombre' => 'GOMAS Y PLÁSTICOS'],
            ['tipo' => 'M', 'codigo' => '18', 'nombre' => 'IMPLEMENTACION VIAL'],
            ['tipo' => 'M', 'codigo' => '19', 'nombre' => 'SEÑALETICA'],
            ['tipo' => 'M', 'codigo' => '20', 'nombre' => 'COMUNICACIONES'],
            ['tipo' => 'M', 'codigo' => '21', 'nombre' => 'INSTRUMENTOS DE MEDICIÓN'],
            ['tipo' => 'M', 'codigo' => '22', 'nombre' => 'LABORATORIO'],
            ['tipo' => 'M', 'codigo' => '23', 'nombre' => 'AISLANTES Y JUNTAS'],
            ['tipo' => 'M', 'codigo' => '24', 'nombre' => 'GASES Y SOLDADURAS'],
            ['tipo' => 'M', 'codigo' => '25', 'nombre' => 'ELEVADORES'],
            ['tipo' => 'M', 'codigo' => '26', 'nombre' => 'MOBILIARIO Y EQUIPAMIENTO'],
        ];

        foreach ($familias as &$fa) {
            $codigo_gen = $fa['tipo'] . $fa['codigo'];

            $familia = Familia::find('codigo_gen', $codigo_gen);
            if ($familia->exist()) {
                $fa['ok'] = false;
                $fa['msg'] = 'existe';
            } else {
                $familia->data('tipo', $fa['tipo']);
                $familia->data('codigo', $fa['codigo']);
                $familia->data('codigo_gen', $codigo_gen);
                $familia->data('nombre', $fa['nombre']);
                if ($familia->create()) {
                    $fa['ok'] = true;
                } else {
                    $fa['ok'] = false;
                    $fa['msg'] = 'error interno';
                }
            }

        }

        return $familias;
    }

    public function subfamilias()
    {
        return rsp('anulado');
        $subfamilias = [
            ['fa_codigo_gen' => 'S00', 'codigo' => '01', 'nombre' => 'VIAJES'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '02', 'nombre' => 'ALOJAMIENTO'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '03', 'nombre' => 'COMIDAS Y EVENTOS'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '04', 'nombre' => 'PRENSA, PUBLICIDAD Y SUSCRIPCIONES'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '05', 'nombre' => 'MENSAJERÍA, ENVÍOS'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '06', 'nombre' => 'SERVICIOS COMUNICACIONES'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '07', 'nombre' => 'RECURSOS HUMANOS - CAPACITACIONES'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '08', 'nombre' => 'FLETES Y MUDANZAS'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '09', 'nombre' => 'REFORMAS Y MANTENCIONES'],
            ['fa_codigo_gen' => 'S00', 'codigo' => '10', 'nombre' => 'CONSULTORÍAS'],
            ['fa_codigo_gen' => 'S01', 'codigo' => '01', 'nombre' => 'REPARACIÓN MAQUINARIA'],
            ['fa_codigo_gen' => 'S01', 'codigo' => '02', 'nombre' => 'REPARACIÓN VEHÍCULOS LIVIANOS'],
            ['fa_codigo_gen' => 'S01', 'codigo' => '03', 'nombre' => 'REPARACIÓN INFORMÁTICA'],
            ['fa_codigo_gen' => 'S01', 'codigo' => '04', 'nombre' => 'MANTENCIONES'],
            ['fa_codigo_gen' => 'S01', 'codigo' => '05', 'nombre' => 'FLETES Y TRANSPORTE DE EQUIPOS'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '01', 'nombre' => 'ARRIENDO VEHÍCULOS'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '02', 'nombre' => 'ARRIENDO MAQUINARIA'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '03', 'nombre' => 'ARRIENDO EQUIPOS TOPOGRÁFICOS'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '04', 'nombre' => 'ARRIENDO EQUIPOS AUXILIARES'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '05', 'nombre' => 'ARRIENDO CONTENEDORES'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '06', 'nombre' => 'ARRIENDO PUNTALES - ALZAPRIMAS'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '07', 'nombre' => 'ARRIENDO ANDAMIOS'],
            ['fa_codigo_gen' => 'S02', 'codigo' => '08', 'nombre' => 'ARRIENDO MOLDAJES'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '01', 'nombre' => 'SUBCONTRATISTAS'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '02', 'nombre' => 'PINTORES'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '03', 'nombre' => 'INSTALADORES'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '04', 'nombre' => 'VIGILANCIA'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '05', 'nombre' => 'ASEO'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '06', 'nombre' => 'SERVICIOS LABORATORIO'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '07', 'nombre' => 'SERVICIOS MEDIO AMBIENTE Y ARQUEOLOGÍA'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '08', 'nombre' => 'SERVICIOS AFECTADOS'],
            ['fa_codigo_gen' => 'S03', 'codigo' => '09', 'nombre' => 'OTROS SERVICIOS'],
            ['fa_codigo_gen' => 'S04', 'codigo' => '01', 'nombre' => 'ESTUDIO PROYECTO - INGENIERÍA'],
            ['fa_codigo_gen' => 'S04', 'codigo' => '02', 'nombre' => 'CUBICACIONES'],
            ['fa_codigo_gen' => 'S04', 'codigo' => '03', 'nombre' => 'ASESORÍAS TÉCNICAS'],
            ['fa_codigo_gen' => 'S04', 'codigo' => '04', 'nombre' => 'OTROS ESTUDIOS'],
            ['fa_codigo_gen' => 'S05', 'codigo' => '01', 'nombre' => 'CERTIFICACIONES'],
            ['fa_codigo_gen' => 'M00', 'codigo' => '01', 'nombre' => 'MATERIALES DE OFICINA'],
            ['fa_codigo_gen' => 'M00', 'codigo' => '02', 'nombre' => 'ARTICULOS DE ASEO'],
            ['fa_codigo_gen' => 'M00', 'codigo' => '03', 'nombre' => 'ABARROTES Y CAFETERÍA'],
            ['fa_codigo_gen' => 'M00', 'codigo' => '04', 'nombre' => 'INFORMÁTICA'],
            ['fa_codigo_gen' => 'M00', 'codigo' => '05', 'nombre' => 'IMPRENTA Y PAPELERIA'],
            ['fa_codigo_gen' => 'M00', 'codigo' => '06', 'nombre' => 'MOBILIARIO OFICINA'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '01', 'nombre' => 'VEHÍCULOS LIVIANOS'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '02', 'nombre' => 'CAMIONES'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '03', 'nombre' => 'MAQ. MOVIMIENTO DE TIERRA'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '04', 'nombre' => 'MANIPULADORAS'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '05', 'nombre' => 'GRÚAS'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '06', 'nombre' => 'COMPRESORES'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '07', 'nombre' => 'GENERADORES'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '08', 'nombre' => 'BOMBAS'],
            ['fa_codigo_gen' => 'M01', 'codigo' => '09', 'nombre' => 'EQUIPOS ILUMINACIÓN'],
            ['fa_codigo_gen' => 'M02', 'codigo' => '01', 'nombre' => 'REPUESTOS MAQUINARIA'],
            ['fa_codigo_gen' => 'M02', 'codigo' => '02', 'nombre' => 'REPUESTOS VEHÍCULOS LIVIANOS'],
            ['fa_codigo_gen' => 'M02', 'codigo' => '03', 'nombre' => 'REPUESTOS INFORMÁTICOS'],
            ['fa_codigo_gen' => 'M02', 'codigo' => '04', 'nombre' => 'LUBRICANTES'],
            ['fa_codigo_gen' => 'M02', 'codigo' => '05', 'nombre' => 'COMBUSTIBLES'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '01', 'nombre' => 'PROTECCIÓN CABEZA'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '02', 'nombre' => 'PROTECCIÓN PIES'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '03', 'nombre' => 'PROTECCIÓN MANOS'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '04', 'nombre' => 'PROTECCIÓN VISUAL'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '05', 'nombre' => 'PROTECCIÓN CAÍDAS'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '06', 'nombre' => 'ROPA DE SEGURIDAD'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '07', 'nombre' => 'PROTECCIÓN AUDITIVA'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '08', 'nombre' => 'PROTECCIÓN RESPIRATORIA'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '09', 'nombre' => 'PROTECCIÓN PIEL'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '10', 'nombre' => 'TEST EXÁMENES'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '11', 'nombre' => 'CAMILLAS Y PRENSAS'],
            ['fa_codigo_gen' => 'M03', 'codigo' => '12', 'nombre' => 'CABLES DE ACERO, ESTROBOS,'],
            ['fa_codigo_gen' => 'M04', 'codigo' => '01', 'nombre' => 'CONTENEDORES'],
            ['fa_codigo_gen' => 'M04', 'codigo' => '02', 'nombre' => 'BAÑOS QUÍMICOS'],
            ['fa_codigo_gen' => 'M04', 'codigo' => '03', 'nombre' => 'CERCOS Y CIERRE PERIMETRAL'],
            ['fa_codigo_gen' => 'M04', 'codigo' => '04', 'nombre' => 'ALAMBRES PUA, CONCERTINA'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '01', 'nombre' => 'DISCOS Y BROCAS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '02', 'nombre' => 'ABRAZADERAS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '03', 'nombre' => 'GOMAS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '04', 'nombre' => 'CERRAJERIA'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '05', 'nombre' => 'CEMENTO'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '06', 'nombre' => 'ESCALERAS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '07', 'nombre' => 'HERRAMIENTAS MANUALES'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '08', 'nombre' => 'ELEMENTOS DE MEDICIÓN'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '09', 'nombre' => 'HERRAMIENTA ELÉCTRICAS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '10', 'nombre' => 'INSUMOS FERRETERÍA'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '11', 'nombre' => 'ADHESIVOS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '12', 'nombre' => 'CLAVOS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '13', 'nombre' => 'CLAVOS DE DISPARO'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '14', 'nombre' => 'TORNILLOS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '15', 'nombre' => 'PERNOS, TUERCAS, GOLILLAS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '16', 'nombre' => 'TARUGOS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '17', 'nombre' => 'MANGUITOS'],
            ['fa_codigo_gen' => 'M05', 'codigo' => '18', 'nombre' => 'TACOS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '01', 'nombre' => 'CABLES ELÉCTRICOS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '02', 'nombre' => 'LUMINARIAS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '03', 'nombre' => 'BANDEJAS Y ESCALERILLAS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '04', 'nombre' => 'POSTES'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '05', 'nombre' => 'ENCHUFES, TOMAS E INTERRUPTORES'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '06', 'nombre' => 'TRANSFORMADORES, UPS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '07', 'nombre' => 'EQUIPOS ELÉCTRICOS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '08', 'nombre' => 'GENERADORES'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '09', 'nombre' => 'INSUMOS ELÉCTRICOS'],
            ['fa_codigo_gen' => 'M06', 'codigo' => '10', 'nombre' => 'EQUIPOS DE CLIMATIZACIÓN'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '01', 'nombre' => 'ARTÍCULOS SANITARIOS'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '02', 'nombre' => 'GRIFERIA'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '03', 'nombre' => 'FITTING'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '04', 'nombre' => 'CAÑERIAS COBRE'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '05', 'nombre' => 'TUBOS PVC'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '06', 'nombre' => 'TUBO HDPE'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '07', 'nombre' => 'TAPAS Y REJILLAS'],
            ['fa_codigo_gen' => 'M07', 'codigo' => '08', 'nombre' => 'CALEFONT, TERMOS'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '01', 'nombre' => 'PINO'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '02', 'nombre' => 'ALAMO'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '03', 'nombre' => 'LENGA'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '04', 'nombre' => 'TABLEROS'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '05', 'nombre' => 'VIGAS'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '06', 'nombre' => 'PILARES'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '07', 'nombre' => 'MADERA IMPREGNADA, POLINES'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '08', 'nombre' => 'MDF'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '09', 'nombre' => 'MELAMINAS'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '10', 'nombre' => 'OSB'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '11', 'nombre' => 'TERCIADOS'],
            ['fa_codigo_gen' => 'M08', 'codigo' => '12', 'nombre' => 'ROBLE, DURMIENTES'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '01', 'nombre' => 'PINTURAS'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '02', 'nombre' => 'BARNICES'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '03', 'nombre' => 'ACEITES'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '04', 'nombre' => 'ANTIOXIDOS'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '05', 'nombre' => 'BROCHAS Y RODILLOS'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '06', 'nombre' => 'PASTAS'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '07', 'nombre' => 'DILUYENTES'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '08', 'nombre' => 'SELLADORES'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '09', 'nombre' => 'PINTURAS SPRAY'],
            ['fa_codigo_gen' => 'M09', 'codigo' => '10', 'nombre' => 'LIJAS Y AUXILIARES PINTURA'],
            ['fa_codigo_gen' => 'M10', 'codigo' => '01', 'nombre' => 'HORMIGONES'],
            ['fa_codigo_gen' => 'M10', 'codigo' => '02', 'nombre' => 'SHOTCRETE'],
            ['fa_codigo_gen' => 'M10', 'codigo' => '03', 'nombre' => 'CEMENTOS'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '01', 'nombre' => 'MOBILIARIO URBANO'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '02', 'nombre' => 'POSTES HORMIGÓN'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '03', 'nombre' => 'SOLERAS'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '04', 'nombre' => 'SEPARADORES'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '05', 'nombre' => 'BARRERAS DE HORMIGÓN'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '06', 'nombre' => 'TAPAS DE CÁMARAS'],
            ['fa_codigo_gen' => 'M11', 'codigo' => '07', 'nombre' => 'CÁMARAS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '01', 'nombre' => 'ACERO ESTRIADO'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '02', 'nombre' => 'ACERO LISO'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '03', 'nombre' => 'PERFILES ACERO CERRADOS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '04', 'nombre' => 'PERFILES ACERO ABIERTOS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '05', 'nombre' => 'PILARES Y CADENAS DE'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '06', 'nombre' => 'ACERO PLETINAS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '07', 'nombre' => 'PLANCHAS LISAS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '08', 'nombre' => 'PLANCHAS DIAMANTADAS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '09', 'nombre' => 'MALLAS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '10', 'nombre' => 'CADENAS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '11', 'nombre' => 'POSTES METÁLICOS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '12', 'nombre' => 'TUBOS CORRUGADOS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '13', 'nombre' => 'CAÑERÍAS, TUBO DE ACERO'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '14', 'nombre' => 'PARRILLAS DE PISO, GROUTING'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '15', 'nombre' => 'ALUMINIOS'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '16', 'nombre' => 'ALAMBRES'],
            ['fa_codigo_gen' => 'M12', 'codigo' => '17', 'nombre' => 'PLACA COLABORANTE'],
            ['fa_codigo_gen' => 'M13', 'codigo' => '01', 'nombre' => 'GRAVA, GRAVILLA'],
            ['fa_codigo_gen' => 'M13', 'codigo' => '02', 'nombre' => 'ARENAS'],
            ['fa_codigo_gen' => 'M13', 'codigo' => '03', 'nombre' => 'BOLONES'],
            ['fa_codigo_gen' => 'M13', 'codigo' => '04', 'nombre' => 'ESTABILIZADO'],
            ['fa_codigo_gen' => 'M13', 'codigo' => '05', 'nombre' => 'POMACITA'],
            ['fa_codigo_gen' => 'M14', 'codigo' => '01', 'nombre' => 'ASFALTOS'],
            ['fa_codigo_gen' => 'M14', 'codigo' => '02', 'nombre' => 'IMPREGNANTES'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '01', 'nombre' => 'VINILOS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '02', 'nombre' => 'PORCELANATOS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '03', 'nombre' => 'CERÁMICAS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '04', 'nombre' => 'PISO FLOTANTE'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '05', 'nombre' => 'ALFOMBRA'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '06', 'nombre' => 'CIELOS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '07', 'nombre' => 'MUROS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '08', 'nombre' => 'PIEDRAS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '09', 'nombre' => 'PLACAS YESO CARTON'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '10', 'nombre' => 'ADHESIVOS'],
            ['fa_codigo_gen' => 'M15', 'codigo' => '11', 'nombre' => 'GEOTEXTIL, GEOGRILLA'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '01', 'nombre' => 'VIDRIO LAMINADO'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '02', 'nombre' => 'TERMOPANEL'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '03', 'nombre' => 'VIDRIO TEMPLADO'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '04', 'nombre' => 'LAMINA DE SEGURIDAD'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '05', 'nombre' => 'ACCESORIOS PUERTAS Y VENTANAS'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '06', 'nombre' => 'CARPINTERIA DE ALUMINIO'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '07', 'nombre' => 'VENTANAS'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '08', 'nombre' => 'PUERTAS DE MADERA'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '09', 'nombre' => 'PUERTAS DE SEGURIDAD'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '10', 'nombre' => 'PUERTAS ANTIFUEGO'],
            ['fa_codigo_gen' => 'M16', 'codigo' => '11', 'nombre' => 'PUERTA PLACAROL'],
            ['fa_codigo_gen' => 'M17', 'codigo' => '01', 'nombre' => 'POLICARBONATOS Y CUBIERTAS DE'],
            ['fa_codigo_gen' => 'M17', 'codigo' => '02', 'nombre' => 'TERMINACIONES EN PVC'],
            ['fa_codigo_gen' => 'M17', 'codigo' => '03', 'nombre' => 'CELOSIAS'],
            ['fa_codigo_gen' => 'M17', 'codigo' => '04', 'nombre' => 'GOMAS'],
            ['fa_codigo_gen' => 'M17', 'codigo' => '05', 'nombre' => 'POLIETILENO'],
            ['fa_codigo_gen' => 'M18', 'codigo' => '01', 'nombre' => 'SEGURIDAD VIAL'],
            ['fa_codigo_gen' => 'M18', 'codigo' => '02', 'nombre' => 'OBRAS DE ARTE'],
            ['fa_codigo_gen' => 'M18', 'codigo' => '03', 'nombre' => 'AMORTIGUADORES DE IMPACTO'],
            ['fa_codigo_gen' => 'M18', 'codigo' => '04', 'nombre' => 'BARRERAS CAMINERAS'],
            ['fa_codigo_gen' => 'M18', 'codigo' => '05', 'nombre' => 'ACCESORIOS, INSUMOS VIALES'],
            ['fa_codigo_gen' => 'M19', 'codigo' => '01', 'nombre' => 'LETREROS CAMINEROS'],
            ['fa_codigo_gen' => 'M19', 'codigo' => '02', 'nombre' => 'LETRERO PUBLICITARIO'],
            ['fa_codigo_gen' => 'M19', 'codigo' => '03', 'nombre' => 'LÁMINA REFLECTANTE'],
            ['fa_codigo_gen' => 'M19', 'codigo' => '04', 'nombre' => 'SEÑALÉTICA'],
            ['fa_codigo_gen' => 'M20', 'codigo' => '01', 'nombre' => 'GPS'],
            ['fa_codigo_gen' => 'M20', 'codigo' => '02', 'nombre' => 'RADIOS'],
            ['fa_codigo_gen' => 'M21', 'codigo' => '03', 'nombre' => 'ESTACION TOTAL'],
            ['fa_codigo_gen' => 'M21', 'codigo' => '04', 'nombre' => 'NIVEL TOPOGRÁFICO'],
            ['fa_codigo_gen' => 'M21', 'codigo' => '05', 'nombre' => 'INSTRUMENTOS HOSPITALARIOS'],
            ['fa_codigo_gen' => 'M21', 'codigo' => '06', 'nombre' => 'INSTRUMENTACIÓN DE PRESAS'],
            ['fa_codigo_gen' => 'M21', 'codigo' => '07', 'nombre' => 'INSTRUMENTOS DE MEDICIÓN'],
            ['fa_codigo_gen' => 'M22', 'codigo' => '01', 'nombre' => 'MATERIALES LABORATORIO'],
            ['fa_codigo_gen' => 'M22', 'codigo' => '02', 'nombre' => 'INSTRUMENTOS LABORATORIO'],
            ['fa_codigo_gen' => 'M22', 'codigo' => '03', 'nombre' => 'INSUMOS LABORATORIO'],
            ['fa_codigo_gen' => 'M23', 'codigo' => '04', 'nombre' => 'AISLANTE ACÚSTICO'],
            ['fa_codigo_gen' => 'M23', 'codigo' => '05', 'nombre' => 'AISLANTE TÉRMICO'],
            ['fa_codigo_gen' => 'M23', 'codigo' => '06', 'nombre' => 'JUNTAS'],
            ['fa_codigo_gen' => 'M24', 'codigo' => '01', 'nombre' => 'GASES'],
            ['fa_codigo_gen' => 'M24', 'codigo' => '02', 'nombre' => 'SOLDADURAS'],
            ['fa_codigo_gen' => 'M24', 'codigo' => '03', 'nombre' => 'EQUIPOS'],
            ['fa_codigo_gen' => 'M24', 'codigo' => '04', 'nombre' => 'MÁQUINAS DE SOLDAR'],
            ['fa_codigo_gen' => 'M25', 'codigo' => '01', 'nombre' => 'ASCENSORES'],
            ['fa_codigo_gen' => 'M25', 'codigo' => '02', 'nombre' => 'CABLES ELEVADORES'],
            ['fa_codigo_gen' => 'M25', 'codigo' => '03', 'nombre' => 'POLEAS'],
            ['fa_codigo_gen' => 'M26', 'codigo' => '01', 'nombre' => 'MUEBLES DE MADERA'],
            ['fa_codigo_gen' => 'M26', 'codigo' => '02', 'nombre' => 'MUEBLES DE METAL'],
            ['fa_codigo_gen' => 'M26', 'codigo' => '03', 'nombre' => 'EQUIPAMIENTO HOSPITALARIO'],
        ];

        foreach ($subfamilias as &$sf) {

            $familia = Familia::find('codigo_gen', $sf['fa_codigo_gen']);

            if (!$familia->exist()) {
                $sf['ok'] = false;
                $sf['msg'] = 'familia no existe';
            } else {
                $subfamilia = Subfamilia::find([
                    'id_familia' => $familia->id,
                    'codigo'     => $sf['codigo'],
                ]);
                if ($subfamilia->exist()) {
                    $sf['ok'] = false;
                    $sf['msg'] = 'existe';
                } else {
                    $subfamilia->data('id_familia', $familia->id);
                    $subfamilia->data('codigo', $sf['codigo']);
                    $subfamilia->data('nombre', $sf['nombre']);
                    if ($subfamilia->create()) {
                        $sf['ok'] = true;
                    } else {
                        $sf['ok'] = false;
                        $sf['msg'] = 'error interno';
                    }
                }
            }

        }

        return $subfamilias;
    }

    public function equipos()
    {
        return rsp('anulado');

        $contenido = file_get_contents(_PATH_ . '/assets/files/importar/equipos.json');
        $equipos = json_decode($contenido);

        foreach ($equipos as $eq) {

            $equipo = Equipo::find([
                'codigo' => @$eq->codigo ?: '',
                'placa'  => @$eq->placa ?: '',
                'serie'  => @$eq->serie ?: '',
            ]);

            if ($equipo->exist()) {
                $eq->msg = 'equipo ya existe';
            } else if (!$eq->rubro) {
                $eq->msg = 'no tiene rubro';

            } else {
                $rubro = RubroEquipo::find('nombre', $eq->rubro);

                if (!$rubro->exist()) {
                    $rubro->create([
                        'nombre' => $eq->rubro,
                    ]);
                }

                if ($equipo->create([
                    'id_rubro_equipo'   => $rubro->id,
                    'codigo'            => @$eq->codigo ?: '',
                    'fecha_adquisicion' => @$eq->fecha_adquisicion ?: '',
                    'nombre'            => @$eq->nombre ?: '',
                    'marca'             => @$eq->marca ?: '',
                    'modelo'            => @$eq->modelo ?: '',
                    'serie'             => @$eq->serie ?: '',
                    'num_motor'         => @$eq->num_motor ?: '',
                    'placa'             => @$eq->placa ?: '',
                ])) {
                    $eq->ok = true;
                } else {
                    $eq->msg = 'error interno';
                }

            }

        }

        return $equipos;
    }

}