<?php
require_once 'database/db.php';
session_start();

//Wylogowywanie
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout']) && $_POST['logout'] === "true") {
    // Zakończenie sesji
    session_start();
    session_destroy();
    
    // Przekierowanie na stronę logowania
    header("Location: logowanie.php");
    exit();
}

// Sprawdzenie, czy użytkownik jest zalogowany i ma rolę admina
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pracownik') {
    // Jeśli użytkownik nie jest administratorem, przekierowanie na stronę główną
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frapza- Panel pracownika</title>
    <link rel="icon" href="assets/frapza_logo.png" type="image/png" />
    <link rel="stylesheet" href="css/panelPracownika.css?v=<?php echo filemtime('css/panelPracownika.css'); ?>" />
</head>
<body>  

<div class="sidebar">
<img src="assets/frapza_logo.png" alt="swiftdrop logo" class="logo" />
    <button class="button-86" onclick="showSection('reservations')">Rezerwacje sal</button>
    <button class="button-86" onclick="showSection('reservations-table')">Rezerwacje stolików</button>
    <button class="button-86" onclick="showSection('contactpracownik')">Kontakt</button>
    <form action="" method="POST">
        <button type="submit" name="logout" value="true" class="button-86">Wyloguj się</button>
    </form>
</div>


<div id="reservations" class="content active">
<h2>Rezerwacje sal użytkowników</h2>
    <div class="operation-box">
        <input type="date" id="reservation-date" onchange="fetchReservations(this.value)">
    </div>
    <table class="fl-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Osoba</th>
                <th>Telefon</th>
                <th>Liczba Osób</th>
                <th>Godzina</th>
                <th>Data</th>
                <th>Przystawka</th>
                <th>Danie Główne</th>
                <th>Ciasto</th>
                <th>Status</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody id="reservation-table-body">
            <!-- Dane będą ładowane dynamicznie -->
        </tbody>
    </table>
</div>

<script>
  let selectedDate = ''; 

// Pobierz rezerwacje dla wybranej daty - sekcja sal
function fetchReservations(date) {
  selectedDate = date;
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "backend/fetch_reservations.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function () {
      if (xhr.status === 200) {
          document.getElementById("reservation-table-body").innerHTML = xhr.responseText;
      }
  };
  xhr.send("date=" + date);
}

// Obsługa kliknięć w przyciski Potwierdź/Anuluj - sekcja sal
document.getElementById('reservations').addEventListener('click', function (event) {
  if (event.target.classList.contains('confirm') || event.target.classList.contains('cancel')) {
      const reservationId = event.target.dataset.id;
      const action = event.target.classList.contains('confirm') ? 'confirm' : 'delete';

      if (action === 'delete' && !confirm('Czy na pewno chcesz usunąć tę rezerwację?')) return;
      if (action === 'confirm' && !confirm('Czy na pewno chcesz potwierdzić tę rezerwację?')) return;

      fetch('backend/fetch_reservations.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=${action}&id=${reservationId}`
      })
      .then(response => response.json())
      .then(data => {
          console.log('Odpowiedź z serwera:', data);
          if (data.success) {
              alert(data.message);
              if (selectedDate) {
                  fetchReservations(selectedDate);  // Odświeżenie rezerwacji po akcji
              }
          } else {
              alert('Błąd: ' + data.message);
          }
      })
      .catch(error => console.error('Błąd:', error));
  }
});
</script>

<div id="reservations-table" class="content">
<h2>Rezerwacje stolików użytkowników</h2>
    <div class="operation-box">
        <input type="date" id="reservation-date" onchange="fetchReservationsTable(this.value)">
    </div>
    <table class="fl-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Osoba</th>
                <th>Telefon</th>    
                <th>Liczba Osób</th>
                <th>Godzina</th>
                <th>Data</th>
                <th>Status</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody id="reservation-table-body-table">
            <!-- Dane będą ładowane dynamicznie -->
        </tbody>
    </table>
</div>

<script>

function fetchReservationsTable(date) {
    if (!date) {
        alert("Wybierz datę.");
        return;
    }
    selectedDate = date;
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "backend/fetch_reservations_table.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                const reservations = JSON.parse(xhr.responseText);
                console.log(reservations); // Debugowanie odpowiedzi

                if (reservations.length > 0) {
                    let rows = '';
                    reservations.forEach(reservation => {
                        rows += `<tr>
                                    <td>${reservation.RezerwacjaId}</td>
                                    <td>${reservation.ImieNazwisko}</td>
                                    <td>${reservation.NumerTelefonu}</td>
                                    <td>${reservation.IloscOsob ? reservation.IloscOsob : 'Brak danych'}</td>
                                    <td>${reservation.GodzinaRozpoczecia}</td>
                                    <td>${reservation.DataRezerwacji}</td>
                                    <td>${reservation.Status ? 'Potwierdzona' : 'Oczekująca'}</td>
                                    <td>
                                        <button class='confirm' data-id='${reservation.RezerwacjaId}'>Potwierdź</button>
                                        <button class='cancel' data-id='${reservation.RezerwacjaId}'>Anuluj</button>
                                    </td>
                                </tr>`;
                    });
                    document.getElementById("reservation-table-body-table").innerHTML = rows;
                } else {
                    document.getElementById("reservation-table-body-table").innerHTML = "<tr><td colspan='8'>Brak rezerwacji na wybraną datę.</td></tr>";
                }
            } catch (e) {
                console.error("Błąd parsowania odpowiedzi: ", e);
            }
        } else {
            console.error('Błąd: ' + xhr.status);
        }
    };
    xhr.send("date=" + date);
  }

  // Obsługa kliknięć w przyciski Potwierdź/Anuluj - sekcja stolików
  document.getElementById('reservations-table').addEventListener('click', function (event) {
    if (event.target.closest('#reservation-table-body-table') && (event.target.classList.contains('confirm') || event.target.classList.contains('cancel'))) {
        const reservationId = event.target.dataset.id;
        const action = event.target.classList.contains('confirm') ? 'confirm' : 'delete';

        if (action === 'delete' && !confirm('Czy na pewno chcesz usunąć tę rezerwację?')) return;
        if (action === 'confirm' && !confirm('Czy na pewno chcesz potwierdzić tę rezerwację?')) return;

        fetch('backend/fetch_reservations_table.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${action}&id=${reservationId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log('Odpowiedź z serwera:', data);
            if (data.success) {
                alert(data.message);
                if (selectedDate) {
                    fetchReservationsTable(selectedDate);  // Odświeżenie rezerwacji po akcji
                }
            } else {
                alert('Błąd: ' + data.message);
            }
        })
        .catch(error => console.error('Błąd:', error));
    }
  });



</script>

<div id="contactpracownik" class="content">
<h2>Kontakt z użytkownikami</h2>
    <div class="operation-box">
        <!-- Modal do odpowiadania na wiadomości -->
        <div id="reply-modal" class="reply-modal" style="display: none;">
            <div class="modal-content">
                <h3>Odpowiedz na wiadomość</h3>
                <p class="response-message" id="message-preview" style="font-style: italic;">Odpowiadasz na wiadomość: </p>
                <textarea id="reply-text" placeholder="Wpisz swoją odpowiedź..."></textarea>
                <div>
                <button class="button-24 edytuj height-smf-down" id="send-reply">Wyślij</button>
                <button class="button-24 height-smf-down" id="close-modal">Zamknij</button>
                </div>
            </div>
        </div>
    </div>
    <table class="fl-table">
        <thead>
            <tr>
                <th>Imię</th>
                <th>Email</th>
                <th>Numer Telefonu</th>
                <th>Treść</th>
                <th>Data</th>
                <th>Przeczytana</th>
                <th>Odpowiedz</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody id="messages-table">
        </tbody>
    </table>
</div>

<script>
    // Funkcja do pobierania wiadomości z serwera
    function loadMessages() {
        fetch('backend/fetch_messages.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Błąd podczas pobierania wiadomości');
                }
                return response.json();
            })
            .then(messages => {
                renderMessages(messages);
            })
            .catch(error => {
                console.error('Wystąpił problem z ładowaniem wiadomości:', error);
            });
    }

    // Funkcja escapowania danych (przed atakami XSS)
    function escapeHTML(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderMessages(messages) {
        const tableBody = document.getElementById('messages-table');
        tableBody.innerHTML = ''; // Czyszczenie tabeli przed dodaniem nowych danych

        messages.forEach(message => {
            const row = document.createElement('tr');

            // Truncowanie treści wiadomości, jeśli jest zbyt długa
            const truncatedText = message.Tresc.length > 50
                ? `${escapeHTML(message.Tresc.substring(0, 50))}... <a href="#" class="show-more" data-full-text="${escapeHTML(message.Tresc)}">Pokaż więcej</a>`
                : escapeHTML(message.Tresc);

            // Truncowanie odpowiedzi, jeśli jest zbyt długa
            const truncatedReply = message.Odpowiedz && message.Odpowiedz.length > 50
                ? `${escapeHTML(message.Odpowiedz.substring(0, 50))}... <a href="#" class="show-more-reply" data-full-reply="${escapeHTML(message.Odpowiedz)}">Pokaż więcej</a>`
                : escapeHTML(message.Odpowiedz || 'Brak odpowiedzi');

            row.innerHTML = `
                <td>${escapeHTML(message.Imie || 'Nieznane')}</td>
                <td>${escapeHTML(message.Email || 'Brak danych')}</td>
                <td>${escapeHTML(message.NumerTelefonu || '-')}</td>
                <td>${truncatedText}</td>
                <td>${new Date(message.DataCzas).toLocaleString('pl-PL')}</td>
                <td>${Number(message.Przeczytana) === 1 ? 'Tak' : 'Nie'}</td>
                <td>${truncatedReply}</td>
                <td>
                    <div class="center-btns">
                    <button class="edit-menu button-24 mark-read-button" data-id="${message.WiadomoscId}">
                        ${Number(message.Przeczytana) === 1 ? 'Oznacz jako nieprzeczytane' : 'Oznacz jako przeczytane'}
                    </button>
                    <button class="edytuj button-24 reply-button" data-id="${message.WiadomoscId}" data-preview="${escapeHTML(message.Tresc.substring(0, 50))}">Odpowiedz</button>
                    </div>
                    </td>
            `;

            tableBody.appendChild(row);
        });

        // Obsługa przycisków "Pokaż więcej"
        document.querySelectorAll('.show-more').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                const fullText = event.target.getAttribute('data-full-text');
                alert(fullText); // Możesz wyświetlić tekst w modalu
            });
        });

        document.querySelectorAll('.show-more-reply').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                const fullReply = event.target.getAttribute('data-full-reply');
                alert(fullReply); // Możesz wyświetlić tekst w modalu
            });
        });

        // Dodanie obsługi przycisków
        document.querySelectorAll('.reply-button').forEach(button => {
            button.addEventListener('click', event => {
                const messageId = event.target.getAttribute('data-id');
                const previewText = event.target.getAttribute('data-preview');
                openReplyDialog(messageId, previewText);
            });
        });

        document.querySelectorAll('.mark-read-button').forEach(button => {
            button.addEventListener('click', event => {
                const messageId = event.target.getAttribute('data-id');
                toggleReadStatus(messageId);
            });
        });
    }

    // Załadowanie wiadomości na starcie
    loadMessages();

    // Funkcja otwierająca modal do odpowiedzi
    function openReplyDialog(messageId, previewText) {
        const modal = document.getElementById('reply-modal');
        const replyText = document.getElementById('reply-text');
        const messagePreview = document.getElementById('message-preview');

        messagePreview.textContent = `Odpowiadasz na wiadomość: ${previewText}...`;
        replyText.value = ''; // Resetowanie pola tekstowego
        modal.style.display = 'block';

        // Obsługuje kliknięcie w 'Zamknij'
        document.getElementById('close-modal').addEventListener('click', () => {
            modal.style.display = 'none';
        }, { once: true });

        // Obsługuje wysyłanie odpowiedzi
        document.getElementById('send-reply').replaceWith(document.getElementById('send-reply').cloneNode(true));
        document.getElementById('send-reply').addEventListener('click', () => {
            const odpowiedz = replyText.value.trim();

            if (odpowiedz) {
                sendReply(messageId, odpowiedz);
                modal.style.display = 'none';  // Zamknij modal po wysłaniu
            } else {
                alert('Proszę wpisać odpowiedź.');
            }
        });
    }

    // Funkcja do wysyłania odpowiedzi do backendu
    function sendReply(messageId, odpowiedz) {
        fetch('backend/send_reply.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                WiadomoscId: messageId,
                Odpowiedz: odpowiedz
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages();  // Przeładuj wiadomości (odśwież tabelę)
            } else {
                alert('Wystąpił błąd podczas zapisywania odpowiedzi.');
            }
        })
        .catch(error => {
            console.error('Wystąpił problem z wysyłaniem odpowiedzi:', error);
        });
    }

    // Funkcja zmieniająca status przeczytania wiadomości
    function toggleReadStatus(messageId) {
        fetch('backend/toggle_read_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ WiadomoscId: messageId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages(); // Odśwież listę wiadomości
            } else {
                console.error('Nie udało się zmienić statusu wiadomości.');
            }
        })
        .catch(error => {
            console.error('Wystąpił problem z aktualizacją statusu wiadomości:', error);
        });
    }
</script>



<script>
    function showSection(sectionId) {
    const sections = document.querySelectorAll('.content');
    sections.forEach(section => section.classList.remove('active'));
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    } else {
        console.error(`Sekcja ${sectionId} nie istnieje!`);
    }
}



    // Możesz dodać funkcje dla guzików, np. zmiana statusu rezerwacji

document.querySelectorAll('.cancel').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const statusCell = row.querySelector('td:nth-child(10)');
        statusCell.textContent = 'Anulowano';
        statusCell.style.color = '#ff6666';
    });
});

document.querySelectorAll('.confirm').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        const statusCell = row.querySelector('td:nth-child(10)');
        statusCell.textContent = 'Zatwierdzono';
        statusCell.style.color = '#4CAF50';
    });
});

</script>

</body>
</html>