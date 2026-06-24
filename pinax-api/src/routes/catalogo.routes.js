// Importamos Express para crear las rutas del modulo catalogo.
const express = require('express');

// Creamos un enrutador propio para este modulo
const router = express.Router();

// Importamos el controlador que contiene la logica de consulta del catalogo.
const {
    obtenerCatalogo,
    crearCuenta
} = require('../controllers/catalogo.controller');

/* Ruta: GET /api/catalogo
   Funcion:
   - Devuelve las cuentas contables registradas en el catalogo.
   - Puede recibir filtros por query params (ej: id para una cuenta especifica).
   
   Ejemplos:
   - /api/catalogo
   - /api/catalogo?id=1
*/
router.get('/', obtenerCatalogo);

/* Ruta: POST /api/catalogo
    Funcion:
    - Recibe los datos de una nueva cuenta contable en formato JSON.
    - Llama al controlador crearCuenta.
*/
router.post('/', crearCuenta);

// Exportamos las rutas para registrarlas en app.js
module.exports = router;