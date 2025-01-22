document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('reservation_date');
    const messageContainer = document.getElementById('reservation-message');
    const startTimeInput = document.getElementById('start_time');
    const hoursContainer = document.getElementById('hours-container'); // Rodzic przycisków

    // Pobieranie wszystkich przycisków w kontenerze
    const buttons = hoursContainer.querySelectorAll('button');

    // Blokowanie przeszłych dat
    const currentDate = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', currentDate);

    // Obsługa zmiany daty rezerwacji
    dateInput.addEventListener('change', function () {
        const selectedDate = this.value;

        fetch('/public_html/backend/check_reservation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `reservation_date=${encodeURIComponent(selectedDate)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP status ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Odpowiedź z backendu:', data);

            // Wyświetl komunikat
            messageContainer.textContent = data.message;
            messageContainer.style.color = data.isDayBlocked ? 'red' : 'green';

            if (data.isDayBlocked) {
                // Jeśli dzień jest całkowicie niedostępny, blokuj wszystkie godziny
                buttons.forEach(button => {
                    button.setAttribute('disabled', 'disabled');
                    button.style.backgroundColor = 'gray';
                });
                return;
            }

            // Obsługa godzin w bieżącym dniu (przeszłe godziny i godziny +1)
            const now = new Date();
            const currentTimeStr = `${now.getHours()}:${now.getMinutes().toString().padStart(2, '0')}`;

            buttons.forEach(button => {
                const buttonTime = button.value;

                // Jeśli wybrano dzisiejszą datę
                if (selectedDate === now.toISOString().split('T')[0]) {
                    // Zablokowanie godziny bieżącej i minionej
                    if (buttonTime <= currentTimeStr) {
                        button.setAttribute('disabled', 'disabled');
                        button.style.backgroundColor = 'gray';
                    } else {
                        button.removeAttribute('disabled');
                        button.style.backgroundColor = '';
                    }
                } else {
                    // W innych dniach nie blokujemy żadnych godzin
                    button.removeAttribute('disabled');
                    button.style.backgroundColor = '';
                }
            });
        })
        .catch(error => {
            console.error('Błąd:', error);
            messageContainer.textContent = 'Błąd podczas sprawdzania dostępności rezerwacji.';
            messageContainer.style.color = 'red';
        });
    });
});


// Rezerwacja Stoliki

document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('table_reservation_date');
    const messageContainer = document.getElementById('reservation-message2');
    const hoursContainer = document.getElementById('table-hours-container');
    const buttons = hoursContainer ? hoursContainer.querySelectorAll('button') : [];

    

    const currentDate = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', currentDate);

    dateInput.addEventListener('change', function () {
        const selectedDate = this.value;

        fetch('/public_html/backend/table_reservation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `table_reservation_date=${encodeURIComponent(selectedDate)}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP status ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Dane zwrócone przez backend:", data);

            messageContainer.textContent = data.message;
            messageContainer.style.color = data.isDayBlocked ? 'red' : 'green';

            if (data.isDayBlocked) {
                buttons.forEach(button => {
                    button.setAttribute('disabled', 'disabled');
                    button.style.backgroundColor = 'gray';
                });
                return;
            }

            const disabledHours = (data.disabledHours || []).map(hour => hour.slice(0, 5)); // Przycinamy sekundy
            console.log("Godziny wyłączone (disabledHours po modyfikacji):", disabledHours);

            buttons.forEach(button => {
                const buttonTime = button.value;
                console.log(`Sprawdzanie przycisku: godzina ${buttonTime}`);

                if (disabledHours.includes(buttonTime)) {
                    console.log(`Godzina ${buttonTime} jest wyłączona.`);
                    button.setAttribute('disabled', 'disabled');
                    button.style.backgroundColor = 'gray';
                } else {
                    console.log(`Godzina ${buttonTime} jest dostępna.`);
                    button.removeAttribute('disabled');
                    button.style.backgroundColor = '';
                }
            });
        })
        .catch(error => {
            console.error("Błąd podczas komunikacji z backendem:", error);
            messageContainer.textContent = 'Błąd podczas sprawdzania dostępności rezerwacji.';
            messageContainer.style.color = 'red';
        });
    });
});



//Menu



  
  document.getElementById("menu").addEventListener("change", function() {
    const customMenuSection = document.getElementById("custom_menu");
    if (this.value === "custom") {
        customMenuSection.style.display = "flex";
    } else {
        customMenuSection.style.display = "none";
    }
  });


// Pobieranie danych do tabeli w adminie na temat rezerwacji sali i stolików
