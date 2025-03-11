document.addEventListener("DOMContentLoaded", function () {
    const navDays = document.querySelectorAll(".page-nav__day");
    const mainContent = document.getElementById("movies-container");
    let currentDateElement = document.querySelector(".page-nav__day_chosen");

    // Форматирование даты
    function formatDate(date) {
        return date.toISOString().split("T")[0]; // YYYY-MM-DD
    }

    // Загрузка фильмов и сеансов для выбранной даты
    async function loadMoviesByDate(selectedDate) {
        try {
            const response = await fetch(`/api/movies-by-date?date=${selectedDate}`);
            if (!response.ok) {
                throw new Error("Ошибка при загрузке данных.");
            }
            const moviesData = await response.json();
            renderMovies(moviesData);
        } catch (error) {
            console.error("Ошибка:", error);
            alert("Произошла ошибка при загрузке данных.");
        }
    }

    // Отрисовка фильмов и сеансов
    function renderMovies(moviesData) {
        mainContent.innerHTML = ""; // Очищаем текущее содержимое

        if (moviesData.length === 0) {
            mainContent.innerHTML = "<p class='movie' style='font-size: 20px; font-weight: bold; text-align: center;'>На эту дату сеансы не запланированы.</p>";
            return;
        }

        moviesData.forEach(movie => {
            const movieSection = document.createElement("section");
            movieSection.className = "movie";

            const movieInfoDiv = document.createElement("div");
            movieInfoDiv.className = "movie__info";

            const moviePosterDiv = document.createElement("div");
            moviePosterDiv.className = "movie__poster";
            const posterImg = document.createElement("img");
            posterImg.className = "movie__poster-image";
            posterImg.src = movie.poster_path;
            posterImg.alt = `${movie.title} постер`;
            moviePosterDiv.appendChild(posterImg);

            const movieDescriptionDiv = document.createElement("div");
            movieDescriptionDiv.className = "movie__description";

            const movieTitleH2 = document.createElement("h2");
            movieTitleH2.className = "movie__title";
            movieTitleH2.textContent = movie.title;

            const movieSynopsisP = document.createElement("p");
            movieSynopsisP.className = "movie__synopsis";
            movieSynopsisP.textContent = movie.synopsis;

            const movieDataP = document.createElement("p");
            movieDataP.className = "movie__data";

            const movieDurationSpan = document.createElement("span");
            movieDurationSpan.className = "movie__data-duration";
            movieDurationSpan.textContent = `${movie.duration} минут`;

            const movieOriginSpan = document.createElement("span");
            movieOriginSpan.className = "movie__data-origin";
            movieOriginSpan.textContent = movie.origin;

            movieDataP.appendChild(movieDurationSpan);
            movieDataP.appendChild(movieOriginSpan);

            movieDescriptionDiv.appendChild(movieTitleH2);
            movieDescriptionDiv.appendChild(movieSynopsisP);
            movieDescriptionDiv.appendChild(movieDataP);

            movieInfoDiv.appendChild(moviePosterDiv);
            movieInfoDiv.appendChild(movieDescriptionDiv);

            movieSection.appendChild(movieInfoDiv);

            // Группировка сеансов по залам
            const groupedSeances = {};
            movie.seances.forEach(seance => {
                const hallName = seance.hall_name;
                if (!groupedSeances[hallName]) {
                    groupedSeances[hallName] = [];
                }
                groupedSeances[hallName].push(seance);
            });

            for (const [hallName, hallSeances] of Object.entries(groupedSeances)) {
                const hallDiv = document.createElement("div");
                hallDiv.className = "movie-seances__hall";

                const hallTitleH3 = document.createElement("h3");
                hallTitleH3.className = "movie-seances__hall-title";
                hallTitleH3.textContent = hallName;

                const seancesListUl = document.createElement("ul");
                seancesListUl.className = "movie-seances__list";

                hallSeances.forEach(seance => {
                    const timeBlockLi = document.createElement("li");
                    timeBlockLi.className = "movie-seances__time-block";

                    const timeA = document.createElement("a");
                    timeA.className = "movie-seances__time";
                    timeA.href = `/select-seat/${seance.id}`;
                    timeA.textContent = seance.start_time;

                    timeBlockLi.appendChild(timeA);
                    seancesListUl.appendChild(timeBlockLi);
                });

                hallDiv.appendChild(hallTitleH3);
                hallDiv.appendChild(seancesListUl);
                movieSection.appendChild(hallDiv);
            }

            mainContent.appendChild(movieSection);
        });
    }

    
        function formatDate(date) {
            return date.toISOString().split("T")[0]; // Формат YYYY-MM-DD
        }
    
        function updateActiveDate(newActiveElement) {
            // Удаляем класс у предыдущего активного дня
            document.querySelectorAll(".page-nav__day").forEach(day => day.classList.remove("page-nav__day_chosen"));
    
            // Добавляем класс новому активному дню
            newActiveElement.classList.add("page-nav__day_chosen");
        }
    
        function handleDayClick(event) {
            event.preventDefault(); // Предотвращаем переход по ссылке
            const clickedElement = event.currentTarget;
            const selectedDate = clickedElement.getAttribute("data-date");
    
            if (clickedElement.classList.contains("page-nav__day_prev")) {
                // Переключение на предыдущий день
                if (currentDateElement) {
                    const currentDate = new Date(currentDateElement.getAttribute("data-date"));
                    currentDate.setDate(currentDate.getDate() - 1);
                    const newDate = formatDate(currentDate);
    
                    // Найти элемент с соответствующей датой
                    const newActiveElement = [...navDays].find(day => day.getAttribute("data-date") === newDate);
                    if (newActiveElement) {
                        updateActiveDate(newActiveElement);
                    }
    
                    loadMoviesByDate(newDate);
                }
            } else if (clickedElement.classList.contains("page-nav__day_next")) {
                // Переключение на следующий день
                if (currentDateElement) {
                    const currentDate = new Date(currentDateElement.getAttribute("data-date"));
                    currentDate.setDate(currentDate.getDate() + 1);
                    const newDate = formatDate(currentDate);
    
                    // Найти элемент с соответствующей датой
                    const newActiveElement = [...navDays].find(day => day.getAttribute("data-date") === newDate);
                    if (newActiveElement) {
                        updateActiveDate(newActiveElement);
                    }
    
                    loadMoviesByDate(newDate);
                }
            } else if (selectedDate) {
                // Клик на конкретную дату
                updateActiveDate(clickedElement);
                loadMoviesByDate(selectedDate);
            }
    
            // Обновляем текущий активный элемент
            currentDateElement = document.querySelector(".page-nav__day_chosen");
        }
    
        // Навешиваем обработчик на все дни
        navDays.forEach(day => {
            day.addEventListener("click", handleDayClick);
        });
    
        // Инициализация при загрузке страницы
        const today = new Date();
        const todayFormatted = formatDate(today);
        
        // Найти элемент с сегодняшней датой и сделать его активным
        const todayElement = [...navDays].find(day => day.getAttribute("data-date") === todayFormatted);
        if (todayElement) {
            updateActiveDate(todayElement);
        }
    
        loadMoviesByDate(todayFormatted);
    });