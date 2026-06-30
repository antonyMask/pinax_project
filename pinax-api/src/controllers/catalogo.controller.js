// Importamos el pool de conexiones configurado para MySQL
const { pool } = require('../config/db');

/* Constantes de validacion
   Funcion:
   - Centralizan los valores permitidos por el modulo catalogo
*/
const NATURALEZAS = ['deudora', 'acreedora'];
const MOVIMIENTOS = ['si', 'no'];
const ESTADOS_CUENTA = ['activo', 'inactivo'];

/* Funcion auxiliar: limpiarTexto
   Funcion:
   - valida si un valor existe
   - convierte el valor a texto
   - elimina espacios al inicio y al final
   - devuelve null si el texto queda vacio
*/
const limpiarTexto = (valor) => {
    if (valor === undefined || valor === null) return null;

    const texto = String(valor).trim();
    return texto.length > 0 ? texto : null;
};

/* Funcion auxiliar: normalizarTexto
   Funcion:
   - limpia un texto
   - lo convierte a minusculas
   - ayuda a validar campos como activo, inactivo, si, no, deudora y acreedora.
*/
const normalizarTexto = (valor) => {
    const texto = limpiarTexto(valor);
    return texto ? texto.toLowerCase() : null;
};

/* Funcion auxiliar: esEntero
   Funcion:
   - valida si un valor puede convertirse correctamente a numero entero
*/
const esEntero = (valor) => {
    return Number.isInteger(Number(valor));
};

/* Funcion auxiliar: esEnteroPositivo

   Funcion:
   - valida si un valor es un numero entero mayor que cero
*/
const esEnteroPositivo = (valor) => {
    return esEntero(valor) && Number(valor) > 0;
};


/* Funcion auxiliar: convertirBooleano

    Funcion:
    - convierte diferentes valores a booleano
    - se usa para saber si se desea actualizar tambien el tipo de cuenta
*/
const convertirBooleano = (valor) => {
    return valor === true || valor === 1 || valor === '1' || valor === 'true';
};


/*
    Controlador: obtenerCatalogo

    Funcion:
    - consulta el catalogo de cuentas contables.
    - permite traer todo el catalogo.
    - permite filtrar por tipo de cuenta usando cod_tipo_cuenta.
    - permite filtrar una cuenta especifica usando cod_cuenta.

    Metodo HTTP:
    - GET

    Rutas esperadas:
    - GET /api/catalogo
    - GET /api/catalogo?cod_tipo_cuenta=1
    - GET /api/catalogo?cod_cuenta=12
*/
const obtenerCatalogo = async (req, res) => {
    try {
        // Extraemos los filtros enviados por query params.
        const { cod_tipo_cuenta, cod_cuenta } = req.query;

        /*
            Validamos cod_cuenta si fue enviado.
            Este filtro sirve para consultar una cuenta especifica.
        */
        if (cod_cuenta && !esEntero(cod_cuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_cuenta debe ser numerico'
            });
        }

        /*
            Validamos cod_tipo_cuenta si fue enviado.
            Este filtro sirve para consultar cuentas por tipo.
        */
        if (cod_tipo_cuenta && !esEntero(cod_tipo_cuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_tipo_cuenta debe ser numerico'
            });
        }

        if (cod_cuenta) {
            const [cuentas] = await pool.query(
                `
                SELECT 
                    cod_cuenta,
                    cod_num_cuenta,
                    nom_cuenta,
                    cod_tipo_cuenta,
                    cod_cuenta_padre,
                    num_nivel_jerarquia,
                    ind_naturaleza,
                    ind_acepta_movimiento,
                    des_cuenta,
                    ind_estado
                FROM cc_catalogo_cuenta
                WHERE cod_cuenta = ?
                `,
                [Number(cod_cuenta)]
            );

            return res.status(200).json({
                estado: 'ok',
                total: cuentas.length,
                datos: cuentas
            });
        }

        const codTipoCuentaParam = cod_tipo_cuenta ? Number(cod_tipo_cuenta) : 0;

        // Ejecutamos el procedimiento almacenado del modulo catalogo.
        const [resultado] = await pool.query(
            'CALL cc_sel_modulo_catalogo(?)',
            [codTipoCuentaParam]
        );

        /*
            MySQL devuelve el resultado principal en la primera posicion.
            Si no viene informacion, devolvemos un arreglo vacio.
        */
        const cuentas = resultado[0] || [];

        // Respondemos con el resultado obtenido.
        return res.status(200).json({
            estado: 'ok',
            total: cuentas.length,
            datos: cuentas
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al obtener catalogo:', {
            codigo: error.code,
            mensaje: error.message,
            sql: error.sql
        });

        // Respondemos con un mensaje controlado para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar el catalogo de cuentas'
        });
    }
};

/*
    Controlador: crearCuenta

    Funcion:
    - registra una nueva cuenta contable en el catalogo.
    - permite usar un tipo de cuenta existente.
    - permite crear un nuevo tipo de cuenta si cod_tipo_cuenta viene como 0 o null.

    Metodo HTTP:
    - POST

    Ruta esperada:
    - POST /api/catalogo
*/
const crearCuenta = async (req, res) => {
    let connection;

    try {
        // Capturamos los datos enviados en el body de la peticion.
        const bodyData = req.body || {};

        /*
            Datos relacionados al tipo de cuenta.

            Si cod_tipo_cuenta viene con un valor existente, se usa ese tipo.
            Si cod_tipo_cuenta viene como 0 o null, el procedimiento puede crear un nuevo tipo.
        */
        const codTipoCuentaTexto = limpiarTexto(bodyData.cod_tipo_cuenta);
        const codTipoCuenta = codTipoCuentaTexto !== null ? Number(codTipoCuentaTexto) : null;

        const nomTipoCuenta = limpiarTexto(bodyData.nom_tipo_cuenta);
        const indNaturalezaTipo = normalizarTexto(bodyData.ind_naturaleza_tipo);
        const desTipoCuenta = limpiarTexto(bodyData.des_tipo_cuenta);

        // Datos principales de la cuenta contable.
        const codNumCuenta = limpiarTexto(bodyData.cod_num_cuenta);
        const nomCuenta = limpiarTexto(bodyData.nom_cuenta);

        /*
            Cuenta padre.

            Si viene null, vacio o 0, se interpreta como una cuenta sin padre.
            Esto es util para cuentas de primer nivel.
        */
        const codCuentaPadreTexto = limpiarTexto(bodyData.cod_cuenta_padre);
        const codCuentaPadre = codCuentaPadreTexto !== null && Number(codCuentaPadreTexto) !== 0
            ? Number(codCuentaPadreTexto)
            : null;

        const numNivelJerarquia = Number(bodyData.num_nivel_jerarquia);

        /*
            Aceptamos ind_naturaleza_cuenta como nombre principal.
            Tambien aceptamos ind_naturaleza por compatibilidad con el script que envio el equipo.
        */
        const indNaturalezaCuenta = normalizarTexto(
            bodyData.ind_naturaleza_cuenta || bodyData.ind_naturaleza
        );

        const indAceptaMovimiento = normalizarTexto(bodyData.ind_acepta_movimiento);
        const desCuenta = limpiarTexto(bodyData.des_cuenta);
        const indEstado = normalizarTexto(bodyData.ind_estado) || 'activo';
        const usrAdicion = limpiarTexto(bodyData.usr_adicion) || 'sistema';

        // Validamos los campos obligatorios de la cuenta contable.
        if (!codNumCuenta || !nomCuenta || !bodyData.num_nivel_jerarquia || !indNaturalezaCuenta || !indAceptaMovimiento) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Faltan campos obligatorios para registrar la cuenta contable'
            });
        }

        // Validamos cod_tipo_cuenta si fue enviado.
        if (codTipoCuentaTexto !== null && (!esEntero(codTipoCuenta) || codTipoCuenta < 0)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_tipo_cuenta debe ser un numero entero igual o mayor que cero'
            });
        }

        // Validamos el nivel de jerarquia.
        if (!esEnteroPositivo(numNivelJerarquia)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo num_nivel_jerarquia debe ser un numero entero positivo'
            });
        }

        // Validamos la naturaleza de la cuenta.
        if (!NATURALEZAS.includes(indNaturalezaCuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_naturaleza_cuenta solo permite: deudora o acreedora'
            });
        }

        // Validamos si la cuenta acepta movimientos.
        if (!MOVIMIENTOS.includes(indAceptaMovimiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_acepta_movimiento solo permite: si o no'
            });
        }

        // Validamos el estado de la cuenta.
        if (!ESTADOS_CUENTA.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite: activo o inactivo'
            });
        }

        // Validamos la cuenta padre si fue enviada.
        if (codCuentaPadre !== null && !esEnteroPositivo(codCuentaPadre)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_cuenta_padre debe ser un numero entero positivo'
            });
        }

        /*
            Determinamos si se va a crear un nuevo tipo de cuenta.

            Si cod_tipo_cuenta viene como null o 0, exigimos los datos del nuevo tipo.
        */
        const crearNuevoTipo = codTipoCuenta === null || codTipoCuenta === 0;

        if (crearNuevoTipo) {
            if (!nomTipoCuenta || !indNaturalezaTipo) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'Para crear un nuevo tipo de cuenta debe enviar nom_tipo_cuenta e ind_naturaleza_tipo'
                });
            }

            if (!NATURALEZAS.includes(indNaturalezaTipo)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo ind_naturaleza_tipo solo permite: deudora o acreedora'
                });
            }
        }

        // Obtenemos una conexion individual porque necesitamos leer una variable OUT.
        connection = await pool.getConnection();

        // Validamos que no exista otra cuenta con el mismo numero contable.
        const [cuentaDuplicada] = await connection.query(
            'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_num_cuenta = ? LIMIT 1',
            [codNumCuenta]
        );

        if (cuentaDuplicada.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe una cuenta contable con ese numero de cuenta'
            });
        }

        // Si se usa un tipo de cuenta existente, validamos que realmente exista.
        if (!crearNuevoTipo) {
            const [tipoCuenta] = await connection.query(
                'SELECT cod_tipo_cuenta FROM cc_tipo_cuenta WHERE cod_tipo_cuenta = ? LIMIT 1',
                [codTipoCuenta]
            );

            if (tipoCuenta.length === 0) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El tipo de cuenta indicado no existe'
                });
            }
        }

        // Si se envio cuenta padre, validamos que exista.
        if (codCuentaPadre !== null) {
            const [cuentaPadre] = await connection.query(
                'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_cuenta = ? LIMIT 1',
                [codCuentaPadre]
            );

            if (cuentaPadre.length === 0) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'La cuenta padre indicada no existe'
                });
            }
        }

        // Ejecutamos el procedimiento almacenado para insertar la cuenta.
        await connection.query(
            'CALL cc_ins_modulo_catalogo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @p_cod_cuenta_generada)',
            [
                codTipoCuenta,
                nomTipoCuenta,
                indNaturalezaTipo,
                desTipoCuenta,
                codNumCuenta,
                nomCuenta,
                codCuentaPadre,
                numNivelJerarquia,
                indNaturalezaCuenta,
                indAceptaMovimiento,
                desCuenta,
                indEstado,
                usrAdicion
            ]
        );

        // Leemos el parametro OUT generado por el procedimiento.
        const [resultadoSalida] = await connection.query(
            'SELECT @p_cod_cuenta_generada AS cod_cuenta'
        );

        const codCuentaGenerada = resultadoSalida[0]?.cod_cuenta;

        // Validamos que el procedimiento haya devuelto un codigo generado.
        if (!codCuentaGenerada) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La cuenta no pudo ser registrada. Revise restricciones de la base de datos.'
            });
        }

        // Respondemos con el codigo de la nueva cuenta.
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Cuenta contable registrada correctamente en el catalogo',
            cod_cuenta: codCuentaGenerada
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al crear cuenta contable:', {
            codigo: error.code,
            estadoSql: error.sqlState,
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

        // Capturamos duplicados por restricciones unique.
        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe un registro duplicado en la base de datos'
            });
        }

        // Respuesta general para errores no controlados.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al registrar la cuenta en el catalogo'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};



/*
    Controlador: actualizarCuenta

    Funcion:
    - actualiza una cuenta contable existente.
    - permite cambiar el estado de la cuenta.
    - permite aplicar soft delete enviando ind_estado = "inactivo".
    - opcionalmente permite actualizar datos del tipo de cuenta.

    Metodo HTTP:
    - PUT

    Ruta esperada:
    - PUT /api/catalogo/:cod_cuenta
*/
const actualizarCuenta = async (req, res) => {
    let connection;

    try {
        // Extraemos el codigo de la cuenta desde la URL.
        const { cod_cuenta } = req.params;

        // Validamos que el codigo de la cuenta sea numerico y positivo.
        if (!esEnteroPositivo(cod_cuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_cuenta debe ser numerico y positivo'
            });
        }

        // Convertimos el codigo de la cuenta a numero.
        const codCuenta = Number(cod_cuenta);

        // Capturamos los datos enviados en el body.
        const bodyData = req.body || {};

        // Datos principales de la cuenta contable.
        const codNumCuenta = limpiarTexto(bodyData.cod_num_cuenta);
        const nomCuenta = limpiarTexto(bodyData.nom_cuenta);

        // Tipo de cuenta asociado.
        const codTipoCuentaTexto = limpiarTexto(bodyData.cod_tipo_cuenta);
        const codTipoCuenta = codTipoCuentaTexto !== null
            ? Number(codTipoCuentaTexto)
            : null;

        /*
            Cuenta padre.

            Si viene null, vacio o 0, se interpreta como una cuenta sin padre.
        */
        const codCuentaPadreTexto = limpiarTexto(bodyData.cod_cuenta_padre);
        const codCuentaPadre = codCuentaPadreTexto !== null && Number(codCuentaPadreTexto) !== 0
            ? Number(codCuentaPadreTexto)
            : null;

        const numNivelJerarquia = Number(bodyData.num_nivel_jerarquia);

        /*
            Aceptamos ind_naturaleza_cuenta como nombre principal.
            Tambien aceptamos ind_naturaleza por compatibilidad.
        */
        const indNaturalezaCuenta = normalizarTexto(
            bodyData.ind_naturaleza_cuenta || bodyData.ind_naturaleza
        );

        const indAceptaMovimiento = normalizarTexto(bodyData.ind_acepta_movimiento);
        const desCuenta = limpiarTexto(bodyData.des_cuenta);

        /*
            Estado de la cuenta.

            Si se envia "activo", la cuenta queda activa.
            Si se envia "inactivo", se aplica soft delete.
        */
        const indEstado = normalizarTexto(bodyData.ind_estado) || 'activo';

        // Usuario que realiza la modificacion.
        const usrModificacion = limpiarTexto(bodyData.usr_modificacion) || 'sistema';

        /*
            Datos opcionales del tipo de cuenta.

            Solo se usan si actualizar_tipo viene como true, 1 o "true".
        */
        const actualizarTipo = convertirBooleano(bodyData.actualizar_tipo);
        const nomTipoCuenta = limpiarTexto(bodyData.nom_tipo_cuenta);
        const indNaturalezaTipo = normalizarTexto(bodyData.ind_naturaleza_tipo);
        const desTipoCuenta = limpiarTexto(bodyData.des_tipo_cuenta);

        // Validamos campos obligatorios.
        if (!codNumCuenta || !nomCuenta || codTipoCuentaTexto === null || !bodyData.num_nivel_jerarquia || !indNaturalezaCuenta || !indAceptaMovimiento) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Faltan campos obligatorios para actualizar la cuenta contable'
            });
        }

        // Validamos que el tipo de cuenta sea numerico y positivo.
        if (!esEnteroPositivo(codTipoCuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_tipo_cuenta debe ser un numero entero positivo'
            });
        }

        // Validamos el nivel de jerarquia.
        if (!esEnteroPositivo(numNivelJerarquia)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo num_nivel_jerarquia debe ser un numero entero positivo'
            });
        }

        // Validamos la naturaleza de la cuenta.
        if (!NATURALEZAS.includes(indNaturalezaCuenta)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_naturaleza_cuenta solo permite: deudora o acreedora'
            });
        }

        // Validamos si la cuenta acepta movimientos.
        if (!MOVIMIENTOS.includes(indAceptaMovimiento)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_acepta_movimiento solo permite: si o no'
            });
        }

        // Validamos el estado de la cuenta.
        if (!ESTADOS_CUENTA.includes(indEstado)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_estado solo permite: activo o inactivo'
            });
        }

        // Validamos la cuenta padre si fue enviada.
        if (codCuentaPadre !== null && !esEnteroPositivo(codCuentaPadre)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo cod_cuenta_padre debe ser un numero entero positivo'
            });
        }

        // Validamos que una cuenta no pueda ser su propia cuenta padre.
        if (codCuentaPadre === codCuenta) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Una cuenta no puede ser su propia cuenta padre'
            });
        }

        /*
            Si se desea actualizar el tipo de cuenta,
            validamos que se envien los datos necesarios.
        */
        if (actualizarTipo) {
            if (!nomTipoCuenta || !indNaturalezaTipo) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'Para actualizar el tipo de cuenta debe enviar nom_tipo_cuenta e ind_naturaleza_tipo'
                });
            }

            if (!NATURALEZAS.includes(indNaturalezaTipo)) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'El campo ind_naturaleza_tipo solo permite: deudora o acreedora'
                });
            }
        }

        // Obtenemos una conexion del pool.
        connection = await pool.getConnection();

        // Validamos que la cuenta que se quiere actualizar exista.
        const [cuentaActual] = await connection.query(
            'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_cuenta = ? LIMIT 1',
            [codCuenta]
        );

        if (cuentaActual.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'La cuenta contable indicada no existe'
            });
        }

        // Validamos que el tipo de cuenta exista.
        const [tipoCuenta] = await connection.query(
            'SELECT cod_tipo_cuenta FROM cc_tipo_cuenta WHERE cod_tipo_cuenta = ? LIMIT 1',
            [codTipoCuenta]
        );

        if (tipoCuenta.length === 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El tipo de cuenta indicado no existe'
            });
        }

        // Validamos que no exista otra cuenta con el mismo numero contable.
        const [cuentaDuplicada] = await connection.query(
            'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_num_cuenta = ? AND cod_cuenta <> ? LIMIT 1',
            [codNumCuenta, codCuenta]
        );

        if (cuentaDuplicada.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe otra cuenta contable con ese numero de cuenta'
            });
        }

        // Si se envio cuenta padre, validamos que exista.
        if (codCuentaPadre !== null) {
            const [cuentaPadre] = await connection.query(
                'SELECT cod_cuenta FROM cc_catalogo_cuenta WHERE cod_cuenta = ? LIMIT 1',
                [codCuentaPadre]
            );

            if (cuentaPadre.length === 0) {
                return res.status(400).json({
                    estado: 'error',
                    mensaje: 'La cuenta padre indicada no existe'
                });
            }
        }

        // Ejecutamos el procedimiento almacenado de actualizacion.
        await connection.query(
            'CALL cc_upd_modulo_catalogo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                codCuenta,
                codNumCuenta,
                nomCuenta,
                codTipoCuenta,
                codCuentaPadre,
                numNivelJerarquia,
                indNaturalezaCuenta,
                indAceptaMovimiento,
                desCuenta,
                indEstado,
                usrModificacion,
                actualizarTipo ? 1 : 0,
                nomTipoCuenta,
                indNaturalezaTipo,
                desTipoCuenta
            ]
        );

        // Respondemos confirmando la actualizacion.
        return res.status(200).json({
            estado: 'ok',
            mensaje: indEstado === 'inactivo'
                ? 'Cuenta contable inactivada correctamente en el catalogo'
                : 'Cuenta contable actualizada correctamente en el catalogo',
            cod_cuenta: codCuenta
        });

    } catch (error) {
        // Mostramos el error tecnico en consola.
        console.error('Error al actualizar cuenta contable:', {
            codigo: error.code,
            estadoSql: error.sqlState,
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

        // Capturamos duplicados por restricciones unique.
        if (error.code === 'ER_DUP_ENTRY') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe un registro duplicado en la base de datos'
            });
        }

        // Respuesta general para errores no controlados.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al actualizar la cuenta en el catalogo'
        });

    } finally {
        // Liberamos la conexion si fue tomada del pool.
        if (connection) connection.release();
    }
};


// Exportamos los metodos del controlador de catalogo.
module.exports = {
    obtenerCatalogo,
    crearCuenta,
    actualizarCuenta
};