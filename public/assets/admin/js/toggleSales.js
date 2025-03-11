document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("toggleSalesButton");
    const hallRadios = document.querySelectorAll('input[name="hall_id"]');

    // Функция для обновления текста кнопки
    function updateButtonText(isActive) {
        toggleButton.textContent = isActive ? 'Приостановить продажу билетов' : 'Открыть продажу билетов';
    }

    // Функция для активации первой радио-кнопки и загрузки её состояния
    function initializeFirstHall() {
        if (hallRadios.length > 0) {
            // Активируем первую радио-кнопку
            hallRadios[0].checked = true;

            // Загружаем состояние первого зала
            const isActive = hallRadios[0].dataset.isActive === "1"; // Преобразуем строку в boolean
            updateButtonText(isActive);
        }
    }

    // Обработчик выбора зала
    hallRadios.forEach(radio => {
        radio.addEventListener("change", function () {
            const isActive = this.dataset.isActive === "1"; // Преобразуем строку в boolean
            updateButtonText(isActive);
        });
    });

    // Обработчик клика по кнопке
    if (toggleButton) {
        toggleButton.addEventListener("click", function () {
            const selectedHall = document.querySelector('input[name="hall_id"]:checked');
            if (!selectedHall) {
                alert("Выберите зал для изменения статуса продаж.");
                return;
            }

            const hallId = selectedHall.value;

            fetch("/admin/toggle-sales", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ hall_id: hallId }),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Ошибка сети");
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        // Обновляем текст кнопки после успешного запроса
                        updateButtonText(data.is_active);

                        // Обновляем статус выбранного зала
                        selectedHall.dataset.isActive = data.is_active ? "1" : "0";

                        console.log(
                            data.is_active
                                ? `Продажа билетов открыта для зала ID ${hallId}`
                                : `Продажа билетов приостановлена для зала ID ${hallId}`
                        );
                    } else {
                        alert(data.message || "Ошибка при изменении статуса продаж");
                    }
                })
                .catch((error) => {
                    console.error("Ошибка:", error);
                    alert("Произошла ошибка при попытке изменить статус продаж");
                });
        });
    }

    // Инициализация текста кнопки при загрузке страницы
    initializeFirstHall();
});