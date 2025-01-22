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
                <p>Rezerwacja nr: {$reservationId} została anulowana.</p>
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
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $action = $_POST['action'];
        $id = (int)$_POST['id'];

        if ($action === 'confirm') {
            $query = $conn->prepare("UPDATE RezerwacjeSale SET Status = 1 WHERE RezerwacjaId = ?");
            $query->bind_param("i", $id);

            if ($query->execute() && $query->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Rezerwacja zatwierdzona.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nie znaleziono rezerwacji do zatwierdzenia.']);
            }
            exit();
        } elseif ($action === 'delete') {
            $query = $conn->prepare("SELECT ImieNazwisko, NumerTelefonu, DataRezerwacji, GodzinaRozpoczecia, UzytkownikId FROM RezerwacjeSale WHERE RezerwacjaId = ?");
            $query->bind_param("i", $id);
            $query->execute();
            $result = $query->get_result();

            if ($row = $result->fetch_assoc()) {
                $name = $row['ImieNazwisko'];
                $phone = $row['NumerTelefonu'];
                $date = $row['DataRezerwacji'];
                $time = $row['GodzinaRozpoczecia'];
                $userId = $row['UzytkownikId'];

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

            $query = $conn->prepare("DELETE FROM RezerwacjeSale WHERE RezerwacjaId = ?");
            $query->bind_param("i", $id);

            if ($query->execute() && $query->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Rezerwacja usunięta.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Nie znaleziono rezerwacji do usunięcia.']);
            }
            exit();
        }
    } elseif (isset($_POST['date'])) {
        $date = $_POST['date'];
        $query = $conn->prepare("SELECT rs.RezerwacjaId, rs.ImieNazwisko, rs.NumerTelefonu, rs.LiczbaOsob, 
                   rs.GodzinaRozpoczecia, rs.DataRezerwacji, rs.Status,
                   COALESCE(p1.Nazwa, rs.Przystawka) AS PrzystawkaNazwa,
                   COALESCE(p2.Nazwa, rs.DanieGlowne) AS DanieGlowneNazwa,
                   COALESCE(p3.Nazwa, rs.Ciasto) AS CiastoNazwa
            FROM RezerwacjeSale rs
            LEFT JOIN Potrawy p1 ON rs.Przystawka = p1.PotrawaId
            LEFT JOIN Potrawy p2 ON rs.DanieGlowne = p2.PotrawaId
            LEFT JOIN Potrawy p3 ON rs.Ciasto = p3.PotrawaId
            WHERE rs.DataRezerwacji = ?");
        $query->bind_param("s", $date);
        $query->execute();
        $result = $query->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            echo "<tr>
                <td>{$row['RezerwacjaId']}</td>
                <td>{$row['ImieNazwisko']}</td>
                <td>{$row['NumerTelefonu']}</td>
                <td>{$row['LiczbaOsob']}</td>
                <td>{$row['GodzinaRozpoczecia']}</td>
                <td>{$row['DataRezerwacji']}</td>
                <td>{$row['PrzystawkaNazwa']}</td>
                <td>{$row['DanieGlowneNazwa']}</td>
                <td>{$row['CiastoNazwa']}</td>
                <td>" . ($row['Status'] ? 'Potwierdzona' : 'Oczekująca') . "</td>
                <td>
                    <button class='confirm' data-id='{$row['RezerwacjaId']}'>Potwierdź</button>
                    <button class='cancel' data-id='{$row['RezerwacjaId']}'>Anuluj</button>
                </td>
            </tr>";
        }
    }
}
?>