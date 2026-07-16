// Importamos Express para crear la API
const express = require('express');

// Importamos CORS para permitir solicitudes desde origenes,
// por ejemplo un frontend.
const cors = require('cors');

// Importamos Helmet para agregar cabeceras basicas de seguridad HTTP
const helmet = require('helmet');

// Importamos las rutas del modulo personas
const personasRoutes = require('./routes/personas.routes');

// Importamos las rutas del modulo catalogo de cuentas
const catalogoRoutes = require('./routes/catalogo.routes');

// Importamos las rutas del modulo de cuentas T & Mayorizacion
const mayorizacionRoutes = require('./routes/mayorizacion.routes');

// Importamos las rutas del modulo de reportes
const reportesRoutes = require('./routes/reportes.routes');

// Importamos las rutas del modulo de asientos
const asientosRoutes = require('./routes/asientos.routes');

// Creamos la aplicacion de Express.
const app = express();

// Middleware de seguridad basica.
app.use(helmet());

// Middleware para permitir solicitudes externas.
// En desarrollo lo dejamos abierto; mas adelante puede limitarse a un dominio especifico.
app.use(cors());

// Middleware para permitir que Express lea datos en formato JSON.
app.use(express.json());

// Importamos las rutas del modulo de autenticacion
const authRoutes = require('./routes/auth.routes');

// Las rutas de autenticacion comienzan con /api/auth.
app.use('/api/auth', authRoutes);

// Registramos las rutas del modulo pesonas.
// Todas las rutas de personas comenzaran con /api/personas
app.use('/api/personas', personasRoutes);

// Registramos las rutas del modulo catalogo de cuentas.
// Todas las rutas del catalogo comenzaran con /api/catalogo
app.use('/api/catalogo', catalogoRoutes);

// Registramos las rutas del modulo de cuentas T & Mayorizacion
// Todas las rutas de mayorizacion comenzaran con /api/mayorizacion
app.use('/api/mayorizacion', mayorizacionRoutes);

// Registramos las rutas del modulo de reportes
// Todas las rutas de reportes comenzaran con /api/reportes
app.use('/api/reportes', reportesRoutes);

// Registramos las rutas del modulo de asientos
// Todas las rutas de asientos comenzaran con /api/asientos
app.use('/api/asientos', asientosRoutes);



// Ruta inicial de prueba
// Sirve para comprobar que la API esta funcionando antes de crear rutas reales.
app.get('/', (req, res) => {
    res.status(200).json({
        estado: 'ok',
        mensaje: 'API Pinax funcionando correctamente'
    });
});

// Exportamos app para que server.js pueda levantar el servidor
module.exports = app;