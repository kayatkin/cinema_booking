console.log("Элементы DOM:", {
    addMovieButton: document.getElementById("add-movie-button"),
    addMovieModal: document.getElementById("add-movie-modal"),
    addMovieOverlay: document.getElementById("movie-modal-overlay"),
    closeAddMovieModalButton: document.getElementById("close-movie-modal-button"),
    addMovieForm: document.getElementById("add-movie-form"),
});
document.addEventListener("DOMContentLoaded", function () {
    // Элементы для добавления фильма
    const addMovieButton = document.getElementById("add-movie-button");
    const addMovieModal = document.getElementById("add-movie-modal");
    const addMovieOverlay = document.getElementById("movie-modal-overlay");
    const closeAddMovieModalButton = document.getElementById(
        "close-movie-modal-button",
    );
    const addMovieForm = document.getElementById("add-movie-form");

    // Проверяем существование элементов
    if (!addMovieButton || !addMovieModal || !addMovieOverlay || !closeAddMovieModalButton || !addMovieForm) {
        console.error("Отсутствующие элементы:", {
            addMovieButton,
            addMovieModal,
            addMovieOverlay,
            closeAddMovieModalButton,
            addMovieForm
        });
        return;
    }
    

    // Открытие модального окна добавления фильма
    addMovieButton.addEventListener("click", function () {
        addMovieModal.classList.remove("hidden");
        addMovieOverlay.classList.remove("hidden");
    });

    // Закрытие модального окна добавления фильма
    function closeAddMovieModals() {
        addMovieModal.classList.add("hidden");
        addMovieOverlay.classList.add("hidden");
    }
    closeAddMovieModalButton.addEventListener("click", closeAddMovieModals);
    addMovieOverlay.addEventListener("click", closeAddMovieModals);

    // Отправка формы создания фильма
    addMovieForm.addEventListener("submit", async function (event) {
        event.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch("/admin/movies/create", {
                method: "POST",
                body: formData,
            });

            if (response.ok) {
                const data = await response.json();
                alert(data.message);
                location.reload(); // Обновляем страницу после успешного создания
            } else {
                const errorData = await response.json();
                alert(
                    `Ошибка: ${errorData.message || "Не удалось создать фильм."}`,
                );
            }
        } catch (error) {
            console.error("Произошла ошибка:", error);
            alert("Произошла ошибка при создании фильма.");
        } finally {
            closeAddMovieModals();
        }
    });

    // Элементы для редактирования фильма
    const editMovieModal = document.getElementById("edit-movie-modal");
    const editMovieOverlay = document.getElementById(
        "edit-movie-modal-overlay",
    );
    const closeEditMovieModalButton = document.getElementById(
        "close-edit-movie-modal-button",
    );
    const editMovieForm = document.getElementById("edit-movie-form");

    // Проверяем существование элементов
    if (!addMovieButton || !addMovieModal || !addMovieOverlay || !closeAddMovieModalButton || !addMovieForm) {
        console.error("Отсутствующие элементы:", {
            addMovieButton,
            addMovieModal,
            addMovieOverlay,
            closeAddMovieModalButton,
            addMovieForm
        });
        return;
    }
    

    // Функция для открытия модального окна редактирования фильма
    function openEditModal(movieId) {
        console.log("Открытие модального окна для фильма с ID:", movieId); // Отладочное сообщение
    
        fetch(`/admin/movies/${movieId}/edit`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`Ошибка HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    const movie = data.movie;
                    console.log("Данные фильма:", movie); // Отладочное сообщение
    
                    // Заполняем форму данными фильма
                    document.getElementById("movie-id").value = movie.id;
                    document.getElementById("movie-title").value = movie.title;
                    document.getElementById("movie-duration").value = movie.duration;
                    document.getElementById("movie-synopsis").value = movie.synopsis;
                    document.getElementById("movie-origin").value = movie.origin;
    
                    // Заполняем новые поля датами проката
                    document.getElementById("movie-start-of-release").value =
                        movie.start_of_release || "";
                    document.getElementById("movie-end-of-release").value =
                        movie.end_of_release || "";
    
                    // Открываем модальное окно
                    editMovieModal.classList.remove("hidden");
                    editMovieOverlay.classList.remove("hidden");
                } else {
                    alert("Ошибка при загрузке данных фильма.");
                }
            })
            .catch((error) => {
                console.error("Ошибка:", error);
                alert("Произошла ошибка при загрузке данных фильма.");
            });
    }

    // Закрытие модального окна редактирования фильма
    function closeEditMovieModals() {
        editMovieModal.classList.add("hidden");
        editMovieOverlay.classList.add("hidden");
    }
    closeEditMovieModalButton.addEventListener("click", closeEditMovieModals);
    editMovieOverlay.addEventListener("click", closeEditMovieModals);

    // Делегирование событий для обработки кликов на фильмы
    document.addEventListener("click", function (event) {
        if (event.target && event.target.closest(".conf-step__movie")) {
            const movieElement = event.target.closest(".conf-step__movie");
            const movieId = movieElement.dataset.movieId;

            if (movieId) {
                openEditModal(movieId);
            } else {
                console.error("ID фильма не найден.");
            }
        }
    });

    // Отправка формы редактирования фильма
    editMovieForm.addEventListener("submit", async function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const movieId = formData.get("id");

        try {
            const response = await fetch(`/admin/movies/${movieId}/update`, {
                method: "POST",
                body: formData,
            });

            if (response.ok) {
                const data = await response.json();
                alert(data.message);
                location.reload(); // Обновляем страницу после успешного обновления
            } else {
                const errorData = await response.json();
                alert(
                    `Ошибка: ${errorData.message || "Не удалось обновить фильм."}`,
                );
            }
        } catch (error) {
            console.error("Произошла ошибка:", error);
            alert("Произошла ошибка при обновлении фильма.");
        } finally {
            closeEditMovieModals();
        }
    });

    // Логика удаления фильма
    document.addEventListener("click", async function (event) {
        if (event.target && event.target.id === "delete-movie-button") {
            const movieId = document.getElementById("movie-id").value;

            if (!movieId) {
                alert("Ошибка: не найден ID фильма.");
                return;
            }

            if (!confirm("Вы уверены, что хотите удалить этот фильм?")) return;

            try {
                const response = await fetch(`/admin/movies/${movieId}/delete`, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        "Content-Type": "application/json",
                    },
                });

                const data = await response.json();

                if (response.ok) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(`Ошибка: ${data.message}`);
                }
            } catch (error) {
                console.error("Ошибка при удалении фильма:", error);
                alert("Не удалось удалить фильм.");
            }
        }
    });
});
