<?php
require_once('../database/db.php');

// Pobieramy wartość wyszukiwania i kryterium
$searchTerm = mysqli_real_escape_string($conn, $_GET['search-term']);
$searchCriteria = mysqli_real_escape_string($conn, $_GET['search-criteria']);

// Zapewnienie, że kryterium wyszukiwania jest dozwolone
$validCriteria = ['imie', 'nazwisko', 'email', 'rola'];

if (!in_array($searchCriteria, $validCriteria)) {
    echo "<tr><td colspan='7'>Błędne kryterium wyszukiwania.</td></tr>";
    exit;
}

// Budowanie zapytania SQL z dynamicznym filtrowaniem
$query = "SELECT * FROM Uzytkownicy WHERE $searchCriteria LIKE '%$searchTerm%'";

// Wykonujemy zapytanie do bazy danych
$result = mysqli_query($conn, $query);

// Sprawdzamy, czy są jakieś wyniki
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
            <td>{$row['UzytkownikId']}</td>
            <td>{$row['Imie']}</td>
            <td>{$row['Nazwisko']}</td>
            <td>{$row['Email']}</td>
            <td>{$row['NumerTelefonu']}</td>
            <td>{$row['Rola']}</td>
           <td><button class='edit button-86 margin-bottom-smf' data-id='{$row['UzytkownikId']}'>Zmień</button> <button class='delete button-86' data-id='{$row['UzytkownikId']}'>Usuń</button></td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7'>Brak wyników</td></tr>";
}
?>
