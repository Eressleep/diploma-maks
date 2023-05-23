<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>
<?php
$APPLICATION->IncludeComponent(
    'diploma:form',
    '.default',
    []
);
?>
<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
