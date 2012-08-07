<?php

/**
 *
 * LICENSE
 *
 * Copyright (c) 2011, Israel Viana
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 *     * Neither the name of Israel Viana nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Zarangotest
 * @copyright  Copyright (c) 2011 Israel Viana
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    1.0
 */

define('T_INICIO', microtime(true));

/**
 * Esqueleto de prueba.
 */
class Zarangotest {

    const EXITO = 'exito';
    const FRACASO = 'fracaso';

    public $nombre;
    public $categoria;
    public $funcion;
    public $resultado = 'fracaso'; //...hasta que se demuestre lo contrario
    public $detalles;
    public $tiempo = 0;
    
    public function __construct($nombre, $categoria, $funcion) {
        $this->nombre = $nombre;
        $this->categoria = $categoria;
        $this->funcion = $funcion;
    }
    
    public function __toString() {
        return $this->resultado;
    }
}

/** 
 * Lanza una ErrorException para cada error que se produzca
 */
function capturar_error($numero, $mensaje, $fichero, $linea, $contexto) {
    throw new ErrorException($mensaje, 0, $numero, $fichero, $linea);
}

/*
 * Inicialización 
 */
set_error_handler("capturar_error");


/*
 * Ejecución de las pruebas 
 *
 * @param   array   $bateria_de_pruebas     Array con los objetos de tipo Zarangotest
 * @param   string  $titulo_de_la_bateria   Título de la batería de pruebas, para la interfaz
 * @param   boolean $ordenar                Ordenar o no las pruebas por categoría
 */
function zarangotest($bateria_de_pruebas, $titulo_de_la_bateria="Zarangotest results", $ordenar=true) {

    $exitos = $fracasos = 0;

    $inicio_pruebas = $ultima_prueba = microtime(true);

    //Ejecuta las pruebas
    foreach ($bateria_de_pruebas as &$prueba) {
        
        try {
            //Ejecuta la función de test
            $funcion = $prueba->funcion;
            $resultado = $funcion();
            
            if ($resultado === true) {
                $prueba->resultado = Zarangotest::EXITO;
                $exitos++;
            } else {
                //Las funciones devuelven los detalles
                //Si no se devuelve nada, se considera fracaso
                $prueba->resultado = Zarangotest::FRACASO;
                $prueba->detalles = $resultado;
                $fracasos++;
            }
        } catch (Exception $e) {
            /* 
             * Si se produce un error PHP en la ejecución del test, se considera
             * fracaso
             */
            $prueba->resultado = Zarangotest::FRACASO;
            $prueba->detalles = $e->getMessage() . "\n\n" . $e->getTraceAsString();
            $fracasos++;
        }
        
        //Time tracking
        $f = microtime(true);
        $prueba->tiempo = round($f - $ultima_prueba, 4);
        $ultima_prueba = $f;
    }
    
    $t_pruebas = microtime(true) - $inicio_pruebas;
    
    if ($ordenar) {
        //Ordena los resultados por categoría
        usort($bateria_de_pruebas, function($a, $b) {
            return strcmp($a->categoria, $b->categoria);
        });
    }

    /**************************** COMIENZA LA VISTA ***************************/
    /******************** (pero no ha terminado la función) *******************/
    ?>
    <html>
    <head>
        <title>Test de despliegue</title>
        <style>
        body { font-family: arial; background: black; color: white; }
        .resumen { background: white; border-radius: 10px; margin: 10px 0; padding: 10px; color: black; font-size: 1.2em; }
        .resumen span { font-weight: bold; }
        .resumen .exitos { color: lime; text-shadow: 0 0 3px white; }
        .resumen .fracasos { color: red; }
        .prueba { margin: 10px 0; padding: 10px; background: #333; border-radius: 10px; }
        .prueba h3 { font-size: 18px; margin: 0; }
        .prueba.exito h3 { color: lime; text-shadow: 0 0 2px #333; }
        .prueba.fracaso h3 { color: red; text-shadow: 0 0 2px #333; }
        .prueba div { font-size: 0.8em; }
        .time { font-weight: normal; color: silver; font-size: .8em; }
        </style>
    </head>
    <body>
        <h1><?php echo $titulo_de_la_bateria ?></h1>
        
        <div class="resumen">
            <span class="exitos"><?php echo $exitos ?> éxitos</span>
            vs
            <span class="fracasos"><?php echo $fracasos ?> fracasos</span>
            en un total de
            <span class="total"><?php echo count($bateria_de_pruebas) ?> pruebas</total>
        </div>
        
        <?php $titulo = "" ?>
        <?php foreach ($bateria_de_pruebas as $resultado) : ?>
            
            <?php if ($resultado->categoria != $titulo) : ?>
            <h2><?php echo $resultado->categoria ?></h2>
            <?php endif ?>
            
            <div class="prueba <?php echo $resultado ?>">
                <h3><?php echo $resultado->nombre ?> <span class="time">[<?php echo $resultado->tiempo ?>s]</span></h3>
                <?php if (!empty($resultado->detalles)) : ?>

                <pre><?php print_r($resultado->detalles) ?></pre>
                <?php endif ?>
            </div>
            
            <?php $titulo = $resultado->categoria; ?>
        <?php endforeach ?>

        <p>
            Total de pruebas: <?php echo round($t_pruebas, 5) ?>s<br />
            Total del script: <?php echo round(microtime(true) - T_INICIO, 5) ?>s
        </p>
    </body>
    </html>
<?php } ?>
