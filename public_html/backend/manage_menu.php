<?php
require_once '../database/db.php';

// Zapytanie SQL do pobrania wszystkich potraw (ze statusem aktywności)
$sql = "SELECT PotrawaId, Nazwa, Cena, Opis, Zdjecie, Kategoria, Aktywny FROM Potrawy";
$result = $conn->query($sql);

// Tworzymy tablicę wyników
$menu_items = [];
if ($result->num_rows > 0) {
    // Dane potraw
    while ($row = $result->fetch_assoc()) {
        $menu_items[] = [
            'id' => $row['PotrawaId'],
            'nazwa' => $row['Nazwa'],
            'cena' => $row['Cena'],
            'opis' => $row['Opis'],
            'zdjecie' => $row['Zdjecie'] ?: 'default_image.jpg',
            'kategoria' => $row['Kategoria'],
            'status' => $row['Aktywny'] == 1 ? 'aktywna' : 'nieaktywna' // Dodajemy status
        ];
    }
    // Zwracamy dane jako JSON
    echo json_encode($menu_items);
} else {
    // Jeśli brak wyników, zwracamy pustą tablicę
    echo json_encode([]);
}
?>
