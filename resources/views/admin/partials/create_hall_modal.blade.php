<!-- resources/views/admin/partials/create_hall_modal.blade.php -->

<div id="create-hall-modal" class="modal hidden popup active">
    <div class="popup__container">
        <div class="modal-content popup__content">
            <div class="popup__header">
                <h2 class="conf-step__title popup__title">
                    Добавление зала
                    <a class="popup__dismiss" href="#"><img src="{{ asset('assets/admin/i/close.png') }}" alt="Закрыть"></a>
                </h2>
            </div>
            <div class="popup__wrapper">
                <form id="create-hall-form">
                    @csrf <!-- Токен CSRF для безопасности -->
                    <label for="hall-name" class="conf-step__label conf-step__label-fullsize">Название зала:
                    <input type="text" class="conf-step__input" id="hall-name"
                        placeholder="Например, &laquo;Зал 1&raquo;" name="name" required>
                    </label>
                    <div class="conf-step__buttons text-center">
                        <button type="submit" class="conf-step__button conf-step__button-accent">Добавить зал</button>
                        <button type="button" id="close-modal-button"
                            class="conf-step__button conf-step__button-regular">Отменить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>    
</div>
<div id="modal-overlay" class="modal-overlay hidden"></div>