document.addEventListener("DOMContentLoaded", function () {
    const hallSelectors = document.querySelectorAll('input[name="prices-hall"]');
    const pricingConfigSection = document.getElementById("pricing-config-section");
    const standartPriceInput = document.getElementById("standart-price-input");
    const vipPriceInput = document.getElementById("vip-price-input");
    const saveButton = document.getElementById("save-pricing-button");
    const cancelButton = document.getElementById("cancel-pricing-button");

    // Переменная для хранения предыдущей конфигурации цен
    let previousPricing = null;

    // Функция для загрузки цен для выбранного зала
    function loadHallPricing(hallId) {
        fetch(`/admin/halls/${hallId}/pricing`, {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const pricing = data.pricing;

                    // Устанавливаем значения цен
                    standartPriceInput.value = pricing.standart_price || 0;
                    vipPriceInput.value = pricing.vip_price || 0;

                    // Сохраняем текущие цены как предыдущие
                    saveCurrentPricing();

                    // Показываем секцию конфигурации цен
                    pricingConfigSection.style.display = "block";
                } else {
                    console.error("Ошибка при загрузке цен:", data.message);
                    alert("Не удалось загрузить цены для зала.");
                }
            })
            .catch((error) => {
                console.error("Произошла ошибка:", error);
                alert("Произошла ошибка при загрузке цен.");
            });
    }

    // Функция для сохранения текущих цен
    function saveCurrentPricing() {
        const standartPrice = parseFloat(standartPriceInput.value) || 0;
        const vipPrice = parseFloat(vipPriceInput.value) || 0;

        // Проверяем, что данные корректны
        if (isNaN(standartPrice) || isNaN(vipPrice)) {
            console.error("Некорректные данные для сохранения цен.");
            return;
        }

        previousPricing = {
            standart_price: standartPrice,
            vip_price: vipPrice,
        };

        console.log("Текущие цены сохранены:", previousPricing);
    }

    // Функция для восстановления предыдущих цен
    function restorePreviousPricing() {
        // Проверяем, что previousPricing существует и содержит корректные данные
        if (!previousPricing || isNaN(previousPricing.standart_price) || isNaN(previousPricing.vip_price)) {
            alert("Невозможно восстановить предыдущие цены. Данные отсутствуют или повреждены.");
            return;
        }

        // Восстанавливаем значения цен
        standartPriceInput.value = previousPricing.standart_price;
        vipPriceInput.value = previousPricing.vip_price;

        console.log("Предыдущие цены восстановлены:", previousPricing);
    }

    // Активация первой радио-кнопки и загрузка данных для первого зала
    if (hallSelectors.length > 0) {
        hallSelectors[0].checked = true; // Активируем первую радио-кнопку
        const firstHallId = hallSelectors[0].getAttribute("data-hall-id");
        loadHallPricing(firstHallId); // Загружаем цены для первого зала
    }

    // Обработчик выбора зала
    hallSelectors.forEach((selector) => {
        selector.addEventListener("change", function () {
            if (this.checked) {
                const hallId = this.getAttribute("data-hall-id");
                loadHallPricing(hallId); // Загружаем цены для выбранного зала
            } else {
                pricingConfigSection.style.display = "none"; // Скрываем секцию, если зал не выбран
            }
        });
    });

    // Восстановление предыдущих цен при нажатии "Отмена"
    cancelButton.addEventListener("click", function () {
        restorePreviousPricing();
    });

    // Сохранение цен
    saveButton.addEventListener("click", function () {
        const standartPrice = parseFloat(standartPriceInput.value) || 0;
        const vipPrice = parseFloat(vipPriceInput.value) || 0;

        // Проверка на корректность ввода
        if (isNaN(standartPrice) || isNaN(vipPrice)) {
            alert("Пожалуйста, введите корректные цены.");
            return;
        }

        // Проверяем, выбран ли зал
        const selectedHall = Array.from(hallSelectors).find((selector) => selector.checked);
        if (!selectedHall) {
            alert("Пожалуйста, выберите зал для конфигурации.");
            return;
        }

        const hallId = selectedHall.getAttribute("data-hall-id");

        fetch(`/admin/halls/${hallId}/pricing`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                standart_price: standartPrice,
                vip_price: vipPrice,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert(data.message);

                    // Сохраняем текущие цены после успешного сохранения
                    saveCurrentPricing();
                } else {
                    alert(`Ошибка: ${data.message}`);
                }
            })
            .catch((error) => {
                console.error("Произошла ошибка:", error);
                alert("Произошла ошибка при сохранении цен.");
            });
    });
});