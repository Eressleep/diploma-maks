<?php
use Bitrix\Main\Localization\Loc;
?>
<div class="form">
    <form id="form">
        <b><?= Loc::getMessage('FORM_ENTER_YOUR_CODE') ?></b>
        <br>
        <label for="formCode"></label>
        <textarea spellcheck="false" id="formCode" name="code" rows="25" cols="40"></textarea>
        <br>
        <input type="submit" value="Отправить">
        <div>
            <label for="oldMethod">Старый метод</label>
            <input name="oldMethod" id="oldMethod" type="radio" checked>
            <label for="newMethod">Новый метод</label>
            <input name="newMethod" id="newMethod" type="radio">
        </div>
    </form>
    <div class="answer">
        <b><?= Loc::getMessage('FORM_RESULT_CODE') ?></b>
        <br>
        <label spellcheck="false" for='result'></label><textarea id = 'result' name="code" rows="25" cols="40"></textarea>
    </div>
    <div class="answer-optimize">
        <br>
        <div><?= Loc::getMessage('FORM_CLASS_COUNT') ?></div>
        <label for="class-count"></label>
        <input
            id="class-count"
            class="answer-optimize-input"
            type="text"
            readonly="readonly"
        >
        <div><?= Loc::getMessage('FORM_COUNT_I_CLASS_USE_IN_CLASS') ?></div>
        <label for="class-copies"></label>
        <input
            id="class-copies"
            class="answer-optimize-input"
            type="text"
            readonly="readonly"
        >
        <div><?= Loc::getMessage('FORM_LANG_VARS_IN_I_CLASS') ?></div>
        <label for="class-composition"></label>
        <input
            id="class-composition"
            class="answer-optimize-input"
            type="text"
            readonly="readonly"
        >
        <div><?= Loc::getMessage('FORM_TIME_USE_PROGRAMM') ?></div>

        <label for="class-time"></label>
        <input
            id="class-time"
            class="answer-optimize-input"
            type="number"
            readonly="readonly"
        >
    </div>
</div>
