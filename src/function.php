<?php

namespace Glacom;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\File;

class Functions
{
    const VERSION = 'v1.1.0';

    /**
     * Get the Nova Custom Fields List giving data from model CoreCustomField.
     *
     * @param  \App\Models\CoreCustomField  $cf
     * @return array
    */
    public function generateNovaCustomField($cf){

        $listCustomField = array();
        $langAv = config('app.lang');
            
        foreach($cf as $cfItem){
            $sizeTmp = ($cfItem->size) ? $cfItem->size : 'w-full';
            switch($cfItem->type){
                case 'text':
                    if($cfItem->is_multilanguage == true){
                        foreach($langAv as $langIt){
                            $tmpName = $cfItem->name.'_'.$langIt;
                            $tmpField = Text::make($tmpName)
                                            ->size($sizeTmp)
                                            ->hideFromIndex();
                            if($cfItem->is_required == true) $tmpField->rules('required');
                            $listCustomField[] = $tmpField;
                        }
                    }else{
                        $tmpField = Text::make($cfItem->name)
                                        ->size($sizeTmp)
                                        ->hideFromIndex();
                        if($cfItem->is_required === true) $tmpField->rules('required');
                        $listCustomField[] = $tmpField;
                    }    
                    break;
                case 'textarea':
                    if($cfItem->is_multilanguage == true){
                        foreach($langAv as $langIt){
                            $tmpName = $cfItem->name.'_'.$langIt;
                            $tmpField = Textarea::make($tmpName)
                                            ->alwaysShow()
                                            ->size($sizeTmp)
                                            ->hideFromIndex();
                            if($cfItem->is_required == true) $tmpField->rules('required');
                            $fieldConf = json_decode($cfItem->configuration);
                            if($fieldConf->row) $tmpField->rows($fieldConf->row);
                            $listCustomField[] = $tmpField;
                        }
                    }else{
                        $tmpField = Textarea::make($cfItem->name)
                                        ->alwaysShow()
                                        ->size($sizeTmp)
                                        ->hideFromIndex();
                        if($cfItem->is_required == true) $tmpField->rules('required');
                        $fieldConf = json_decode($cfItem->configuration);
                        if($fieldConf->row) $tmpField->rows($fieldConf->row);
                        $listCustomField[] = $tmpField;
                    }    
                    break;
                case 'number':
                    if($cfItem->is_multilanguage == true){
                        foreach($langAv as $langIt){
                            $tmpName = $cfItem->name.'_'.$langIt;
                            $tmpField = Number::make($tmpName)
                                            ->size($sizeTmp)
                                            ->hideFromIndex();
                            if($cfItem->is_required == true) $tmpField->rules('required');
                            if($cfItem->configuration != ''){
                                $fieldConf = json_decode($cfItem->configuration);
                                if($fieldConf->min) $tmpField->min($fieldConf->min);
                                if($fieldConf->max) $tmpField->min($fieldConf->max);
                                if($fieldConf->step) $tmpField->min($fieldConf->step);
                            }    
                            $listCustomField[] = $tmpField;
                        }
                    }else{
                        $tmpField = Number::make($cfItem->name)
                                        ->size($sizeTmp)
                                        ->hideFromIndex();
                        if($cfItem->is_required == true) $tmpField->rules('required');
                        if($cfItem->configuration != ''){
                            $fieldConf = json_decode($cfItem->configuration);
                            if($fieldConf->min) $tmpField->min($fieldConf->min);
                            if($fieldConf->max) $tmpField->min($fieldConf->max);
                            if($fieldConf->step) $tmpField->min($fieldConf->step);
                        }    
                        $listCustomField[] = $tmpField;
                    }
                    break;
                case 'boolean':
                    $tmpField = Boolean::make($cfItem->name)
                                    ->size($sizeTmp)
                                    ->hideFromIndex();
                    if($cfItem->is_required == true) $tmpField->rules('required');
                    $listCustomField[] = $tmpField;
                    break;
                case 'image':
                    if($cfItem->is_multilanguage == true){
                        foreach($langAv as $langIt){
                            $tmpName = $cfItem->name.'_'.$langIt;
                            $tmpField = Image::make($tmpName)
                                            ->size($sizeTmp)
                                            ->hideFromIndex();
                            /*$tmpField->squared()->maxWidth(100)->path('uploads')->storeAs(function (Request $request) {
                                $trace = debug_backtrace();
                                $caller = $trace[2]['object'];
                                $name = $caller->name;
                                Log::debug($trace);
                                trigger_error($name);
                                return $request->{$name}->getClientOriginalName();
                            });*/
                            if($cfItem->is_required == true) $tmpField->rules('required');
                            $listCustomField[] = $tmpField;
                        }
                    }else{
                        $tmpName = $cfItem->name;
                        $tmpField = Image::make($tmpName)
                                        ->size($sizeTmp)
                                        ->hideFromIndex();
                        //$tmpField->path('uploads')->storeAs(function (Request $request, $tmpName) {
                        //    return $request->$tmpName->getClientOriginalName();
                        //});
                        if($cfItem->is_required == true) $tmpField->rules('required');
                        $listCustomField[] = $tmpField;
                    }
                    break;
                case 'file':
                    if($cfItem->is_multilanguage == true){
                        foreach($langAv as $langIt){
                            $tmpName = $cfItem->name.'_'.$langIt;
                            $tmpField = File::make($tmpName)
                                            ->size($sizeTmp)
                                            ->hideFromIndex();
                            //$tmpField->path('uploads')->storeAs(function (Request $request, $tmpName) {
                            //   return $request->$tmpName->getClientOriginalName();
                            //});
                            if($cfItem->is_required == true) $tmpField->rules('required');
                            $listCustomField[] = $tmpField;
                        }
                    }else{
                        $tmpName = $cfItem->name;
                        $tmpField = File::make($tmpName)
                                        ->size($sizeTmp)
                                        ->hideFromIndex();
                        //$tmpField->path('uploads')->storeAs(function (Request $request, $tmpName) {
                        //    return $request->$tmpName->getClientOriginalName();
                        //});
                        if($cfItem->is_required == true) $tmpField->rules('required');
                        $listCustomField[] = $tmpField;
                    }
                    break;
            }            
        }

        return $listCustomField;
    }

}   