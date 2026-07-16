const jwt = require('jsonwebtoken');

const {
    obtenerConfiguracionAuth
} = require('../config/auth');

/* Verifica el Bearer Token enviado en Authorization.
*/
const verificarToken = (req, res, next) => {
    try {
        const authorization = req.headers.authorization;

        if (
            !authorization || !authorization.startsWith('Bearer ')
        ) {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'Debe proporcionar un token de autenticación'
            });
        }

        // Extraemos unicamente el token
        const token = authorization.substring(7).trim();

        if (!token) {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'El token de autenticación esta vacío'
            });
        }

        const config = obtenerConfiguracionAuth();

        // Verificamos firma, algoritmo, emisor, destinatario y expiracion.
        const payload = jwt.verify(token, config.secret, {
            algorithms: ['HS256'],
            issuer: config.issuer,
            audience: config.audience
        });

        // El controlador podra consultar los datos autenticados.
        req.auth = payload;

        return next();
    } catch (error) {
        if (error.name === 'TokenExpiredError') {
            return res.status(401).json({
                estado: 'error',
                mensaje: 'La sesión ha expirado'
            });
        }

        console.error('Error al verificar token:', {
            nombre: error.name,
            mensaje: error.message
        });

        return res.status(401).json({
            estado: 'error',
            mensaje: 'El token de autenticación no es válido'
        });
    }
};

module.exports = {
    verificarToken
};