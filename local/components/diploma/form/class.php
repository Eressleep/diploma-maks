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

if (!defined("B_PROLOG_INCLUDED") or B_PROLOG_INCLUDED !== true or !Loader::includeModule("iblock")) {
    die();
}

class Form extends CBitrixComponent implements Controllerable, Bitrix\Main\Errorable
{

    protected object $errorCollection;
    protected object $errors;

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
                'prefilters' => [],
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

    protected function classVariablesSeparate(string $data)
    {
        return $data;
    }

    protected function validataFormData()
    {
        $data = $this->request->getPost('code');
        if (is_string($data) and strlen($data) === 0) {
            $this->errorCollection->add([
                new Bitrix\Main\Error('Empty form-data',),
            ]);
        }
        return $data;
    }

    protected function getClassFromString(string $data, $out = [])
    {
        while (true) {
            preg_match('~class .*?(.*?)};~is', $data, $answer);
            if (empty($answer)) {
                break;
            }
            $out[] = $answer[0];
            $data = str_replace($answer[0], '', $data);
        }
        return $out;
    }

    protected function getVariablesFromString($data, $out = [])
    {
        foreach ($data as $item) {
            preg_match('~class.*?(.*?){~is', $item, $name);
            $name = trim($name[1]);
            preg_match('~:.*?(.*?)' . $name . '~is', $item, $variables);
            $variables = str_replace(["\n", '\r'], '', trim($variables[1]));
            $variables = array_diff(explode(';', $variables), ['']);
            foreach ($variables as &$val) {
                $val = trim($val, ' ');
            }
            $out[] = [
                'name' => $name,
                'variables' => $variables,
            ];
        }
        return $out;
    }

    public function checkAction()
    {
        $data = $this->validataFormData();
        $dataParse = $this->getClassFromString($data);
        $dataParse = $this->getVariablesFromString($dataParse);


        return $dataParse;
    }
}
