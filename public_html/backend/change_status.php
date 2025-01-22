<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../database/db.php');

// Sprawdzamy, czy przekazano ID i nowy status
if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'] == 'aktywna' ? 1 : 0; // Konwertujemy 'aktywna' na 1, 'nieaktywna' na 0

    // Przygotowujemy zapytanie SQL do zmiany statusu
    $query = "UPDATE Potrawy SET Aktywny = ? WHERE PotrawaId = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die('Błąd przygotowania zapytania: ' . $conn->error);
    }

    $stmt->bind_param("ii", $status, $id); // Parametry: 'i' dla int

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status został zmieniony']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Błąd podczas zmiany statusu']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Brak wymaganych danych']);
}
?>
