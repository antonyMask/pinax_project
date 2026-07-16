const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const jwt = require('jsonwebtoken');

const { pool } = require('../config/db');

const {
    obtenerConfiguracionAuth
} = require('../config/auth');


// Determina si una clave almacenada tiene formato MD5 heredado.
const esHashMd5 = (clave) => {
    return /^[a-f0-9]{32}$/i.test(String(clave || ''));
};


// Calcula MD5 exclusivamente para validar usuarios heredados.

const calcularMd5 = (clave) => {
    return crypto
        .createHash('md5')
        .update(clave, 'utf8')
        .digest('hex');
};


// Compara dos hashes MD5 con una comparación de tiempo constante.
const compararMd5 = (claveIngresada, hashAlmacenado) => {
    const hashIngresado = calcularMd5(claveIngresada);

    const bufferIngresado = Buffer.from(hashIngresado, 'hex');
    const bufferAlmacenado = Buffer.from(hashAlmacenado, 'hex');

    if (bufferIngresado.length !== bufferAlmacenado.length) {
        return false;
    }

    return crypto.timingSafeEqual(
        bufferIngresado,
        bufferAlmacenado
    );
};

// Construye la representación pública del usuario.

const construirUsuarioPublico = (usuario) => {
    return {
        cod_user: Number(usuario.cod_user),
        cod_people: usuario.cod_people
            ? Number(usuario.cod_people)
            : null,
        cod_tipusers: usuario.cod_tipusers
            ? Number(usuario.cod_tipusers)
            : null,
        name: usuario.name,
        firstname: usuario.firstname || null,
        middlename: usuario.middlename || null,
        lastname: usuario.lastname || null,
        role: usuario.role || null,
        ind_usr: usuario.ind_usr,
        ind_ins: usuario.ind_ins
    };
};

/*
 - POST /api/auth/login
 - Valida las credenciales y devuelve un JWT firmado.
 */
const login = async (req, res) => {
    let connection;

    try {
        const { name, password } = req.body || {};

        // El usuario puede limpiarse; la contraseña nunca debe recortarse.
        const nombreUsuario = typeof name === 'string'
            ? name.trim()
            : '';

        const claveIngresada = typeof password === 'string'
            ? password
            : '';

        if (!nombreUsuario || !claveIngresada) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El usuario y la contraseña son obligatorios'
            });
        }

        if (nombreUsuario.length > 255) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El nombre de usuario no es válido'
            });
        }

        // bcrypt trabaja con un máximo efectivo de 72 bytes.
        if (Buffer.byteLength(claveIngresada, 'utf8') > 72) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'La contraseña supera la longitud permitida'
            });
        }

        connection = await pool.getConnection();

        // Consultamos la cuenta y los datos básicos asociados.
        const [usuarios] = await connection.query(
            `
                SELECT
                    u.COD_USER AS cod_user,
                    u.COD_PEOPLE AS cod_people,
                    u.COD_TIPUSERS AS cod_tipusers,
                    u.NAME AS name,
                    u.CLAVE AS clave,
                    u.IND_USR AS ind_usr,
                    u.IND_INS AS ind_ins,
                    p.FIRSTNAME AS firstname,
                    p.MIDDLENAME AS middlename,
                    p.LASTNAME AS lastname,
                    t.NOM_TYPE AS role
                FROM users u
                LEFT JOIN pa_people p
                    ON p.COD_PEOPLE = u.COD_PEOPLE
                LEFT JOIN pa_tipusers t
                    ON t.COD_TIPUSERS = u.COD_TIPUSERS
                WHERE u.NAME = ?
                LIMIT 1
            `,
            [nombreUsuario]
        );

        // Usamos un mensaje genérico para no revelar usuarios existentes.
        if (usuarios.length === 0) {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'Credenciales no válidas'
            });
        }

        const usuario = usuarios[0];

        if (String(usuario.ind_usr) !== '1') {
            return res.status(403).json({
                estado: 'error',
                mensaje: 'El usuario se encuentra inactivo'
            });
        }

        const config = obtenerConfiguracionAuth();
        let claveValida = false;
        let claveHeredadaMigrada = false;

        /*
         - Compatibilidad con usuarios antiguos.
         - Si la clave es MD5, se valida una única vez y después
         - se reemplaza automáticamente por bcrypt.
         */
        if (esHashMd5(usuario.clave)) {
            claveValida = compararMd5(
                claveIngresada,
                usuario.clave
            );

            if (claveValida) {
                const nuevaClaveSegura = await bcrypt.hash(
                    claveIngresada,
                    config.bcryptRounds
                );

                await connection.query(
                    `
                        UPDATE users
                        SET CLAVE = ?
                        WHERE COD_USER = ?
                    `,
                    [
                        nuevaClaveSegura,
                        usuario.cod_user
                    ]
                );

                claveHeredadaMigrada = true;
            }
        } else {
            // Los usuarios nuevos se validarán directamente con bcrypt.
            claveValida = await bcrypt.compare(
                claveIngresada,
                usuario.clave
            );
        }

        if (!claveValida) {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'Credenciales no válidas'
            });
        }

        const usuarioPublico = construirUsuarioPublico(usuario);

        // El JWT contiene solo identidad y rol; nunca credenciales.
        const token = jwt.sign(
            {
                name: usuarioPublico.name,
                role: usuarioPublico.role,
                cod_people: usuarioPublico.cod_people
            },
            config.secret,
            {
                algorithm: 'HS256',
                expiresIn: config.expiresIn,
                issuer: config.issuer,
                audience: config.audience,
                subject: String(usuarioPublico.cod_user),
                jwtid: crypto.randomUUID()
            }
        );

        return res.status(200).json({
            estado: 'ok',
            mensaje: 'Inicio de sesión correcto',
            token_type: 'Bearer',
            access_token: token,
            expires_in: config.expiresIn,
            clave_heredada_migrada: claveHeredadaMigrada,
            usuario: usuarioPublico
        });
    } catch (error) {
        console.error('Error al iniciar sesión:', {
            codigo: error.code,
            mensaje: error.message,
            sql: error.sql
        });

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al iniciar sesión'
        });
    } finally {
        if (connection) {
            connection.release();
        }
    }
};

/*
 - GET /api/auth/me
 - Devuelve el usuario correspondiente al token actual.
 */
const me = async (req, res) => {
    try {
        const codUser = Number(req.auth?.sub);

        if (!Number.isInteger(codUser) || codUser <= 0) {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'La identidad del token no es válida'
            });
        }

        const [usuarios] = await pool.query(
            `
                SELECT
                    u.COD_USER AS cod_user,
                    u.COD_PEOPLE AS cod_people,
                    u.COD_TIPUSERS AS cod_tipusers,
                    u.NAME AS name,
                    u.IND_USR AS ind_usr,
                    u.IND_INS AS ind_ins,
                    p.FIRSTNAME AS firstname,
                    p.MIDDLENAME AS middlename,
                    p.LASTNAME AS lastname,
                    t.NOM_TYPE AS role
                FROM users u
                LEFT JOIN pa_people p
                    ON p.COD_PEOPLE = u.COD_PEOPLE
                LEFT JOIN pa_tipusers t
                    ON t.COD_TIPUSERS = u.COD_TIPUSERS
                WHERE u.COD_USER = ?
                LIMIT 1
            `,
            [codUser]
        );

        if (
            usuarios.length === 0
            || String(usuarios[0].ind_usr) !== '1'
        ) {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'La sesión ya no es válida'
            });
        }

        return res.status(200).json({
            estado: 'ok',
            usuario: construirUsuarioPublico(usuarios[0])
        });
    } catch (error) {
        console.error('Error al consultar usuario autenticado:', {
            codigo: error.code,
            mensaje: error.message,
            sql: error.sql
        });

        return res.status(500).json({
            estado: 'error',
            mensaje: 'Error interno al consultar la sesión'
        });
    }
};

module.exports = {
    login,
    me
};