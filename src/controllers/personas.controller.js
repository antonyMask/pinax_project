// Importamos el pool de conexiones configurado en src/config/db.js.
// Este pool nos permite ejecutar consultas en MySql de forma controlada
const { pool } = require('../config/db');

/*
    Base:
    - sistema_contable_pinax
    Controlador: obtenerPersonas
    Funcion:
    - Consulta personas usando el procedimiento almacenado:
      pa_sel_modulo_personas.

    Metodo HTTP:
    - GET

    Rutas esperadas:
    - /api/personas
    - /api/personas?cod_people=1
    - /api/personas?dni=0801199900001
    - /api/personas?ind_people=activo
    - /api/personas?ind_people=inactivo
*/

const obtenerPersonas = async (req, res) => {
    try {
        // Extraemos los filtros enviados por query params.
        const { cod_people, dni, ind_people } = req.query;

        // Validamos cod_people solo si viene en la URL.
        if (cod_people && isNaN(Number(cod_people))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_people debe ser numerico'
            });
        }

        // Validamos ind_people solo si viene en la URL.
        if (ind_people && !['activo', 'inactivo'].includes(ind_people)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro ind_people solo permite activo o inactivo'
            });
        }

        // Convertimos cod_people a numero si viene informado.
        const codPeopleParam = cod_people ? Number(cod_people) : null;

        // Limpiamos el DNI si viene informado.
        const dniParam = dni ? dni.trim() : null;

        // Limpiamos el estado si viene informado.
        const indPeopleParam = ind_people ? ind_people.trim() : null;
        const [resultado] = await pool.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [codPeopleParam, dniParam, indPeopleParam]
        );

        // MySQL devuelve los resultados del CALL dentro del primer arreglo.
        const personas = resultado[0] || [];

        // Respondemos con los datos encontrados.
        return res.status(200).json({
            estado: 'ok',
            total: personas.length,
            datos: personas
        });

    } catch (error) {
        // Mostramos informacion tecnica solo en la terminal.
        console.error('Error al obtener personas:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Respuesta controlada para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar personas'
        });
    }
};


/*
    Controlador: crearPersona
    Funcion:
    - registra una nueva persona usando el procedimiento almacenado:
      pa_ins_modulo_personas.

    Metodo HTTP:
    - POST

    Ruta esperada:
    - /api/personas
*/
const crearPersona = async (req, res) => {
    let connection;

    try {
        // Extraemos los datos enviados en el cuerpo de la peticion.
        const {
            dni,
            firstname,
            middlename,
            lastname,
            sex,
            ind_civil,
            age,
            tip_person,
            usr_add
        } = req.body;

        // Validamos campos obligatorios.
        if (!dni || !firstname || !middlename || !lastname || !sex || !ind_civil || age === undefined || !tip_person) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Todos los campos obligatorios deben ser enviados'
            });
        }

        // Validamos que age sea numerico.
        if (isNaN(Number(age))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe ser numerico'
            });
        }

        // Validamos rango permitido para la edad.
        if (Number(age) < 0 || Number(age) > 120) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe estar entre 0 y 120'
            });
        }

        // Validamos valores permitidos para sex.
        const sexPermitidos = ['M', 'W', 'F', 'D'];

        if (!sexPermitidos.includes(sex)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo sex solo permite los valores M, W, F o D'
            });
        }

        // Validamos valores permitidos para estado civil.
        const estadosCivilesPermitidos = ['S', 'M', 'W'];

        if (!estadosCivilesPermitidos.includes(ind_civil)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_civil solo permite los valores S, M o W'
            });
        }

        // Validamos valores permitidos para tipo de persona.
        const tiposPersonaPermitidos = ['N', 'J'];

        if (!tiposPersonaPermitidos.includes(tip_person)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_person solo permite los valores N o J'
            });
        }

        // Obtenemos una conexion del pool.
        connection = await pool.getConnection();

        // Verificamos si ya existe una persona con el mismo DNI.
        const [resultadoDuplicado] = await connection.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [null, dni.trim(), null]
        );

        const personasDuplicadas = resultadoDuplicado[0] || [];

        if (personasDuplicadas.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe una persona registrada con ese DNI'
            });
        }

        // Ejecutamos el procedimiento almacenado de insercion.
        await connection.query(
            'CALL pa_ins_modulo_personas(?, ?, ?, ?, ?, ?, ?, ?, ?, @p_cod_people_generado)',
            [
                dni.trim(),
                firstname.trim(),
                middlename.trim(),
                lastname.trim(),
                sex,
                ind_civil,
                Number(age),
                tip_person,
                usr_add ? usr_add.trim() : 'sistema'
            ]
        );

        // Consultamos el valor generado por el parametro OUT.
        const [resultadoSalida] = await connection.query(
            'SELECT @p_cod_people_generado AS cod_people'
        );

        const codPeopleGenerado = resultadoSalida[0].cod_people;

        // Validamos que el procedimiento haya devuelto un codigo.
        if (!codPeopleGenerado) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se pudo registrar la persona'
            });
        }

        // Respondemos con codigo 201 porque se creo un nuevo registro.
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Persona registrada correctamente',
            cod_people: codPeopleGenerado
        });

    } catch (error) {
        // Mostramos informacion tecnica solo en la terminal.
        console.error('Error al crear persona:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Respuesta controlada para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al registrar persona'
        });

    } finally {
        // Liberamos la conexion si fue utilizada.
        if (connection) {
            connection.release();
        }
    }
};

/*
    Controlador: actualizarPersona
    Funcion:
    - actualiza una persona usando el procedimiento almacenado:
      pa_upd_modulo_personas.

    Metodo HTTP:
    - PUT

    Ruta esperada:
    - /api/personas/:cod_people
*/
const actualizarPersona = async (req, res) => {
    try {
        // Extraemos el codigo de persona enviado por la URL.
        const { cod_people } = req.params;

        // Extraemos los datos enviados en el cuerpo de la peticion.
        const {
            dni,
            firstname,
            middlename,
            lastname,
            sex,
            ind_civil,
            age,
            tip_person,
            ind_people
        } = req.body;

        // Validamos el codigo de persona.
        if (!cod_people || isNaN(Number(cod_people))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parametro cod_people debe ser numerico'
            });
        }

        // Validamos campos obligatorios.
        if (!dni || !firstname || !middlename || !lastname || !sex || !ind_civil || age === undefined || !tip_person || !ind_people) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Todos los campos obligatorios deben ser enviados'
            });
        }

        // Validamos que age sea numerico.
        if (isNaN(Number(age))) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe ser numerico'
            });
        }

        // Validamos rango permitido para la edad.
        if (Number(age) < 0 || Number(age) > 120) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe estar entre 0 y 120'
            });
        }

        // Validamos valores permitidos para sex.
        const sexPermitidos = ['M', 'W', 'F', 'D'];

        if (!sexPermitidos.includes(sex)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo sex solo permite los valores M, W, F o D'
            });
        }

        // Validamos valores permitidos para estado civil.
        const estadosCivilesPermitidos = ['S', 'M', 'W'];

        if (!estadosCivilesPermitidos.includes(ind_civil)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_civil solo permite los valores S, M o W'
            });
        }

        // Validamos valores permitidos para tipo de persona.
        const tiposPersonaPermitidos = ['N', 'J'];

        if (!tiposPersonaPermitidos.includes(tip_person)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_person solo permite los valores N o J'
            });
        }

        // Validamos valores permitidos para estado logico.
        const estadosPersonaPermitidos = ['activo', 'inactivo'];

        if (!estadosPersonaPermitidos.includes(ind_people)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_people solo permite activo o inactivo'
            });
        }

        // Verificamos que la persona exista.
        const [resultadoPersona] = await pool.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [Number(cod_people), null, null]
        );

        const personaEncontrada = resultadoPersona[0] || [];

        if (personaEncontrada.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No existe una persona registrada con ese codigo'
            });
        }

        // Verificamos que el DNI no pertenezca a otra persona.
        const [resultadoDni] = await pool.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [null, dni.trim(), null]
        );

        const personasConDni = resultadoDni[0] || [];

        const dniPerteneceAOtraPersona = personasConDni.some(
            persona => Number(persona.cod_people) !== Number(cod_people)
        );

        if (dniPerteneceAOtraPersona) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El DNI ya pertenece a otra persona registrada'
            });
        }

        // Ejecutamos el procedimiento almacenado de actualizacion.
        await pool.query(
            'CALL pa_upd_modulo_personas(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                Number(cod_people),
                dni.trim(),
                firstname.trim(),
                middlename.trim(),
                lastname.trim(),
                sex,
                ind_civil,
                Number(age),
                tip_person,
                ind_people
            ]
        );

        // Respondemos con exito.
        return res.status(200).json({
            estado: 'ok',
            mensaje: 'Persona actualizada correctamente',
            cod_people: Number(cod_people)
        });

    } catch (error) {
        // Mostramos informacion tecnica solo en la terminal.
        console.error('Error al actualizar persona:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Respuesta controlada para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al actualizar persona'
        });
    }
};

// Exportamos el controlador para poder usarlos en las rutas.
module.exports = {
    obtenerPersonas,
    crearPersona,
    actualizarPersona
};