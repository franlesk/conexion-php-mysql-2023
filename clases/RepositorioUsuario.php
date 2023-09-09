<?php

require_once 'Usuario.php';
require_once '.env.php';

class RepositorioUsuario
{
    private static $conexion = null;

    /***
     * Método constructor. Si ya había una conexión a la base de datos
     * establecida, no hace nada. Si la conexión aún no se estableció, realiza
     * la misma con mysqli, utilizando la función credenciales() del archivo
     * .env.php.
     */
    public function __construct()
    {
        $credenciales = credenciales();
        if (is_null(self::$conexion)) {
            self::$conexion = new mysqli(
                $credenciales['servidor'],
                $credenciales['usuario'],
                $credenciales['clave'],
                $credenciales['base_de_datos'],
            );
        }
        if (self::$conexion->connect_error) {
            $error = 'Error de conexion: ' . self::$conexion->connect_error;
            self::$conexion = null;
            die($error);
        }
        self::$conexion->set_charset('utf8mb4');
    }

    /**
     * Verifica el login de usuario, y retorna una instancia de la clase Usuario
     * si tiene éxito.
     *
     * @param string $nombre_usuario El nombre de usuario ingresado en el login
     * @param string $clave          La contraseña ingresada en el login
     *
     * @return mixed Si el login fracasa, retorna el valor booleano false.
     *               Si tiene éxito, retorna una instancia de la clase Usuario
     *               con los datos del usuario autenticado.
     */
    public function login($nombre_usuario, $clave)
    {
        $q = "SELECT id, clave, nombre, apellido FROM usuarios WHERE nombre_usuario = ?;";
        $query = self::$conexion->prepare($q);
        $query->bind_param("s", $nombre_usuario);

        if ($query->execute()) {
            $query->bind_result($id, $clave_encriptada, $nombre, $apellido);
            if ($query->fetch()) {
                // Validar que la clave esté bien:
                if (password_verify($clave, $clave_encriptada)) {
                    return new Usuario($id, $nombre_usuario, $nombre, $apellido);

                }
            }

        }
        return false;
    }

}
