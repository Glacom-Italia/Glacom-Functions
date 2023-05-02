<?php

namespace Glacom\Functions;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\File;
use App\Models\CoreUrl;
use App\Models\CoreUrlTemplate;

use Illuminate\Support\Facades\Log;

class GlacomFunctions
{

    /**
     * Calculate new url check template/modrewrite(opt)/resource name.
     *
     * @param  string $url
     * @param  bool $lowercase
     * @return App\Models\CoreUrl
    */
    public function calculateURL($lang, $table, $useModrewrite=true, $resourceName, $model, $modelID){

        $newURL = '';
        $data = $model::find($modelID);

        if(isset($table)){
            $tpl = CoreUrlTemplate::where('table', $table)->first();
            
            if(!is_null($tpl)){
                $urlTmp = '';

                // check template for lang else default
                if(!is_null($tpl->url_template[$lang]) && $tpl->url_template[$lang] != ''){
                    $urlTmp = $tpl->url_template[$lang];

                }elseif(!is_null($tpl->url_template['default']) && $tpl->url_template['default'] != ''){
                    $urlTmp = $tpl->url_template['default'];

                }

                if($urlTmp != ''){
                    switch($table){
                        case 'core-pages':
                            if(strpos($urlTmp, '{resourceID}'))
                                $urlTmp = str_replace('{resourceID}', $data->id, $urlTmp);

                            if(strpos($urlTmp, '{resourceName}'))
                                $urlTmp = str_replace('{resourceName}', $data->name, $urlTmp);
                            
                            if(strpos($urlTmp, '{resourceTitle}'))
                                $urlTmp = str_replace('{resourceTitle}', $data->title[$lang], $urlTmp);
                            
                    }
                    
                    if(substr($urlTmp, 0, 1) == '/') $newURL = '/'.$lang.$urlTmp;
                    else $newURL = '/'.$lang.'/'.$urlTmp;
                }    
            }
        }

        if($newURL == '' && $useModrewrite == true){
            $newURL = '/'.$lang.'/'.$data->modrewrite[$lang];
        }

        if($newURL == ''){
            $newURL = '/'.$lang.'/'.$resourceName;
        }

        return $newURL;
    }    

    /**
     * Get the clean url giving string.
     *
     * @param  string $url
     * @param  bool $lowercase
     * @return App\Models\CoreUrl
    */
    public function cleanURL($string, $lowercase=true, $nospecialletter=true){
        
        $url = str_replace("'", '', $string);
        $url = str_replace('"', '', $string);
        $url = str_replace('%20', ' ', $url);

        if($nospecialletter==true){
            $url = str_replace("à", "a", $url);
            $url = str_replace("á", "a", $url);
            $url = str_replace("â", "a", $url);
            $url = str_replace("ã", "a", $url);
            $url = str_replace("ä", "a", $url);
            $url = str_replace("å", "a", $url);
            $url = str_replace("æ", "a", $url);
            $url = str_replace("è", "e", $url);
            $url = str_replace("é", "e", $url);
            $url = str_replace("ê", "e", $url);
            $url = str_replace("ë", "e", $url);
            $url = str_replace("ì", "i", $url);
            $url = str_replace("í", "i", $url);
            $url = str_replace("î", "i", $url);
            $url = str_replace("ï", "i", $url);
            $url = str_replace("ò", "o", $url);
            $url = str_replace("ó", "o", $url);
            $url = str_replace("ô", "o", $url);
            $url = str_replace("õ", "o", $url);
            $url = str_replace("ö", "o", $url);
            $url = str_replace("ø", "o", $url);
            $url = str_replace("œ", "o", $url);
            $url = str_replace("ù", "u", $url);
            $url = str_replace("ú", "u", $url);
            $url = str_replace("û", "u", $url);
            $url = str_replace("ü", "u", $url);
            $url = str_replace("ç", "c", $url);
            $url = str_replace("ð", "d", $url);
            $url = str_replace("ñ", "n", $url);
        }

        //Log::debug('clean 1: '.$url);
        $url = preg_replace('~[^\\pL0-9-\/]+~u', '-', $url); // substitutes anything but letters, numbers, '/' and '-' with separator
        //Log::debug('clean 2: '.$url);
        $url = trim($url, "-");
        //$url = iconv("utf-8", "us-ascii//TRANSLIT", $url);  // you may opt for your own custom character map for encoding.
        //Log::debug('clean 3: '.$url);
        if($lowercase==true){
            $url = strtolower($url);
        }
        //Log::debug('clean 4: '.$url);
        $url = preg_replace('~[^-a-zA-Z0-9\/]+~', '', $url); // keep only letters, numbers, '/' and separator
        //Log::debug('clean 5: '.$url);
        return $url;
    }

    /**
     * Get the Url information (model CoreUrl) giving url string.
     *
     * @param  string $url
     * @return App\Models\CoreUrl
    */
    public function getUrlInfoByUrl($url){
        $url = CoreUrl::where('url', $url)
            ->orderBy('created_at', 'desc')
            ->first();

        if(!is_null($url)){
            if($url->is_301 = true){
                // trova ultimo record NON is_301
                $url = CoreUrl::where('url', $url)
                    ->where('is_301', 0)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
        }
    }

    /**
     * Get the Url giving table, table id and locale.
     *
     * @param string $locale
     * @param string $table
     * @param integer $table_id
     * @return string
    */
    public function getUrlByInfo($locale, $table, $table_id){

        $url = CoreUrl::where('locale', $locale)
            ->where('table', $table)
            ->where('table_id', $table_id)
            ->orderBy('updated_at', 'desc')
            ->first();

        if(!is_null($url)) return $url;

        return false;
    }

    /**
     * Check if the giving url is unique.
     *
     * @param string $url
     * @param string $locale
     * @param string $table
     * @param integer $table_id
     * @return string
    */
    public function checkUniqueUrl($url, $locale, $table=null, $table_id=null){

        if(!is_null($table) && !is_null($table_id)){
            $url = CoreUrl::where('url',$url)
                ->where('locale', $locale)
                ->where('is_404', 0)
                ->whereNotIn('id', function($q) use($url, $locale, $table, $table_id){ 
                    $q->select('id')->from('core_urls')->where('url',$url)->where('locale', $locale)->where('table', $table)->where('table_id', $table_id); 
                })->first();
        }else{
            $url = CoreUrl::where('url',$url)
                ->where('locale', $locale)
                ->where('is_404', 0)
                ->first();
        }
        if(!is_null($url)){
            return false;
        }

        return true;
    }

    /**
     * Check if the giving url is unique.
     *
     * @param string $url
     * @param string $locale
     * @param string $table
     * @param integer $table_id
     * @return string
    */
    public function insertUpdateUrl($url, $locale, $table, $table_id, $set_404=false){

        /*
            cerca ultima occorrenza per locale, table, table_id
            se set_404 = true
                aggiorno vecchio come 404
            else    
                se url != new 
                    se now() - updated_at > 24h 
                        aggiorna vecchio come 301
                        inserisce new
                    else
                        aggiorna vecchio con new
                else 
                    se vecchio is_404 e set_404 = false
                        tolgo 404

        */
        
        $oldURL = $this->getUrlByInfo($locale, $table, $table_id);
        
        if(!$oldURL){
            // inserisco new
            $coreUrl = CoreUrl::create([
                'locale' => $locale,
                'core_page_id' => ($table=='core-pages') ? $table_id : $this->getPageIDbyTable($table),
                'table' => $table,
                'table_id' => $table_id,
                'url' => $url,
                'is_301' => false,
                'is_404' => $set_404,
                'url_redirect' => null
            ]);

        }else{
            if($set_404 == true){
                $oldURL->is_404 = true;
                $oldURL->save();

            }else{
                if($oldURL->is_404 == true && $set_404 == false){
                    if($oldURL->url == $url){
                        $oldURL->is_404 = false;
                        $oldURL->url_redirect = null;
                        $oldURL->save();

                    }else{
                        $oldURL->url_redirect = $url;
                        $oldURL->save();

                        // inserisco new
                        $coreUrl = CoreUrl::create([
                            'locale' => $locale,
                            'core_page_id' => ($table=='core-pages') ? $table_id : $this->getPageIDbyTable($table),
                            'table' => $table,
                            'table_id' => $table_id,
                            'url' => $url,
                            'is_301' => false,
                            'is_404' => false,
                            'url_redirect' => null
                        ]);
                    }
                
                }elseif($oldURL->is_301 == true && $set_404 == false){
                    if($oldURL->url != $url){
                        // inserisco new
                        $coreUrl = CoreUrl::create([
                            'locale' => $locale,
                            'core_page_id' => ($table=='core-pages') ? $table_id : $this->getPageIDbyTable($table),
                            'table' => $table,
                            'table_id' => $table_id,
                            'url' => $url,
                            'is_301' => false,
                            'is_404' => false,
                            'url_redirect' => null
                        ]);
                    }

                }else{
                    if($oldURL->url != $url){
                        $start = new \DateTime($oldURL->updated_at);
                        $end = new \DateTime();

                        //determine what interval should be used - can change to weeks, months, etc
                        $interval = new \DateInterval('PT1H');

                        //create periods every hour between the two dates
                        $periods = new \DatePeriod($start, $interval, $end);

                        if(iterator_count($periods) >= 24){
                            $oldURL->is_301 = true;
                            $oldURL->save();

                            // inserisco new
                            $coreUrl = CoreUrl::create([
                                'locale' => $locale,
                                'core_page_id' => ($table=='core-pages') ? $table_id : $this->getPageIDbyTable($table),
                                'table' => $table,
                                'table_id' => $table_id,
                                'url' => $url,
                                'is_301' => false,
                                'is_404' => false,
                                'url_redirect' => null
                            ]);

                        }else{
                            $oldURL->url = $url;
                            $oldURL->save();
                        }
                    }
                }    
            }
        }    

        return true;

    }

    /**
     * Get the CorePages ID giving module's table.
     *
     * @param  module's tabke $table
     * @return array
    */
    public function getPageIDbyTable($table){

        return null;
    }

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
                            $tmpField = Text::make($cfItem->name.' '.__('lang_'.$langIt), $tmpName)
                                            ->placeholder(' ')
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
                            $tmpField = Textarea::make($cfItem->name.' '.__('lang_'.$langIt), $tmpName)
                                            ->placeholder(' ')
                                            ->alwaysShow()
                                            ->size($sizeTmp)
                                            ->hideFromIndex();
                            if($cfItem->is_required == true) $tmpField->rules('required');
                            $fieldConf = json_decode($cfItem->configuration);
                            if($fieldConf && $fieldConf->row) $tmpField->rows($fieldConf->row);
                            else $tmpField->rows(4);
                            $listCustomField[] = $tmpField;
                        }
                    }else{
                        $tmpField = Textarea::make($cfItem->name)
                                        ->alwaysShow()
                                        ->size($sizeTmp)
                                        ->hideFromIndex();
                        if($cfItem->is_required == true) $tmpField->rules('required');
                        $fieldConf = json_decode($cfItem->configuration);
                        if($fieldConf && $fieldConf->row) $tmpField->rows($fieldConf->row);
                        else $tmpField->rows(4);
                        $listCustomField[] = $tmpField;
                    }    
                    break;
                case 'number':
                    if($cfItem->is_multilanguage == true){
                        foreach($langAv as $langIt){
                            $tmpName = $cfItem->name.'_'.$langIt;
                            $tmpField = Number::make($cfItem->name.' '.__('lang_'.$langIt), $tmpName)
                                            ->placeholder(' ')
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
                            $tmpField = Image::make($cfItem->name.__('lang_'.$langIt), $tmpName)
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
                            $tmpField = File::make($cfItem->name.__('lang_'.$langIt), $tmpName)
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
