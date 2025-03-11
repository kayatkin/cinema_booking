document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const movies = document.querySelectorAll(".conf-step__movie");
    const halls = document.querySelectorAll(".conf-step__seances-hall");
    const saveButton = document.getElementById("save-seances-button");
    const cancelButton = document.getElementById("cancel-seances-button");

    const currentDateElement = document.getElementById("current-date"); // Элемент для отображения текущей даты
    let currentDate = new Date(); // Текущая дата

    const BASE_TIME_START = 10 * 60; // 10:00 = 600 минут
    const BASE_TIME_END = 22 * 60; // 22:00 = 1320 минут
    const minutesPerPixel = 1; // 1 минута = 1px
    let pendingSeances = []; // Временный массив сеансов

    // Форматирование даты
    function formatDate(date) {
        return `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, "0")}-${date.getDate().toString().padStart(2, "0")}`;
    }

    // Обновление отображаемой даты
    function updateCurrentDate(newDate) {
        currentDate = newDate;
        currentDateElement.textContent = formatDate(currentDate);
        loadSavedSeances(formatDate(currentDate)); // Загружаем сеансы для новой даты
    }

    // Переключение на предыдущий день
    document.getElementById("prev-day").addEventListener("click", function () {
        const newDate = new Date(currentDate);
        newDate.setDate(newDate.getDate() - 1);
        updateCurrentDate(newDate);
    });

    // Переключение на следующий день
    document.getElementById("next-day").addEventListener("click", function () {
        const newDate = new Date(currentDate);
        newDate.setDate(newDate.getDate() + 1);
        updateCurrentDate(newDate);
    });

    // Загрузка сохраненных сеансов из базы данных
    async function loadSavedSeances(selectedDate) {
        try {
            const response = await fetch(`/admin/seances/load?date=${selectedDate}`, {
                method: "GET",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            if (!response.ok) {
                console.error("Ошибка при загрузке сеансов.");
                return;
            }

            const seancesData = await response.json();
            renderSavedSeances(seancesData);
        } catch (error) {
            console.error("Ошибка при загрузке сеансов:", error);
        }
    }

    // Отображение сохраненных сеансов на таймлайне
    function renderSavedSeances(seancesData) {
        // Очищаем существующие сеансы на таймлайне
        halls.forEach((hall) => {
            const timeline = hall.querySelector(".conf-step__seances-timeline");
            if (timeline) {
                timeline.innerHTML = ""; // Очищаем содержимое таймлайна
            }
        });

        seancesData.forEach((seance) => {
            const hall = Array.from(halls).find(
                (hall) => hall.dataset.hallId === seance.hall_id.toString(),
            );

            if (!hall) return;

            const timeline = hall.querySelector(".conf-step__seances-timeline");
            if (!timeline) return;

            const startTimeMinutes = parseTimeToMinutes(seance.start_time);
            const duration = parseTimeToMinutes(seance.end_time) - startTimeMinutes;

            const xPosition = (startTimeMinutes - BASE_TIME_START) * minutesPerPixel;

            const savedSeance = document.createElement("div");
            savedSeance.classList.add("conf-step__seances-movie");
            savedSeance.style.left = `${xPosition}px`;
            savedSeance.style.width = `${duration * minutesPerPixel}px`;
            savedSeance.style.backgroundColor = "rgb(133, 255, 137)";
            savedSeance.dataset.seanceId = seance.id; // Устанавливаем ID сеанса

            // Заголовок фильма
            const titleElement = document.createElement("p");
            titleElement.classList.add("conf-step__seances-movie-title");
            titleElement.textContent = seance.movie_title;

            // Время начала
            const timeElement = document.createElement("p");
            timeElement.classList.add("conf-step__seances-movie-start");
            timeElement.textContent = seance.start_time;

            // Время окончания
            const endTimeElement = document.createElement("p");
            endTimeElement.classList.add("conf-step__seances-movie-end");
            endTimeElement.textContent = seance.end_time;

            // Кнопка "Удалить"
            const deleteButton = document.createElement("button");
            deleteButton.classList.add("conf-step__seances-delete");
            deleteButton.textContent = "Удалить";
            deleteButton.addEventListener("click", () => deleteSeance(savedSeance));

            // Добавляем элементы в блок сеанса
            savedSeance.appendChild(titleElement);
            savedSeance.appendChild(timeElement);
            savedSeance.appendChild(endTimeElement);
            savedSeance.appendChild(deleteButton);

            timeline.appendChild(savedSeance);
        });
    }

    // Преобразование времени в формате HH:MM в минуты
    function parseTimeToMinutes(timeStr) {
        const [hours, minutes] = timeStr.split(":").map(Number);
        return hours * 60 + minutes;
    }

    // Форматирование минут в формат HH:MM
    function formatTime(minutes) {
        const hours = Math.floor(minutes / 60);
        const minutesRemainder = minutes % 60;
        return `${String(hours).padStart(2, "0")}:${String(minutesRemainder).padStart(2, "0")}`;
    }

    // Добавление drag-and-drop функциональности
    movies.forEach((movie) => {
        movie.addEventListener("dragstart", function (event) {
            const duration = movie.dataset.movieDuration;
            if (!duration || isNaN(duration)) {
                console.error("Ошибка: Длительность фильма не указана или не является числом.");
                return;
            }

            event.dataTransfer.setData("movieId", movie.dataset.movieId);
            event.dataTransfer.setData("duration", duration);
            event.dataTransfer.setData("title", movie.dataset.movieTitle); // Добавляем название фильма
        });
    });

    // Обработка drag-and-drop для залов
    halls.forEach((hall) => {
        const timeline = hall.querySelector(".conf-step__seances-timeline");
        if (!timeline) return;

        timeline.addEventListener("dragover", function (event) {
            event.preventDefault();
        });

        timeline.addEventListener("drop", async function (event) {
            event.preventDefault();

            const movieId = event.dataTransfer.getData("movieId");
            const duration = parseInt(event.dataTransfer.getData("duration"), 10);
            const title = event.dataTransfer.getData("title"); // Получаем название фильма

            if (isNaN(duration)) return;

            const hallId = hall.dataset.hallId;
            const timelineRect = timeline.getBoundingClientRect();
            const xPosition = event.clientX - timelineRect.left;
            const relativeMinutes = Math.floor(xPosition / minutesPerPixel);

            let startTimeMinutes = BASE_TIME_START + relativeMinutes;

            if (
                startTimeMinutes < BASE_TIME_START ||
                startTimeMinutes + duration > BASE_TIME_END
            ) {
                alert("Сеанс выходит за пределы рабочего времени кинотеатра.");
                return;
            }

            const startTime = formatTime(startTimeMinutes);
            const endTime = formatTime(startTimeMinutes + duration);

            // Проверяем пересечение с уже существующими сеансами
            const existingSeances = Array.from(timeline.children).filter(
                (child) => child.classList.contains("conf-step__seances-movie"),
            );

            const newSeanceInterval = {
                start: startTimeMinutes,
                end: startTimeMinutes + duration,
            };

            const isOverlapping = existingSeances.some((seance) => {
                const seanceLeft = parseFloat(seance.style.left.replace("px", ""));
                const seanceWidth = parseFloat(seance.style.width.replace("px", ""));
                const seanceStartMinutes =
                    BASE_TIME_START + Math.floor(seanceLeft / minutesPerPixel);
                const seanceEndMinutes =
                    seanceStartMinutes + Math.floor(seanceWidth / minutesPerPixel);

                return (
                    newSeanceInterval.start < seanceEndMinutes &&
                    newSeanceInterval.end > seanceStartMinutes
                );
            });

            if (isOverlapping) {
                alert("Сеанс пересекается с другим сеансом в этом зале.");
                return;
            }

            // Создаем временный сеанс для отображения на таймлайне
            const newSeance = document.createElement("div");
            newSeance.classList.add("conf-step__seances-movie");
            newSeance.dataset.movieId = movieId;
            newSeance.style.left = `${xPosition}px`;
            newSeance.style.width = `${duration * minutesPerPixel}px`;
            newSeance.style.backgroundColor = "rgb(133, 255, 137)";

            // Добавляем заголовок фильма
            const titleElement = document.createElement("p");
            titleElement.classList.add("conf-step__seances-movie-title");
            titleElement.textContent = title;

            // Добавляем время начала сеанса
            const timeElement = document.createElement("p");
            timeElement.classList.add("conf-step__seances-movie-start");
            timeElement.textContent = startTime;

            // Добавляем время окончания сеанса
            const endTimeElement = document.createElement("p");
            endTimeElement.classList.add("conf-step__seances-movie-end");
            endTimeElement.textContent = endTime;

            // Кнопка "Удалить"
            const deleteButton = document.createElement("button");
            deleteButton.classList.add("conf-step__seances-delete");
            deleteButton.textContent = "Удалить";
            deleteButton.addEventListener("click", () => deletePendingSeance(newSeance));

            // Добавляем дочерние элементы в блок сеанса
            newSeance.appendChild(titleElement);
            newSeance.appendChild(timeElement);
            newSeance.appendChild(endTimeElement);
            newSeance.appendChild(deleteButton);

            timeline.appendChild(newSeance);

            // Добавляем сеанс во временный массив
            pendingSeances.push({
                hall_id: hallId,
                movie_id: movieId,
                start_time: `${formatDate(currentDate)} ${startTime}`,
                end_time: `${formatDate(currentDate)} ${endTime}`,
            });
        });
    });

    // Сохранение сеансов
    saveButton.addEventListener("click", async function () {
        if (pendingSeances.length === 0) {
            alert("Нет изменений для сохранения.");
            return;
        }
    
        try {
            const response = await fetch("/admin/seances/store", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    seances: pendingSeances,
                    date: formatDate(currentDate), // Передаем дату в теле запроса
                }),
            });
    
            if (!response.ok) {
                const errorData = await response.json();
                alert(`Ошибка: ${errorData.message || "Не удалось сохранить сеансы."}`);
                return;
            }
    
            alert("Сеансы успешно сохранены!");
            pendingSeances = []; // Очищаем временный массив
    
            // Перезагружаем таймлайн для отображения сохраненных сеансов
            loadSavedSeances(formatDate(currentDate));
        } catch (error) {
            console.error("Ошибка при сохранении:", error);
            alert("Ошибка при сохранении сеансов.");
        }
    });

    // Отмена изменений
    cancelButton.addEventListener("click", function () {
        if (
            confirm(
                "Отменить изменения? Все несохраненные сеансы будут удалены.",
            )
        ) {
            pendingSeances = []; // Очищаем массив изменений
            document
                .querySelectorAll(".conf-step__seances-movie")
                .forEach((seance) => seance.remove());
            loadSavedSeances(formatDate(currentDate));
        }
    });

    // Удаление существующего сеанса
    async function deleteSeance(seanceElement) {
        const seanceId = seanceElement.dataset.seanceId;
        if (!seanceId) return;

        if (!confirm("Вы уверены, что хотите удалить этот сеанс?")) {
            return;
        }

        try {
            const response = await fetch(`/admin/seances/${seanceId}`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            if (!response.ok) {
                const errorData = await response.json();
                alert(
                    `Ошибка: ${errorData.message || "Не удалось удалить сеанс."}`,
                );
                return;
            }

            alert("Сеанс успешно удален!");
            if (seanceElement && seanceElement.parentNode) {
                seanceElement.remove(); // Удаляем элемент
            }
        } catch (error) {
            console.error("Ошибка при удалении сеанса:", error);
            alert("Произошла ошибка при удалении сеанса.");
        }
    }

    // Удаление временного сеанса
    function deletePendingSeance(seanceElement) {
        const movieId = seanceElement.dataset.movieId;
        const startTime = seanceElement.querySelector(
            ".conf-step__seances-movie-start",
        ).textContent;

        // Удаляем из временного массива
        pendingSeances = pendingSeances.filter(
            (seance) =>
                seance.movie_id !== movieId || seance.start_time !== `${formatDate(currentDate)} ${startTime}`,
        );

        // Удаляем элемент с таймлайна
        seanceElement.remove();
    }

    // Загружаем сеансы для текущей даты при первой загрузке страницы
    loadSavedSeances(formatDate(currentDate));
});
