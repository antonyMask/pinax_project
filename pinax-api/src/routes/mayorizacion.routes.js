const express = require('express');

// Importamos el metodo GET del controlador de mayorizacion
const {
    obtenerSaldosCuentas,
    crearSaldoCuenta,
    actualizarSaldoCuenta
} = require('../controllers/ct_mayorizacion.controller');

const router = express.Router();

/* Ruta GET /api/mayorizacion
   Funcion:
   - consulta los salgos de cuenta por periodo.
   - permite filtrar por cod_saldo, cod_periodo, cod_cuenta o ind_estado.
*/
router.get('/', obtenerSaldosCuentas);


/* Ruta POST /api/mayorizacion
   Funcion:
   - registra un nuevo saldo de cuenta por periodo.
*/
router.post('/', crearSaldoCuenta)


/* Ruta PUT /api/mayorizacion
   Funcion:
   - actualiza un saldo de cuenta.
   - permite soft delete cambiando ind_estado a cerrado.
*/
router.put('/:cod_saldo', actualizarSaldoCuenta);


// Exportamos las rutas del modulo de mayorizacion
module.exports = router;