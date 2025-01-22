<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../database/db.php');

$response = [];
try {
    if (isset($_POST['table_reservation_date'])) {
        $reservationDate = $conn->real_escape_string($_POST['table_reservation_date']);

        // Aktualna data i godzina
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        // Sprawdzenie, czy dzień jest niedzielą
        $dayOfWeek = date('w', strtotime($reservationDate)); // 0 = niedziela
        $isSunday = ($dayOfWeek == 0);

        // Sprawdzenie, czy dzień jest przeszły
        $isPastDate = ($reservationDate < $currentDate);

        // Sprawdzenie, czy sala jest zarezerwowana w tym dniu
        $sqlRoomBooking = "
            SELECT COUNT(*) as RoomBooked 
            FROM RezerwacjeSale
            WHERE DataRezerwacji = '$reservationDate'";
        $resultRoomBooking = $conn->query($sqlRoomBooking);
        $isRoomBooked = false;
        if ($resultRoomBooking && $resultRoomBooking->num_rows > 0) {
            $row = $resultRoomBooking->fetch_assoc();
            $isRoomBooked = ($row['RoomBooked'] > 0);
        }

        // Pobranie zarezerwowanych godzin i liczby osób dla stolików
        $reservedHours = [];
        $sqlReservedSeats = "
            SELECT GodzinaRozpoczecia, SUM(IloscOsob) as IloscOsob 
            FROM RezerwacjeStoliki 
            WHERE DataRezerwacji = '$reservationDate'
            GROUP BY GodzinaRozpoczecia";
        $resultReservedSeats = $conn->query($sqlReservedSeats);

        // Jeżeli brak wyników w zapytaniu, to uznajemy, że brak rezerwacji
        if ($resultReservedSeats->num_rows > 0) {
            while ($row = $resultReservedSeats->fetch_assoc()) {
                $reservedHours[$row['GodzinaRozpoczecia']] = (int)$row['IloscOsob'];
            }
        } else {
            // Brak rezerwacji na ten dzień, uznajemy, że dzień jest dostępny
            $reservedHours = [];
        }

        // Dodanie przeszłych godzin, jeśli to bieżący dzień
        $pastHours = [];
        $allHours = ['09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00',
                     '14:00:00', '15:00:00', '16:00:00', '17:00:00', '18:00:00',
                     '19:00:00', '20:00:00', '21:00:00', '22:00:00', '23:00:00'];

        if ($reservationDate === $currentDate) {
            // Dodaj godziny, które już minęły
            foreach ($allHours as $hour) {
                if ($hour < date('H:i:s')) {  // Porównujemy godzinę z aktualną godziną
                    $pastHours[] = $hour;
                }
            }
        }

        // Określamy, które godziny są pełne (42 osoby)
        $disabledHours = [];
        foreach ($reservedHours as $hour => $numPeople) {
            if ($numPeople >= 42) {
                $disabledHours[] = $hour;
            }
        }

        // Generowanie dostępnych godzin
        $availableHours = [];
        foreach ($allHours as $hour) {
            // Dodajemy godzinę, jeśli nie jest pełna, nie jest przeszła i sala nie jest zarezerwowana
            if (!in_array($hour, $pastHours) && !in_array($hour, $disabledHours) && !$isRoomBooked) {
                $availableHours[] = $hour;
            }
        }

        // Ustawienie flagi, jeśli dzień jest zablokowany
        $isDayBlocked = $isSunday || $isPastDate || $isRoomBooked;

        // Tworzymy odpowiedź
        $response = [
            'success' => true,
            'isDayBlocked' => $isDayBlocked,
            'isSunday' => $isSunday,
            'isPastDate' => $isPastDate,
            'isRoomBooked' => $isRoomBooked,
            'message' => $isDayBlocked 
                ? "Dzień $reservationDate jest niedostępny do rezerwacji."
                : "Dzień $reservationDate jest dostępny do rezerwacji.",
            'hours' => $availableHours,
            'pastHours' => $pastHours,
            'disabledHours' => $disabledHours,  // Dodajemy godziny, które są pełne
        ];
    } else {
        throw new Exception("Nie podano daty rezerwacji.");
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
