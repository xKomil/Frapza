<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../database/db.php');
require_once '../libs/phpmailer/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once '../libs/phpmailer/PHPMailer-master/PHPMailer-master/src/SMTP.php';
require_once '../libs/phpmailer/PHPMailer-master/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendCancellationEmail($userEmail, $name, $phone, $reservationId, $date, $time) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($str, $level) {
            error_log("SMTP DEBUG: $str");
        };
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'frapzarestauracja@gmail.com';
        $mail->Password = 'gcnk micv dwyt wcya';
        $mail->Port = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom('frapzarestauracja@gmail.com', 'Frapza Restauracja');
        $mail->addAddress($userEmail);

        $mail->isHTML(true);
        $mail->Subject = "Anulowanie rezerwacji #{$reservationId}";
        $mail->Body = "
            <html>
            <body>
                <p>Rezerwacja Stolika nr: {$reservationId} została anulowana.</p>
                <p>Imię i nazwisko: {$name}</p>
                <p>Telefon: {$phone}</p>
                <p>Data: {$date}</p>
                <p>Godzina: {$time}</p>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Błąd wysyłania e-maila: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obsługuje akcje: potwierdzenie lub anulowanie rezerwacji
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $action = $_POST['action'];
        $id = (int)$_POST['id'];

        if ($action === 'confirm') {
            // Sprawdzanie, czy rezerwacja już została potwierdzona
            $checkQuery = $conn->prepare("SELECT Status FROM RezerwacjeStoliki WHERE RezerwacjaId = ?");
            $checkQuery->bind_param("i", $id);
            $checkQuery->execute();
            $checkQuery->bind_result($status);
            $checkQuery->fetch();
            $checkQuery->close();

            if ($status == 1) {
                // Jeśli rezerwacja jest już potwierdzona
                echo json_encode(['success' => false, 'message' => 'Rezerwacja już jest potwierdzona.']);
            } else {
                // Potwierdzenie rezerwacji
                $query = $conn->prepare("UPDATE RezerwacjeStoliki SET Status = 1 WHERE RezerwacjaId = ?");
                $query->bind_param("i", $id);

                if ($query->execute() && $query->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Rezerwacja zatwierdzona.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Nie znaleziono rezerwacji do zatwierdzenia.']);
                }
            }
            exit();
        } elseif ($action === 'delete') {
            // Pobranie danych rezerwacji przed jej usunięciem
            $query = $conn->prepare("SELECT ImieNazwisko, NumerTelefonu, DataRezerwacji, GodzinaRozpoczecia, UzytkownikId FROM RezerwacjeStoliki WHERE RezerwacjaId = ?");
            $query->bind_param("i", $id);
            $query->execute();
            $result = $query->get_result();

            if ($row = $result->fetch_assoc()) {
                $name = $row['ImieNazwisko'];
                $phone = $row['NumerTelefonu'];
                $date = $row['DataRezerwacji'];
                $time = $row['GodzinaRozpoczecia'];
                $userId = $row['UzytkownikId'];

                // Jeśli mamy ID użytkownika, próbujemy pobrać jego e-mail
                if ($userId) {
                    $userQuery = $conn->prepare("SELECT Email FROM Uzytkownicy WHERE UzytkownikId = ?");
                    $userQuery->bind_param("i", $userId);
                    $userQuery->execute();
                    $userResult = $userQuery->get_result();

                    if ($userRow = $userResult->fetch_assoc()) {
                        $fromEmail = $userRow['Email'];

                        if (!sendCancellationEmail($fromEmail, $name, $phone, $id, $date, $time)) {
                            echo json_encode(['success' => false, 'message' => 'Nie udało się wysłać e-maila.']);
                            exit();
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Nie znaleziono e-maila nadawcy.']);
                        exit();
                    }
                }
            }

            // Usuwanie rezerwacji stolika
            $query = $conn->prepare("DELETE FROM RezerwacjeStoliki WHERE RezerwacjaId = ?");
            $query->bind_param("i", $id);

            if ($query->execute() && $query->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Rezerwacja usunięta.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nie znaleziono rezerwacji do usunięcia.']);
            }
            exit();
        }
    }

    // Obsługuje zapytanie o rezerwacje po dacie
    if (isset($_POST['date'])) {
        $date = $_POST['date'];

        // Zapytanie do tabeli RezerwacjeStoliki
        $query = $conn->prepare("SELECT rs.RezerwacjaId, rs.ImieNazwisko, rs.NumerTelefonu, rs.IloscOsob, 
                   rs.GodzinaRozpoczecia, rs.DataRezerwacji, rs.Status
            FROM RezerwacjeStoliki rs
            WHERE rs.DataRezerwacji = ?");
        $query->bind_param("s", $date);
        $query->execute();
        $result = $query->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            // Upewnienie się, że 'IloscOsob' jest ustawiona poprawnie
            $row['IloscOsob'] = isset($row['IloscOsob']) ? $row['IloscOsob'] : 'Brak danych';

            $rows[] = $row;
        }

        // Zwrócenie wyników w formacie JSON
        echo json_encode($rows);
    }
}
?>
