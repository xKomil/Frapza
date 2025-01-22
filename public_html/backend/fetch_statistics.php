<?php

require_once '../database/db.php'; // Połączenie z bazą danych

// Funkcja do wykonywania zapytań SQL
function executeQuery($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param(...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

// Funkcja do walidacji daty
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Funkcja do pobierania daty na podstawie bieżącego miesiąca/roku
function getCurrentMonthOrYearDate($period = 'month') {
    $date = new DateTime();
    if ($period == 'month') {
        $date->modify('first day of this month');
    } elseif ($period == 'year') {
        $date->modify('first day of January this year');
    }
    return $date->format('Y-m-d');
}

// Sprawdzanie, czy dane zostały przesłane
if (isset($_POST['startDate'], $_POST['endDate'])) {
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];

    // Walidacja dat
    if (!validateDate($startDate) || !validateDate($endDate)) {
        echo json_encode(['error' => 'Nieprawidłowy format daty']);
        exit;
    }

    $response = [];

    // 1. Liczba nowych użytkowników
    $query = "SELECT COUNT(*) AS userCountDate FROM Uzytkownicy WHERE DataRejestracji BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['users']['newUsers'] = $result->fetch_assoc()['userCountDate'];

    // 2. Liczba nowych wiadomości
    $query = "SELECT COUNT(*) AS messageCountDate FROM Wiadomosci WHERE DataCzas BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['messages']['newMessages'] = $result->fetch_assoc()['messageCountDate'];

    // 3. Liczba nowych opinii
    $query = "SELECT COUNT(*) AS opinionCountDate FROM Opinie WHERE DataDodania BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['opinions']['newOpinions'] = $result->fetch_assoc()['opinionCountDate'];

    // 4. Liczba nowych rezerwacji stolików
    $query = "SELECT COUNT(*) AS tableReservations FROM RezerwacjeStoliki WHERE DataRezerwacji BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['reservations']['tables'] = $result->fetch_assoc()['tableReservations'];

    // 5. Liczba nowych rezerwacji sal
    $query = "SELECT COUNT(*) AS roomReservations FROM RezerwacjeSale WHERE DataRezerwacji BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['reservations']['rooms'] = $result->fetch_assoc()['roomReservations'];

    // 6. Zarobek z rezerwacji stolików
    $query = "SELECT SUM(IloscOsob * 50) AS tableEarnings FROM RezerwacjeStoliki WHERE DataRezerwacji BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['earnings']['tables'] = $result->fetch_assoc()['tableEarnings'] ?? 0;

    // 7. Zarobek z rezerwacji sal
    $query = "SELECT SUM((42 - LiczbaOsob) * 50 + LiczbaOsob * Cena) AS roomEarnings 
    FROM RezerwacjeSale 
    JOIN Potrawy ON Potrawy.PotrawaId IN (RezerwacjeSale.Przystawka, RezerwacjeSale.DanieGlowne, RezerwacjeSale.Ciasto)
    WHERE DataRezerwacji BETWEEN ? AND ?";

    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['earnings']['rooms'] = $result->fetch_assoc()['roomEarnings'] ?? 0;

    // 8. Łączna liczba rezerwacji
    $query = "SELECT SUM(reservations) AS totalReservations 
              FROM (
                  SELECT COUNT(*) AS reservations FROM RezerwacjeStoliki WHERE DataRezerwacji BETWEEN ? AND ?
                  UNION ALL
                  SELECT COUNT(*) AS reservations FROM RezerwacjeSale WHERE DataRezerwacji BETWEEN ? AND ?
              ) AS total";
    $result = executeQuery($conn, $query, ['ssss', $startDate, $endDate, $startDate, $endDate]);
    $response['reservations']['total'] = $result->fetch_assoc()['totalReservations'] ?? 0;

    // 9. Łączne zarobki
    $response['earnings']['total'] = $response['earnings']['tables'] + $response['earnings']['rooms'];

    // 10. Potrawy najczęściej wybierane w rezerwacjach sal
    $dishCategory = $_POST['dishCategory'] ?? '';
    $query = "SELECT Kategoria, Nazwa, COUNT(*) AS dishFrequency 
              FROM RezerwacjeSale 
              JOIN Potrawy ON Potrawy.PotrawaId IN (RezerwacjeSale.Przystawka, RezerwacjeSale.DanieGlowne, RezerwacjeSale.Ciasto)
              WHERE DataRezerwacji BETWEEN ? AND ?";

    if ($dishCategory) {
        $query .= " AND Kategoria = ?";
    }

    $query .= " GROUP BY Kategoria, Nazwa ORDER BY dishFrequency DESC";
    $params = $dishCategory ? ['sss', $startDate, $endDate, $dishCategory] : ['ss', $startDate, $endDate];
    $result = executeQuery($conn, $query, $params);
    $response['dishes']['mostFrequent'] = $result->fetch_all(MYSQLI_ASSOC);

    // 11. Potrawy z największym zarobkiem
    $query = "SELECT Kategoria, Nazwa, SUM(Cena) AS dishRevenue 
              FROM RezerwacjeSale 
              JOIN Potrawy ON Potrawy.PotrawaId IN (RezerwacjeSale.Przystawka, RezerwacjeSale.DanieGlowne, RezerwacjeSale.Ciasto)
              WHERE DataRezerwacji BETWEEN ? AND ?";

    if ($dishCategory) {
        $query .= " AND Kategoria = ?";
    }

    $query .= " GROUP BY Kategoria, Nazwa ORDER BY dishRevenue DESC";
    $params = $dishCategory ? ['sss', $startDate, $endDate, $dishCategory] : ['ss', $startDate, $endDate];
    $result = executeQuery($conn, $query, $params);
    $response['dishes']['highestEarnings'] = $result->fetch_all(MYSQLI_ASSOC);

    // Zwrócenie odpowiedzi w formacie JSON
    echo json_encode($response);
} else if (isset($_POST['period'])) {
    $period = $_POST['period'];
    if ($period == 'month') {
        $startDate = getCurrentMonthOrYearDate('month');
        $endDate = (new DateTime())->format('Y-m-d');
    } else if ($period == 'year') {
        $startDate = getCurrentMonthOrYearDate('year');
        $endDate = (new DateTime())->format('Y-m-d');
    }

    $response = [];

    // 1. Liczba nowych użytkowników
    $query = "SELECT COUNT(*) AS userCountDate FROM Uzytkownicy WHERE DataRejestracji BETWEEN ? AND ?";
    $result = executeQuery($conn, $query, ['ss', $startDate, $endDate]);
    $response['users']['newUsers'] = $result->fetch_assoc()['userCountDate'];

    // Kontynuuj resztę zapytań w sposób identyczny jak wcześniej...

    // Zwrócenie odpowiedzi w formacie JSON
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Brak daty początkowej lub końcowej']);
}

?>