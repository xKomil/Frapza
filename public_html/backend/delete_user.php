<?php
require_once('../database/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("SELECT Rola FROM Uzytkownicy WHERE UzytkownikId = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user['Rola'] === 'admin') {
        echo json_encode(['success' => false, 'error' => 'Nie można usunąć administratora.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM Uzytkownicy WHERE UzytkownikId = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Nie udało się usunąć użytkownika.']);
    }

    $stmt->close();
    $conn->close();
}
?>
