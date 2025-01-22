<?php
require_once('../database/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $email = trim($_POST['email']);
    $numer = trim($_POST['numer']);
    $rola = $_POST['rola'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Nieprawidłowy adres email.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE Uzytkownicy SET Imie = ?, Nazwisko = ?, Email = ?, NumerTelefonu = ?, Rola = ? WHERE UzytkownikId = ?");
    $stmt->bind_param("sssssi", $imie, $nazwisko, $email, $numer, $rola, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Nie udało się zaktualizować użytkownika.']);
    }

    $stmt->close();
    $conn->close();
}
?>
