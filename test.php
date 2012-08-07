<?php

date_default_timezone_set('Europe/Madrid');

chdir('/var/www/proto2/libs/tests/integracion');
include '../unitarios/testInit.php';
include 'zarangotest.php';

$test = array();

/* Envío de e-mail */
$test[] = new Zarangotest("Envío de e-mail", "Conectividad", function() {
    
});

/* Memcache */
$test[] = new Zarangotest("Conexión a Memcache", "Conectividad", function() {
    //Clave aleatoria
    return (Cache::get(md5(uniqid('zarangollo', true))) === false);
});

/* MySQL */
$test[] = new Zarangotest("Conexión a MySQL", "Bases de datos", function() {
    return (bool) mysql_connect(BD_HOST, BD_USUARIO, BD_CLAVE, BD_NOMBRE);
});

/* Oracle */
$test[] = new Zarangotest("Conexión a Oracle", "Bases de datos", function() {
    /** @todo Implementar con Bd::get() */
    return "No implementado";
});

$test[] = new Zarangotest("Permisos de ficheros en document root", "Sistema de archivos", function() {
    
    $directorio = $_SERVER['DOCUMENT_ROOT'];

    //Crea un fichero en el directorio
    $g = fopen("$directorio/borrame", "w");
    fwrite($g, "por favor, elimine este fichero");
    fclose($g);
    
    //Lista el directorio temporal en busca del fichero creado
    $salida = array();
    exec("ls $directorio", $salida);
    $devolver = (array_search("borrame", $salida) !== false);
    exec("rm $directorio/borrame");
    return $devolver;
});

/* Permisos en /tmp o el directorio temporal de subida */
$test[] = new Zarangotest("Permisos de ficheros en el directorio temporal de subida", "Sistema de archivos", function() {

    //Encuentra el directorio temporal
    $directorio = ini_get("upload_tmp_dir");
    //Si la configuración de PHP no lo especifica, tomamos el directorio temporal del sistema
    if (strlen($directorio) < 2) $directorio = sys_get_temp_dir();

    //Crea un fichero en el directorio temporal
    $g = fopen("$directorio/borrame", "w");
    fwrite($g, "por favor, elimine este fichero");
    fclose($g);
    
    //Lista el directorio temporal en busca del fichero creado
    $salida = array();
    exec("ls $directorio", $salida);
    $devolver = (array_search("borrame", $salida) !== false);
    exec("rm $directorio/borrame");
    return $devolver;
});

/* Reporte de errores E_ALL */
$test[] = new Zarangotest("Reporte de errores = E_ALL", "Entorno PHP", function() {
    
    $niveles = array(
        22527 => "E_ALL & ~E_DEPRECATED"
    );
    
    $nivel = ini_get("error_reporting");
    
    if ($nivel == E_ALL) {
        return true;
    } else {
        return "Valor actual: " . ((key_exists($nivel, $niveles)) ? $niveles[$nivel] : $nivel);
    }
});

/* Manejo de sesiones */
$test[] = new Zarangotest("Manejo de sesiones", "Entorno PHP", function() {

    /** @todo Mejorar el test leyendo el PHPSESSID, analizando el almacenamiento de la sesión... */

    session_start();
    $_SESSION['foo'] = 'bar';
    
    return ($_SESSION['foo'] == 'bar');
});

/* Display_errors */
$test[] = new Zarangotest("Display_errors = Off", "Entorno PHP", function() {
    return ini_get("display_errors") == true;
});

/* Log de errores */
$test[] = new Zarangotest("Log_errors = On", "Entorno PHP", function() {
    return ini_get("log_errors") == true;
});

/* Mysql */
$test[] = new Zarangotest("Extensión mysql", "Entorno PHP", function() {
    return function_exists("mysql_connect");
});

/* Mysqli */
$test[] = new Zarangotest("Extensión mysqli", "Entorno PHP", function() {
    return function_exists("mysqli_close");
});

/* Oci */
$test[] = new Zarangotest("Extensión oci", "Entorno PHP", function() {
    return function_exists("oci_connect");
});

/* Curl */
$test[] = new Zarangotest("Extensión CURL", "Entorno PHP", function() {
    return function_exists("curl_init");
});

/* Memcached */
$test[] = new Zarangotest("Extensión memcached", "Entorno PHP", function() {
    return class_exists("Memcache");
});

/* APC */
$test[] = new Zarangotest("Extensión APC", "Entorno PHP", function() {
    return (function_exists("apc_cache_info")) ? true : "No existe la función apc_cache_info()";
});

$test[] = new Zarangotest("Manejo de ficheros remotos", "Entorno PHP", function() {
    return (bool) ini_get("allow_url_fopen");
});

/* GraphicsMagick */
$test[] = new Zarangotest("GraphicsMagick", "Bibliotecas", function() {
    $salida = array();
    $estado = null;
    exec("convert", $salida, $estado);
    return !empty($salida);
});

/* Zend Framework */
/*$test[] = new Zarangotest("Zend Framework", "Bibliotecas", function() {
    return "No implementado";
});*/

zarangotest($test, "Integración de Mi Proyecto");
