<?php
// Połączenie z bazą danych i zapytanie
require_once '../database/db.php';

$sql = "SELECT WiadomoscId, UserId, Imie, NumerTelefonu, Email, Tresc, DataCzas, Przeczytana, Odpowiedz FROM Wiadomosci";
$result = $conn->query($sql);

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Konwersja 'Przeczytana' na int
        $row['Przeczytana'] = (int) $row['Przeczytana'];
        $messages[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($messages);
$conn->close();
?>
