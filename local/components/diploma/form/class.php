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
        $out = [];
        $classNames = array_map(fn($item) => $item['name'], $parseData);
        foreach ($parseData as $item) {
            foreach ($item['variables'] as $val) {
                $tmp = explode(' ', $val);
                if (in_array($tmp[0], $classNames)) {
                    $out[] = [
                        'valName' => $tmp[1],
                        'className' => $item['name'],
                        'classNameToDel' => $tmp[0],
                    ];
                }
            }
        }
        $answer = [];
        foreach ($out as $item)
        {
            preg_match('~class '.$item['className'].' .*?(.*?)};~is', $data, $match);
            $tmp = explode("\n", $match[0]);
            foreach ($tmp as $tm)
            {
                if(strripos($tm,$item['className'].'(')){
                    preg_match('/\{\s*(?P<str>[^}]+?)\s*\}/', $tm, $test);
                    $tmp1 = str_replace(';',',-', $test[1]);
                    $tmp1 = substr($tmp1,0,-1);
                    $tmp1 = str_replace('=','', $tmp1);
                    $tmp1 = str_replace('','', $tmp1);
                    $tmp1 = str_replace($item['classNameToDel'],'', $tmp1);
                    $tmp1 = explode('-', $tmp1);
                    foreach ($tmp1 as &$tmp2)
                    {

                        if( !strripos( $tmp2,'('))
                            {
                                $tmp2 = trim($tmp2);
                                $tmp2 = array_values(array_diff(explode(' ', $tmp2),['']));
                                $tmp2[1] = '('.$tmp2[1].')';
                                $tmp2 = implode('',$tmp2);
                                $tmp2 = str_replace(',', '', $tmp2);
                            }
                    }
                    $tmp1 = implode('', $tmp1);
                    $tmp1 = str_replace(' ', '', $tmp1 );
                    $answer[] = [
                        'a' => $test[1],
                        'b' => $tmp1,
                        'c' => $item,
                    ];
                    $data = str_replace($test[1], '', $data);
                    $data = str_replace($item['className'].'()', $item['className'].':()'. $tmp1, $data);
                }
            }
        }



        return $data;
    }

    public function checkAction()
    {
        $data = $this->validataFormData();
        $dataParse = $this->getClassFromString($data);
        $dataParse = $this->getVariablesFromString($dataParse);
        $out = $this->optimizeCode($data, $dataParse);

//        return $dataParse;
        return $out;
    }
}
