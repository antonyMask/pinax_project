// Importamos dotenv para cargar variables de entorno desde .env.
require('dotenv').config();

// Importamos la aplicacion Express configurada en src/app.js
const app = require('./src/app');

// Importamos la funcion para probar la conexion con MySQL
const { probarConexion }= require('./src/config/db');

// Obtenemos el puert desde .env
// Si no existe, usamos 3000 como respaldo
const PORT = process.env.PORT || 3000;

// Funcion principal para iniciar el servidor
const iniciarServidor = async () => {
    // Primero probamos la conexion a MySQL.
    await probarConexion();

    // Si la conexion fue exitosa, levantamos el servidor.
    app.listen(PORT, () => {
        console.log(`Servidor Pinax ejecutandose en el puerto ${PORT}`);
    });
};

// Ejecutamos la funcion principal
iniciarServidor();