const express = require('express');

const {
    rateLimit
} = require('express-rate-limit');

const {
    login,
    me
} = require('../controllers/auth.controller');

const {
    verificarToken
} = require('../middlewares/auth.middleware');

const router = express.Router();


// Limita los intentos fallidos para dificultar ataques de fuerza bruta.

const limitarLogin = rateLimit({
    windowMs: 15 * 60 * 1000,
    limit: 10,
    standardHeaders: 'draft-8',
    legacyHeaders: false,
    skipSuccessfulRequests: true,
    message: {
        estado: 'error',
        mensaje: 'Demasiados intentos. Intente nuevamente en 15 minutos.'
    }
});

// El login es público porque todavía no existe un token.
router.post('/login', limitarLogin, login);

// La consulta de sesión requiere un Bearer Token válido.
router.get('/me', verificarToken, me);

module.exports = router;