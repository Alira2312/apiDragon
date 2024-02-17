<?php
header("Access-Control-Allow-Origin: *"); // Allow all origins (not recommended for production)
// or
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); // Allowed methods

header("Access-Control-Allow-Headers: Content-Type, *"); // Allowed headers

// Definir la clase DragonBallCharacter
class DragonBallCharacter {
    public $id;
    public $name;
    public $ki;
    public $maxKi;
    public $race;
    public $gender;
    public $description;
    public $image;
    public $affiliation;

    function __construct($id, $name, $ki, $maxKi, $race, $gender, $description, $image, $affiliation) {
        $this->id = $id;
        $this->name = $name;
        $this->ki = $ki;
        $this->maxKi = $maxKi;
        $this->race = $race;
        $this->gender = $gender;
        $this->description = $description;
        $this->image = $image;
        $this->affiliation = $affiliation;
    }

    function to_array() {
        return array(
            "id" => $this->id,
            "name" => $this->name,
            "ki" => $this->ki,
            "maxKi" => $this->maxKi,
            "race" => $this->race,
            "gender" => $this->gender,
            "description" => $this->description,
            "image" => $this->image,
            "affiliation" => $this->affiliation
        );
    }
}

// Iniciar la sesión
session_start();

// Definir la lista de personajes de Dragon Ball
if (!isset($_SESSION['dragonball_characters'])) {
    $_SESSION['dragonball_characters'] = array();
}

// Funciones para la API

function obtener_personajes() {
    return $_SESSION['dragonball_characters'];
}

function ingresar_personaje($personaje) {
    $personaje_obj = new DragonBallCharacter($personaje->id, $personaje->name, $personaje->ki, $personaje->maxKi, $personaje->race, $personaje->gender, $personaje->description, $personaje->image, $personaje->affiliation);
    $_SESSION['dragonball_characters'][] = $personaje_obj;
    return array("mensaje" => "Personaje agregado");
}

function obtener_personaje($personaje_id) {
    foreach ($_SESSION['dragonball_characters'] as $personaje) {
        if ($personaje->id == $personaje_id) {
            return $personaje;
        }
    }
    return null;
}

function eliminar_personaje($personaje_id) {
    foreach ($_SESSION['dragonball_characters'] as $key => $personaje) {
        if ($personaje->id == $personaje_id) {
            unset($_SESSION['dragonball_characters'][$key]);
            $_SESSION['dragonball_characters'] = array_values($_SESSION['dragonball_characters']); // Reindexar el array
            return array("mensaje" => "Personaje con ID $personaje_id eliminado");
        }
    }
    return null;
}

function eliminar_todos_los_personajes() {
    $_SESSION['dragonball_characters'] = array();
    return array("mensaje" => "Todos los personajes han sido eliminados");
}

function actualizar_personaje($personaje_id, $personaje_actualizado) {
    if (is_array($_SESSION['dragonball_characters'])) {
        foreach ($_SESSION['dragonball_characters'] as $key => $personaje) {
            if ($personaje->id == $personaje_id) {
                // Elimina el personaje original
                unset($_SESSION['dragonball_characters'][$key]);
        
                // Verifica si el personaje actualizado tiene el mismo id
                if ($personaje_actualizado->id == $personaje_id) {
                    // Si el id es el mismo, actualiza el personaje actualizado en la misma posición
                    $_SESSION['dragonball_characters'][$key] = $personaje_actualizado;
                }
        
                return array("mensaje" => "Personaje editado");
            }
        }
    } 
    return array("error" => "Personaje no encontrado");
}

// Ruta para obtener el mensaje de inicio
if ($_SERVER['REQUEST_URI'] == '/') {
    echo json_encode(array("mensaje" => "Bienvenido a la API de gestión de personajes de Dragon Ball"));
    exit;
}

// Rutas para la API

$ruta = explode("/", $_SERVER['REQUEST_URI']);

if ($ruta[4] == "dragonball_characters") {
    switch ($_SERVER['REQUEST_METHOD']) {
        case "GET":
            if (isset($ruta[5])) {
                $personaje = obtener_personaje($ruta[5]);
                if ($personaje != null) {
                    echo json_encode($personaje);
                } else {
                    echo json_encode(array("error" => "Personaje no encontrado"));
                }
            } else {
                echo json_encode(obtener_personajes());
            }
            break;
        case "POST":
            $data = json_decode(file_get_contents("php://input"));
            $personaje = new DragonBallCharacter($data->id, $data->name, $data->ki, $data->maxKi, $data->race, $data->gender, $data->description, $data->image, $data->affiliation);
            $resultado = ingresar_personaje($personaje);
            echo json_encode($resultado);
            break;
        case "DELETE":
            if (isset($ruta[5])) {
                $resultado = eliminar_personaje(intval($ruta[5]));

                if ($resultado != null) {
                    echo json_encode($resultado);
                } else {
                    echo json_encode(array("error" => "Personaje no encontrado"));
                }
            } else {
                echo json_encode(array("error" => "Falta el ID del personaje"));
            }
            break;
        default:
            echo json_encode(array("error" => "Método no permitido"));
            break;
    }
} else if ($ruta[4] == "eliminar_todos_los_personajes") {
    if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
        $resultado = eliminar_todos_los_personajes();
        echo json_encode($resultado);
    } else {
        echo json_encode(array("error" => "Método no permitido"));
    }
} else if ($ruta[4] == "actualizar_personaje") {
    if ($_SERVER['REQUEST_METHOD'] == "PUT") {
        if (isset($ruta[5])) {
            $data = json_decode(file_get_contents("php://input"));
            $personaje_actualizado = new DragonBallCharacter($data->id, $data->name, $data->ki, $data->maxKi, $data->race, $data->gender, $data->description, $data->image, $data->affiliation);
            $resultado = actualizar_personaje($ruta[5], $personaje_actualizado);
            if ($resultado != null) {
                echo json_encode($resultado);
            } else {
                echo json_encode(array("error" => "Personaje no encontrado"));
            }
        } else {
            echo json_encode(array("error" => "Falta el ID del personaje"));
        }
    } else {
        echo json_encode(array("error" => "Método no permitido"));
    }
} else {
    echo json_encode(array("error" =>$ruta[4] ));
}
?>
