// Importamos el pool de conexiones configurado en src/config/db.js.
const { pool } = require('../config/db');

/*
    Controlador: obtenerCatalogo
    Funcion: Consulta el catálogo de cuentas usando el procedimiento: cc_sel_modulo_catalogo
    Metodo HTTP: GET
    Rutas esperadas:
    - /api/catalogo          (Trae todo enviando 0)
    - /api/catalogo?id=1     (Trae una cuenta específica)
*/
const obtenerCatalogo = async (req, res) => {
    try {
        // Extraemos el ID si lo mandan por Query Params (ej: ?id=5)
        const { id } = req.query;

        // Si viene ID, validamos que sea numérico
        if (id && isNaN(Number(id))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro id debe ser numerico'
            });
        }

        // Si no viene id, mandamos 0 para traer todo el árbol
        const idParam = id ? Number(id) : 0;

        // Ejecutamos el procedimiento
        const [resultado] = await pool.query(
            'CALL cc_sel_modulo_catalogo(?)',
            [idParam]
        );

        // MySQL devuelve los resultados en la primera posición del array
        const cuentas = resultado[0] || [];

        return res.status(200).json({
            estado: 'ok',
            total: cuentas.length,
            datos: cuentas
        });

    } catch (error) {
        console.error('Error al obtener catalogo:', {
            codigo: error.code,
            mensaje: error.message,
            sql: error.sql
        });

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar el catalogo de cuentas'
        });
    }
};

/*
    Controlador: crearCuenta
    Funcion: Registra una cuenta contable usando el procedimiento: cc_ins_modulo_catalogo
    Metodo HTTP: POST
    Ruta esperada: /api/catalogo
*/
const crearCuenta = async (req, res) => {
    let connection;

    try {
        // Si req.body no está mapeado por el router, capturamos los datos manualmente
        let bodyData = req.body;

        if (!bodyData) {
            const buffers = [];
            for await (const chunk of req) {
                buffers.push(chunk);
            }
            const rawString = Buffer.concat(buffers).toString();
            bodyData = rawString ? JSON.parse(rawString) : {};
        }

        const {
            cod_tipo_cuenta,       // Para usar uno existente (ej: 2 para Pasivo)
            nom_tipo_cuenta,       // (Opcional) Solo si cod_tipo_cuenta es 0 o null
            ind_naturaleza_tipo,   // (Opcional) Solo si cod_tipo_cuenta es 0 o null
            des_tipo_cuenta,       // (Opcional) Solo si cod_tipo_cuenta es 0 o null
            cod_num_cuenta,
            nom_cuenta,
            cod_cuenta_padre,
            num_nivel_jerarquia,
            ind_naturaleza,        // Naturaleza de la cuenta ('deudora'/'acreedora')
            ind_acepta_movimiento, // 'si'/'no'
            des_cuenta,
            ind_estado,
            usr_adicion
        } = bodyData;

        // Validaciones mínimas obligatorias para la cuenta
        if (!cod_num_cuenta || !nom_cuenta || !cod_tipo_cuenta || !num_nivel_jerarquia || !ind_naturaleza || !ind_acepta_movimiento) {
            return res.writeHead(400, { 'Content-Type': 'application/json' }).end(JSON.stringify({
                estado: 'error',
                mensaje: 'Faltan campos obligatorios para registrar la cuenta contable'
            }));
        }

        connection = await pool.getConnection();

        // Ejecución del procedimiento almacenado con 13 parámetros IN y 1 parámetro OUT (@p_cod_gen)
await connection.query(
    'CALL cc_ins_modulo_catalogo(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @p_cod_gen)',
    [
        Number(cod_tipo_cuenta),                                 // 1. p_cod_tipo_cuenta
        nom_tipo_cuenta ? nom_tipo_cuenta.trim() : null,         // 2. p_nom_tipo_cuenta
        ind_naturaleza_tipo ? ind_naturaleza_tipo.trim() : null, // 3. p_ind_naturaleza_tipo
        des_tipo_cuenta ? des_tipo_cuenta.trim() : null,         // 4. p_des_tipo_cuenta
        cod_num_cuenta.trim(),                                   // 5. p_cod_num_cuenta
        nom_cuenta.trim(),                                       // 6. p_nom_cuenta
        cod_cuenta_padre ? Number(cod_cuenta_padre) : null,      // 7. p_cod_cuenta_padre
        Number(num_nivel_jerarquia),                             // 8. p_num_nivel_jerarquia
        ind_naturaleza.toLowerCase().trim(),                     // 9. p_ind_naturaleza_cuenta
        ind_acepta_movimiento.toLowerCase().trim(),              // 10. p_ind_acepta_movimiento
        des_cuenta ? des_cuenta.trim() : null,                   // 11. p_des_cuenta
        ind_estado ? ind_estado.toLowerCase().trim() : 'activo', // 12. p_ind_estado
        usr_adicion ? usr_adicion.trim() : 'sistema'             // 13. p_usr_adicion
    ]
);

        // Rescatamos la variable OUT para verificar si realmente se creó o si falló silenciosamente
        const [rows] = await connection.query('SELECT @p_cod_gen AS id_generado');
        const idGenerado = rows[0]?.id_generado;

        if (!idGenerado) {
            return res.writeHead(400, { 'Content-Type': 'application/json' }).end(JSON.stringify({
                estado: 'error',
                mensaje: 'La cuenta no pudo ser insertada. Verifique las restricciones jerárquicas o duplicados en la base de datos.'
            }));
        }

        return res.writeHead(201, { 'Content-Type': 'application/json' }).end(JSON.stringify({
            estado: 'ok',
            mensaje: 'Cuenta contable registrada correctamente en el catalogo',
            cod_cuenta: idGenerado
        }));

    } catch (error) {
        console.error('Error al crear cuenta contable:', error);
        return res.writeHead(500, { 'Content-Type': 'application/json' }).end(JSON.stringify({
            estado: 'error',
            mensaje: 'Error interno al registrar la cuenta en el catalogo'
        }));
    } finally {
        if (connection) connection.release();
    }
};

// Exportamos las funciones para las rutas
module.exports = {
    obtenerCatalogo,
    crearCuenta
};