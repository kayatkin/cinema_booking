<!-- resources/views/admin/partials/edit_movie_modal.blade.php -->

<div id="edit-movie-modal" class="modal hidden">
    <div class="modal-content">
        <h2>Редактирование фильма</h2>
        <form id="edit-movie-form">
            @csrf
            <input type="hidden" id="movie-id" name="id">
            <label for="movie-title">Название фильма:</label>
            <input type="text" id="movie-title" name="title" required>

            <label for="movie-duration">Продолжительность (минут):</label>
            <input type="number" id="movie-duration" name="duration" required min="1">

            <label for="movie-synopsis">Описание фильма:</label>
            <textarea id="movie-synopsis" name="synopsis"></textarea>

            <label for="movie-origin">Страна производства:</label>
            <input type="text" id="movie-origin" name="origin">

            <label for="movie-poster">Постер:</label>
            <input type="file" id="movie-poster" name="poster">

            <button type="submit" class="conf-step__button conf-step__button-accent">Сохранить</button>
            <button type="button" id="delete-movie-button"
                class="conf-step__button conf-step__button-regular">Удалить</button>
            <button type="button" id="close-edit-movie-modal-button"
                class="conf-step__button conf-step__button-regular">Отмена</button>
        </form>
    </div>
</div>

<div id="edit-movie-modal-overlay" class="modal-overlay hidden"></div>