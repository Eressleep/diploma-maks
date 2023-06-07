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
            $variables = str_replace(["\n", "\r"], '', trim($variables[1]));
            $variables = array_diff(explode(';', $variables), ['']);
            foreach ($variables as &$val) {
                $val = trim($val, ' ');
            }
            $out[] = [
                'name' => $name,
                'variables' => $variables,
                'class' => $item,
            ];
        }

        return $out;
    }

    protected function optimizeCode($data, $parseData)
    {
        //B() :m_z(5, 10)
        $classNames = array_map(fn($item) => $item['name'], $parseData);
        foreach ($parseData as &$item)
        {
            foreach ($item['variables'] as &$val)
            {
                $tmp =explode(' ', $val);
                if( in_array($tmp[0], $classNames))
                {
                    $val = $tmp;
                    preg_match('~'.$tmp[1].' .*?(.*?);~is', $item['class'], $stringToDel);
                    $val = $stringToDel[0];



                    $optimazeCode = str_replace(' ','', $val);
                    $optimazeCode = str_replace('=','', $optimazeCode);
                    $optimazeCode = str_replace($tmp[0],'', $optimazeCode);
                    $optimazeCode = str_replace(';','', $optimazeCode);


                    $revClass = $item['class'];
                    $revClass = str_replace(') {', '):'.$optimazeCode.'{', $revClass);
                    $data = str_replace($item['class'], $revClass, $data);
                    $data = str_replace($val,'', $data);







                }else{
                    $val = false;
                }
            }
        }




        return $data;
        return $parseData;
    }

    public function checkAction()
    {
        $data = $this->validataFormData();
        $dataParse = $this->getClassFromString($data);
        $dataParse = $this->getVariablesFromString($dataParse);
        $out = $this->optimizeCode($data, $dataParse);

        return $out;
    }
}
