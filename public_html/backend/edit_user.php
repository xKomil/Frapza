<?php
require_once('../database/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $id = $_POST['id'];
    $imie = trim($_POST['imie']);
    $nazwisko = trim($_POST['nazwisko']);
    $email = trim($_POST['email']);
    $numer = trim($_POST['numer']);
    $rola = $_POST['rola'];

    // Walidacja imienia
    if (empty($imie)) {
        $errors[] = "Proszę podać imię.";
    }

    // Walidacja nazwiska
    if (empty($nazwisko)) {
        $errors[] = "Proszę podać nazwisko.";
    }

    // Walidacja adresu email
    if (empty($email)) {
        $errors[] = "Proszę podać adres email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Podano nieprawidłowy adres email.";
    }

    // Walidacja numeru telefonu
    if (empty($numer)) {
        $errors[] = "Proszę podać numer telefonu.";
    } elseif (!preg_match("/^[0-9]{9}$/", $numer)) {
        $errors[] = "Numer telefonu powinien składać się z dokładnie 9 cyfr.";
    }

    // Jeśli wystąpiły błędy, zwróć je w odpowiedzi
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Aktualizacja użytkownika w bazie danych
    $stmt = $conn->prepare("UPDATE Uzytkownicy SET Imie = ?, Nazwisko = ?, Email = ?, NumerTelefonu = ?, Rola = ? WHERE UzytkownikId = ?");
    $stmt->bind_param("sssssi", $imie, $nazwisko, $email, $numer, $rola, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'errors' => ['Nie udało się zaktualizować użytkownika.']]);
    }

    $stmt->close();
    $conn->close();
}
?>