<?php

namespace App\Controllers;

// Importar Modelos
use Exception;
use App\Middlewares\AuthMiddleware;
use App\Models\{
  GradesModel,
  CitationsModel,
  StudentsModel
};

class ViewCiteParentsController extends BaseController {
  protected AuthMiddleware $authMiddlewareInstance;
  protected StudentsModel $studentsModelInstance;
  protected GradesModel $gradesModelInstance;
  protected CitationsModel $citationsModelInstance;

  public function __construct()
  {
    // Llamamos al constructor del padre
    parent::__construct();

    // Inicializar los modelos
    $this->authMiddlewareInstance = new AuthMiddleware;
    $this->studentsModelInstance = new StudentsModel;
    $this->gradesModelInstance = new GradesModel;
    $this->citationsModelInstance = new CitationsModel;

    // Verificar si el usuario está logueado
    $this->authMiddlewareInstance->handle();
  }

  public function viewCitations(): void
  {
    // Si existe el grado y el documento de identidad vemos la página de pedir esos datos
    if (empty($_GET['grade']) && empty($_GET['search'])) {
      $this->showViewCiteParentsPage();
      return;
    }

    // Si existen entonces vamos a visualizar las citaciones
    $this->showSelectStudentsPage();
  }

  public function showSelectStudentsPage(): void
  {
    try {
      // Verificar si el estudiante esta registrado en la base de datos del observador
      $studentFound = $this->studentsModelInstance->getStudentByDocumentOrName($_GET['search']);

      // Si no esta registrado, mostrar mensaje de error
      if (!$studentFound) {
        throw new Exception("El estudiante no fué encontrado en la base de datos del observador");
      }

      // Renderizar la vista de seleccionar estudiante
      echo $this->twig->render('select-student.twig', [
        'current_template' => 'view-cite-parents',
        'title' => 'Ver citaciones de padres',
        'userLogged' => $_SESSION['user_discipline_observer'],
        'studentsFound' => $studentFound,
        'grade' => $_GET['grade']
      ]);
    } catch (Exception $e) {
      $error = $e->getMessage();
      $grades = $this->gradesModelInstance->getAllGrades();

      echo $this->twig->render('request-student.twig', [
        'current_template' => 'view-cite-parents',
        'title' => 'Error',
        'userLogged' => $_SESSION['user_discipline_observer'],
        'error' => $error,
        'grades' => $grades
      ]);
    }
  }
  
  public function showViewCiteParentsPage(): void
  {
    // Obtener todos los grados
    $grades = $this->gradesModelInstance->getAllGrades();
    
    // Vemos la vista de pedir el grado y el documento de identidad
    echo $this->twig->render('request-student.twig', [
      'current_template' => 'view-cite-parents',
      'title' => 'Ver citaciones de padres',
      'userLogged' => $_SESSION['user_discipline_observer'],
      'grades' => $grades
    ]);
  }

  public function visualizingCitations(): void
  {
    try {
      // Validar que los datos realmente fueron enviados
      if (empty($_GET['grade'])) {
        throw new Exception('Ingrese el grado del estudiante');
      }

      if (empty($_GET['_id'])) {
        throw new Exception('Ingrese el número de documento del estudiante');
      }
      
      // Validar que el estudiante exista
      $studentFound = $this->studentsModelInstance->getByIdStudent($_GET['_id']);

      // Si el estudiante no existe, lanzar una excepción
      if (!$studentFound) {
        throw new Exception("El estudiante no fué encontrado en la base de datos del observador");
      }

      // Obtener todas las citaciones del estudiante
      $citationFound = $this->citationsModelInstance->findCitationsByStudent($studentFound->_id);

      // Mostramos la página de todas las citaciones de ese estudiante
      echo $this->twig->render('visualizing-citations.twig', [
        'title' => 'Ver citaciones a padres de familia en el Observador',
        'userLogged' => $_SESSION['user_discipline_observer'],
        'observerStudent' => $citationFound
      ]);
    } catch (Exception $e) {
      $error = $e->getMessage();
      $grades = $this->gradesModelInstance->getAllGrades();

      echo $this->twig->render('request-student.twig', [
        'current_template' => 'view-cite-parents',
        'title' => 'Error',
        'userLogged' => $_SESSION['user_discipline_observer'],
        'error' => $error,
        'grades' => $grades
      ]);
    }
  }
}
