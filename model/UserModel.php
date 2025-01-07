<?php

class UserModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getAllUsersOrderedByScore() {
        $query = "SELECT id, fullname, score FROM user ORDER BY score DESC";
        $stmt = $this->database->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        $stmt->close();
        return $users;
    }

    public function getUserQuestionStats($userId) {
        $query = "SELECT 
                    SUM(CASE WHEN wasRight = 1 THEN 1 ELSE 0 END) AS correct,
                    SUM(CASE WHEN wasRight = 0 THEN 1 ELSE 0 END) AS incorrect
                  FROM user_question
                  WHERE id_user = ?";

        $stmt = $this->database->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die('Execute failed: ' . htmlspecialchars($stmt->error));
        }

        $stats = $result->fetch_assoc();

        $stmt->close();
        return $stats;
    }

    public function getUserById($id) {
        $query = "SELECT * FROM user WHERE id = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();
        return $user;
    }

    public function addInactiveAndCreadaQuestion($question, $category, $option_a, $option_b, $option_c, $option_d, $right_answer) {
        $query = "INSERT INTO question (pregunta, category, isCreada, active) VALUES (?, ?, 1, 0)";
        $stmt = $this->database->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }
        $stmt->bind_param('ss', $question, $category);
        $stmt->execute();

        $question_id = $stmt->insert_id;
        $stmt->close();

        $query = "INSERT INTO answer (question_id, option_a, option_b, option_c, option_d, right_answer) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }
        $stmt->bind_param('isssss', $question_id, $option_a, $option_b, $option_c, $option_d, $right_answer);
        $stmt->execute();
        $stmt->close();
    }

    public function claimQuestionWrong($questionId) {
        $query = "UPDATE question SET reports = reports + 1 WHERE id = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param('i', $questionId);
        $stmt->execute();
        $stmt->close();
        return true;
    }
}