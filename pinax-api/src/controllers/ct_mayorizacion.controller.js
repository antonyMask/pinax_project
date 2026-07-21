// Importamos el pool de conexiones configurado para MySQL.
const { pool } = require('../config/db');

/*
    Constantes de validacion

    Funcion:
    - centralizan los estados permitidos para los saldos de mayorizacion.
    - se usan en GET, POST y PUT.
*/
const ESTADOS_SALDO = ['abierto', 'recalculando', 'cerrado','inactivo'];
const VISTAS_MAYORIZACION = ['resumen', 'cuenta_t', 'opciones'];
const ACCIONES_MAYORIZACION = ['recalcular', 'cerrar', 'inactivar'];

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
    Funcion auxiliar: fueEnviado
    Distingue entre:
    - un parametro que no fue enviado.
    - un parametro que si fue enviado, pero puede ser invalido.

    Esto evita que los valores como 0 sean confundidos con la ausencia del campo.
*/
const fueEnviado = (valor) => {
    return valor !== undefined &&
           valor !== null &&
           valor !== '';
};

/* Funcion auxiliar: obtenerConjuntosDeResultados */
const obtenerConjuntosDeResultados = (resultado) => {
    if (!Array.isArray(resultado)) {
        return [];
    }

    return resultado.filter((elemento) => {
        return Array.isArray(elemento);
    });
};

/* Funcion auxiliar: responderErrorBaseDatos */
const responderErrorBaseDatos = (res, error, mensajeInterno) => {
    console.error(mensajeInterno, {
        codigo: error.code,
        estadoSql: error.sqlState,
        numero: error.errno,
        mensaje: error.message,
        sql: error.sql
    });

    if (error.sqlState === '45000') {
        return res.status(400).json({
            estado: 'error',
            mensaje: error.sqlMessage || 'Error de validación en la base de datos'
        });
    }

    if (error.code === 'ER_DUP_ENTRY') {
        return res.status(400).json({
            estado: 'error',
            mensaje: 'La cuenta ya fue mayorizada en el período seleccionado'
        });
    }

    return res.status(500).json({
        estado: 'error',
        mensaje: 'Error interno en el modulo de Cuentas T y Mayorización'
    });
};

/*
    Controlador GET: obtenerSaldosCuentas

    Utiliza el mismo endpoint para tres tipos de consulta:
    - resumen: saldos mayorizados.
    - cuenta_t: resumen y movimientos individuales.
    - opciones: cuentas y periodos disponibles.
*/
const obtenerSaldosCuentas = async (req, res) => {
    try {
        /*
            Los filtros GET se reciben mediante req.query.

            Si no se envía una vista, utilizamos "resumen"
            como comportamiento predeterminado.
        */
        const vista = normalizarTexto(req.query.vista) || 'resumen';
        const estado = normalizarTexto(req.query.ind_estado);

        const {
            cod_saldo,
            cod_periodo,
            cod_cuenta
        } = req.query;

        /*
            Validamos la vista utilizando la lista blanca definida
            al inicio del controlador.
        */
        if (!VISTAS_MAYORIZACION.includes(vista)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro vista solo permite: resumen, cuenta_t u opciones'
            });
        }

        /*
            Los identificadores son opcionales para algunas consultas,
            pero si fueron enviados deben ser enteros positivos.
        */
        if (fueEnviado(cod_saldo) && !esEnteroPositivo(cod_saldo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro cod_saldo debe ser numérico y positivo'
            });
        }

        if (fueEnviado(cod_periodo) && !esEnteroPositivo(cod_periodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro cod_periodo debe ser numérico y positivo'
            });
        }

        if (fueEnviado(cod_cuenta) && !esEnteroPositivo(cod_cuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro cod_cuenta debe ser numérico y positivo'
            });
        }

        /*
            El estado también es opcional, pero debe pertenecer
            a los estados permitidos cuando se utilice.
        */
        if (estado && !ESTADOS_SALDO.includes(estado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro ind_estado no contiene un estado permitido'
            });
        }

        /*
            Una Cuenta T pertenece obligatoriamente a una cuenta
            dentro de un periodo concreto.

            Sin estos dos valores podríamos mezclar movimientos
            que contablemente no corresponden.
        */
        if (
            vista === 'cuenta_t'
            && (
                !esEnteroPositivo(cod_periodo)
                || !esEnteroPositivo(cod_cuenta)
            )
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La vista cuenta_t requiere cod_periodo y cod_cuenta'
            });
        }

        /*
            Ejecutamos el único procedimiento SELECT permitido.

            Orden de los parámetros:
            1. Tipo de vista.
            2. Código del saldo.
            3. Código del periodo.
            4. Código de la cuenta.
            5. Estado del saldo.
        */
        const [resultado] = await pool.query(
            'CALL cm_sel_modulo_mayorizacion(?, ?, ?, ?, ?)',
            [
                vista,
                fueEnviado(cod_saldo)
                    ? Number(cod_saldo)
                    : null,
                fueEnviado(cod_periodo)
                    ? Number(cod_periodo)
                    : null,
                fueEnviado(cod_cuenta)
                    ? Number(cod_cuenta)
                    : null,
                estado
            ]
        );

        /*
            Separamos los conjuntos de filas devueltos por el
            procedimiento de la información técnica del CALL.
        */
        const conjuntos = obtenerConjuntosDeResultados(resultado);

        /*
            La consulta Cuenta T devuelve dos conjuntos:

            conjuntos[0] = resumen de la cuenta.
            conjuntos[1] = movimientos individuales.
        */
        if (vista === 'cuenta_t') {
            const resumen = conjuntos[0]?.[0] || null;
            const movimientos = conjuntos[1] || [];

            return res.status(200).json({
                estado: 'ok',
                datos: {
                    resumen,
                    total_movimientos: movimientos.length,
                    movimientos
                }
            });
        }

        /*
            La vista de opciones también devuelve dos conjuntos:

            conjuntos[0] = cuentas disponibles.
            conjuntos[1] = periodos disponibles.
        */
        if (vista === 'opciones') {
            return res.status(200).json({
                estado: 'ok',
                datos: {
                    cuentas: conjuntos[0] || [],
                    periodos: conjuntos[1] || []
                }
            });
        }

        /*
            Si no se solicitó cuenta_t ni opciones,
            respondemos con el resumen de Mayorización.
        */
        const saldos = conjuntos[0] || [];

        return res.status(200).json({
            estado: 'ok',
            total: saldos.length,
            datos: saldos
        });

    } catch (error) {
        /*
            Delegamos el tratamiento del error a la función auxiliar
            creada en el bloque anterior.
        */
        return responderErrorBaseDatos(
            res,
            error,
            'Error al consultar Cuentas T y Mayorización'
        );
    }
};



/*
    Controlador POST: crearSaldoCuenta

    El cliente solamente indica la cuenta y el periodo.
    Los importes se calculan en MySQL desde los asientos aprobados.
*/
const crearSaldoCuenta = async (req, res) => {
    /*
        Guardaremos aquí una conexión individual del pool.

        La necesitamos porque la variable OUT del procedimiento pertenece
        exclusivamente a la sesión de MySQL que ejecutó el CALL.
    */
    let connection;

    try {
        /*
            Si Express no recibe un cuerpo JSON, usamos un objeto vacío
            para evitar errores al intentar acceder a sus propiedades.
        */
        const bodyData = req.body || {};

        const codCuenta = bodyData.cod_cuenta;
        const codPeriodo = bodyData.cod_periodo;

        /*
            Ambos identificadores son obligatorios y deben representar
            registros válidos mediante números enteros positivos.
        */
        if (!esEnteroPositivo(codCuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_cuenta debe ser numerico y positivo'
            });
        }

        if (!esEnteroPositivo(codPeriodo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_periodo debe ser numerico y positivo'
            });
        }

        /*
            Estos campos pertenecen exclusivamente al cálculo contable.

            Los rechazamos expresamente para impedir que una interfaz antigua
            o un usuario intente introducir saldos manualmente.
        */
        const camposControlados = [
            'sal_inicial',
            'tot_debe',
            'tot_haber',
            'sal_final',
            'ind_estado'
        ];

        /*
            hasOwnProperty comprueba si el campo fue enviado, incluso si su
            valor es cero, null o una cadena vacía.
        */
        const contieneCamposControlados = camposControlados.some((campo) => {
            return Object.prototype.hasOwnProperty.call(bodyData, campo);
        });

        if (contieneCamposControlados) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Los saldos y el estado son calculados por el sistema y no deben enviarse'
            });
        }

        /*
            Obtenemos una conexión individual.

            No usamos pool.query directamente porque debemos ejecutar el CALL
            y consultar la variable OUT dentro de la misma sesión de MySQL.
        */
        connection = await pool.getConnection();

        /*
            El procedimiento recibe:
            1. Código de la cuenta.
            2. Código del periodo.
            3. Variable OUT donde devolverá el saldo creado.
        */
        await connection.query(
            'CALL cm_ins_modulo_mayorizacion(?, ?, @p_cod_saldo_generado)',
            [
                Number(codCuenta),
                Number(codPeriodo)
            ]
        );

        /*
            Consultamos la variable OUT utilizando exactamente
            la misma conexión.
        */
        const [resultadoSalida] = await connection.query(
            'SELECT @p_cod_saldo_generado AS cod_saldo'
        );

        /*
            mysql2 devuelve las filas dentro de la primera posición.
            Mediante encadenamiento opcional evitamos errores si no hay filas.
        */
        const codSaldoGenerado = resultadoSalida[0]?.cod_saldo;

        /*
            Esta condición detectaría una ejecución anormal:
            el procedimiento terminó, pero no devolvió el identificador.
        */
        if (!codSaldoGenerado) {
            return res.status(500).json({
                estado: 'error',
                mensaje: 'La base de datos no devolvio el saldo generado'
            });
        }

        /*
            HTTP 201 indica que se creó correctamente un recurso.
        */
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Cuenta mayorizada correctamente desde sus asientos aprobados',
            cod_saldo: codSaldoGenerado
        });

    } catch (error) {
        /*
            Los errores contables generados con SIGNAL serán convertidos
            en respuestas 400 por la función compartida.
        */
        return responderErrorBaseDatos(
            res,
            error,
            'Error al ejecutar la primera mayorizacion'
        );

    } finally {
        /*
            Toda conexión obtenida manualmente debe regresar al pool,
            tanto si la operación funcionó como si produjo un error.
        */
        if (connection) {
            connection.release();
        }
    }
};



/*
    Controlador PUT: actualizarSaldoCuenta

    Acciones disponibles:
    - recalcular: vuelve a obtener los importes desde los asientos aprobados.
    - cerrar: consolida el saldo de un periodo contable cerrado.
    - inactivar: realiza el soft delete del registro.
*/
const actualizarSaldoCuenta = async (req, res) => {
    try {
        /*
            El código del saldo se recibe como parámetro de la URL:
            PUT /api/mayorizacion/1
            Express guarda el valor "1" dentro de req.params.cod_saldo.
        */
        const { cod_saldo } = req.params;

        /*
            La acción solicitada se recibe en el cuerpo JSON.
            Si req.body no existe, usamos un objeto vacío para evitar errores.
        */
        const bodyData = req.body || {};
        const accion = normalizarTexto(bodyData.accion);

        /*
            El registro que se modificará debe identificarse mediante
            un número entero positivo.
        */
        if (!esEnteroPositivo(cod_saldo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_saldo debe ser numerico y positivo'
            });
        }

        /*
            Solo permitimos las acciones definidas en la lista blanca
            ACCIONES_MAYORIZACION.
        */
        if (!accion || !ACCIONES_MAYORIZACION.includes(accion)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo accion solo permite: recalcular, cerrar o inactivar'
            });
        }

        /*
            El método PUT tampoco debe recibir importes ni estados manuales.

            MySQL determina:
            - saldo inicial;
            - total del debe;
            - total del haber;
            - saldo final;
            - nuevo estado.
        */
        const camposControlados = [
            'sal_inicial',
            'tot_debe',
            'tot_haber',
            'sal_final',
            'ind_estado'
        ];

        /*
            Verificamos si el cliente intentó enviar alguno de los
            campos que son responsabilidad del procedimiento almacenado.
        */
        const contieneCamposControlados = camposControlados.some((campo) => {
            return Object.prototype.hasOwnProperty.call(bodyData, campo);
        });

        if (contieneCamposControlados) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Use accion; los saldos y el estado no deben enviarse manualmente'
            });
        }

        /*
            Ejecutamos el único procedimiento UPDATE del módulo.

            Parámetros:
            1. Código del saldo que se modificará.
            2. Acción que debe realizar el procedimiento.
        */
        await pool.query(
            'CALL cm_upd_modulo_mayorizacion(?, ?)',
            [
                Number(cod_saldo),
                accion
            ]
        );

        /*
            Después de actualizar, recuperamos el registro utilizando
            el procedimiento SELECT existente.

            No hacemos una consulta SQL directa desde el controlador.
        */
        const estadoFiltro = accion === 'inactivar'
            ? 'inactivo'
            : null;

        const [resultadoConsulta] = await pool.query(
            'CALL cm_sel_modulo_mayorizacion(?, ?, ?, ?, ?)',
            [
                'resumen',
                Number(cod_saldo),
                null,
                null,
                estadoFiltro
            ]
        );

        /*
            Extraemos los conjuntos de filas devueltos por el CALL
            y tomamos el primer registro encontrado.
        */
        const conjuntos = obtenerConjuntosDeResultados(resultadoConsulta);
        const saldoActualizado = conjuntos[0]?.[0] || null;

        /*
            Cada acción devuelve un mensaje específico para que Laravel
            pueda mostrar una notificación comprensible al usuario.
        */
        const mensajes = {
            recalcular: 'Mayorizacion recalculada desde los asientos aprobados',
            cerrar: 'Saldo mayorizado cerrado correctamente',
            inactivar: 'Saldo mayorizado inactivado mediante soft delete'
        };

        /*
            HTTP 200 indica que el recurso existente fue actualizado
            correctamente y devolvemos su estado resultante.
        */
        return res.status(200).json({
            estado: 'ok',
            mensaje: mensajes[accion],
            datos: saldoActualizado
        });

    } catch (error) {
        /*
            Los errores de negocio generados por el procedimiento
            se convierten en respuestas HTTP mediante la función compartida.
        */
        return responderErrorBaseDatos(
            res,
            error,
            'Error al actualizar la mayorizacion'
        );
    }
};


// Exportamos los metodos del controlador de mayorizacion.
module.exports = {
    obtenerSaldosCuentas,
    crearSaldoCuenta,
    actualizarSaldoCuenta
};