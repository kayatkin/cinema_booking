// Открытие модального окна для создания зала
document
    .getElementById("create-hall-button")
    .addEventListener("click", function (event) {
        event.preventDefault();
        document.getElementById("create-hall-modal").classList.add("active");
    });

// Закрытие модального окна создания зала
document
    .getElementById("close-create-hall-modal")
    .addEventListener("click", function () {
        document.getElementById("create-hall-modal").classList.remove("active");
    });

// Открытие модального окна для конфигурации зала
document.querySelectorAll(".admin-section a[data-hall-id]").forEach((link) => {
    link.addEventListener("click", function (event) {
        event.preventDefault();
        const hallId = this.getAttribute("data-hall-id");
        const hallName = this.textContent;

        // Устанавливаем название зала
        document.getElementById("hall-name").textContent = hallName;

        // Открываем модальное окно
        document.getElementById("configure-hall-modal").classList.add("active");

        // Генерируем схему зала
        generateHallScheme();
    });
});

// Закрытие модального окна конфигурации зала
document
    .getElementById("close-configure-hall-modal")
    .addEventListener("click", function () {
        document
            .getElementById("configure-hall-modal")
            .classList.remove("active");
    });

// Генерация схемы зала
function generateHallScheme() {
    const rowsInput = document.getElementById("hall-rows");
    const seatsPerRowInput = document.getElementById("hall-seats-per-row");
    const hallGrid = document.getElementById("hall-scheme");

    rowsInput.addEventListener("input", updateHallScheme);
    seatsPerRowInput.addEventListener("input", updateHallScheme);

    function updateHallScheme() {
        const rows = parseInt(rowsInput.value) || 10;
        const seatsPerRow = parseInt(seatsPerRowInput.value) || 8;

        if (rows <= 0 || seatsPerRow <= 0) {
            alert("Введите корректные значения для рядов и мест!");
            return;
        }

        hallGrid.innerHTML = ""; // Очистить предыдущую схему
        hallGrid.style.display = "block";

        let globalSeatNumber = 1; // Начальный номер места

        // Создание схемы зала
        for (let row = 1; row <= rows; row++) {
            const rowDiv = document.createElement("div");
            rowDiv.classList.add("conf-step__row");

            for (let seat = 1; seat <= seatsPerRow; seat++) {
                const seatDiv = document.createElement("div");
                seatDiv.classList.add(
                    "conf-step__chair",
                    "conf-step__chair_standart",
                ); // По умолчанию все места стандартные
                seatDiv.setAttribute("data-global-seat", globalSeatNumber); // Глобальный номер места
                seatDiv.setAttribute("data-row", row);
                seatDiv.setAttribute("data-seat", seat);
                seatDiv.addEventListener("click", toggleSeatType);

                rowDiv.appendChild(seatDiv);
                globalSeatNumber++; // Увеличиваем глобальный номер
            }

            hallGrid.appendChild(rowDiv);
        }
    }

    // Переключение типа места при клике
    function toggleSeatType() {
        const seatTypes = [
            "conf-step__chair_standart",
            "conf-step__chair_vip",
            "conf-step__chair_disabled",
        ];
        const currentTypeIndex = seatTypes.indexOf(this.classList[1]);

        // Удалить текущий тип
        this.classList.remove(...seatTypes);

        // Назначить следующий тип
        const nextTypeIndex = (currentTypeIndex + 1) % seatTypes.length;
        this.classList.add(seatTypes[nextTypeIndex]);
    }

    // Сохранение конфигурации перед отправкой
    document
        .getElementById("save-configuration-form")
        .addEventListener("submit", function (event) {
            const configuration = [];
            const seats = document.querySelectorAll(".conf-step__chair");

            seats.forEach((seat) => {
                const globalSeat = seat.getAttribute("data-global-seat");
                const type = Array.from(seat.classList).find((cls) =>
                    cls.includes("conf-step__chair_"),
                );

                if (type) {
                    configuration.push({
                        global_seat: globalSeat,
                        type: type.split("_")[2],
                    });
                }
            });

            document.getElementById("configuration-data").value =
                JSON.stringify(configuration);
        });
}

// Удаление сеанса
document.querySelectorAll(".trash-seance").forEach((button) => {
    button.addEventListener("click", function (event) {
        event.stopPropagation(); // Предотвращаем переход по ссылке
        const seanceId = this.getAttribute("data-seance-id");

        if (confirm("Вы уверены, что хотите удалить этот сеанс?")) {
            fetch(`/admin/seance/${seanceId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("Сеанс успешно удален!");
                        location.reload(); // Обновляем страницу
                    } else {
                        alert("Ошибка при удалении сеанса.");
                    }
                });
        }
    });
});
