<?php
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

if (! defined("B_PROLOG_INCLUDED") or B_PROLOG_INCLUDED !== true or ! Loader::includeModule("iblock")) {
    die();
}

class Form extends CBitrixComponent implements Controllerable, Bitrix\Main\Errorable
{

    protected object $errorCollection;


    public function onPrepareComponentParams($arParams): array
    {
        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }


    public function executeComponent(): void
    {
        $this->includeComponentTemplate();
    }

    public function configureActions()
    {
        return [
            'check' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [
                            ActionFilter\HttpMethod::METHOD_POST,
                        ]
                    ),
                ],
                'postfilters' => [],
            ],
        ];
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function checkAction()
    {
        return $this->request->getPostList();
    }
}
