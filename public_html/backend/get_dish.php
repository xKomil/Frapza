<?php
require_once '../database/db.php';

// Pobierz ID potrawy z parametrów GET
if (isset($_GET['id'])) {
    $dishId = $_GET['id'];

    // Przygotowanie zapytania SQL z aliasami
    $query = "SELECT 
        PotrawaId AS id, 
        Nazwa AS nazwa, 
        Cena AS cena, 
        Opis AS opis, 
        Zdjecie AS zdjecie, 
        Kategoria AS kategoria 
    FROM Potrawy WHERE PotrawaId = ?";

    // Przygotowanie i wykonanie zapytania
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $dishId); // Przypisz parametr
        $stmt->execute(); // Wykonaj zapytanie

        // Pobierz wynik zapytania
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Zwróć dane potrawy w formacie JSON
            $dish = $result->fetch_assoc();
            echo json_encode($dish);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Potrawa nie znaleziona']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Błąd zapytania']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Brak ID potrawy']);
}

$conn->close(); // Zamknij połączenie z bazą danych

?>
