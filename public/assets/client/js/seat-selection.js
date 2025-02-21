let selectedSeats = [];

function toggleSeatSelection(element) {
    if (
        element.classList.contains("buying-scheme__chair_taken") ||
        element.classList.contains("buying-scheme__chair_disabled")
    ) {
        console.warn(
            "Cannot select this seat - it is either taken or disabled.",
        );
        return;
    }

    const seatNumber = element.getAttribute("data-seat");

    if (selectedSeats.includes(seatNumber)) {
        selectedSeats = selectedSeats.filter((seat) => seat !== seatNumber);
        element.classList.remove("buying-scheme__chair_selected");
    } else {
        selectedSeats.push(seatNumber);
        element.classList.add("buying-scheme__chair_selected");
    }

    document.getElementById("selected_seats").value =
        JSON.stringify(selectedSeats);

    const bookButton = document.querySelector(".acceptin-button");
    bookButton.disabled = selectedSeats.length === 0;

    console.log("Updated selected seats:", selectedSeats);
}

document.addEventListener("DOMContentLoaded", () => {
    const schemeWrapper = document.querySelector(".buying-scheme__wrapper");
    if (!schemeWrapper) {
        console.error("Buying scheme wrapper not found!");
        return;
    }

    schemeWrapper.addEventListener("click", (event) => {
        const target = event.target;
        if (!target.classList.contains("buying-scheme__chair")) {
            console.log("Clicked outside of a seat.");
            return;
        }
        toggleSeatSelection(target);
    });

    console.log("Seat selection initialized successfully.");
});

document.querySelector("form").addEventListener("submit", (event) => {
    console.groupCollapsed("Form submission");
    const selectedSeatsInput = document.getElementById("selected_seats");
    if (!selectedSeatsInput.value) {
        event.preventDefault();
        alert("Пожалуйста, выберите хотя бы одно место.");
        console.warn("Form submission prevented - no seats selected.");
        console.groupEnd();
        return;
    }

    try {
        const parsedSeats = JSON.parse(selectedSeatsInput.value);
        if (!Array.isArray(parsedSeats) || parsedSeats.length === 0) {
            event.preventDefault();
            alert("Выбранные места должны быть массивом.");
            console.error(
                "Selected seats is not a valid array:",
                selectedSeatsInput.value,
            );
            console.groupEnd();
            return;
        }
    } catch (e) {
        event.preventDefault();
        alert("Произошла ошибка при обработке выбранных мест.");
        console.error("Error parsing selected seats:", e.message);
        console.groupEnd();
        return;
    }

    console.log(
        "Form submitted with selected seats:",
        selectedSeatsInput.value,
    );
    console.groupEnd();
});
