// Importamos el pool de conexiones configurado en src/config/db.js
// Este pool nos permite ejecutar consultas en MySQL de forma controlada
const { pool } = require('../config/db');


const obtenerReportesFinancieros = async (req, res) => {
    try {


        // Extraemos los filltros enviados por query params......
        // Los query son los que permiten filtrar los resultados12
        const { 
            cod_reporte, 
            cod_periodo, 
            tip_reporte, 
            ind_estado, 
            cod_user,
            incluir_detalle 
        } = req.query;

        
        // Validamos parametros 

        // Validamos cod_reporte solo si viene en la URL
        // Debe ser numérico porque es un BIGINT en la base de datos


        if (cod_reporte && isNaN(Number(cod_reporte))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_reporte debe ser numerico'
            });
        }

        if (cod_periodo && isNaN(Number(cod_periodo))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_periodo debe ser numerico'
            });
        }

        if (cod_user && isNaN(Number(cod_user))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_user debe ser numerico'
            });
        }

        // Validamos tip_reporte solo si viene en la URL
        // Solo permite dos tipos de reportes financieros estándar
        if (tip_reporte && !['balance_general', 'estado_resultados'].includes(tip_reporte)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro tip_reporte solo permite balance_general o estado_resultados'
            });
        }

        // Validamos ind_estado solo si viene en la URL
        // Los estados representan el ciclo de vida del reporte
        if (ind_estado && !['generado', 'confirmado', 'anulado'].includes(ind_estado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro ind_estado solo permite generado, confirmado o anulado'
            });
        }

        // Validamos incluir_detalle solo si viene en la URL
        // Debe ser 'true' o 'false' para controlar si se incluye el detalle
        if (incluir_detalle && !['true', 'false'].includes(incluir_detalle)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro incluir_detalle solo permite true o false'
            });
        }

        
        // CONVERSIÓN Y LIMPIEZA DE PARÁMETROS
    
        // Convertimos los parámetros al tipo correcto que espera el SP

        const codReporteParam = cod_reporte ? Number(cod_reporte) : null;
        const codPeriodoParam = cod_periodo ? Number(cod_periodo) : null;
        const codUserParam = cod_user ? Number(cod_user) : null;
        const tipReporteParam = tip_reporte ? tip_reporte.trim() : null;
        const indEstadoParam = ind_estado ? ind_estado.trim() : null;
        
        // Convertimos el string 'true' a booleano para el SP
        // El SP espera un BOOLEAN para controlar si incluir el detalle
        const incluirDetalleParam = incluir_detalle === 'true' ? true : false;

        
        // Ejecucion de los procedmientos almacenados
        
        // El procedimiento rf_sel_modulo_reportes está diseñado para:
        // 1. Retornar la cabecera del reporte en el primer resultset
        // 2. Retornar el detalle en el segundo resultset (si incluir_detalle=true)
        // Para separar cabecera y detalle optimiza el rendimiento
        // y permite al frontend decidir si necesita el detalle completo

        const [resultado] = await pool.query(
            'CALL rf_sel_modulo_reportes(?, ?, ?, ?, ?, ?)',
            [
                codReporteParam,      // Filtro por codigo de reporte
                codPeriodoParam,      // Filtro por periodo contable
                tipReporteParam,      // Filtro por tipo de reporte
                indEstadoParam,       // Filtro por estado
                codUserParam,         // Filtro por usuario
                incluirDetalleParam   // Controla si incluir detalle
            ]
        );

        
        // PROCESAMIENTO DE RESULTADOS
        
        // MySQL devuelve los resultados del CALL en un arreglo de arreglos
        // resultado[0] = cabecera del reporte (siempre presente)
        // resultado[1] = detalle del reporte (solo si incluir_detalle=true)
        
        const cabecera = resultado[0] || [];
        
        // Construimos la respuesta base con la cabecera
        const responseData = {
            cabecera: cabecera
        };

        // Si se solicito el detalle y existe, lo agregamos a la respuesta
        if (incluirDetalleParam && resultado.length > 1) {
            const detalle = resultado[1] || [];
            responseData.detalle = detalle;
        }

        
        // Validamos el balance
        
        // El SP ya calcula el campo estado_validacion, pero hacemos
        // una validacion adicional para reportes de balance general
         
        // La ecuacin contable (Activo = Pasivo + Patrimonio)
        // es fundamental para la integridad financiera.
        // Detectamos automaticamente si hay inconsistencias.

        let balanceValido = true;
        let mensajeBalance = '';

        // Solo validamos si hay reportes y es de tipo balance_general
        if (cabecera.length > 0 && cabecera[0].tip_reporte === 'balance_general') {
            cabecera.forEach(reporte => {
                // Verificamos si el balance esta cuadrado
                // Si no lo esta, marcamos el reporte para alertar al usuario
                if (reporte.estado_validacion === 'balance descuadrado') {
                    balanceValido = false;
                    mensajeBalance = `El reporte ${reporte.cod_reporte} tiene inconsistencias contables`;
                }
            });
        }

        
        // Construimos la respuesta finial
        
        // Respondemos con una estructura clara y consistente

        return res.status(200).json({
            estado: 'ok',
            total_reportes: cabecera.length,
            incluye_detalle: incluirDetalleParam,
            balance_valido: balanceValido,
            mensaje_validacion: mensajeBalance || 'Todos los balances están cuadrados',
            ...responseData
        });

    } catch (error) {
        
        // MANEJO DE ERRORES
        
        // Mostramos informacion tecnica detallada en la terminal


        console.error('Error al obtener reportes financieros:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql,
            stack: error.stack
        });

        // Respondemos con un mensaje generico al usuario
        // por seguridad (no exponemos detalles internos)
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar reportes financieros'
        });
    }
};

/*
    CONTROLADOR: crearReporteFinanciero
    
    
    FUNCION:
     Genera un nuevo reporte financiero usando el procedimiento:
      rf_ins_modulo_reportes
    
    
*/

const crearReporteFinanciero = async (req, res) => {
    let connection;

    try {
        
        // EXTRACCION DE DATOS 

        // El usario envia los parametros en el cuerpo de la peticion

        const {
            cod_periodo,
            cod_user,
            tip_reporte,
            calcular_automaticamente,
            tot_activo,
            tot_pasivo,
            tot_patrimonio,
            mon_utilidad_perdida
        } = req.body;

        // VALIDACION DE CAMPOS OBLIGATORIOS

        
        if (!cod_periodo || !cod_user || !tip_reporte) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Los campos cod_periodo, cod_user y tip_reporte son obligatorios'
            });
        }

        // VALIDACION DE TIPOS
        // Aseguramos que los tipos de datos sean correctos
        // antes de enviarlos a la base de datos

        if (isNaN(Number(cod_periodo))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_periodo debe ser numerico'
            });
        }

        if (isNaN(Number(cod_user))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_user debe ser numerico'
            });
        }

        // VALIDACION DE VALORES PERMITIDOS 
        
        // Los ENUMs en la base de datos son restrictivos
        // Validamos aqui para dar mensajes claros al usuario

        const tiposReporte = ['balance_general', 'estado_resultados'];
        if (!tiposReporte.includes(tip_reporte)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_reporte solo permite balance_general o estado_resultados'
            });
        }

        // Validamos calcular_automaticamente si viene
        // Debe ser un booleano (true o false)
        if (calcular_automaticamente !== undefined && 
            typeof calcular_automaticamente !== 'boolean') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo calcular_automaticamente debe ser booleano'
            });
        }

        
        // VALIDACION DE CAMPOS MANUALES

        
        if (calcular_automaticamente === false) {

            // Validamos que los totales sean numericos
            if (tot_activo !== undefined && isNaN(Number(tot_activo))) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo tot_activo debe ser numerico'
                });
            }

            if (tot_pasivo !== undefined && isNaN(Number(tot_pasivo))) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo tot_pasivo debe ser numerico'
                });
            }

            if (tot_patrimonio !== undefined && isNaN(Number(tot_patrimonio))) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo tot_patrimonio debe ser numerico'
                });
            }

            if (mon_utilidad_perdida !== undefined && isNaN(Number(mon_utilidad_perdida))) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo mon_utilidad_perdida debe ser numerico'
                });
            }
        }

        
        // OBTENCION DE CONEXION DEL POOL

        // Usamos una conexion dedicada porque el SP tiene parametros OUT
        // y necesitamos consultarlos despues de la ejecucion
        // 
        // Por que los parametros OUT solo son accesibles en la misma
        // conexion donde se ejecuto el SP
        connection = await pool.getConnection();

        // PREPARACION DE PARÁMETROS

        // Convertimos valores y establecemos defaults
        const codPeriodo = Number(cod_periodo);
        const codUser = Number(cod_user);
        const calcularAuto = calcular_automaticamente !== undefined ? 
                             calcular_automaticamente : true; 

        const totActivo = calcularAuto ? null : (tot_activo ? Number(tot_activo) : null);
        const totPasivo = calcularAuto ? null : (tot_pasivo ? Number(tot_pasivo) : null);
        const totPatrimonio = calcularAuto ? null : (tot_patrimonio ? Number(tot_patrimonio) : null);
        const utilidadPerdida = calcularAuto ? null : (mon_utilidad_perdida ? Number(mon_utilidad_perdida) : null);

        // EJECUCION DEL PROCEDIMIENTO ALMACENADO

        

        await connection.query(
            'CALL rf_ins_modulo_reportes(?, ?, ?, ?, ?, ?, ?, ?, @p_cod_reporte_generado, @p_mensaje)',
            [
                codPeriodo,
                codUser,
                tip_reporte,
                calcularAuto,
                totActivo,
                totPasivo,
                totPatrimonio,
                utilidadPerdida
            ]
        );


        // RECUPERACION DE PARAMETROS OUT

        // Los parametros OUT se recuperan con SELECT en la misma conexion
        const [resultadoOut] = await connection.query(
            'SELECT @p_cod_reporte_generado AS cod_reporte, @p_mensaje AS mensaje'
        );

        const codReporteGenerado = resultadoOut[0].cod_reporte;
        const mensajeSp = resultadoOut[0].mensaje;


        // VALIDACION DE RESULTADO

        // Verificamos que el SP haya generado un codigo válido
        if (!codReporteGenerado) {
            return res.status(400).json({
                estado: 'error',
                mensaje: mensajeSp || 'No se pudo generar el reporte financiero'
            });
        }


        // RESPUESTA EXITO

        // Respondemos con código 201 (CREATED) y los datos generados
        return res.status(201).json({
            estado: 'ok',
            mensaje: mensajeSp || 'Reporte financiero generado correctamente',
            cod_reporte: codReporteGenerado,
            tipo: tip_reporte,
            periodo: codPeriodo
        });

    } catch (error) {

        // MANEJO DE ERRORES

        console.error('Error al crear reporte financiero:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Si es un error conocido del SP, lo mostramos al cliente
        // Los errores del SP tienen un mensaje especifico
        if (error.message && error.message.includes('Error:')) {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.message
            });
        }

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al generar reporte financiero'
        });

    } finally {

        // LIBERACIÓN DE CONEXIÓN

        // Importante: Siempre liberar la conexión al finalizar

        if (connection) {
            connection.release();
        }
    }
};

/*
    
    CONTROLADOR: actualizarReporteFinanciero
    
    
    FUNCION:
    - Actualiza un reporte financiero usando el procedimiento:
      rf_upd_modulo_reportes
    
   
*/

const actualizarReporteFinanciero = async (req, res) => {
    try {

        // EXTRACCION DE DATOS
        
        // cod_reporte viene en la URL (params)
        // Los demas datos vienen en el body
        const { cod_reporte } = req.params;
        const {
            ind_estado,
            tot_activo,
            tot_pasivo,
            tot_patrimonio,
            mon_utilidad_perdida
        } = req.body;


        // VALIDACION DEL CODIGO DE REPORTE
        // Es obligatorio y debe ser numérico
        if (!cod_reporte || isNaN(Number(cod_reporte))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_reporte debe ser numerico'
            });
        }


        // VALIDACIÓN DE CAMPOS OBLIGATORIOS

        // Al menos un campo debe ser enviado para actualizar
        if (!ind_estado && tot_activo === undefined && 
            tot_pasivo === undefined && tot_patrimonio === undefined &&
            mon_utilidad_perdida === undefined) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Debe enviar al menos un campo para actualizar'
            });
        }


        // VALIDACION DE VALORES PERMITIDOS

        // Validamos el estado si viene
        if (ind_estado && !['generado', 'confirmado', 'anulado'].includes(ind_estado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite generado, confirmado o anulado'
            });
        }


        // VALIDACIÓN DE CAMPOS NUMERICOS
        // Los totales deben ser numericos
        if (tot_activo !== undefined && isNaN(Number(tot_activo))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_activo debe ser numerico'
            });
        }

        if (tot_pasivo !== undefined && isNaN(Number(tot_pasivo))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_pasivo debe ser numerico'
            });
        }

        if (tot_patrimonio !== undefined && isNaN(Number(tot_patrimonio))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_patrimonio debe ser numerico'
            });
        }

        if (mon_utilidad_perdida !== undefined && isNaN(Number(mon_utilidad_perdida))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo mon_utilidad_perdida debe ser numerico'
            });
        }


        // CONVERSION DE PARAQMETROS

        const codReporte = Number(cod_reporte);
        const estado = ind_estado ? ind_estado : null;
        const activo = tot_activo !== undefined ? Number(tot_activo) : null;
        const pasivo = tot_pasivo !== undefined ? Number(tot_pasivo) : null;
        const patrimonio = tot_patrimonio !== undefined ? Number(tot_patrimonio) : null;
        const utilidadPerdida = mon_utilidad_perdida !== undefined ? Number(mon_utilidad_perdida) : null;


        // VERIFICACION DE EXISTENCIA DEL REPORTE

        // Antes de actualizar, verificamos que el reporte exista
        // Usamos el SP de consulta para obtener el estado actual
        const [resultadoExistencia] = await pool.query(
            'CALL rf_sel_modulo_reportes(?, ?, ?, ?, ?, ?)',
            [codReporte, null, null, null, null, false]
        );

        const reporteExistente = resultadoExistencia[0] || [];

        // Si no existe, retornamos error 404 (Not Found)
        if (reporteExistente.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No existe un reporte financiero con ese codigo'
            });
        }

        // Verificamos si el reporte esta anulado
        // El SP de actualizacion también valida esto, pero lo hacemos
        // aqui para dar un mensaje mas claro al usuario
        const reporte = reporteExistente[0];
        if (reporte.ind_estado === 'anulado') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se puede actualizar un reporte que ya está anulado'
            });
        }


        // EJECUCION DE LA ACTUALIZACION

        // El SP rf_upd_modulo_reportes tiene:
        // - 5 parámetros de entrada (IN)
        // - 1 parámetro de salida (OUT)
        await pool.query(
            'CALL rf_upd_modulo_reportes(?, ?, ?, ?, ?, @p_mensaje)',
            [
                codReporte,
                estado,
                activo,
                pasivo,
                patrimonio,
                utilidadPerdida
            ]
        );


        // MENSAJE DE SALIDA
        const [resultadoMensaje] = await pool.query(
            'SELECT @p_mensaje AS mensaje'
        );

        const mensaje = resultadoMensaje[0].mensaje;


        // RESPUESTA EXITO
        return res.status(200).json({
            estado: 'ok',
            mensaje: mensaje || 'Reporte financiero actualizado correctamente',
            cod_reporte: codReporte,
            estado_anterior: reporte.ind_estado,
            estado_actual: estado || reporte.ind_estado
        });

    } catch (error) {

        // MANEJO DE ERRORES

        console.error('Error al actualizar reporte financiero:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Si es un error del SP, mostramos el mensaje al usuario
        if (error.message && error.message.includes('Error:')) {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.message
            });
        }

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al actualizar reporte financiero'
        });
    }
};

/*
    
    CONTROLADOR: obtenerDetalleReporte

    
    FUNCION:
    - Obtiene solo el detalle de un reporte específico
    - Es un helper para obtener el detalle sin la cabecera
    
*/

const obtenerDetalleReporte = async (req, res) => {
    try {
        const { cod_reporte } = req.params;

        // Validamos el codigo de reporte
        if (!cod_reporte || isNaN(Number(cod_reporte))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_reporte debe ser numerico'
            });
        }

        const codReporte = Number(cod_reporte);


        // LLAMADA AL SP CON incluir_detalle = true
        
        // Solo obtenemos el detalle, no necesitamos la cabecera
        // pero el SP siempre retorna la cabecera primero
        const [resultado] = await pool.query(
            'CALL rf_sel_modulo_reportes(?, ?, ?, ?, ?, ?)',
            [codReporte, null, null, null, null, true]
        );

        // El detalle esta en el segundo resultset (indice 1)
        const detalle = resultado[1] || [];

        if (detalle.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No se encontró detalle para el reporte especificado'
            });
        }

        return res.status(200).json({
            estado: 'ok',
            total_lineas: detalle.length,
            cod_reporte: codReporte,
            detalle: detalle
        });

    } catch (error) {
        console.error('Error al obtener detalle del reporte:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message
        });

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al obtener detalle del reporte'
        });
    }
};

// EXPORTACION DE CONTROLADORES

// Exportamos todas las funciones para usarlas en las rutas
// Cada función corresponde a un endpoint específico

module.exports = {
    obtenerReportesFinancieros,    // GET /api/reportes
    crearReporteFinanciero,        // POST /api/reportes
    actualizarReporteFinanciero,   // PUT /api/reportes/:cod_reporte
    obtenerDetalleReporte          // GET /api/reportes/:cod_reporte/detalle
};