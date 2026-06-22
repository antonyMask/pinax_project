// Importamos Express para crear las rutas del modulo personas.
const express = require('express');

// Creamos un enrutador propio para este modulo
const router = express.Router();

// Importamos el controlador que contiene la logica de consulta.
const {
    obtenerPersonas,
    crearPersona,
    actualizarPersona
} = require('../controllers/personas.controller');

/* Ruta: GET /api/personas
   Funcion:
   - Devuelve las personas registradas.
   - Puede recibir filtros por query params.
   
   Ejemplos:
   - /api/personas
   - /api/personas?cod_people=1
   - /api/personas?dni=0801199900001
*/
router.get('/', obtenerPersonas);

/* Ruta: POST /api/personas
    Funcion:
    - Recibe los datos de una nueva persona en formato JSON.
    - Llama al controlador crearPersona
*/
router.post('/', crearPersona);

/*
    Ruta: PUT /api/personas/:cod_people

    Funcion:
    - Actualiza una persona existente.
    - El codigo de persona viaja como parametro en la URL.
*/
router.put('/:cod_people', actualizarPersona);

// Exportamos las rutas para registrarlas en app.js
module.exports = router;