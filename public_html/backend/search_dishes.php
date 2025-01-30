<?php
require_once('../database/db.php');

// Pobieramy wartość wyszukiwania i kryterium
$searchTerm = mysqli_real_escape_string($conn, $_GET['search-term']);
$searchCriteria = mysqli_real_escape_string($conn, $_GET['search-criteria']);

// Zapewnienie, że kryterium wyszukiwania jest dozwolone
$validCriteria = ['id', 'nazwa', 'cena', 'opis', 'kategoria'];

if (!in_array($searchCriteria, $validCriteria)) {
    echo "<tr><td colspan='8'>Błędne kryterium wyszukiwania.</td></tr>";
    exit;
}

// Budowanie zapytania SQL z dynamicznym filtrowaniem
$query = "SELECT PotrawaId, Nazwa, Cena, Opis, Zdjecie, Kategoria, Aktywny 
          FROM Potrawy 
          WHERE $searchCriteria LIKE '%$searchTerm%'";

// Wykonujemy zapytanie do bazy danych
$result = mysqli_query($conn, $query);

// Sprawdzamy, czy są jakieś wyniki
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Dodajemy status
        $status = $row['Aktywny'] == 1 ? 'aktywna' : 'nieaktywna';

        echo "<tr>
            <td>{$row['PotrawaId']}</td>
            <td>{$row['Nazwa']}</td>
            <td>{$row['Cena']}</td>
            <td>{$row['Opis']}</td>
            <td><img src='{$row['Zdjecie']}' alt='Zdjęcie potrawy' width='50' height='50'></td>
            <td>{$row['Kategoria']}</td>
            <td>{$status}</td> <!-- Wyświetlamy status -->
            <td><button class='edit-menu button-24 edytuj' data-id='{$row['PotrawaId']}'>Edytuj</button> 
                 <button class='change-status button-24' data-id='{$row['PotrawaId']}' data-status='{$status}'>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8'>Brak wyników wyszukiwania</td></tr>";
}
?>
