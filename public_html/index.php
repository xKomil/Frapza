<?php
require_once 'database/db.php';
session_start();

// Generowanie unikalnego tokenu
if (!isset($_SESSION['form_token'])) {
  $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Sprawdzenie, czy użytkownik jest zalogowany
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  $user_id = $_SESSION['user_id'];

  // Pobieranie roli z bazy danych
  $stmt = $conn->prepare("SELECT Rola FROM Uzytkownicy WHERE UzytkownikId = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
      $role = $result->fetch_assoc()['Rola'];

      // Przekierowanie na odpowiedni panel
      if ($role === 'admin') {
          header("Location: panelAdmina.php");
          exit();
      } elseif ($role === 'pracownik') {
          header("Location: panelPracownika.php");
          exit();
      }
  }
}

// Obsługa wylogowania
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] == true) {
    session_destroy();
    header("Location: logowanie.php");
    exit();
}
#Opinie 
$sql = "
SELECT Opinie.UzytkownikId, Opinie.Ocena, Opinie.Opis, Opinie.DataDodania,
Uzytkownicy.Imie, Uzytkownicy.Nazwisko, Uzytkownicy.Zdjecie
FROM Opinie
JOIN Uzytkownicy ON Opinie.UzytkownikId = Uzytkownicy.UzytkownikId
ORDER BY Opinie.DataDodania DESC
LIMIT 3
";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Błąd w zapytaniu: " . mysqli_error($conn));
}

$opinions = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

#Menu

// Funkcja do pobrania potrawy na podstawie PotrawaId
function getDishById($conn, $id) {
  $sql = "SELECT Nazwa, Cena FROM Potrawy WHERE PotrawaId = $id LIMIT 1";
  $result = mysqli_query($conn, $sql);
  return mysqli_fetch_assoc($result);
}

// Pobranie danych dla Zestawów
$set1 = [
  'starter' => getDishById($conn, 29),
  'main' => getDishById($conn, 38),
  'cake' => getDishById($conn, 34)
];

$set2 = [
  'starter' => getDishById($conn, 31),
  'main' => getDishById($conn, 39),
  'cake' => getDishById($conn, 35)
];

$set3 = [
  'starter' => getDishById($conn, 32),
  'main' => getDishById($conn, 16),
  'cake' => getDishById($conn, 5)
];

$set4 = [
  'starter' => getDishById($conn, 33),
  'main' => getDishById($conn, 14),
  'cake' => getDishById($conn, 7)
];

// Pobranie opcji dla custom menu (Przystawki, Dania główne, Ciasta)
$sql_starters = "SELECT PotrawaId, Nazwa, Cena FROM Potrawy WHERE Kategoria = 'Przystawki' ORDER BY DataDodania DESC";
$starters = mysqli_fetch_all(mysqli_query($conn, $sql_starters), MYSQLI_ASSOC);

$sql_main_courses = "SELECT PotrawaId, Nazwa, Cena FROM Potrawy WHERE Kategoria = 'Dania główne' ORDER BY DataDodania DESC";
$main_courses = mysqli_fetch_all(mysqli_query($conn, $sql_main_courses), MYSQLI_ASSOC);

$sql_cakes = "SELECT PotrawaId, Nazwa, Cena FROM Potrawy WHERE Kategoria = 'Ciasta' ORDER BY DataDodania DESC";
$cakes = mysqli_fetch_all(mysqli_query($conn, $sql_cakes), MYSQLI_ASSOC);

// Rezerwacja Sali
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {

  // Sprawdzenie, czy token jest obecny i czy zgadza się z sesją
  if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
      $message = " ";
      // Generowanie nowego tokenu na przyszłość
      $_SESSION['form_token'] = bin2hex(random_bytes(32));
  } else {
    // Wyczyść token, aby zapobiec ponownemu użyciu
    unset($_SESSION['form_token']);
    // Zapis rezerwacji w bazie
    $fullName = $conn->real_escape_string($_POST['full_name']);		
    $phone = $conn->real_escape_string($_POST['phone']);
    $guests = intval($_POST['guests']);
    $reservationDate = $conn->real_escape_string($_POST['reservation_date']);
    $startTime = $conn->real_escape_string($_POST['start_time']);
    $menuChoice = $conn->real_escape_string($_POST['menu']);
    // Walidacja numeru telefonu

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
      $user_id = $_SESSION['user_id'];
    } else {
        $message = "Musisz być zalogowany, aby dokonać rezerwacji.";
        return;
    }
    
    if (!preg_match('/^\+?[0-9]{9,15}$/', $phone)) {
      $message = "Nieprawidłowy format numeru telefonu. Wprowadź poprawny numer.";
    } else {
      // Dodajemy dane do bazy
      $starter = null;
      $mainCourse = null;
      $dessert = null;
      
      // Sprawdzamy, czy wybrano niestandardowe menu
      if ($menuChoice === 'custom') {
          $starter = $conn->real_escape_string($_POST['starter']);
          $mainCourse = $conn->real_escape_string($_POST['main_course']);
          $dessert = $conn->real_escape_string($_POST['dessert']);
      } else {
          // Zamiast nazw potraw zapisujemy ich identyfikatory
          switch ($menuChoice) {
              case '1':
                  $starter = 29;  // PotrawaId dla Bruschetty
                  $mainCourse = 38;  // PotrawaId dla Filet Mignon
                  $dessert = 34;  // PotrawaId dla Tarty cytrynowej z bezą
                  break;
              case '2':
                  $starter = 31;
                  $mainCourse = 39;
                  $dessert = 35;
                  break;
              case '3':
                  $starter = 32;
                  $mainCourse = 16;
                  $dessert = 5;
                  break;
              case '4':
                  $starter = 33;
                  $mainCourse = 14;
                  $dessert = 7;
                  break;
          }
      }
      
      // Zapisanie rezerwacji do bazy danych
      $sql = "INSERT INTO RezerwacjeSale 
          (ImieNazwisko, NumerTelefonu, LiczbaOsob, DataRezerwacji, GodzinaRozpoczecia, Przystawka, DanieGlowne, Ciasto, UzytkownikId) 
          VALUES ('$fullName', '$phone', $guests, '$reservationDate', '$startTime', $starter, $mainCourse, $dessert, $user_id)";
      
      if ($conn->query($sql) === TRUE) {
        $_SESSION['modal_message'] = "Rezerwacja została pomyślnie zapisana!";
      } else {
        $_SESSION['modal_message'] = "Błąd podczas zapisywania rezerwacji: " . $conn->error;
      }
    }
    // Generowanie nowego tokena na przyszłe zamówienia
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
  }
}

// Rezerwacje stoliki

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_name'])) {


  // Sprawdzenie, czy użytkownik jest zalogowany
  $user_id = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true ? $_SESSION['user_id'] : null;

  // Sprawdzenie tokenu
  if (!isset($_POST['form_token']) || $_POST['form_token'] !== $_SESSION['form_token']) {
      $message2 = " ";
      $_SESSION['form_token'] = bin2hex(random_bytes(32));
  } else {
      unset($_SESSION['form_token']);

      // Dane wejściowe
      $fullNameStolik = $conn->real_escape_string($_POST['table_name']);
      $phoneStolik = $conn->real_escape_string($_POST['table_phone']);
      $guestsStolik = intval($_POST['table_guests']);
      $reservationDateStolik = $conn->real_escape_string($_POST['table_reservation_date']);
      $startTimeStolik = $conn->real_escape_string($_POST['table_start_time']);

      // Walidacja numeru telefonu
      if (!preg_match('/^\+?[0-9]{9,15}$/', $phoneStolik)) {
          $message2 = "<span style='color: red;'>Nieprawidłowy format numeru telefonu. Wprowadź poprawny numer.</span>";
      } else {
          // Pobranie istniejących rezerwacji
          $sqlCheck = "SELECT SUM(IloscOsob) AS TotalGuests 
                       FROM RezerwacjeStoliki 
                       WHERE DataRezerwacji = '$reservationDateStolik' 
                       AND GodzinaRozpoczecia = '$startTimeStolik'";
          $result = $conn->query($sqlCheck);
          $totalGuests = $result && $result->num_rows > 0 ? intval($result->fetch_assoc()['TotalGuests']) : 0;

          // Sprawdzenie dostępnych miejsc
          if ($totalGuests + $guestsStolik > 42) {
              $remainingSeats = 42 - $totalGuests;

              if ($remainingSeats > 0) {
                  $_SESSION['modal_message'] = "Nie można zarezerwować stolika dla $guestsStolik osób. 
                  Dostępnych jest tylko $remainingSeats miejsc.";
              } else {
                  $_SESSION['modal_message'] = "Brak dostępnych miejsc na wybraną datę i godzinę.";
              }
          } else {
              // Dodanie rezerwacji
              $sqlInsert = $conn->prepare("INSERT INTO RezerwacjeStoliki 
                                          (ImieNazwisko, NumerTelefonu, IloscOsob, DataRezerwacji, GodzinaRozpoczecia, UzytkownikId) 
                                          VALUES (?, ?, ?, ?, ?, ?)");

              if (!$sqlInsert) {
                  die("Błąd w przygotowaniu zapytania: " . $conn->error);
              }

              // Bindowanie parametrów
              $sqlInsert->bind_param("ssissi", $fullNameStolik, $phoneStolik, $guestsStolik, $reservationDateStolik, $startTimeStolik, $user_id);

              if ($sqlInsert->execute()) {
                  $_SESSION['modal_message'] = "Rezerwacja została pomyślnie zapisana! 
                  Proszę potwierdzić rezerwację godzinę przed rezerwacją dzwoniąc na numer restauracji.";
              } else {
                  $_SESSION['modal_message'] = "Błąd podczas zapisywania rezerwacji: " . $conn->error;
              }
          }
      }

      // Generowanie nowego tokenu
      $_SESSION['form_token'] = bin2hex(random_bytes(32));
  }
}



// Kontakt
// Obsługa formularza kontaktowego
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {

  $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Pobierz user_id z sesji

  // Pobranie danych z formularza
  $imie = mysqli_real_escape_string($conn, $_POST['imie']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $telefon = isset($_POST['telefon']) ? mysqli_real_escape_string($conn, $_POST['telefon']) : null;
  $tresc = mysqli_real_escape_string($conn, $_POST['tresc']);

  // Walidacja danych
  $errors = [];

  if (!preg_match('/^[a-zA-ZÀ-ÖØ-öø-ÿ\s\-]+$/', $imie)) {
      $errors[] = "Imię może zawierać tylko litery, spacje i myślniki.";
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "Nieprawidłowy format adresu e-mail.";
  }

  if ($telefon && !preg_match('/^\+?[0-9]{9,15}$/', $telefon)) {
      $errors[] = "Nieprawidłowy format numeru telefonu.";
  }

  if (strlen($tresc) < 10) {
      $errors[] = "Treść wiadomości jest za krótka. Powinna mieć co najmniej 10 znaków.";
  } elseif (strlen($tresc) > 1000) {
      $errors[] = "Treść wiadomości jest za długa. Maksymalna długość to 1000 znaków.";
  }

  if (empty($errors)) {
      // Przygotowanie zapytania SQL
      $sql = "INSERT INTO Wiadomosci (Imie, NumerTelefonu, Email, Tresc, Przeczytana, UserId) 
              VALUES (?, ?, ?, ?, 0, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssi", $imie, $telefon, $email, $tresc, $user_id);

      if ($stmt->execute()) {
        $_SESSION['modal_message'] = "Twoja wiadomość została wysłana!";
      } else {
        $_SESSION['modal_message'] = "Błąd podczas wysyłania wiadomości: " . $stmt->error;
      }
  } else {
    $_SESSION['modal_message'] = "Wystąpiły błędy: " . implode(" ", $errors);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content="Frapza jest stroną restauracji" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>" />
    <link rel="stylesheet" href="css/general.css?v=<?php echo filemtime('css/general.css'); ?>" />
    <link rel="stylesheet" href="css/queries.css?v=<?php echo filemtime('css/queries.css'); ?>" />



    <!-- import ikon i skryptu -->
    <script
      type="module"
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"
    ></script>
    <script
      nomodule
      src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"
    ></script>
    <script defer src="js/script.js?v=<?php echo filemtime('js/script.js'); ?>"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script defer src="js/behaviour.js?v=<?php echo filemtime('js/behaviour.js'); ?>"></script>

    <title>Frapza</title>
    <link rel="icon" href="assets/frapza_logo.png" type="image/png" />
  </head>
  <body>
    
    <div class="bg-image">  
    <div id="modal" class="modal">
  <div class="modal-content">
    <span class="close-button">&times;</span>
    <p id="modal-message"></p>
  </div>
</div>

    <header class="header">
      <a href="#">
        <img src="assets/frapza_logo.png" alt="swiftdrop logo" class="logo" />
      </a>

      <nav class="main-nav">
        <ul class="main-nav-list">
          <li><a class="main-nav-link" href="#">Strona główna</a></li>
          <li><a class="main-nav-link" href="#menu-section">Menu</a></li>
          <li><a class="main-nav-link" href="#opinions-section">Opinie</a></li>
          <li><a class="main-nav-link" href="#reservation-section">Rezerwacja</a></li>
          <li><a class="main-nav-link" href="#contact-section">Kontakt</a></li>
          <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <li><a class="main-nav-link" href="profile.php">Profil</a></li>
          <?php endif; ?>

          <li>  
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
              <!-- Przycisk wylogowania dla zalogowanych użytkowników -->
              <form action="" method="POST">
                <button type="submit" name="logout" value="true" class="main-nav-link nav-cta button_main">Wyloguj się</button>
              </form>
            <?php else: ?>
              <!-- Link logowania dla niezalogowanych użytkowników -->
              <form action="logowanie.php" method="get" style="display:inline;">
                <button type="submit" class="main-nav-link nav-cta button_main">Zaloguj się</button>
              </form>
            <?php endif; ?>
          </li>
        </ul>
      </nav>
      <button class="btn-mobile-nav">
        <ion-icon class="icon-mobile-nav" name="menu-outline"></ion-icon>
        <ion-icon class="icon-mobile-nav" name="close-outline"></ion-icon>
      </button>
    </header>
    <main>
    <section class="section-hero" id="hero-section">
  <div class="hero-container">
    <div class="hero" id="hero-animation">
      <div class="hero-text-box">
        <h1 class="heading-primary">FRAPZA</h1>
        <p class="hero-description">Zniewalająco <br />pysznie</p>
        <a href="#reservation-section" class="btn btn--full margin-right-smf">Rezerwacja</a>
        <a href="#contact-section" class="btn btn--outline margin-right-smf">Masz pytanie🤓? &darr;</a>
      </div>
      <div id="openHours">
      <div id="openHours">
      <div class="oh-content">
    <h1>Godziny otwarcia</h1>
    <table class="oh-table delay-demo">
        
        <?php
        // Pobranie godzin otwarcia z tabeli GodzinyOtwarcia
        $sql_hours = "SELECT DzienTygodnia, GodzinaOtwarcia, GodzinaZamkniecia FROM GodzinyOtwarcia ORDER BY FIELD(DzienTygodnia, 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela')";
        $result_hours = mysqli_query($conn, $sql_hours);

        if (!$result_hours) {
            echo "<tr><td colspan='2'>Błąd pobierania danych</td></tr>";
        } else {
            while ($row = mysqli_fetch_assoc($result_hours)) {
                $dzien = $row['DzienTygodnia'];
                
                if ($dzien === 'Niedziela') {
                    $godziny = "Zamknięte";
                } else {
                    $godzinaOtwarcia = date('H:i', strtotime($row['GodzinaOtwarcia']));
                    $godzinaZamkniecia = date('H:i', strtotime($row['GodzinaZamkniecia']));
                    $godziny = ($godzinaOtwarcia && $godzinaZamkniecia) ? "$godzinaOtwarcia - $godzinaZamkniecia" : "Zamknięte";
                }

                echo "<tr><td>$dzien</td><td>$godziny</td></tr>";
            }
        }
        mysqli_free_result($result_hours);
        ?>
    </table>
</div>

</div>

      </div>
    </div>
  </div>
</section>

    </div>
    <section class="section-menu" id="menu-section">
    <div class="menu container">
      <h3 class="menu-heading">Menu</h3>
      <div class="menu-position-types"> 
        <!-- Dodanie atrybutu data-category -->
        <a href="#" data-category="Ciasta" class="menu-link active">Ciasta</a>
        <a href="#" data-category="Pizza" class="menu-link">Pizza</a>
        <a href="#" data-category="Dania główne" class="menu-link">Dania główne</a>
        <a href="#" data-category="Śniadania" class="menu-link">Śniadania</a>
        <a href="#" data-category="Napoje" class="menu-link">Napoje</a>
        <a href="#" data-category="Przystawki" class="menu-link">Przystawki</a>
        <div class="underline" id="underline"></div>
      </div>
  
      <!-- Sekcja, która będzie aktualizowana przez AJAX -->
      <div class="menu-items" id="menu-items">
        <p>Wybierz kategorię, aby zobaczyć potrawy.</p>
      </div>
  
      <div class="btn-menu">
        <button class="menu-button" onclick="window.location.href='menu.php'">Zobacz pełne menu</button>
      </div>
    </div>
    </section>


    <section class="section-opinions" id="opinions-section">
      <div class="opinions container">
          <h4 class="opinions-heading">Ostatnie opinie</h4>
          <p class="opinions-description">Zapoznaj się z ostatnimi opiniami na nasz temat! :)</p> 
          <div class="opinions-box">
              <?php foreach ($opinions as $opinion): ?>
                  <div class="opinion">
                      <p class="opinion-text"><?php echo htmlspecialchars($opinion['Opis']); ?></p>
                      <div class="opinion-info">
                          <img 
                              src="<?php echo !empty($opinion['Zdjecie']) ? htmlspecialchars($opinion['Zdjecie']) : 'assets/tymczasowe_profilowe_dla_samego_html.jpg'; ?>" 
                              alt="Profile Picture" 
                              class="profile-picture"
                          >
                          <p class="person-name"><?php echo htmlspecialchars($opinion['Imie'] . ' ' . $opinion['Nazwisko']); ?></p>
                          <div class="stars">
                              <?php for ($i = 1; $i <= 5; $i++): ?>
                                  <ion-icon name="star<?php echo $i <= $opinion['Ocena'] ? '' : '-outline'; ?>"></ion-icon>
                              <?php endfor; ?>
                          </div>
                      </div>
                  </div>
              <?php endforeach; ?>
          </div>
      </div>
    </section>  

    <section class="section-reservation" id="reservation-section" >  
      <h4 class="reservation-heading">Rezerwacja</h4>
      <div class="reservation container">
        <div class="event-reservation">
          <!-- Rezerwacja sali -->
          <form method="POST" class="reservation-form" id="hall-reservation-form" id="reservation-form-section">
            <h5 class="reservation-form-heading">Rezerwacja sali 🏢</h5>
            <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token']; ?>">
            <!-- Wyświetlenie wiadomości -->
            <p id="reservation-message" style="font-weight: bold;"></p>
            <?php if (isset($message)) : ?>
              <p style="color: black; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <label for="full_name">Imię i nazwisko</label>
            <input type="text" id="full_name" name="full_name" required />

            <label for="phone">Numer telefonu</label>
            <input type="tel" id="phone" name="phone" pattern="[0-9]{9}" title="Numer telefonu powinien mieć 9 cyfr" required/>

            <label for="guests">Ilość osób (max. 42)</label>
            <input type="number" id="guests" min='1' max="42" name="guests" required />

            <label for="reservation_date">Data rezerwacji</label>
            <input type="date" id="reservation_date" name="reservation_date" required />
            
            <label for="start_time">Godzina rozpoczęcia</label>
            <div class="hours" id="hours-container">
                <!-- Przyciski godzin będą generowane tutaj -->
                <?php 
                $times = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
                foreach ($times as $time): ?>
                    <button class="hour-button" type="button" value="<?php echo $time; ?>">
                        <?php echo $time; ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="start_time" name="start_time" required />

            <script>
                function setTime(time) {
                    // Dodaj sekundy (00) do wybranej godziny
                    const fullTime = time + ':00';  // np. '09:00' => '09:00:00'
                    const input = document.getElementById('start_time');
                    input.value = fullTime;
                }
              
                const buttons = document.querySelectorAll('.hour-button');
              
                buttons.forEach(button => {
                    button.addEventListener('click', () => {
                        // Usuń klasę "clicked" z każdego przycisku
                        buttons.forEach(btn => btn.classList.remove('clicked'));
                      
                        // Dodaj klasę "clicked" tylko do klikniętego przycisku
                        button.classList.add('clicked');
                      
                        // Ustaw godzinę w ukrytym polu
                        setTime(button.value); // Przekazuje godzinę do funkcji setTime
                    });
                });
            </script>

            <label for="menu">Wybór menu</label>
            <select id="menu" name="menu" required>
              <option value="1">
                <?php echo htmlspecialchars($set1['starter']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set1['main']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set1['cake']['Nazwa']); ?> 
                - Cena: <?php echo number_format(
                    $set1['starter']['Cena'] + $set1['main']['Cena'] + $set1['cake']['Cena'], 
                    2, ',', ' '
                ); ?> zł
              </option>
              <option value="2">
                <?php echo htmlspecialchars($set2['starter']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set2['main']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set2['cake']['Nazwa']); ?> 
                - Cena: <?php echo number_format(
                    $set2['starter']['Cena'] + $set2['main']['Cena'] + $set2['cake']['Cena'], 
                    2, ',', ' '
                ); ?> zł
              </option>
              <option value="3">
                <?php echo htmlspecialchars($set3['starter']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set3['main']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set3['cake']['Nazwa']); ?> 
                - Cena: <?php echo number_format(
                    $set3['starter']['Cena'] + $set3['main']['Cena'] + $set3['cake']['Cena'], 
                    2, ',', ' '
                ); ?> zł
              </option>
              <option value="4">
                <?php echo htmlspecialchars($set4['starter']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set4['main']['Nazwa']); ?>, 
                <?php echo htmlspecialchars($set4['cake']['Nazwa']); ?> 
                - Cena: <?php echo number_format(
                    $set4['starter']['Cena'] + $set4['main']['Cena'] + $set4['cake']['Cena'], 
                    2, ',', ' '
                ); ?> zł
              </option>
              <option value="custom">Własne menu</option>
            </select>
                
            <div id="custom_menu" style="display: none;">
              <div>
              <label for="starter">Przystawki</label>
              <select id="starter" name="starter">
                <?php foreach ($starters as $starter): ?>
                    <option value="<?php echo htmlspecialchars($starter['PotrawaId']); ?>">
                        <?php echo htmlspecialchars($starter['Nazwa']); ?> - <?php echo number_format($starter['Cena'], 2, ',', ' '); ?> zł
                    </option>
                <?php endforeach; ?>
              </select>
              </div>
                

              <div>
              <label for="main_course">Dania główne</label>
              <select id="main_course" name="main_course">
                <?php foreach ($main_courses as $main_course): ?>
                    <option value="<?php echo htmlspecialchars($main_course['PotrawaId']); ?>">
                        <?php echo htmlspecialchars($main_course['Nazwa']); ?> - <?php echo number_format($main_course['Cena'], 2, ',', ' '); ?> zł
                    </option>
                <?php endforeach; ?>
              </select>
              </div>
                
              <div>
              <label for="dessert">Ciasta</label>
              <select id="dessert" name="dessert">
                <?php foreach ($cakes as $cake): ?>
                    <option value="<?php echo htmlspecialchars($cake['PotrawaId']); ?>">
                        <?php echo htmlspecialchars($cake['Nazwa']); ?> - <?php echo number_format($cake['Cena'], 2, ',', ' '); ?> zł
                    </option>
                <?php endforeach; ?>
              </select>
              </div>
            </div>
            
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
              <button type="submit" class="menu-button">Zarezerwuj</button>
              <div class="form-feedback" id="hall-reservation-feedback"></div>
            <?php else: ?>
                <!-- Wyświetlenie wiadomości dla niezalogowanych -->
                <p style="color: black; font-weight: bold; font-size: 14px; padding: 2rem;">
                    <?php 
                    // Domyślna wiadomość dla niezalogowanych
                    $message = $message ?? "Musisz być zalogowany, aby dokonać rezerwacji.";
                    echo htmlspecialchars($message); 
                    ?>
                </p>
            <?php endif; ?>
          </form>
        </div>

        <div class="event-reservation">
          <!-- Rezerwacja na stolik -->
          <form method="POST" class="reservation-form" id="table-reservation-form">
            <h5 class="reservation-form-heading">Rezerwacja stolika 🍽️</h5>
            <div id="reservation-feedback">
            <input type="hidden" name="form_token" value="<?php echo $_SESSION['form_token']; ?>">
            <!-- Wyświetlenie wiadomości -->
            <p id="reservation-message2" style="font-weight: bold;"></p>
            <?php if (isset($message2)) : ?>
                <p style="color: black; font-weight: bold;"><?php echo htmlspecialchars($message2); ?></p>
              <?php endif; ?>
            </div>

            <label for="table_name">Imię i nazwisko</label>
            <input type="text" id="table_name" name="table_name" required />

            <label for="table_phone">Numer telefonu</label>
            <input type="tel" id="table_phone" name="table_phone" pattern="[0-9]{9}" title="Numer telefonu powinien mieć 9 cyfr" required/>

            <label for="table_guests">Ilość osób (max. 6)</label>
            <input type="number" id="table_guests" name="table_guests" min="1" max="6" required />

            <label for="table_reservation_date">Data rezerwacji</label>
            <input type="date" id="table_reservation_date" name="table_reservation_date" required />

            <label for="table_start_time">Godzina rozpoczęcia</label>
            <!-- Przyciski godzin będą generowane tutaj -->
            <div class="hours" id="table-hours-container">
            <?php 
              $times = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
              foreach ($times as $time): ?>
                  <button class="hour-button" type="button" value="<?php echo $time; ?>">
                      <?php echo $time; ?>
                  </button>
              <?php endforeach; ?>
            </div>

            <input type="hidden" id="table_start_time" name="table_start_time"  required />
            <button type="submit" class="menu-button">Zarezerwuj stolik</button>
              <div class="form-feedback" id="table-reservation-feedback"></div>
            <script>
              function setTimeForTable(time) {
                const input = document.getElementById('table_start_time');
                input.value = time; // Przypisz klikniętą godzinę do ukrytego pola `table_start_time`
              }
            
              const hourButtons = document.querySelectorAll('.hour-button');
            
              hourButtons.forEach(button => {
                button.addEventListener('click', () => {
                  // Usuń klasę "clicked" z każdego przycisku
                  hourButtons.forEach(btn => btn.classList.remove('clicked'));
                
                  // Dodaj klasę "clicked" tylko do klikniętego przycisku
                  button.classList.add('clicked');
                
                  // Ustaw wartość czasu
                  setTimeForTable(button.value);
                });
              });
            </script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modal");
    const modalMessage = document.getElementById("modal-message");
    const closeButton = document.querySelector(".close-button");

    // Sprawdź, czy istnieje wiadomość do wyświetlenia
    const message = <?php echo json_encode(isset($_SESSION['modal_message']) ? $_SESSION['modal_message'] : ''); ?>;
    if (message) {
      modalMessage.textContent = message;
      modal.style.display = "block";
      <?php unset($_SESSION['modal_message']); ?> // Usuń wiadomość po wyświetleniu
    }

    // Zamknij modal po kliknięciu w "X"
    closeButton.addEventListener("click", () => {
      modal.style.display = "none";
    });

    // Zamknij modal po kliknięciu poza nim
    window.addEventListener("click", (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
      }
    });
  });
</script>


          </form>
        </div>
      </div>
    </section> 
    
<section class="section-contact" id="contact-section">
  <div class="contact container">
    <div class="contact-box">
      <div class="left"></div>
      <div class="right">
        <h2 class="contact-header">Skontaktuj się</h2>
        <?php if (isset($message1)) : ?>
          <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message1); ?></p>
        <?php endif; ?>
        <form action="#contact-section" method="post">
          <input type="text" class="field" name="imie" placeholder="Imię" required>
          <input type="email" class="field" name="email" placeholder="E-mail" required>
          <input type="tel" class="field" name="telefon" placeholder="Telefon" pattern="[0-9]{9}" title="Numer telefonu powinien mieć 9 cyfr" required>
          <textarea name="tresc" placeholder="Treść wiadomości" class="field" required></textarea>
          <button type="submit" name="submit_contact" class="btn">Wyślij</button>
        </form>
      </div>
    </div>
  </div>
</section>




    </main>

    <footer class="footer">
      <div class="container grid--footer">
        <div class="logo-col">
          <a href="#">
            <img
              src="assets/frapza_logo.png"
              alt="frapza logo"
              class="logo logo-footer-fix"
          /></a>

          <ul class="social-links">
            <li>
              <a class="footer-link" href="#"
                ><ion-icon class="social-icon" name="logo-instagram"></ion-icon
              ></a>
            </li>
            <li>
              <a class="footer-link" href="#"
                ><ion-icon class="social-icon" name="logo-facebook"></ion-icon
              ></a>
            </li>
            <li>
              <a class="footer-link" href="#"
                ><ion-icon class="social-icon" name="logo-tiktok"></ion-icon
              ></a>
            </li>
          </ul>

          <p class="copyright">
            Copyright &copy; <span class="year">2027</span> by Frapza, Inc. All
            rights reserved
          </p>
        </div>

        <div class="address-col">
          <p class="footer-heading">Kontakt</p>
          <address class="contacts">
            <p class="address">Polska, Łódź, Kilińskiego 46 Street, 90-256</p>
            <p>
              <a class="footer-link" href="tel:123-123-123">+48 123-123-123</a
              ><br />
              <a class="footer-link" href="mailto:swiftdropsupport@gmail.com"
                >frapza@gmail.com</a
              >
            </p>
          </address>
        </div>
        <nav class="nav-col">
          <p class="footer-heading">Firma</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="#">O nas</a></li>
            <li><a class="footer-link" href="#">Praca</a></li>
            <li><a class="footer-link" href="#">Nasi partnerzy</a></li>
            <li><a class="footer-link" href="#">Kariery</a></li>
          </ul>
        </nav>
        <nav class="nav-col">
          <p class="footer-heading">Konto</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="#">Stwórz konto</a></li>
            <li><a class="footer-link" href="#">Zaloguj się</a></li>
            <li><a class="footer-link" href="#">iOS</a></li>
            <li><a class="footer-link" href="#">Android</a></li>
          </ul>
        </nav>

        <nav class="nav-col">
          <p class="footer-heading">Zasoby</p>
          <ul class="footer-nav">
            <li><a class="footer-link" href="#">Pomoc</a></li>
            <li><a class="footer-link" href="#">Prywatność i warunki</a></li>
          </ul>
        </nav>
      </div>
    </footer>
  </body>
</html>

