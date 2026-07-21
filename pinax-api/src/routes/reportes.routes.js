const express = require('express');

// Importamos los métodos del controlador de reportes financieros.
const {
    obtenerReportesFinancieros,
    crearReporteFinanciero,
    actualizarReporteFinanciero,
    obtenerDetalleReporte
} = require('../controllers/reportes.controller');

const router = express.Router();

/*
    Ruta: GET /api/reportes
*/
router.get('/', obtenerReportesFinancieros);

/*
    Ruta: GET /api/reportes/:cod_reporte
    Obtiene un reporte específico por su código.
*/
router.get('/:cod_reporte', async (req, res) => {
    try {
        const { cod_reporte } = req.params;
        const { pool } = require('../config/db');

        // Validar que cod_reporte sea numérico
        if (!/^\d+$/.test(cod_reporte) || Number(cod_reporte) <= 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El código del reporte debe ser numérico y positivo'
            });
        }

        // ✅ CONSULTA CORREGIDA - Usando el procedimiento almacenado directamente
        // El procedimiento rf_sel_modulo_reportes devuelve los datos con el nombre correcto
        const [resultado] = await pool.query(
            'CALL rf_sel_modulo_reportes(?, ?, ?, ?, ?, ?)',
            [
                Number(cod_reporte), // cod_reporte
                null,                // cod_periodo
                null,                // tip_reporte
                null,                // ind_estado
                null,                // cod_user
                false                // incluir_detalle
            ]
        );

        // MySQL devuelve la cabecera en resultado[0]
        const cabecera = resultado[0] || [];

        if (cabecera.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'Reporte no encontrado'
            });
        }

        // Devolver el primer reporte encontrado
        res.json(cabecera[0]);
    } catch (error) {
        console.error('Error al obtener reporte:', error);
        res.status(500).json({
            estado: 'error',
            mensaje: 'Error al obtener reporte',
            error: error.message
        });
    }
});

/*
    Ruta: GET /api/reportes/:cod_reporte/detalle
*/
router.get('/:cod_reporte/detalle', obtenerDetalleReporte);

/*
    Ruta: POST /api/reportes
*/
router.post('/', crearReporteFinanciero);

/*
    Ruta: PUT /api/reportes/:cod_reporte
*/
router.put('/:cod_reporte', actualizarReporteFinanciero);

/*
    Ruta: DELETE /api/reportes/:cod_reporte
*/
router.delete('/:cod_reporte', async (req, res) => {
    try {
        const { cod_reporte } = req.params;
        const { pool } = require('../config/db');

        if (!/^\d+$/.test(cod_reporte) || Number(cod_reporte) <= 0) {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El código del reporte debe ser numérico y positivo'
            });
        }

        const [reporteExists] = await pool.query(
            'SELECT cod_reporte, ind_estado FROM rf_reporte_financiero WHERE cod_reporte = ?',
            [cod_reporte]
        );

        if (reporteExists.length === 0) {
            return res.status(404).json({
                estado: 'error',
                mensaje: 'Reporte no encontrado'
            });
        }

        if (reporteExists[0].ind_estado === 'anulado') {
            return res.status(400).json({
                estado: 'error',
                mensaje: 'El reporte ya está anulado'
            });
        }

        await pool.query(
            'UPDATE rf_reporte_financiero SET ind_estado = "anulado" WHERE cod_reporte = ?',
            [cod_reporte]
        );

        res.json({
            estado: 'ok',
            mensaje: 'Reporte anulado exitosamente'
        });
    } catch (error) {
        console.error('Error al anular reporte:', error);
        res.status(500).json({
            estado: 'error',
            mensaje: 'Error al anular reporte',
            error: error.message
        });
    }
});

module.exports = router;