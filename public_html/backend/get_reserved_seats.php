<?php
// Ustawienie odpowiednich nagłówków, aby odpowiedź była w formacie JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Załóżmy, że masz połączenie z bazą danych w pliku db.php
require_once('../database/db.php');

// Sprawdzamy, czy została podana data rezerwacji
if (isset($_GET['table_reservation_date'])) {
    $reservationDate = $conn->real_escape_string($_GET['table_reservation_date']);

    // Przygotowanie zapytania SQL, aby pobrać dane z tabeli RezerwacjeStoliki
    $sqlReservedSeats = "
        SELECT GodzinaRozpoczecia, SUM(IloscOsob) as IloscOsob 
        FROM RezerwacjeStoliki 
        WHERE DataRezerwacji = '$reservationDate'
        GROUP BY GodzinaRozpoczecia";
    
    // Wykonanie zapytania
    $resultReservedSeats = $conn->query($sqlReservedSeats);

    // Przygotowanie tablicy odpowiedzi
    $response = [];
    if ($resultReservedSeats->num_rows > 0) {
        // Przetwarzanie wyników zapytania
        while ($row = $resultReservedSeats->fetch_assoc()) {
            $response[] = $row;  // Dodajemy każdy wiersz do tablicy odpowiedzi
        }
    } else {
        $response['message'] = 'Brak rezerwacji na ten dzień.';
    }

    // Zwrócenie odpowiedzi w formacie JSON
    echo json_encode($response);

} else {
    echo json_encode(['message' => 'Brak podanej daty rezerwacji']);
}
?>
