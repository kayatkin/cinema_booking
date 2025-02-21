document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("toggleSalesButton");

    if (toggleButton) {
        toggleButton.addEventListener("click", function () {
            fetch("/admin/toggle-sales", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({}),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Ошибка сети");
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        this.textContent = data.button_text;

                        if (data.is_active) {
                            console.log("Продажа билетов открыта"); // Вывод в консоль при успешном открытии продаж
                        } else {
                            console.log("Продажа билетов приостановлена"); // Вывод при остановке продаж
                        }
                    } else {
                        alert(
                            data.message ||
                                "Ошибка при изменении статуса продаж",
                        );
                    }
                })
                .catch((error) => {
                    console.error("Ошибка:", error);
                    alert(
                        "Произошла ошибка при попытке изменить статус продаж",
                    );
                });
        });
    }
});
