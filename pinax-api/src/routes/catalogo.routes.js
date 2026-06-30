const express = require('express');

// Importamos el metodo GET del controlador de catalogo
const {
    obtenerCatalogo,
    crearCuenta,
    actualizarCuenta
} = require('../controllers/catalogo.controller');

const router = express.Router();

/*
    Ruta: GET /api/catalogo
    Funcion:
    - Consulta el catalogo de Cuentas.
    - Permite filtrar por cod_tipo_cuenta.
*/
router.get('/', obtenerCatalogo);

/* Ruta: POST /api/catalogo

   Funcion:
   - registra una nueva cuenta contable en el catalogo
*/
router.post('/', crearCuenta);


/* Ruta: PUT /api/catalogo/:cod_cuenta
   Funcion:
   - actualiza una cuenta contable existente
   - permite soft delete cambiando ind_estado a inactivo
*/

router.put('/:cod_cuenta', actualizarCuenta)


// Exportamos las rutas del modulo catalogo.
module.exports = router;