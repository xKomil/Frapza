<?php
require_once '../database/db.php'; // Połączenie z bazą danych

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo 'invalid';
    exit;
}

$reservationId = intval($_GET['id']);

// Usunięcie rezerwacji
$query = $conn->prepare("DELETE FROM RezerwacjeSale WHERE RezerwacjaId = ?");
$query->bind_param('i', $reservationId);

if ($query->execute() && $query->affected_rows > 0) {
    echo 'success';
} else {
    echo 'error';
}
?>
