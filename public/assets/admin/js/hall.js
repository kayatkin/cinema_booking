document.addEventListener("DOMContentLoaded", function () {
    // Обработка создания зала
    const createHallButton = document.getElementById("create-hall-button");
    const modal = document.getElementById("create-hall-modal");
    const overlay = document.getElementById("modal-overlay");
    const closeModalButton = document.getElementById("close-modal-button");
    const createHallForm = document.getElementById("create-hall-form");

    // Открыть модальное окно
    createHallButton?.addEventListener("click", function () {
        modal?.classList.remove("hidden");
        overlay?.classList.remove("hidden");
    });

    // Закрыть модальное окно
    function closeModals() {
        modal?.classList.add("hidden");
        overlay?.classList.add("hidden");
    }

    // Закрытие по кнопке "Отменить"
    closeModalButton?.addEventListener("click", closeModals);

    // Закрытие по клику на затемнённый фон
    overlay?.addEventListener("click", closeModals);

    // Закрытие по иконке закрытия (крестик)
    const dismissButton = document.querySelector("#create-hall-modal .popup__dismiss");
    dismissButton?.addEventListener("click", function (event) {
        event.preventDefault(); // Предотвращаем действие по умолчанию для ссылки
        closeModals();
    });

    // Закрытие по нажатию клавиши Escape
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && !modal.classList.contains("hidden")) {
            closeModals();
        }
    });

    // Отправка формы создания зала
    createHallForm?.addEventListener("submit", async function (event) {
        event.preventDefault();

        const hallName = document
            .querySelector('#create-hall-form input[name="name"]')
            .value.trim();

        if (!hallName) {
            alert("Пожалуйста, введите название зала.");
            return;
        }

        try {
            const response = await fetch("/admin/halls/store-simple", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ name: hallName }),
            });

            if (response.ok) {
                const data = await response.json();
                alert(`Зал "${data.hall.name}" успешно создан!`);
                location.reload(); // Обновляем страницу для отображения нового зала
            } else {
                const text = await response.text();
                try {
                    const errorData = JSON.parse(text);
                    alert(
                        `Ошибка: ${errorData.message || "Не удалось создать зал."}`,
                    );
                } catch {
                    alert(`Ошибка: ${text}`);
                }
            }
        } catch (error) {
            console.error("Произошла ошибка:", error);
            alert("Произошла ошибка при создании зала.");
        } finally {
            closeModals();
        }
    });

    // Обработка удаления зала
    document
        .querySelectorAll(".conf-step__button.conf-step__button-trash")
        .forEach((button) => {
            button.addEventListener("click", async function () {
                const hallId = this.getAttribute("data-hall-id");

                if (!confirm("Вы уверены, что хотите удалить этот зал?")) {
                    return;
                }

                try {
                    const response = await fetch(`/admin/halls/${hallId}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                            "Content-Type": "application/json",
                        },
                    });

                    if (response.ok) {
                        const data = await response.json();
                        alert(data.message);
                        location.reload(); // Обновляем страницу после успешного удаления
                    } else {
                        const text = await response.text();
                        try {
                            const errorData = JSON.parse(text);
                            alert(
                                `Ошибка: ${errorData.message || "Не удалось удалить зал."}`,
                            );
                        } catch {
                            alert(`Ошибка: ${text}`);
                        }
                    }
                } catch (error) {
                    console.error("Произошла ошибка:", error);
                    alert("Произошла ошибка при удалении зала.");
                }
            });
        });
});