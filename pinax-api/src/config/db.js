// Importamos mysql2 con soporte para promesas.
// Esto nos permite usar async/await en las consultas a la base de datos.
const mysql = require('mysql2/promise');

// Importamos dotenv para poder leer las variables del archivo .env.
require('dotenv').config();

// Creamos un pool de conexiones.
// Un pool permite reutilizar conexiones y manejar varias solicitudes de forma
// más eficiente
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    port: process.env.DB_PORT,

    // Cantidad máxima de conexiones simultaneas permitidas.
    connectionLimit: 10,

    // Espera si todas las conexiones estan ocupadas.
    waitForConnections: true,

    // Cantidad maxima de solicitudes en espera. 0 significa sin limite.
    queueLimit:0
});

// Funcion para probar la conexion con MySQL
// La usaremos al iniciar el servidor
const probarConexion = async () => {
    try {
        // Obtenemos una conexion del pool
        const connection = await pool.getConnection();
        
        // Si la conexion fue exitosa, mostramos mensaje
        console.log('Conexion exitosa a MySql');

        // Liberamos la conexion para que pueda ser reutilizada
        connection.release();
    } catch (error) {
        // Mostramos un mensaje controlado si falla la conexion.
        console.error('Error al conectar con MySQL:', error.message);

        // Detenemos la aplicacion porque sin base de datos la API no pude trabajar
        process.exit(1);
    }
};

// Exportamos el pool y la funcion de prueba
// Otros archivos podran usar pool para ejecutar consultas o procedimientos almacenados.
module.exports = {
    pool,
    probarConexion
};