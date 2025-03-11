document.addEventListener("DOMContentLoaded", function () {
    // Открытие модального окна для создания зала
    document.getElementById("create-hall-button").addEventListener("click", function (event) {
        event.preventDefault();
        document.getElementById("create-hall-modal").classList.add("active");
    });

    // Закрытие модального окна создания зала
    document.getElementById("close-create-hall-modal").addEventListener("click", function () {
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

            // Генерация схемы зала
            // Вызываем функцию из hall_config.js
            generateHallScheme(); // Если эта функция используется, убедитесь, что она доступна глобально
        });
    });

    // Закрытие модального окна конфигурации зала
    document.getElementById("close-configure-hall-modal").addEventListener("click", function () {
        document.getElementById("configure-hall-modal").classList.remove("active");
    });

    // Удаление зала
    document.querySelectorAll(".conf-step__button-trash").forEach((button) => {
        button.addEventListener("click", function () {
            const hallId = this.getAttribute("data-hall-id");

            if (confirm("Вы уверены, что хотите удалить этот зал?")) {
                fetch(`/admin/halls/${hallId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            alert("Зал успешно удален!");
                            location.reload(); // Обновляем страницу
                        } else {
                            alert("Ошибка при удалении зала.");
                        }
                    });
            }
        });
    });

    // Удаление сеанса
    document.querySelectorAll(".trash-seance").forEach((button) => {
        button.addEventListener("click", function (event) {
            event.stopPropagation(); // Предотвращаем переход по ссылке
            const seanceId = this.getAttribute("data-seance-id");

            if (confirm("Вы уверены, что хотите удалить этот сеанс?")) {
                fetch(`/admin/seance/${seanceId}`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
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
});