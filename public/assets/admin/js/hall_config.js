document.addEventListener("DOMContentLoaded", function () {
    const hallSelectors = document.querySelectorAll(
        'input[name="chairs-hall"]',
    );
    const hallConfigSection = document.getElementById("hall-config-section");
    const rowsInput = document.getElementById("rows-input");
    const seatsPerRowInput = document.getElementById("seats-per-row-input");
    const hallScheme = document.getElementById("hall-scheme");
    const saveButton = document.getElementById("save-hall-config-button");
    const cancelButton = document.getElementById("cancel-hall-config-button");

    // Обработка выбора зала
    hallSelectors.forEach((selector) => {
        selector.addEventListener("change", async function () {
            if (this.checked) {
                hallConfigSection.style.display = "block";
                const hallId = this.getAttribute("data-hall-id");
                console.log(`Выбран зал с ID: ${hallId}`);

                try {
                    // Загрузка текущей конфигурации зала
                    const response = await fetch(
                        `/admin/halls/${hallId}/configuration`,
                        {
                            method: "GET",
                            headers: {
                                "X-CSRF-TOKEN": document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute("content"),
                                "Content-Type": "application/json",
                            },
                        },
                    );

                    if (!response.ok) {
                        throw new Error(
                            "Ошибка при загрузке конфигурации зала.",
                        );
                    }

                    const data = await response.json();
                    console.log(data); // Логирование полученных данных

                    const rows = data.rows || 0;
                    const seatsPerRow = data.seats_per_row || 0;

                    // Проверяем, что данные корректны
                    if (rows > 0 && seatsPerRow > 0) {
                        rowsInput.value = rows;
                        seatsPerRowInput.value = seatsPerRow;
                        // Генерация схемы зала
                        generateHallScheme(
                            rows,
                            seatsPerRow,
                            data.configuration,
                        );
                    } else {
                        alert(
                            "Конфигурация зала некорректна. Проверьте данные.",
                        );
                    }
                } catch (error) {
                    console.error("Произошла ошибка:", error);
                    alert("Не удалось загрузить конфигурацию зала.");
                }
            }
        });
    });
    // Отмена конфигурации
    cancelButton.addEventListener("click", function () {
        hallConfigSection.style.display = "none";
    });
    // Генерация схемы зала
    function generateHallScheme(rows, seatsPerRow, configuration = []) {
        hallScheme.innerHTML = "";
        let seatIndex = 0;

        for (let i = 0; i < rows; i++) {
            const row = document.createElement("div");
            row.classList.add("conf-step__row");

            for (let j = 0; j < seatsPerRow; j++) {
                const chair = document.createElement("span");
                chair.classList.add("conf-step__chair");

                const seatData = configuration[seatIndex] || {
                    type: "standart",
                };
                chair.dataset.type = seatData.type;
                chair.classList.add(`conf-step__chair_${seatData.type}`);

                chair.addEventListener("click", function () {
                    const types = ["standart", "vip", "disabled"];
                    const currentIndex = types.indexOf(chair.dataset.type);
                    const nextType = types[(currentIndex + 1) % types.length];

                    chair.className = `conf-step__chair conf-step__chair_${nextType}`;
                    chair.dataset.type = nextType;
                });

                row.appendChild(chair);
                seatIndex++;
            }
            hallScheme.appendChild(row);
        }
    }
    // Обновление схемы при изменении количества рядов или мест
    function updateHallScheme() {
        const rows = parseInt(rowsInput.value, 10);
        const seatsPerRow = parseInt(seatsPerRowInput.value, 10);

        if (rows > 0 && seatsPerRow > 0) {
            generateHallScheme(rows, seatsPerRow);
        }
    }

    rowsInput.addEventListener("input", updateHallScheme);
    seatsPerRowInput.addEventListener("input", updateHallScheme);
    // Сохранение конфигурации зала
    saveButton.addEventListener("click", async function () {
        const rows = parseInt(rowsInput.value, 10);
        const seatsPerRow = parseInt(seatsPerRowInput.value, 10);

        // Проверка на корректность данных
        if (rows <= 0 || seatsPerRow <= 0) {
            alert("Пожалуйста, укажите корректное количество рядов и мест.");
            return;
        }

        // Формируем конфигурацию мест
        const configuration = Array.from(
            hallScheme.querySelectorAll(".conf-step__chair"),
        ).map((chair, index) => ({
            global_seat: index + 1,
            type: chair.dataset.type,
        }));

        // Проверяем, выбран ли зал
        const selectedHall = [...hallSelectors].find(
            (selector) => selector.checked,
        );
        if (!selectedHall) {
            alert("Пожалуйста, выберите зал для конфигурации.");
            return;
        }

        const hallId = selectedHall.getAttribute("data-hall-id");

        try {
            // Отправляем данные на сервер
            const response = await fetch(`/admin/halls/${hallId}/configure`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    rows: rows,
                    seats_per_row: seatsPerRow,
                    configuration: configuration,
                }),
            });

            // Проверяем статус ответа
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(
                    errorData.message || "Не удалось сохранить конфигурацию.",
                );
            }

            const data = await response.json();
            alert(data.success ? data.message : `Ошибка: ${data.message}`);

            // Обновляем интерфейс
            if (data.success) {
                // можно обновить схему зала
                generateHallScheme(rows, seatsPerRow, configuration);
            }
        } catch (error) {
            console.error("Произошла ошибка:", error);
            alert(
                "Произошла ошибка при сохранении конфигурации: " +
                    error.message,
            );
        }
    });
});
