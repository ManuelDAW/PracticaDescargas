<?php

/**
 * Created by PhpStorm.
 * User: manuel
 * Date: 1/11/17
 * Time: 21:58
 */

/**
 * @param $ficheros un array
 * @param $dir una palabra a agregar con cada elemento
 * @return $lista una cadena de caracteres con los elementos del array separado por comas.
 * A cada elemento se le agrega la palabra $dir si existe
 */
function array_to_string($ficheros, $dir = null) {

//Por si no hau ficheros en el directorio.
  $lista = null;
  if (sizeof($ficheros) == 0)
    return null;

  foreach ($ficheros as $c) {
    $lista .= "'$dir/$c',";
  }
  $lista = substr($lista, 0, -1);


  return $lista;
}

/**
 *
 * @param type $ficheros array con ficheros
 * @description Esta función mueve los ficheros que tenemos en el directorio de uploads a downloads para dejarlos visibles
 */
function publicar_ficheros($ficheros) {
  $copia = true;
  foreach ($ficheros as $fichero) {
    $c = rename("./descargas/uploads/$fichero", "./descargas/downloads/$fichero");
    $copia = $c;
  }
}

function pon_acentos($texto) {
  switch ($texto) {
    case 'musica':
      $texto = "música";
      break;
    case 'imagenes':
      $texto = "imágenes";
      break;
  }
  return $texto;
}

/**
 *
 * @param type $fichero fichero a subir (array asociativo).
 * @description copia el fichero que se ha subido a la especificación establecida
 * @return boolean Si se ha subido o no correctamente.
 */
function upload_file($fichero) {
  $subida = false;
  $destino = './descargas/uploads/';
  $origen = $fichero['tmp_name'];

  $tipo = explode('/', $fichero['type']);

  switch ($tipo[0]) {
    case 'audio':
      $destino .= 'musica/';
      break;
    case 'image':
      $destino .= 'imagenes/';
      break;
    case 'pdf':
      $destino .= 'pdf/';
      break;
    default:
      $destino .= 'otros/';
  }
  $destino .= str_replace(" ", "_", $fichero['name']);

  $subida = move_uploaded_file($origen, $destino);
  return $subida;
}

/**
 * Devuelve un html con el contenido de todos los ficheros tanto publicados como pendientes de publciar
 */
function show_files($admin) {
  $html = null;
  $html .= show_files_directory_downloads();
  if ($admin) {
    $html .= show_files_directory_uploads();
  }

  return $html;
}

//Zona de administrador
function show_files_directory_uploads() {

  global $lista_musica;
  global $lista_imagenes;
  global $lista_pdf;
  global $lista_otros;
  $html = "<h2>Zona de administración para publciar ficheros </h2>";
  $titulo = ' Ficheros para publicar';


  $directorio = scandir("./descargas/uploads");


  $html .= "<fieldset class='fieldset1'><legend>$titulo</legend>\n";
  $html .= "<form action='descarga.php' method='POST' id=f1 >\n";
  $html .= "<input type=submit name=enviar value='publicar'
             title='Pasar los ficheros seleccionadados a la sección pública'
             :disabled='deshabilitado' />\n";

  //Para cada directorio (imagenes, musica, pdf, otros, ...
  foreach ($directorio as $dir) {
    if (($dir != '.') && ($dir !== '..')) {
      $texto_dir = pon_acentos($dir);
      $html .= "<fieldset class='fieldset2'><legend class=legend2>$texto_dir</legend>\n";
      $ficheros = scandir("./descargas/uploads/$dir");
      //Leo los ficheros de ese directorio
      $contenido = false; //Nos dirá si hay o no ficheros del tipo actual (música , ...)
      //Eliminamos los directorios . y ..
      unset($ficheros[array_search('.', $ficheros)]);
      unset($ficheros[array_search('..', $ficheros)]);

      //Guardo los valores para Vue
      $lista_musica = ($dir == "musica") ? array_to_string($ficheros, "musica") : $lista_musica;
      $lista_pdf = ($dir == "pdf") ? array_to_string($ficheros, "pdf") : $lista_pdf;
      $lista_otros = ($dir == "otros") ? array_to_string($ficheros, "otros") : $lista_otros;
      $lista_imagenes = ($dir == "imagenes") ? array_to_string($ficheros, "imagenes") : $lista_imagenes;

      foreach ($ficheros as $fichero) {
        // $ficheros = str_replace( " ", "\ ", $ficheros );
        $contenido = true;
        $html .= "<input type=checkbox name='ficheros_publicar[]' :checked='check_$dir'
                    value='$dir/$fichero' v-model='$dir'  @click='actualizar' />\n";
        $html .= "<a href = ./descargas/uploads/$dir/$fichero>$fichero</a><br />\n";
      }
      if ($contenido) {
        $html .= "<br /><input type=button :value='boton_check_$dir' v-on:click='select_all_$dir'  />\n";
      } else {
        $html .= "<h3>No hay ficheros de $texto_dir actualmente</h3>";
      }
      $html .= "</fieldset>\n";
    }
  }
  $html .= "<input type=hidden name=name value='admin' />\n";
  $html .= "<input type=hidden name=pass value='admin' />\n";
  $html .= "<input type=submit name=enviar value='publicar'
             title='Pasar los ficheros seleccionadados a la sección pública'
             :disabled='deshabilitado' />\n";

  $html .= "</form>\n";
  $html .= "</fieldset>\n";
  return $html;
}

$html .= "<input type=hidden name=name value='admin' />\n";

//Zona pública
function show_files_directory_downloads() {
//Obetenmos los ficheros a visualizar
//

  $html = "<h2>Espacio pública de ficheros para acceder</h2>";
  $titulo = ' Ficheros listos para descargas';

  $directorio = scandir("./descargas/downloads");

  $html .= "<fieldset class='fieldset1'><legend>$titulo</legend>\n";
  //Para cada directorio (imagenes, musica, pdf, otros, ...
  foreach ($directorio as $dir) {
    if (($dir != '.') && ($dir !== '..')) {
      $texto_dir = pon_acentos($dir);

      $html .= "<fieldset class='fieldset2'><legend class=legend2>$texto_dir</legend>\n";

      $ficheros = scandir("./descargas/downloads/$dir");
      //Leo los ficheros de ese directorio
      foreach ($ficheros as $fichero) {
        if (($fichero != '.') && ($fichero !== '..')) {
          // $ficheros = str_replace( " ", "\ ", $ficheros );
          $html .= "<a href = ./descargas/downloads/$dir/$fichero>$fichero</a><br />\n";
        }
      }
      $html .= "</fieldset>\n";
    }
  }
  $html .= "</fieldset>\n";
  return $html;
}

//End fucntion show files directory
