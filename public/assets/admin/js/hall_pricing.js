document.addEventListener("DOMContentLoaded", function () {
    const hallSelectors = document.querySelectorAll(
        'input[name="prices-hall"]',
    );
    const pricingConfigSection = document.getElementById(
        "pricing-config-section",
    );
    const standartPriceInput = document.getElementById("standart-price-input");
    const vipPriceInput = document.getElementById("vip-price-input");
    const saveButton = document.getElementById("save-pricing-button");
    const cancelButton = document.getElementById("cancel-pricing-button");

    // Загрузка цен для выбранного зала
    function loadHallPricing(hallId) {
        fetch(`/admin/halls/${hallId}/pricing`, {
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                "Content-Type": "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const pricing = data.pricing;

                    standartPriceInput.value = pricing.standart_price || 0;
                    vipPriceInput.value = pricing.vip_price || 0;

                    pricingConfigSection.style.display = "block";
                } else {
                    console.error("Ошибка при загрузке цен:", data.message);
                }
            })
            .catch((error) => console.error("Произошла ошибка:", error));
    }

    // Обработчик выбора зала
    hallSelectors.forEach((selector) => {
        selector.addEventListener("change", function () {
            if (this.checked) {
                const hallId = this.getAttribute("data-hall-id");
                loadHallPricing(hallId);
            } else {
                pricingConfigSection.style.display = "none";
            }
        });
    });

    // Скрыть форму при нажатии "Отмена"
    cancelButton.addEventListener("click", function () {
        pricingConfigSection.style.display = "none";
    });

    // Сохранение цен
    saveButton.addEventListener("click", function () {
        const standartPrice = parseFloat(standartPriceInput.value) || 0;
        const vipPrice = parseFloat(vipPriceInput.value) || 0;

        if (isNaN(standartPrice) || isNaN(vipPrice)) {
            alert("Пожалуйста, введите корректные цены.");
            return;
        }

        const selectedHall = Array.from(hallSelectors).find(
            (selector) => selector.checked,
        );
        if (!selectedHall) {
            alert("Пожалуйста, выберите зал для конфигурации.");
            return;
        }

        const hallId = selectedHall.getAttribute("data-hall-id");

        fetch(`/admin/halls/${hallId}/pricing`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
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
