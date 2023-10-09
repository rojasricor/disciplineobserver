<?php

namespace App\Models;

class StudentsModel extends BaseModel {
  public function create(
    string $_id,
    string $student,
    string $grade,
    string $parent_id
  ): bool
  {
    // Vamos a crear un nuevo estudiante en la base de datos
    $statement = $this->db->prepare("INSERT INTO students (_id, student, grade, parent_id) VALUES (?, ?, ?, ?)");
    // Ejecutamos la consulta y retornamos el resultado
    return $statement->execute([
      $_id,
      $student,
      $grade,
      $parent_id
    ]);
  }

  public function getAllStudents(): array
  {
    $statement = $this->db->query("SELECT S._id, S.student, S.grade, S.parent_id, U.name as parent_name, U.lastname as parent_lastname, U.email as parent_email FROM students as S INNER JOIN parents_students as PS ON S.parent_id = PS._id INNER JOIN users as U ON U._id = PS._id");
    return $statement->fetchAll();
  }

  public function getStudentBySearchAdmin(string $search): array
  {
    $statement = $this->db->prepare("SELECT S._id, S.student, S.grade, S.parent_id, U.name as parent_name, U.lastname as parent_lastname, U.email as parent_email FROM students as S INNER JOIN parents_students as PS ON S.parent_id = PS._id INNER JOIN users as U ON U._id = PS._id WHERE (S._id = ? OR S.student LIKE ? OR S.grade LIKE ?)");
    $statement->execute([
      $search,
      "$search%",
      "$search%"
    ]);
    return $statement->fetchAll();
  }

  public function getStudentByDocumentOrName(string $search): array
  {
    $statement = $this->db->prepare("SELECT S._id, S.student, S.grade, S.parent_id, U.name as parent_name, U.lastname as parent_lastname, U.email as parent_email FROM students as S INNER JOIN parents_students as PS ON S.parent_id = PS._id INNER JOIN users as U ON U._id = PS._id WHERE (S._id = ? OR S.student LIKE ?)");
    $statement->execute([
      $search,
      "$search%"
    ]);
    return $statement->fetchAll();
  }

  public function getByIdStudent(string $_id): object | bool
  {
    // Obtenemos el estudiante por su id
    $statement = $this->db->prepare("SELECT S._id, S.student, S.grade, S.parent_id, U.name as parent_name, U.lastname as parent_lastname, U.email as parent_email FROM students as S INNER JOIN parents_students as PS ON S.parent_id = PS._id INNER JOIN users as U ON U._id = PS._id WHERE S._id = ?");
    // Ejecutamos la consulta y retornamos el resultado
    $statement->execute([$_id]);
    // Retornamos al estudiante
    return $statement->fetchObject();
  }

  public function updateStudent(
    string $_id,
    string $student,
    string $grade
  ): bool
  {
    $statement = $this->db->prepare("UPDATE students SET student = ?, grade = ? WHERE _id = ?");
    return $statement->execute([
      $student,
      $grade,
      $_id
    ]);
  }

  public function deleteStudent(string $_id): bool
  {
    $statement = $this->db->prepare("DELETE FROM students WHERE _id = ?");
    return $statement->execute([$_id]);
  }
}
