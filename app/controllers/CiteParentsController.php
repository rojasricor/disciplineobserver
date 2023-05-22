<?php

namespace App\Controllers;

use Exception;
use App\Middleware\AuthMiddleware;
use App\Models\{
  GradesModel,
  StudentsModel,
  NotationsModel,
  CitationsModel,
  EmailSenderModel
};

class CiteParentsController extends BaseController
{
  public function showCiteParentsPage()
  {
    $authMiddlewareInstance = new AuthMiddleware();
    $authMiddlewareInstance->handle();

    $gradesModelInstance = new GradesModel();
    $grades = $gradesModelInstance->getAllGrades();

    echo $this->twig->render('cite-parents.twig', [
      'title' => 'Citar padres de familia',
      'userLogged' => $_SESSION['user_discipline_observer'],
      'success' => $_SESSION['success_msg'] ?? NULL,
      'grades' => $grades
    ]);
    $_SESSION['success_msg'] = NULL;
  }

  public function citingParents()
  {
    try {
      $authMiddlewareInstance = new AuthMiddleware();
      $authMiddlewareInstance->handle();

      $gradesModelInstance = new GradesModel();
      $grade = $gradesModelInstance->getByIdGrade($_GET['grade']);

      $studentsModelInstance = new StudentsModel();
      $studentFound = $studentsModelInstance->getByIdStudent($_GET['_id']);

      if (!$studentFound) {
        throw new Exception("El estudiante no fué encontrado en la base de datos del observador");
      }

      echo $this->twig->render('citing-parents.twig', [
        'title' => 'Citación de padres de familia',
        'userLogged' => $_SESSION['user_discipline_observer'],
        '_studentInfo' => $studentFound->student . ' de ' . $grade->grade . ' grado',
        '_emailParent' => $studentFound->email_parent,
        '_id' => $_GET['_id']
      ]);
    } catch (Exception $e) {
      $error = $e->getMessage();
      $gradesModelInstance = new GradesModel();
      $grades = $gradesModelInstance->getAllGrades();

      echo $this->twig->render('cite-parents.twig', [
        'title' => 'Error',
        'userLogged' => $_SESSION['user_discipline_observer'],
        'error' => $error,
        'grades' => $grades
      ]);
    }
  }

  public function saveCitation()
  {
    try {
      if (!$_POST || !$_GET) {
        http_response_code(400);
        throw new Exception('petición incorrecta');
      }

      $authMiddlewareInstance = new AuthMiddleware();
      $authMiddlewareInstance->handle();

      if (empty($_GET['_id'])) {
        throw new Exception('Ingrese el número de documento del estudiante');
      }

      if (empty($_POST['notation'])) {
        throw new Exception('Ingrese la anotación del estudiante al observador');
      }

      if (empty($_POST['student'])) {
        throw new Exception('Ingrese el nombre del estudiante');
      }

      if (empty($_POST['testimony'])) {
        throw new Exception('Ingrese el testimonio del estudiante');
      }

      if (empty($_POST['notice'])) {
        throw new Exception('Ingrese el aviso al padre de familia');
      }

      if (empty($_POST['email'])) {
        throw new Exception('Ingrese el correo del padre de familia');
      }

      $studentsModelInstance = new StudentsModel();
      $studentFound = $studentsModelInstance->getByIdStudent($_GET['_id']);

      if (!$studentFound) {
        throw new Exception("El estudiante no fué encontrado en la base de datos del observador");
      }

      $notationsModelInstance = new NotationsModel();
      $newNotation = $notationsModelInstance->create(
        $_GET['_id'],
        $_POST['notation'],
        $_GET['grade'],
        $_POST['testimony']
      );

      $citationsModelInstance = new CitationsModel();
      $newCitation = $citationsModelInstance->create(
        $_GET['_id'],
        $_POST['notice'],
        $_POST['email']
      );

      $emailSenderModelInstance = new EmailSenderModel();
      $citacionSended = $emailSenderModelInstance->sendEmail(
        'Citación padre de familia - Alumno ' . $_POST['student'] . ' Grado ' . $_GET['grade'] . ' San José Obrero, Espinal - Tolima, Discipline Observer',
        $_POST['email'],
        $_POST['notice']
      );

      if (!$newNotation && !$newCitation && !$citacionSended) {
        throw new Exception('Ocurrió un error al enviar la citacion a ' . $_POST['email']);
      }
      
      $_SESSION['success_msg'] = 'Citación enviada a ' . $_POST['email'];
      header('Location: /citacion/padres');

    } catch (Exception $e) {
      $error = $e->getMessage();

      echo $this->twig->render('citing-parents.twig', [
        'title' => 'Error',
        'userLogged' => $_SESSION['user_discipline_observer'],
        'error' => $error,
      ]);
    }
  }

  public function citeParents()
  {
    $authMiddlewareInstance = new AuthMiddleware();
    $authMiddlewareInstance->handle();
    if (empty($_GET['grade']) && empty($_GET['_id'])) {
      return $this->showCiteParentsPage();
    }

    $this->citingParents();
  }
}
