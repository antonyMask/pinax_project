// Importamos Express para crear la API
const express = require('express');

// Importamos CORS para permitir solicitudes desde origenes,
// por ejemplo un frontend.
const cors = require('cors');

// Importamos Helmet para agregar cabeceras basicas de seguridad HTTP
const helmet = require('helmet');

// Importamos las rutas del modulo personas
const personasRoutes = require('./routes/personas.routes');

// Creamos la aplicacion de Express.
const app = express();

// Middleware de seguridad basica.
app.use(helmet());

// Middleware para permitir solicitudes externas.
// En desarrollo lo dejamos abierto; mas adelante puede limitarse a un dominio especifico.
app.use(cors());

// Middleware para permitir que Express lea datos en formato JSON.
app.use(express.json());

// Registramos las rutas del modulo pesonas.
// Todas las rutas de personas comenzaran con /api/personas
app.use('/api/personas', personasRoutes);

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