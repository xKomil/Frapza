<?php
require_once 'database/db.php';
session_start();

// Sprawdzanie, czy formularz został złożony
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pobieranie danych z formularza
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Zabezpieczenie przed atakami SQL Injection
    $email = mysqli_real_escape_string($conn, $email);
    $password = mysqli_real_escape_string($conn, $password);
    
    // Zapytanie do bazy danych
    $sql = "SELECT * FROM Uzytkownicy WHERE Email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    // Sprawdzenie, czy zapytanie zwróciło wynik
    if (!$result) {
        echo "<script>window.onload = function() { alert('Błąd podczas logowania. Spróbuj ponownie później.'); }</script>";
    } else {
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Sprawdzanie hasła
            if (password_verify($password, $row['Haslo'])) {
                // Zapisanie danych użytkownika w sesji
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $row['UzytkownikId'];
                $_SESSION['user_name'] = $row['Imie'];
                $_SESSION['user_surname'] = $row['Nazwisko'];
                $_SESSION['user_role'] = $row['Rola'];
                
                // Przekierowanie na podstawie roli użytkownika
                if ($row['Rola'] == 'admin') {
                    header("Location: panelAdmina.php");
                } else if ($row['Rola'] == 'pracownik'){
                    header("Location: panelPracownika.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                // Nieprawidłowe hasło
                echo "<script>window.onload = function() { alert('Nieprawidłowe hasło. Spróbuj ponownie.'); }</script>";
            }
        } else {
            // Użytkownik o podanym adresie email nie istnieje
            echo "<script>window.onload = function() { alert('Nie znaleziono użytkownika o podanym adresie email.'); }</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>STRONA LOGOWANIA - Zaloguj się</title>
    <link rel="stylesheet" href="css/loginstyle.css?v=<?php echo filemtime('css/loginstyle.css'); ?>" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body>
    <div class="karta-logowania-container">
        <div class="karta-logowania">
            <div class="karta-logowania-logo">
                <img src="assets/frapza_logo.png" alt="logo">
            </div>
            <div class="karta-logowania-header">
                <h1>Zaloguj się</h1>
                <div>Wpisz swoje dane, w celu zalogowania</div>
            </div>
            <form class="karta-logowania-form" method="POST" action="">
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">mail</span>
                    <input type="text" name="email" placeholder="Wpisz e-mail" required autofocus>
                </div>
                <div class="form-rzecz">
                    <span class="form-rzecz-ikona material-symbols-outlined">lock</span>
                    <input type="password" name="password" placeholder="Wpisz hasło" required>
                </div>
                <button type="submit">Zaloguj się</button>
            </form>
            <div class="karta-logowania-footer">
                Nie masz jeszcze konta?
                <a href="rejestracja.php">Zarejestruj się za darmo</a> <br><br>
                <a href="index.php">Kontynuuj bez logowania</a>
            </div>
        </div>
    </div>
</body>
</html>
