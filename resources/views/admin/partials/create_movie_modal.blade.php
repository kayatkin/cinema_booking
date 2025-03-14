<!-- resources/views/admin/partials/create_movie_modal.blade.php -->

<div id="add-movie-modal" class="modal hidden popup active">
    <div class="popup__container">
        <div class="modal-content popup__content">
            <div class="popup__header">
                <h2 class="conf-step__title popup__title">
                    Добавление фильма
                    <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
                </h2>
            </div>
            <div class="popup__wrapper">
                <form id="add-movie-form" action="add_movie" method="post" accept-charset="utf-8">
                    @csrf
                    <div class="popup__container">
                        <!-- Блок для постера -->
                        <div class="popup__poster"></div>
                        <!-- Блок для формы -->
                        <div class="popup__form">
                            <!-- Название фильма -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-title">
                                Название фильма
                                <input class="conf-step__input" type="text" id="movie-title"
                                    placeholder="Например, &laquo;Гражданин Кейн&raquo;" name="title" required>
                            </label>
                            <!-- Продолжительность фильма -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-duration">
                                Продолжительность фильма (мин.)
                                <input class="conf-step__input" type="text" id="movie-duration" name="duration" required>
                            </label>
                            <!-- Описание фильма -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-synopsis">
                                Описание фильма
                                <textarea class="conf-step__input" id="movie-synopsis" name="synopsis" required></textarea>
                            </label>
                            <!-- Страна производства -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-origin">
                                Страна
                                <input class="conf-step__input" type="text" id="movie-origin" name="origin" required>
                            </label>
                            <!-- Начало проката -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-start-of-release">
                                Начало проката
                                <input class="conf-step__input" type="date" id="movie-start-of-release" name="start_of_release" required>
                            </label>
                            <!-- Окончание проката -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-end-of-release">
                                Окончание проката
                                <input class="conf-step__input" type="date" id="movie-end-of-release" name="end_of_release" required>
                            </label>
                        </div>
                    </div>
                    <div class="conf-step__buttons text-center">
                        <!-- Кнопка добавления фильма -->
                        <input type="submit" value="Добавить фильм" class="conf-step__button conf-step__button-accent">
                        <!-- Кнопка загрузки постера -->
                        <input type="file" id="movie-poster" name="poster" class="conf-step__button conf-step__button-accent">
                        <!-- Кнопка отмены -->
                        <button type="button" id="close-movie-modal-button"
                            class="conf-step__button conf-step__button-regular">Отменить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div id="movie-modal-overlay" class="modal-overlay hidden"></div>