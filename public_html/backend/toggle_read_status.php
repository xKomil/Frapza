<?php
require_once '../database/db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['WiadomoscId'])) {
    $wiadomoscId = $data['WiadomoscId'];

    // Pobranie statusu przeczytania
    $query = $conn->prepare("SELECT Przeczytana FROM Wiadomosci WHERE WiadomoscId = ?");
    $query->bind_param('i', $wiadomoscId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nowyStatus = $row['Przeczytana'] == 1 ? 0 : 1;

        // Aktualizacja statusu
        $update = $conn->prepare("UPDATE Wiadomosci SET Przeczytana = ? WHERE WiadomoscId = ?");
        $update->bind_param('ii', $nowyStatus, $wiadomoscId);
        if ($update->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $update->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Nie znaleziono wiadomości.']);
    }
    $query->close();
}
$conn->close();

?>
