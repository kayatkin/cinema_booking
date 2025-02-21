<!-- resources/views/admin/partials/create_hall_modal.blade.php -->

<div id="create-hall-modal" class="modal hidden">
    <div class="modal-content">
        <h2 class="conf-step__title">Создание нового зала</h2>
        <form id="create-hall-form">
            @csrf <!-- Токен CSRF для безопасности -->
            <label for="hall-name" class="conf-step__label">Название зала:</label>
            <input type="text" class="conf-step__input" id="hall-name" name="name" required>
            <button type="submit" class="conf-step__button conf-step__button-accent">Создать</button>
            <button type="button" id="close-modal-button"
                class="conf-step__button conf-step__button-regular">Отмена</button>
        </form>
    </div>
</div>

<div id="modal-overlay" class="modal-overlay hidden"></div>