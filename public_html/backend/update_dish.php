<?php
require_once '../database/db.php';
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Nieznany błąd'];

if (isset($_POST['id'], $_POST['nazwa'], $_POST['cena'], $_POST['opis'], $_POST['kategoria'])) {
    $id = intval($_POST['id']);
    $nazwa = trim($_POST['nazwa']);
    $cena = floatval($_POST['cena']);
    $opis = trim($_POST['opis']);
    $kategoria = trim($_POST['kategoria']);
    $zdjecie = !empty($_POST['link_zdjecie']) ? trim($_POST['link_zdjecie']) : null;

    $query = "UPDATE Potrawy SET Nazwa = ?, Cena = ?, Opis = ?, Kategoria = ?, Zdjecie = ? WHERE PotrawaId = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("sdsssi", $nazwa, $cena, $opis, $kategoria, $zdjecie, $id);
        
        if ($stmt->execute()) {
            $response = [
                'status' => 'success',
                'message' => 'Potrawa została zaktualizowana',
                'dish' => [
                    'id' => $id,
                    'nazwa' => $nazwa,
                    'cena' => $cena,
                    'opis' => $opis,
                    'kategoria' => $kategoria,
                    'zdjecie' => $zdjecie
                ]
            ];
        } else {
            $response['message'] = 'Wystąpił błąd podczas aktualizacji potrawy';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Błąd zapytania SQL';
    }
} else {
    $response['message'] = 'Brak wymaganych danych';
}

echo json_encode($response);
$conn->close();
?>
