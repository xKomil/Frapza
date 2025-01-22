<?php
// Ustaw nagłówek dla odpowiedzi JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');  // Dodane nagłówki CORS

// Włączanie wyświetlania błędów
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../database/db.php');

// Sprawdzamy, czy parametr 'category' jest dostępny
if (isset($_GET['category'])) {
    $category = $_GET['category'];
    
    // SQL do pobrania potraw, filtrując po kategorii i statusie aktywnym (1 oznacza aktywne)
    $sql = "SELECT PotrawaId, Nazwa, Cena, Opis, Zdjecie, DataDodania FROM Potrawy WHERE Kategoria = ? AND Aktywny = 1 LIMIT 3";

    // Sprawdzamy połączenie z bazą danych
    if ($conn) {
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, 's', $category); // Parametr 's' dla kategorii
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            // Sprawdzamy, czy dane zostały pobrane
            if ($result) {
                $dishes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                echo json_encode($dishes); // Zwrócenie wyników w formacie JSON
            } else {
                echo json_encode(['error' => 'Błąd zapytania do bazy danych.']);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo json_encode(['error' => 'Nie udało się przygotować zapytania.']);
        }
    } else {
        echo json_encode(['error' => 'Błąd połączenia z bazą danych.']);
    }
} else {
    echo json_encode(['error' => 'Brak kategorii w zapytaniu.']);
}
?>
