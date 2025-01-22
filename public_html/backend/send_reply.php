<?php
require_once '../database/db.php';

// Pobieranie danych z żądania
$data = json_decode(file_get_contents('php://input'), true);

// Sprawdzenie, czy wszystkie wymagane dane zostały przesłane
if (isset($data['WiadomoscId'], $data['Odpowiedz'])) {
    $wiadomoscId = (int) $data['WiadomoscId'];  // Upewnij się, że WiadomoscId to liczba całkowita
    $odpowiedz = trim($data['Odpowiedz']);  // Usuwamy zbędne spacje z odpowiedzi

    // Jeśli odpowiedź jest pusta, zwróć błąd
    if (empty($odpowiedz)) {
        echo json_encode(['success' => false, 'error' => 'Odpowiedź nie może być pusta.']);
        exit;
    }

    // Przygotowanie zapytania do bazy danych
    $stmt = $conn->prepare("UPDATE Wiadomosci SET Odpowiedz = ?, Przeczytana = 1 WHERE WiadomoscId = ?");
    
    // Wiązanie parametrów
    $stmt->bind_param('si', $odpowiedz, $wiadomoscId);

    // Wykonanie zapytania
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        // W przypadku błędu wykonania zapytania
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }

    // Zamknięcie zapytania
    $stmt->close();
} else {
    // Jeśli nie przekazano wymaganych danych
    echo json_encode(['success' => false, 'error' => 'Niepoprawne dane wejściowe.']);
}

// Zamknięcie połączenia z bazą
$conn->close();

?>
