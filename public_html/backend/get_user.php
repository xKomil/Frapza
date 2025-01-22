<?php
require_once('../database/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM Uzytkownicy WHERE UzytkownikId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'Użytkownik nie został znaleziony.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Nieprawidłowe żądanie.']);
}
?>
