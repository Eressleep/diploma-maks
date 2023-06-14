<?php

?>
<div class="form">
    <form id="form">
        <b>Введите ваш код:</b>
        <br>
        <textarea name="code" rows="25" cols="40"></textarea>
        <br>
        <input type="submit" value="Отправить">
    </form>
    <div class="answer">
        <b>Результат кода:</b>
        <br>
        <textarea id = 'result' name="code" rows="25" cols="40"></textarea>
    </div>
    <div class="answer-optimize">
        <br>
        <div>Количество классов :</div>
        <input
            id="class-count"
            class="answer-optimize-input"
            type="text"
            readonly="readonly"
        >
        <div>Количество экземпляров i-го класса, используемых в программе :</div>
        <input
            id="class-copies"
            class="answer-optimize-input"
            type="text"
            readonly="readonly"
        >
        <div>Количество переменных в составе i-го класса :</div>
        <input
            id="class-composition"
            class="answer-optimize-input"
            type="text"
            readonly="readonly"
        >
        <div>Время выполнения программы :</div>
        <input
            id="class-time"
            class="answer-optimize-input"
            type="number"
            readonly="readonly"
        >
    </div>
</div>
