<?php

use Bitrix\Main\Loader;

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\LoaderException;

try {
    if (!defined("B_PROLOG_INCLUDED") or B_PROLOG_INCLUDED !== true or !Loader::includeModule("iblock")) {
        die();
    }
} catch (LoaderException $e) {
}

class Form extends CBitrixComponent implements Controllerable, Bitrix\Main\Errorable
{

    protected object $errorCollection;
    protected object $errors;
    protected array|string $data;
    protected array|string $dataParse;
    protected string|array|null $out = null;
    protected array|string|null  $optimizeData = null;

    public function onPrepareComponentParams($arParams): array
    {
        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }

    public function executeComponent(): void
    {
        $this->includeComponentTemplate();
    }

    public function configureActions(): array
    {
        return [
            'check' => [
                'prefilters' => [],
                'postfilters' => [],
            ],
        ];
    }

    public function getErrors(): array
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code): \Bitrix\Main\Error
    {
        return $this->errorCollection->getErrorByCode($code);
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
        $classNames = array_map(fn($item) => $item['name'], $parseData);
        foreach ($parseData as &$item) {
            foreach ($item['variables'] as &$val) {
                $tmp = explode(' ', $val);
                if (in_array($tmp[0], $classNames)) {
                    $val = $tmp;
                    preg_match('~' . $tmp[1] . ' .*?(.*?);~is', $item['class'], $stringToDel);
                    $val = $stringToDel[0];

                    $optimazeCode = str_replace(' ', '', $val);
                    $optimazeCode = str_replace('=', '', $optimazeCode);
                    $optimazeCode = str_replace($tmp[0], '', $optimazeCode);
                    $optimazeCode = str_replace(';', '', $optimazeCode);

                    $revClass = $item['class'];
                    $revClass = str_replace(') {', '):' . $optimazeCode . '{', $revClass);
                    $data = str_replace($item['class'], $revClass, $data);
                    $data = str_replace($val, '', $data);
                } else {
                    $val = false;
                }
            }
        }

        return $data;
    }
    protected function enterClass($className)
    {
        return
            'template <class '.$className.'>
    class Creator
    {
        public:
          int m_ind;
          static std:: vector <'.$className.'> m_Cache;
          void setsize(int sz)
          {
            m_Cache.resize(sz);
            memset(m_Cache.data(),0,sizeof(A)*sz);
          }
          Creator::Creator():m_ind(0)
          {
          }
          static '.$className.' & CreateNew()
          {
           if (ind m_Cache.size)
           {
            m_Cache.resize(m_Cache.size()*2);
            memset(m_Cache.data,0,sizeof(A));
           }
          ind++;
          return m_Cache [ind];
        }';
}


    protected function optimizeCodeVersion2($data, $parseData){
        $classNames = array_map(fn($item) => $item['name'], $parseData);
        $str = '#include"iostream'.PHP_EOL.'#include<vector>'.PHP_EOL;

        foreach ($parseData as &$item) {
            foreach ($item['variables'] as &$val) {
                $tmp = explode(' ', $val);
                if (in_array($tmp[0], $classNames)) {
                    $val = $tmp;
                    preg_match('~' . $tmp[1] . ' .*?(.*?);~is', $item['class'], $stringToDel);
                    $val = $stringToDel[0];

                    preg_match(';.*()(.*?)};', $item['class'], $stringToDel);
                    $revClass = $item['class'];
                    $revClass = str_replace($stringToDel[0], '', $revClass);
                    $data = str_replace($item['class'], $revClass, $data);
                    $str .= PHP_EOL.$this->enterClass($item['name']);


                } else {
                    $val = false;
                }
            }
        }
        $data = str_replace('#include"iostream"', $str, $data);
        return $data;
    }


    protected function optimizeData($data): array
    {
        $class = array_map(fn($item) => $item['name'], $data);
        $classCopies = [];
        foreach ($data as $key => $item) {
            $classCopies[] = $key . '->' . $item['name'] . '-' . count(array_map(fn($tmp) => $tmp, $item['variables']));
        }
        $classOptimize = [];
        foreach ($data as $key => $item) {
            foreach ($item['variables'] as $val) {
                $tmp = explode(' ', $val);
                $classOptimize[$item['name']]['name'] = $key;

                if (in_array($tmp[0], $class)) {
                    $classOptimize[$item['name']]['cnt'][] = $tmp[0];
                }
            }
        }
        $classTest = [];
        foreach ($classOptimize as $key => $item) {
            $classTest[] = implode(' ',
                [
                    $key,
                    $item['name'],
                    'count =',
                    isset($item['cnt']) ? count($item['cnt']) : 0
                ]);
        }
        return [
            'classCount' => count($class),
            'classOptimize' => implode('---', $classTest),
            'classComposition' => implode(' ', $classCopies),
        ];
    }

    public function checkAction() : array
    {
        $start = getmicrotime();

        $this->data = $this->validataFormData();

        if( $this->request->getPost('oldMethod'))
        {

            $this->dataParse = $this->getClassFromString($this->data);
            $this->dataParse = $this->getVariablesFromString($this->dataParse);
            $this->out = $this->optimizeCode($this->data, $this->dataParse );
            $this->optimizeData = $this->optimizeData($this->dataParse);
        }else{
            $this->dataParse = $this->getClassFromString($this->data);
            $this->dataParse = $this->getVariablesFromString($this->dataParse);
            $this->out = $this->optimizeCodeVersion2($this->data, $this->dataParse );
            $this->optimizeData = $this->optimizeData($this->dataParse);

        }

        return [
            'optimazeCode' => is_string($this->out) ? $this->out : false,
            'optimazeData' => is_array($this->optimizeData) ? $this->optimizeData : false,
            'optimazeTime' => getmicrotime() - $start,
        ];
    }
}
