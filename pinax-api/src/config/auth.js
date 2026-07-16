/*
 - Devuelve y valida la configuración necesaria para autenticación.
 - Todos los valores sensibles se leen desde el archivo .env.
 */
const obtenerConfiguracionAuth = () => {
    const secret = process.env.JWT_SECRET;

    // El secreto debe existir y ser suficientemente largo.
    if (!secret || secret.length < 64) {
        throw new Error(
            'JWT_SECRET debe existir y contener al menos 64 caracteres'
        );
    }

    const roundsConfigurados = Number(
        process.env.BCRYPT_ROUNDS || 12
    );

    // Solo permitimos un costo bcrypt seguro y razonable.
    const bcryptRounds = Number.isInteger(roundsConfigurados)
        && roundsConfigurados >= 10
        && roundsConfigurados <= 14
        ? roundsConfigurados
        : 12;

    return {
        secret,
        expiresIn: process.env.JWT_EXPIRES_IN || '2h',
        issuer: process.env.JWT_ISSUER || 'pinax-api',
        audience: process.env.JWT_AUDIENCE || 'pinax-frontend',
        bcryptRounds
    };
};

// Exportamos un objeto con el mismo nombre que importará el controlador.
module.exports = {
    obtenerConfiguracionAuth
};