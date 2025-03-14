<!-- resources/views/admin/partials/edit_movie_modal.blade.php -->

<div id="edit-movie-modal" class="modal hidden popup active">
    <div class="popup__container">
        <div class="modal-content popup__content">
            <div class="popup__header">
                <h2 class="conf-step__title popup__title">
                    Редактирование фильма
                    <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
                </h2>
            </div>
            <div class="popup__wrapper">
                <form id="edit-movie-form" action="edit_movie" method="post" accept-charset="utf-8">
                    @csrf
                    <div class="popup__container">
                        <!-- Блок для постера -->
                        <div class="popup__poster"></div>
                        <input type="hidden" id="movie-id" name="id">
                        <div class="popup__form">
                            <!-- Название фильма -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-title">
                                Название фильма
                                <input class="conf-step__input" type="text" id="movie-title"
                                    placeholder="Например, &laquo;Гражданин Кейн&raquo;" name="title" required>
                            </label>
                            <!-- Продолжительность фильма -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-duration">
                                Продолжительность фильма (минут)
                                <input class="conf-step__input" type="number" id="movie-duration" name="duration"
                                    required min="1" required>
                            </label>
                            <!-- Описание фильма -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-synopsis">
                                Описание фильма
                                <textarea class="conf-step__input" id="movie-synopsis" name="synopsis"
                                    required></textarea>
                            </label>
                            <!-- Страна производства -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-origin">
                                Страна
                                <input class="conf-step__input" type="text" id="movie-origin" name="origin" required>
                            </label>
                            <!-- Начало проката -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-start-of-release">
                                Начало проката
                                <input class="conf-step__input" type="date" id="movie-start-of-release"
                                    name="start_of_release" required>
                            </label>
                            <!-- Окончание проката -->
                            <label class="conf-step__label conf-step__label-fullsize" for="movie-end-of-release">
                                Окончание проката
                                <input class="conf-step__input" type="date" id="movie-end-of-release"
                                    name="end_of_release" required>
                            </label>
                        </div>
                    </div>
                    <div class="conf-step__buttons text-center">
                        <!-- Кнопки управления -->
                        <!-- Кнопка загрузки постера -->
                        <input type="file" id="movie-poster" name="poster"
                            class="conf-step__button conf-step__button-accent">
                        <button type="submit" class="conf-step__button conf-step__button-accent">Сохранить</button>
                        <button type="button" id="delete-movie-button"
                            class="conf-step__button conf-step__button-regular">Удалить</button>
                        <button type="button" id="close-edit-movie-modal-button"
                            class="conf-step__button conf-step__button-regular">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="edit-movie-modal-overlay" class="modal-overlay hidden"></div>