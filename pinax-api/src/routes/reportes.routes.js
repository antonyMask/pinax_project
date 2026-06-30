const express = require('express');

// Importamos el metodo GET del controlador de reportes financieros.
const {
    obtenerReportesFinancieros,
    crearReporteFinanciero,
    actualizarReporteFinanciero
} = require('../controllers/reportes.controller');

const router = express.Router();

/*
    Ruta: GET /api/reportes
    Funcion:
    - consulta reportes financieros.
    - permite filtrar por cod_reporte, cod_periodo, tip_reporte, ind_estado y cod_user.
    - permite incluir detalle cuando se envia cod_reporte.
*/
router.get('/', obtenerReportesFinancieros);

/* Ruta: POST /api/
   Funcion:
   - genera un nuevo reporte financiero.
   - permite generar balance general o estado de resultados.
*/
router.post('/', crearReporteFinanciero);

/* Ruta: PUT /api/reportes
   Funcion:
   - genera un nuevo reporte financiero
   - permite generar balance general o estado de resultados
*/
router.put('/:cod_reporte', actualizarReporteFinanciero);

/* Ruta: PUT /api/reportes/:cod_reporte
   Funcion:
   - actualiza un reporte financiero.
   - permite soft delete cambiando ind_estado a anulado.
*/
router.put('/:cod_reporte', actualizarReporteFinanciero);

// Exportamos las rutas del modulo de reportes financieros.
module.exports = router;