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
        // Extraemos los datos enviados en el cuerpo de la petición.
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

        /*
            Función auxiliar para limpiar textos de forma segura.

            - Acepta únicamente valores de tipo string.
            - Elimina espacios al inicio y al final.
            - Devuelve una cadena vacía si recibe otro tipo de dato.
        */
        const limpiarTexto = (valor) => {
            return typeof valor === 'string' ? valor.trim() : '';
        };

        // Limpiamos los campos de texto antes de validarlos.
        const dniLimpio = limpiarTexto(dni);
        const primerNombre = limpiarTexto(firstname);
        const segundoNombre = limpiarTexto(middlename);
        const apellido = limpiarTexto(lastname);

        /*
            Normalizamos los valores enumerados a mayúsculas.

            Esto permite que la API acepte, por ejemplo, "m" y lo convierta
            a "M", que es el valor esperado por la base de datos.
        */
        const sexo = limpiarTexto(sex).toUpperCase();
        const estadoCivil = limpiarTexto(ind_civil).toUpperCase();
        const tipoPersona = limpiarTexto(tip_person).toUpperCase();
        const usuarioAdicion = limpiarTexto(usr_add) || 'sistema';

        // Convertimos la edad a número para validarla y enviarla a MySQL.
        const edad = Number(age);

        /*
            Validamos campos obligatorios.

            Se valida después de limpiar los textos para evitar que un valor
            compuesto únicamente por espacios sea considerado válido.
        */
        if (
            !dniLimpio ||
            !primerNombre ||
            !segundoNombre ||
            !apellido ||
            !sexo ||
            !estadoCivil ||
            age === undefined ||
            age === null ||
            age === '' ||
            !tipoPersona
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Todos los campos obligatorios deben ser enviados.'
            });
        }

        /*
            Validación del DNI.

            ^[0-9]{13}$ significa:
            - inicio del valor;
            - exactamente 13 caracteres numéricos;
            - final del valor.

            El DNI se trata como texto, no como número, para preservar
            posibles ceros al inicio.
        */
        const dniValido = /^[0-9]{13}$/.test(dniLimpio);

        if (!dniValido) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El DNI debe contener exactamente 13 dígitos numéricos.'
            });
        }

        // Validamos que age sea un número entero.
        if (!Number.isInteger(edad)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe ser un número entero.'
            });
        }

        // Validamos el rango permitido para la edad.
        if (edad < 0 || edad > 120) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe estar entre 0 y 120.'
            });
        }

        // Validamos los valores permitidos para sexo.
        const sexPermitidos = ['M', 'W', 'F', 'D'];

        if (!sexPermitidos.includes(sexo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo sex solo permite los valores M, W, F o D.'
            });
        }

        // Validamos los valores permitidos para estado civil.
        const estadosCivilesPermitidos = ['S', 'M', 'W'];

        if (!estadosCivilesPermitidos.includes(estadoCivil)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_civil solo permite los valores S, M o W.'
            });
        }

        // Validamos los valores permitidos para tipo de persona.
        const tiposPersonaPermitidos = ['N', 'J'];

        if (!tiposPersonaPermitidos.includes(tipoPersona)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_person solo permite los valores N o J.'
            });
        }

        // Obtenemos una conexión individual del pool.
        connection = await pool.getConnection();

        /*
            Verificamos si ya existe una persona con el mismo DNI.

            Usamos el procedimiento de consulta para respetar la arquitectura
            actual de la API y no escribir SQL directo contra pa_people.
        */
        const [resultadoDuplicado] = await connection.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [null, dniLimpio, null]
        );

        // El primer result set contiene las personas encontradas.
        const personasDuplicadas = resultadoDuplicado[0] || [];

        if (personasDuplicadas.length > 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Ya existe una persona registrada con ese DNI.'
            });
        }

        /*
            Ejecutamos el procedimiento almacenado de inserción.

            El procedimiento devuelve el código generado mediante el
            parámetro de salida @p_cod_people_generado.
        */
        await connection.query(
            'CALL pa_ins_modulo_personas(?, ?, ?, ?, ?, ?, ?, ?, ?, @p_cod_people_generado)',
            [
                dniLimpio,
                primerNombre,
                segundoNombre,
                apellido,
                sexo,
                estadoCivil,
                edad,
                tipoPersona,
                usuarioAdicion
            ]
        );

        // Consultamos el valor generado por el parámetro OUT.
        const [resultadoSalida] = await connection.query(
            'SELECT @p_cod_people_generado AS cod_people'
        );

        const codPeopleGenerado = resultadoSalida[0]?.cod_people;

        // Validamos que el procedimiento haya devuelto un código válido.
        if (!codPeopleGenerado) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'No se pudo registrar la persona.'
            });
        }

        // Respondemos con 201 porque se creó un nuevo recurso.
        return res.status(201).json({
            estado: 'ok',
            mensaje: 'Persona registrada correctamente.',
            cod_people: codPeopleGenerado
        });

    } catch (error) {
        // Mostramos detalles técnicos únicamente en la consola del servidor.
        console.error('Error al crear persona:', {
            codigo: error.code,
            estadoSql: error.sqlState,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Respondemos con un mensaje seguro para el cliente.
        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al registrar persona.'
        });

    } finally {
        // Liberamos la conexión para que vuelva al pool.
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
        // Extraemos el código de persona recibido en la URL.
        const { cod_people } = req.params;

        // Extraemos los datos recibidos en el cuerpo de la petición.
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

        // Función local para limpiar textos de forma segura.
        const limpiarTexto = (valor) => {
            return typeof valor === 'string' ? valor.trim() : '';
        };

        // Normalizamos los valores antes de validarlos.
        const codigoPersona = Number(cod_people);
        const dniLimpio = limpiarTexto(dni);
        const primerNombre = limpiarTexto(firstname);
        const segundoNombre = limpiarTexto(middlename);
        const apellido = limpiarTexto(lastname);
        const sexo = limpiarTexto(sex).toUpperCase();
        const estadoCivil = limpiarTexto(ind_civil).toUpperCase();
        const tipoPersona = limpiarTexto(tip_person).toUpperCase();
        const estadoPersona = limpiarTexto(ind_people).toLowerCase();
        const edad = Number(age);

        // Validamos que el código recibido en la URL sea válido.
        if (!Number.isInteger(codigoPersona) || codigoPersona <= 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El parámetro cod_people debe ser un número entero mayor que cero'
            });
        }

        // Validamos que todos los campos requeridos tengan contenido.
        if (
            !dniLimpio ||
            !primerNombre ||
            !segundoNombre ||
            !apellido ||
            !sexo ||
            !estadoCivil ||
            age === undefined ||
            age === '' ||
            !tipoPersona ||
            !estadoPersona
        ) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'Todos los campos obligatorios deben ser enviados'
            });
        }

        // El DNI debe tener solo números y exactamente 13 dígitos.
        if (!/^[0-9]{13}$/.test(dniLimpio)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El DNI debe contener exactamente 13 dígitos numéricos'
            });
        }

        // La edad debe ser un número entero.
        if (!Number.isInteger(edad)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe ser un número entero'
            });
        }

        // Validamos el rango permitido para la edad.
        if (edad < 0 || edad > 120) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo age debe estar entre 0 y 120'
            });
        }

        // Validamos los códigos aceptados para sexo.
        const sexPermitidos = ['M', 'W', 'F', 'D'];

        if (!sexPermitidos.includes(sexo)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo sex solo permite los valores M, W, F o D'
            });
        }

        // Validamos los códigos aceptados para estado civil.
        const estadosCivilesPermitidos = ['S', 'M', 'W'];

        if (!estadosCivilesPermitidos.includes(estadoCivil)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_civil solo permite los valores S, M o W'
            });
        }

        // Validamos los códigos aceptados para tipo de persona.
        const tiposPersonaPermitidos = ['N', 'J'];

        if (!tiposPersonaPermitidos.includes(tipoPersona)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo tip_person solo permite los valores N o J'
            });
        }

        // Validamos el estado lógico de la persona.
        const estadosPersonaPermitidos = ['activo', 'inactivo'];

        if (!estadosPersonaPermitidos.includes(estadoPersona)) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El campo ind_people solo permite activo o inactivo'
            });
        }

        // Confirmamos que exista una persona con ese código.
        const [resultadoPersona] = await pool.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [codigoPersona, null, null]
        );

        const personaEncontrada = resultadoPersona[0] || [];

        if (personaEncontrada.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'No existe una persona registrada con ese código'
            });
        }

        // Consultamos si el DNI pertenece a otra persona.
        const [resultadoDni] = await pool.query(
            'CALL pa_sel_modulo_personas(?, ?, ?)',
            [null, dniLimpio, null]
        );

        const personasConDni = resultadoDni[0] || [];

        const dniPerteneceAOtraPersona = personasConDni.some((persona) => {
            return Number(persona.cod_people) !== codigoPersona;
        });

        if (dniPerteneceAOtraPersona) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El DNI ya pertenece a otra persona registrada'
            });
        }

        // Ejecutamos el procedimiento almacenado de actualización.
        await pool.query(
            'CALL pa_upd_modulo_personas(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                codigoPersona,
                dniLimpio,
                primerNombre,
                segundoNombre,
                apellido,
                sexo,
                estadoCivil,
                edad,
                tipoPersona,
                estadoPersona
            ]
        );

        // Respondemos con éxito a Laravel.
        return res.status(200).json({
            estado: 'ok',
            mensaje: 'Persona actualizada correctamente',
            cod_people: codigoPersona
        });

    } catch (error) {
        // El detalle técnico queda únicamente en la terminal de Node.js.
        console.error('Error al actualizar persona:', {
            codigo: error.code,
            numero: error.errno,
            mensaje: error.message,
            sql: error.sql
        });

        // Laravel recibe un mensaje seguro, sin detalles de base de datos.
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