document.addEventListener("DOMContentLoaded", async function () {
    const hallSelectors = document.querySelectorAll('input[name="chairs-hall"]');
    const hallConfigSection = document.getElementById("hall-config-section");
    const rowsInput = document.getElementById("rows-input");
    const seatsPerRowInput = document.getElementById("seats-per-row-input");
    const hallScheme = document.getElementById("hall-scheme");
    const saveButton = document.getElementById("save-hall-config-button");
    const cancelButton = document.getElementById("cancel-hall-config-button");

    // Прогресс-бар
    const progressBarContainer = document.getElementById("progress-bar-container");
    const progressBar = document.getElementById("progress-bar");

    // Переменная для хранения предыдущей конфигурации
    let previousConfiguration = null;

    // Функция для обновления прогресс-бара
    function updateProgress(percent) {
        progressBar.style.width = percent + "%";
    }

    // Вспомогательная функция для задержки
    function sleep(ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
    }

    // Функция для сохранения текущей конфигурации
    function saveCurrentConfiguration() {
        const rows = parseInt(rowsInput.value, 10);
        const seatsPerRow = parseInt(seatsPerRowInput.value, 10);

        // Проверяем, что данные корректны
        if (isNaN(rows) || isNaN(seatsPerRow) || rows <= 0 || seatsPerRow <= 0) {
            console.error("Некорректные данные для сохранения конфигурации.");
            return;
        }

        // Проверяем, что схема зала существует
        const chairs = Array.from(hallScheme.querySelectorAll(".conf-step__chair"));
        if (chairs.length === 0) {
            console.error("Схема зала пустая. Невозможно сохранить конфигурацию.");
            return;
        }

        previousConfiguration = {
            rows: rows,
            seatsPerRow: seatsPerRow,
            configuration: chairs.map((chair, index) => ({
                global_seat: index + 1,
                type: chair.dataset.type || "standart", // Устанавливаем значение по умолчанию
            })),
        };

        console.log("Текущая конфигурация сохранена:", previousConfiguration);
    }

    // Функция для восстановления предыдущей конфигурации
    function restorePreviousConfiguration() {
        // Проверяем, что previousConfiguration существует и содержит корректные данные
        if (!previousConfiguration || isNaN(previousConfiguration.rows) || isNaN(previousConfiguration.seatsPerRow)) {
            alert("Невозможно восстановить предыдущую конфигурацию. Данные отсутствуют или повреждены.");
            return;
        }

        // Восстанавливаем количество рядов и мест в ряду
        rowsInput.value = previousConfiguration.rows;
        seatsPerRowInput.value = previousConfiguration.seatsPerRow;

        // Генерируем схему зала
        generateHallScheme(
            previousConfiguration.rows,
            previousConfiguration.seatsPerRow,
            previousConfiguration.configuration
        );
    }

    // Функция для загрузки конфигурации с сервера
    async function loadConfigurationFromServer(hallId) {
        if (!hallId) return;

        try {
            const response = await fetch(`/admin/halls/${hallId}/configuration`, {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json",
                },
            });

            if (!response.ok) {
                throw new Error("Ошибка при загрузке конфигурации.");
            }

            const data = await response.json();

            if (data.success && data.rows > 0 && data.seats_per_row > 0) {
                rowsInput.value = data.rows;
                seatsPerRowInput.value = data.seats_per_row;
                generateHallScheme(data.rows, data.seats_per_row, data.configuration);
                saveCurrentConfiguration(); // Сохраняем загруженную конфигурацию
            } else {
                alert("Конфигурация зала некорректна. Проверьте данные.");
            }
        } catch (error) {
            console.error("Ошибка:", error);
            alert("Не удалось загрузить конфигурацию зала.");
        }
    }

    // Устанавливаем первую радио-кнопку активной по умолчанию и загружаем конфигурацию
    if (hallSelectors.length > 0) {
        hallSelectors[0].checked = true;
        await loadConfigurationFromServer(hallSelectors[0].getAttribute("data-hall-id"));
    
    // Показываем секцию схемы зала
    hallConfigSection.style.display = "block";
    }

    // Обработка выбора зала
    hallSelectors.forEach((selector) => {
        selector.addEventListener("change", async function () {
            if (this.checked) {
                // Сохраняем текущую конфигурацию перед загрузкой новой
                saveCurrentConfiguration();

                hallConfigSection.style.display = "block";
                await loadConfigurationFromServer(this.getAttribute("data-hall-id"));
            }
        });
    });

    // Отмена конфигурации
    cancelButton.addEventListener("click", function () {
        restorePreviousConfiguration();
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

                const seatData = configuration.find((seat) => seat.global_seat === seatIndex + 1) || { type: "standart" };
                chair.dataset.type = seatData.type || "standart"; // Устанавливаем значение по умолчанию
                chair.classList.add(`conf-step__chair_${chair.dataset.type}`);

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
        const configuration = Array.from(hallScheme.querySelectorAll(".conf-step__chair")).map(
            (chair, index) => ({
                global_seat: index + 1,
                type: chair.dataset.type || "standart", // Устанавливаем значение по умолчанию
            })
        );

        // Проверяем, выбран ли зал
        const selectedHall = [...hallSelectors].find((selector) => selector.checked);
        if (!selectedHall) {
            alert("Пожалуйста, выберите зал для конфигурации.");
            return;
        }

        const hallId = selectedHall.getAttribute("data-hall-id");

        try {
            // Показываем прогресс-бар
            progressBarContainer.style.display = "block";

            // Подготовка данных
            for (let i = 0; i <= 25; i++) {
                updateProgress(i);
                await sleep(10); // Плавное заполнение до 25%
            }
            console.log("Подготовка данных...");

            // Отправка данных на сервер
            for (let i = 26; i <= 75; i++) {
                updateProgress(i);
                await sleep(10); // Плавное заполнение до 75%
            }
            console.log("Отправка данных на сервер...");
            const response = await fetch(`/admin/halls/${hallId}/configure`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    rows: rows,
                    seats_per_row: seatsPerRow,
                    configuration: configuration,
                }),
            });

            // Обработка ответа
            for (let i = 76; i <= 100; i++) {
                updateProgress(i);
                await sleep(10); // Плавное заполнение до 100%
            }
            console.log("Обработка ответа...");
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || "Не удалось сохранить конфигурацию.");
            }

            const data = await response.json();
            alert(data.success ? "Конфигурация сохранена." : `Ошибка: ${data.message}`);
            if (data.success) {
                saveCurrentConfiguration();
                generateHallScheme(rows, seatsPerRow, configuration);
            }
        } catch (error) {
            console.error("Произошла ошибка:", error);
            alert("Произошла ошибка при сохранении конфигурации: " + error.message);
        } finally {
            // Скрываем прогресс-бар через 1 секунду
            setTimeout(() => {
                progressBarContainer.style.display = "none";
                updateProgress(0);
            }, 1000);
        }
    });
});