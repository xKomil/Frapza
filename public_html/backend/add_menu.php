<?php
require_once '../database/db.php';

// Dodawanie nowej potrawy
if (isset($_POST['nazwa']) && isset($_POST['cena']) && isset($_POST['kategoria'])) {
    // Pobieranie danych z formularza
    $nazwa = $_POST['nazwa'];
    $cena = $_POST['cena'];
    $opis = isset($_POST['opis']) ? $_POST['opis'] : null; // Opis może być pusty
    $kategoria = $_POST['kategoria'];
    $zdjecie = null;
    $link_zdjecie = isset($_POST['link_zdjecie']) ? $_POST['link_zdjecie'] : null;

    // Obsługa zdjęcia przesyłanego przez formularz
    if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] == 0) {
        $zdjecie = $_FILES['zdjecie'];
        $upload_dir = '../uploads/';
        $upload_file = $upload_dir . basename($zdjecie['name']);

        // Sprawdzenie, czy plik jest obrazem
        if (getimagesize($zdjecie['tmp_name'])) {
            if (move_uploaded_file($zdjecie['tmp_name'], $upload_file)) {
                $zdjecie = basename($zdjecie['name']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Błąd podczas przesyłania pliku.']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'To nie jest plik graficzny.']);
            exit;
        }
    } elseif (!empty($link_zdjecie)) {
        $zdjecie = $link_zdjecie; // Używamy linku, jeśli został podany
    } else {
        $zdjecie = 'default_image.jpg'; // Używamy domyślnego zdjęcia, jeśli nie ma pliku ani linku
    }

    // Przygotowanie zapytania SQL
    $sql = "INSERT INTO Potrawy (Nazwa, Cena, Opis, Zdjecie, Kategoria) 
            VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Powiązanie parametrów
        $stmt->bind_param("sssss", $nazwa, $cena, $opis, $zdjecie, $kategoria);

        // Wykonanie zapytania
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Potrawa została dodana pomyślnie!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Błąd: ' . $stmt->error]);
        }

        // Zamknięcie przygotowanego zapytania
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Błąd przygotowania zapytania: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Brak wymaganych danych (nazwa, cena, kategoria)']);
}

?>