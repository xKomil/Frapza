<?php
require_once('../database/db.php');

$errors = [];
$success = false;
$resetForm = false;  // Flaga resetowania formularza

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Walidacja imienia
    if (empty(trim($_POST["imie"]))) {
        $errors[] = "Proszę podać imię.";
    } elseif (!preg_match("/^[a-zA-ZąęćłńóśźżĄĘĆŁŃÓŚŹŻ]+$/u", $_POST["imie"])) {
        $errors[] = "Imię może zawierać tylko litery.";
    } else {
        $imie = trim($_POST["imie"]);
    }

    // Walidacja nazwiska
    if (empty(trim($_POST["nazwisko"]))) {
        $errors[] = "Proszę podać nazwisko.";
    } elseif (!preg_match("/^[a-zA-ZąęćłńóśźżĄĘĆŁŃÓŚŹŻ]+$/u", $_POST["nazwisko"])) {
        $errors[] = "Nazwisko może zawierać tylko litery.";
    } else {
        $nazwisko = trim($_POST["nazwisko"]);
    }

    // Walidacja adresu email
    if (empty(trim($_POST["email"]))) {
        $errors[] = "Proszę podać adres email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Podano nieprawidłowy adres email.";
    } else {
        $email = trim($_POST["email"]);
        
        // Sprawdzanie, czy email już istnieje w bazie
        $emailCheckQuery = "SELECT * FROM Uzytkownicy WHERE Email = '$email'";
        $result = mysqli_query($conn, $emailCheckQuery);
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Podany adres e-mail został już użyty do założenia konta.";
        }
    }

    // Walidacja numeru telefonu
    if (empty(trim($_POST["numer"]))) {
        $errors[] = "Proszę podać numer telefonu.";
    } elseif (!preg_match("/^[0-9]{9}$/", $_POST["numer"])) {
        $errors[] = "Numer telefonu powinien składać się z dokładnie 9 cyfr.";
    } else {
        $numerTelefonu = trim($_POST["numer"]);
    }

    // Walidacja hasła
    if (empty(trim($_POST["haslo"]))) {
        $errors[] = "Proszę podać hasło.";
    } elseif (strlen(trim($_POST["haslo"])) < 6) {
        $errors[] = "Hasło musi zawierać co najmniej 6 znaków.";
    } else {
        $haslo = trim($_POST["haslo"]);
    }

    // Walidacja powtórzenia hasła
    if (empty(trim($_POST["powtorz_haslo"]))) {
        $errors[] = "Proszę powtórzyć hasło.";
    } else {
        $powtorz_haslo = trim($_POST["powtorz_haslo"]);
        if (empty($errors) && ($haslo != $powtorz_haslo)) {
            $errors[] = "Podane hasła nie są takie same.";
        }
    }

    if (empty($errors)) {
        // Zabezpieczenie przed atakami SQL Injection
        $imie = mysqli_real_escape_string($conn, $imie);
        $nazwisko = mysqli_real_escape_string($conn, $nazwisko);
        $email = mysqli_real_escape_string($conn, $email);
        $numerTelefonu = mysqli_real_escape_string($conn, $numerTelefonu);
        $haslo = mysqli_real_escape_string($conn, $haslo);
        $haslo = password_hash($haslo, PASSWORD_DEFAULT);  // Haszowanie hasła

        // Zapytanie SQL
        $sql = "INSERT INTO Uzytkownicy (Imie, Nazwisko, Email, Haslo, NumerTelefonu, Rola) 
                VALUES ('$imie', '$nazwisko', '$email', '$haslo', '$numerTelefonu', 'user')";

        if (mysqli_query($conn, $sql)) {
            $success = true;
            $resetForm = true;  // Ustawiamy flagę, aby formularz został zresetowany
        } else {
            $errors[] = "Błąd podczas dodawania użytkownika: " . mysqli_error($conn);
        }
    }

    // Zwracamy odpowiedź JSON
    echo json_encode(['success' => $success, 'errors' => $errors, 'resetForm' => $resetForm]);
    exit;
}
?>