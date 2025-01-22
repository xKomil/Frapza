<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../database/db.php');

$response = [];

if (isset($_POST['reservation_date'])) {
    $reservationDate = $conn->real_escape_string($_POST['reservation_date']);

    // Aktualna data i godzina
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    // Sprawdzenie, czy dzień jest niedzielą
    $dayOfWeek = date('w', strtotime($reservationDate)); // 0 = niedziela
    $isSunday = ($dayOfWeek == 0);

    // Sprawdzenie, czy dzień jest przeszły
    $isPastDate = ($reservationDate < $currentDate);

    // Pobranie godzin dla danego dnia z bazy
    $reservedHours = [];
    $sqlReservedHours = "SELECT GodzinaRozpoczecia FROM RezerwacjeSale WHERE DataRezerwacji = '$reservationDate'";
    $resultReservedHours = $conn->query($sqlReservedHours);
    while ($row = $resultReservedHours->fetch_assoc()) {
        $reservedHours[] = $row['GodzinaRozpoczecia'];
    }

    // Dodanie przeszłych godzin, jeśli to bieżący dzień
    $pastHours = [];
    if ($reservationDate === $currentDate) {
        $allHours = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00',
                     '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00',
                     '19:00:00', '20:00:00', '21:00:00', '22:00:00', '23:00:00'];
    
        // Dodaj godziny, które już minęły
        foreach ($allHours as $hour) {
            if ($hour < date('H:i:s')) {  // Porównujemy godzinę z aktualną godziną
                $pastHours[] = $hour;
            }
        }
    }

    // Sprawdzenie, czy sala lub stolik są zarezerwowane
    $sqlHall = "SELECT 1 FROM RezerwacjeSale WHERE DataRezerwacji = '$reservationDate' LIMIT 1";
    $resultHall = $conn->query($sqlHall);
    $isHallReserved = ($resultHall->num_rows > 0);

    $sqlTable = "SELECT 1 FROM RezerwacjeStoliki WHERE DataRezerwacji = '$reservationDate' LIMIT 1";
    $resultTable = $conn->query($sqlTable);
    $isTableReserved = ($resultTable->num_rows > 0);

    // Czy dzień jest całkowicie zablokowany
    $isDayBlocked = $isPastDate || $isSunday || $isHallReserved || $isTableReserved;

    $message = [
        'isDayBlocked' => $isDayBlocked,
        'isSunday' => $isSunday,
        'isPastDate' => $isPastDate,
        'isHallReserved' => $isHallReserved,
        'isTableReserved' => $isTableReserved,
        'pastHours' => array_merge($pastHours, $reservedHours),
        'message' => $isDayBlocked
            ? "Dzień $reservationDate jest niedostępny do rezerwacji."
            : "Dzień $reservationDate jest dostępny do rezerwacji.",
    ];
} else {
}

echo json_encode($message);
?>
