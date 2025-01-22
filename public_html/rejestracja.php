<?php
require_once 'database/db.php';

$imie = $nazwisko = $email = $numerTelefonu = $haslo = $powtorz_haslo = "";
$errors = []; // Tablica na błędy

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Sprawdź, czy nie ma błędów walidacji przed dodaniem do bazy danych
    if (empty($errors)) {
        // Zabezpieczenie przed atakami SQL Injection
        $imie = mysqli_real_escape_string($conn, $imie);
        $nazwisko = mysqli_real_escape_string($conn, $nazwisko);
        $email = mysqli_real_escape_string($conn, $email);
        $numerTelefonu = mysqli_real_escape_string($conn, $numerTelefonu);
        $haslo = mysqli_real_escape_string($conn, $haslo);
        $haslo = password_hash($haslo, PASSWORD_DEFAULT); // Haszowanie hasła

        // Zapytanie SQL
        $sql = "INSERT INTO Uzytkownicy (Imie, Nazwisko, Email, Haslo, NumerTelefonu, Rola) VALUES ('$imie', '$nazwisko', '$email', '$haslo', '$numerTelefonu', 'user')";

        if (mysqli_query($conn, $sql)) {
            // Jeśli rejestracja powiodła się, przekieruj użytkownika do strony logowania
            header("Location: logowanie.php");
            exit();
        } else {
            $errors[] = "Błąd podczas rejestracji: " . mysqli_error($conn);
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarejestruj się w KKino!</title>
    <link rel="stylesheet" href="css/rejestracjastyle.css?v=<?php echo filemtime('css/rejestracjastyle.css'); ?>" />
    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
</head>
<body>
    <div class="karta-logowania-container">
        <div class="karta-logowania">
            <div class="karta-logowania-logo">
                <img src="assets/frapza_logo.png" alt="logo" />
            </div>
            <div class="karta-logowania-header">
                <h1>Zarejestruj się</h1>
                <div>Wpisz swoje dane, w celu rejestracji</div>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
            <form class="karta-logowania-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">account_circle</span>
                    <input type="text" name="imie" placeholder="Wpisz swoje imię" value="<?php echo htmlspecialchars($imie); ?>" required autofocus>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">account_circle</span>
                    <input type="text" name="nazwisko" placeholder="Wpisz swoje nazwisko" value="<?php echo htmlspecialchars($nazwisko); ?>" required>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">mail</span>
                    <input type="email" name="email" placeholder="Wpisz e-mail" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">phone</span>
                    <input type="text" name="numer" placeholder="Wpisz numer telefonu" value="<?php echo htmlspecialchars($numerTelefonu); ?>" required>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">lock</span>
                    <input type="password" name="haslo" placeholder="Wpisz hasło" required>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">lock</span>
                    <input type="password" name="powtorz_haslo" placeholder="Wpisz hasło ponownie" required>
                </div>
                <button type="submit" name="submit">Zarejestruj się</button>
                <a class="powrot" href="logowanie.php"><p>POWRÓT DO LOGOWANIA</p></a>
            </form>
        </div>
    </div>
</body>
</html>
