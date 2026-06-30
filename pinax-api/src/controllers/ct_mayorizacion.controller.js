// Importamos el pool de conexiones configurado para MySQL.
const { pool } = require('../config/db');

/*
    Constantes de validacion

    Funcion:
    - centralizan los estados permitidos para los saldos de mayorizacion.
    - se usan en GET, POST y PUT.
*/
const ESTADOS_SALDO = ['abierto', 'cerrado', 'recalculado'];

/*
    Funcion auxiliar: limpiarTexto

    Funcion:
    - valida si un valor existe.
    - convierte el valor a texto.
    - elimina espacios al inicio y al final.
    - devuelve null si el texto queda vacio.
*/
const limpiarTexto = (valor) => {
    if (valor === undefined || valor === null) return null;

    const texto = String(valor).trim();
    return texto.length > 0 ? texto : null;
};

/*
    Funcion auxiliar: normalizarTexto

    Funcion:
    - limpia un texto.
    - lo convierte a minusculas.
    - ayuda a validar campos como abierto, cerrado y recalculado.
*/
const normalizarTexto = (valor) => {
    const texto = limpiarTexto(valor);
    return texto ? texto.toLowerCase() : null;
};

/*
    Funcion auxiliar: esEntero

    Funcion:
    - valida si un valor puede convertirse correctamente a numero entero.
*/
const esEntero = (valor) => {
    return Number.isInteger(Number(valor));
};

/*
    Funcion auxiliar: esEnteroPositivo

    Funcion:
    - valida si un valor es un numero entero mayor que cero.
*/
const esEnteroPositivo = (valor) => {
    return esEntero(valor) && Number(valor) > 0;
};

/*
    Funcion auxiliar: esNumero

    Funcion:
    - valida si un valor puede convertirse correctamente a numero.
    - se usa para validar montos contables.
*/
const esNumero = (valor) => {
    return valor !== undefined &&
           valor !== null &&
           valor !== '' &&
           Number.isFinite(Number(valor));
};

/*
    Funcion auxiliar: esNumeroNoNegativo

    Funcion:
    - valida si un valor numerico es mayor o igual que cero.
    - se usa para tot_debe y tot_haber.
*/
const esNumeroNoNegativo = (valor) => {
    return esNumero(valor) && Number(valor) >= 0;
};

/*
    Controlador: obtenerSaldosCuentas

    Funcion:
    - consulta los saldos de cuenta por periodo.
    - permite listar todos los saldos.
    - permite filtrar por cod_saldo.
    - permite filtrar por cod_periodo.
    - permite filtrar por cod_cuenta.
    - permite filtrar por ind_estado.

    Metodo HTTP:
    - GET

    Rutas esperadas:
    - GET /api/mayorizacion
    - GET /api/mayorizacion?cod_saldo=1
    - GET /api/mayorizacion?cod_periodo=1
    - GET /api/mayorizacion?cod_cuenta=5
    - GET /api/mayorizacion?ind_estado=abierto
    - GET /api/mayorizacion?ind_estado=cerrado
*/
const obtenerSaldosCuentas = async (req, res) => {
    try {
        // Extraemos los filtros enviados por query params.
        const {
            cod_saldo,
            cod_periodo,
            cod_cuenta,
            ind_estado
        } = req.query;

        // Validamos cod_saldo si fue enviado.
        if (cod_saldo && !esEnteroPositivo(cod_saldo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_saldo debe ser numerico y positivo'
            });
        }

        // Validamos cod_periodo si fue enviado.
        if (cod_periodo && !esEnteroPositivo(cod_periodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_periodo debe ser numerico y positivo'
            });
        }

        // Validamos cod_cuenta si fue enviado.
        if (cod_cuenta && !esEnteroPositivo(cod_cuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_cuenta debe ser numerico y positivo'
            });
        }

        // Normalizamos el estado recibido.
        const indEstadoParam = normalizarTexto(ind_estado);

        // Validamos ind_estado si fue enviado.
        if (indEstadoParam && !ESTADOS_SALDO.includes(indEstadoParam)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro ind_estado solo permite: abierto, cerrado o recalculado'
            });
        }

        /*
            Convertimos los parametros.

            Si un filtro no viene informado, se envia null.
            El procedimiento almacenado interpreta null como no aplicar filtro.
        */
        const codSaldoParam = cod_saldo ? Number(cod_saldo) : null;
        const codPeriodoParam = cod_periodo ? Number(cod_periodo) : null;
        const codCuentaParam = cod_cuenta ? Number(cod_cuenta) : null;

        /*
            Ejecutamos el procedimiento almacenado real del modulo.

            Orden de parametros:
            1. cod_saldo
            2. cod_periodo
            3. cod_cuenta
            4. ind_estado
        */
        const [resultado] = await pool.query(
            'CALL cm_sel_modulo_mayorizacion(?, ?, ?, ?)',
            [
                codSaldoParam,
                codPeriodoParam,
                codCuentaParam,
                indEstadoParam
            ]
        );

        // MySQL devuelve el resultado principal en la primera posicion.
        const saldos = resultado[0] || [];

        // Respondemos con los datos obtenidos.
        return res.status(200).json({
            estado: 'ok',
            total: saldos.length,
            datos: saldos
        });

    } catch (error) {
        // Mostramos el error tecnico en consola para depuracion.
        console.error('Error al obtener saldos de cuentas:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Capturamos errores controlados enviados desde MySQL.
        if (error.sqlState === '45000') {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.sqlMessage || 'Error de validacion en la base de datos'
            });
        }

        // Respondemos con un mensaje controlado para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar el modulo de saldos y mayorizacion'
        });
    }
};

/*
    Controlador: crearSaldoCuenta

    Funcion:
    - registra un nuevo saldo de cuenta por periodo.
    - valida que la cuenta exista.
    - valida que el periodo contable exista.
    - evita duplicar una misma cuenta en el mismo periodo.
    - el procedimiento calcula automaticamente el saldo final.

    Metodo HTTP:
    - POST

    Ruta esperada:
    - POST /api/mayorizacion
*/
const crearSaldoCuenta = async (req, res) => {
    let connection;

    try {
        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        const codCuenta = bodyData.cod_cuenta;
        const codPeriodo = bodyData.cod_periodo;
        const salInicial = bodyData.sal_inicial;
        const totDebe = bodyData.tot_debe;
        const totHaber = bodyData.tot_haber;

        /*
            Normalizamos el estado.
            Si no se envia ind_estado, se toma "abierto" como valor por defecto.
        */
        const indEstado = normalizarTexto(bodyData.ind_estado) || 'abierto';

        // Validamos campos obligatorios.
        if (
            codCuenta === undefined ||
            codPeriodo === undefined ||
            salInicial === undefined ||
            totDebe === undefined ||
            totHaber === undefined
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Faltan campos obligatorios para registrar el saldo de cuenta'
            });
        }

        // Validamos que cod_cuenta sea un entero positivo.
        if (!esEnteroPositivo(codCuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_cuenta debe ser numerico y positivo'
            });
        }

        // Validamos que cod_periodo sea un entero positivo.
        if (!esEnteroPositivo(codPeriodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_periodo debe ser numerico y positivo'
            });
        }

        // Validamos que sal_inicial sea numerico.
        if (!esNumero(salInicial)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo sal_inicial debe ser numerico'
            });
        }

        /*
            Validamos debe y haber.
            El saldo inicial puede ser negativo si contablemente aplica,
            pero tot_debe y tot_haber no deben ser negativos.
        */
        if (!esNumeroNoNegativo(totDebe)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_debe debe ser numerico y mayor o igual que cero'
            });
        }

        if (!esNumeroNoNegativo(totHaber)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_haber debe ser numerico y mayor o igual que cero'
            });
        }

        // Validamos el estado del saldo.
        if (!ESTADOS_SALDO.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite: abierto, cerrado o recalculado'
            });
        }

        // Obtenemos una conexion individual porque usaremos una variable OUT.
        connection = await pool.getConnection();

        // Validamos que la cuenta contable exista.
        const [cuentaExiste] = await connection.query(
            'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_cuenta = ? LIMIT 1',
            [Number(codCuenta)]
        );

        if (cuentaExiste.length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La cuenta contable indicada no existe'
            });
        }

        // Validamos que el periodo contable exista.
        const [periodoExiste] = await connection.query(
            'SELECT cod_periodo FROM ga_periodo_contable WHERE cod_periodo = ? LIMIT 1',
            [Number(codPeriodo)]
        );

        if (periodoExiste.length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El periodo contable indicado no existe'
            });
        }

        /*
            Validamos que no exista ya un saldo para la misma cuenta y periodo.
            Esto evita duplicar informacion en la mayorizacion.
        */
        const [saldoDuplicado] = await connection.query(
            `
            SELECT cod_saldo
            FROM cm_saldo_cuenta_periodo
            WHERE cod_cuenta = ?
              AND cod_periodo = ?
            LIMIT 1
            `,
            [Number(codCuenta), Number(codPeriodo)]
        );

        if (saldoDuplicado.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe un saldo registrado para esta cuenta en este periodo'
            });
        }

        /*
            Ejecutamos el procedimiento almacenado.
            Orden de parametros:
            1. cod_cuenta
            2. cod_periodo
            3. sal_inicial
            4. tot_debe
            5. tot_haber
            6. ind_estado
            7. OUT cod_saldo_generado
        */
        await connection.query(
            'CALL cm_ins_modulo_mayorizacion(?, ?, ?, ?, ?, ?, @p_cod_saldo_generado)',
            [
                Number(codCuenta),
                Number(codPeriodo),
                Number(salInicial),
                Number(totDebe),
                Number(totHaber),
                indEstado
            ]
        );

        // Leemos el parametro OUT generado por el procedimiento.
        const [resultadoSalida] = await connection.query(
            'SELECT @p_cod_saldo_generado AS cod_saldo'
        );

        const codSaldoGenerado = resultadoSalida[0]?.cod_saldo;

        // Validamos que el procedimiento haya devuelto un codigo generado.
        if (!codSaldoGenerado) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se pudo registrar el saldo. Revise las restricciones de cuenta, periodo o estado.'
            });
        }

        // Respondemos con el codigo generado.
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Saldo de cuenta y mayorizacion registrado correctamente',
            cod_saldo: codSaldoGenerado
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al crear saldo de cuenta:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Capturamos errores controlados enviados desde MySQL.
        if (error.sqlState === '45000') {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.sqlMessage || 'Error de validacion en la base de datos'
            });
        }

        // Capturamos duplicados por restricciones unique, si existieran.
        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe un registro duplicado en la base de datos'
            });
        }

        // Respuesta general para errores no controlados.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al registrar el saldo de cuenta'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};



/*
    Controlador: actualizarSaldoCuenta

    Funcion:
    - actualiza un saldo de cuenta por periodo.
    - recalcula automaticamente el saldo final mediante el procedimiento almacenado.
    - permite aplicar soft delete cambiando ind_estado a "cerrado".
    - no permite modificar saldos que ya estan cerrados.

    Metodo HTTP:
    - PUT

    Ruta esperada:
    - PUT /api/mayorizacion/:cod_saldo
*/
const actualizarSaldoCuenta = async (req, res) => {
    let connection;

    try {
        // Extraemos el codigo del saldo desde la URL.
        const { cod_saldo } = req.params;

        // Validamos que cod_saldo sea numerico y positivo.
        if (!esEnteroPositivo(cod_saldo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_saldo debe ser numerico y positivo'
            });
        }

        // Convertimos el codigo del saldo a numero.
        const codSaldo = Number(cod_saldo);

        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        const salInicial = bodyData.sal_inicial;
        const totDebe = bodyData.tot_debe;
        const totHaber = bodyData.tot_haber;

        /*
            Normalizamos el estado.

            Para actualizar normalmente se puede usar:
            - abierto
            - recalculado

            Para soft delete logico se usa:
            - cerrado
        */
        const indEstado = normalizarTexto(bodyData.ind_estado);

        // Validamos campos obligatorios.
        if (
            salInicial === undefined ||
            totDebe === undefined ||
            totHaber === undefined ||
            !indEstado
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Faltan campos obligatorios para actualizar el saldo de cuenta'
            });
        }

        // Validamos que sal_inicial sea numerico.
        if (!esNumero(salInicial)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo sal_inicial debe ser numerico'
            });
        }

        /*
            Validamos tot_debe y tot_haber.

            Permitimos sal_inicial negativo si contablemente aplica,
            pero tot_debe y tot_haber no deben ser negativos.
        */
        if (!esNumeroNoNegativo(totDebe)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_debe debe ser numerico y mayor o igual que cero'
            });
        }

        if (!esNumeroNoNegativo(totHaber)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tot_haber debe ser numerico y mayor o igual que cero'
            });
        }

        // Validamos que el estado sea permitido por la base de datos.
        if (!ESTADOS_SALDO.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite: abierto, cerrado o recalculado'
            });
        }

        // Obtenemos una conexion del pool.
        connection = await pool.getConnection();

        /*
            Validamos que el saldo exista antes de llamar al procedimiento.

            Esto es importante porque el procedimiento tiene handler interno
            y puede hacer rollback sin devolver un error claro a Node.
        */
        const [saldoActual] = await connection.query(
            `
            SELECT 
                cod_saldo,
                ind_estado
            FROM cm_saldo_cuenta_periodo
            WHERE cod_saldo = ?
            LIMIT 1
            `,
            [codSaldo]
        );

        if (saldoActual.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'El saldo de cuenta indicado no existe'
            });
        }

        /*
            Validamos que no se modifique un saldo cerrado.
            En este modulo, "cerrado" funciona como cierre logico o soft delete.
        */
        if (saldoActual[0].ind_estado === 'cerrado') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se puede modificar un saldo que ya esta cerrado'
            });
        }

        /*
            Ejecutamos el procedimiento almacenado.
            Orden de parametros:
            1. cod_saldo
            2. sal_inicial
            3. tot_debe
            4. tot_haber
            5. ind_estado
        */
        await connection.query(
            'CALL cm_upd_modulo_mayorizacion(?, ?, ?, ?, ?)',
            [
                codSaldo,
                Number(salInicial),
                Number(totDebe),
                Number(totHaber),
                indEstado
            ]
        );

        /*
            Consultamos el saldo actualizado para confirmar el resultado.
            Esto permite devolver al cliente el sal_final recalculado.
        */
        const [saldoActualizado] = await connection.query(
            `
            SELECT 
                cod_saldo,
                cod_cuenta,
                cod_periodo,
                sal_inicial,
                tot_debe,
                tot_haber,
                sal_final,
                ind_estado,
                fec_actualizacion
            FROM cm_saldo_cuenta_periodo
            WHERE cod_saldo = ?
            LIMIT 1
            `,
            [codSaldo]
        );

        // Respondemos confirmando la actualizacion o el cierre logico.
        return res.status(200).json({
            estado: 'ok',
            mensaje: indEstado === 'cerrado'
                ? 'Saldo de cuenta cerrado correctamente como soft delete logico'
                : 'Saldo de cuenta actualizado correctamente',
            datos: saldoActualizado[0]
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al actualizar saldo de cuenta:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Capturamos errores controlados enviados desde MySQL.
        if (error.sqlState === '45000') {
            return res.status(400).json({
                estado: 'error',
                mensaje: error.sqlMessage || 'Error de validacion en la base de datos'
            });
        }

        // Respuesta general para errores no controlados.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al actualizar el saldo de cuenta'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};


// Exportamos los metodos del controlador de mayorizacion.
module.exports = {
    obtenerSaldosCuentas,
    crearSaldoCuenta,
    actualizarSaldoCuenta
};