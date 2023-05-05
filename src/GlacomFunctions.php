<?php

namespace Glacom\Functions;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\File;
use App\Models\Core\CoreUrl;
use App\Models\Core\CoreUrlTemplate;

use Illuminate\Support\Facades\Log;

class GlacomFunctions
{

    /**
     * Calculate new url check template/modrewrite(opt)/resource name.
     *
     * @param  string $url
     * @param  bool $lowercase
     * @return App\Models\Core\CoreUrl
    */
    public function calculateURL($lang, $table, $model, $modelID, $modelData, $resourceName, $resourceNameAlt=null, $useModrewrite=true){

        $newURL = '';
        if(!$modelData || is_null($modelData)) $data = $model::find($modelID);
        else $data = $modelData;

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
                    // {placeholder} di default presenti su tutte le table
                    if(strpos($urlTmp, '{resourceID}'))
                        $urlTmp = str_replace('{resourceID}', $data->id, $urlTmp);

                    if(strpos($urlTmp, '{resourceName}'))
                        $urlTmp = str_replace('{resourceName}', $data->name, $urlTmp);
                    
                    if(strpos($urlTmp, '{resourceTitle}'))
                        $urlTmp = str_replace('{resourceTitle}', $data->title[$lang], $urlTmp);

                    switch($table){
                        case 'core-pages':
                            break;
                        case 'magazine-authors':
                            if(strpos($urlTmp, '{magazineAuthorName}'))
                                $urlTmp = str_replace('{magazineAuthorName}', $data->name, $urlTmp);

                            if(strpos($urlTmp, '{magazineAuthorSurname}'))
                                $urlTmp = str_replace('{magazineAuthorSurname}', $data->surname, $urlTmp);                            
                            
                            break;
                        case 'magazine-groups':
                            break;
                        case 'magazine-news':
                            if(strpos($urlTmp, '{magazineNewsPublishDate}')){
                                $dtTmp = explode(' ', $data->publish_datetime);
                                $dtTmp2 = explode('-', $dtTmp);
                                $urlTmp = str_replace('{magazineNewsPublishDate}', $dtTmp2[2].'-'.$dtTmp2[1].'-'.$dtTmp2[0], $urlTmp);
                            }    
                            
                            break;
                        case 'magazine-tags':
                            break;
                    }
                    
                    if(substr($urlTmp, 0, 1) == '/') $newURL = '/'.$lang.$urlTmp;
                    else $newURL = '/'.$lang.'/'.$urlTmp;
                }    
            }
        }

        if($useModrewrite == true && !is_null($data->modrewrite[$lang]) && trim($data->modrewrite[$lang]) != ''){ //$newURL == '' && 
            //se c'è modrewrite custom sovrascrivo template
            $newURL = '/'.$lang.'/'.$data->modrewrite[$lang];
        }

        if($newURL == '' && !is_null($resourceName) && $resourceName!=''){
            $newURL = '/'.$lang.'/'.$resourceName;
        }

        if($newURL == '' && !is_null($resourceNameAlt) && $resourceNameAlt!=''){
            $newURL = '/'.$lang.'/'.$resourceNameAlt;
        }

        return $newURL;
    }    

    /**
     * Get the clean url giving string.
     *
     * @param  string $url
     * @param  bool $lowercase
     * @return string
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
     * @return App\Models\Core\CoreUrl
    */
    public function getUrlInfoByUrl($url){
        $url = CoreUrl::where('url', $url)
            ->orderBy('updated_at', 'desc')
            ->first();

        if(!is_null($url)){
            if($url->is_301 = true){
                // trova ultimo record NON is_301
                $url = CoreUrl::where('url', $url)
                    ->where('is_301', 0)
                    ->orderBy('updated_at', 'desc')
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
            ->orderBy('id', 'desc')
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
    public function checkUniqueUrl($locale, $url, $table=null, $table_id=null){

        if(!is_null($table) && !is_null($table_id)){
            $url = CoreUrl::where('url',$url)
                ->where('locale', $locale)
                //->where('is_404', 0) ???
                ->whereNotIn('id', function($q) use($url, $locale, $table, $table_id){ 
                    $q->select('id')->from('core_urls')->where('url',$url)->where('locale', $locale)->where('table', $table)->where('table_id', $table_id); 
                })->first();
        }else{
            $url = CoreUrl::where('url',$url)
                ->where('locale', $locale)
                //->where('is_404', 0) ??
                ->first();
        }
        if(!is_null($url)){
            return false;
        }

        return true;
    }

    /**
     * Insert new url in core_urls.
     *
     * @param string $locale
     * @param string $table
     * @param integer $table_id
     * @param string $url
     * @param boolean $is301
     * @param boolean $is404
     * @param string $urlRedirect
     * @return boolean
    */
    public function insertNewUrl($locale, $table, $table_id, $url, $is301=false, $is404=false, $urlRedirect=null){
    
        //Log::debug('insertNewUrl > '.$url.'|'.$is301.'|'.$is404.'|'.$urlRedirect);
        $coreUrl = CoreUrl::create([
            'locale' => $locale,
            'core_page_id' => ($table=='core-pages') ? $table_id : $this->getPageIDbyTable($table),
            'table' => $table,
            'table_id' => $table_id,
            'url' => $url,
            'is_301' => $is301,
            'is_404' => $is404,
            'url_redirect' => $urlRedirect
        ]);

        return true;
    }

    /**
     * Retrive Url and update it in core_urls.
     *
     * @param string $locale
     * @param string $table
     * @param integer $table_id
     * @param string $url
     * @param boolean $is301
     * @param boolean $is404
     * @param string $urlRedirect
     * @return boolean
    */
    public function updateCurrentUrl($locale, $table, $table_id, $url, $is301, $is404, $urlRedirect){
    
        $currentURL = $this->getUrlByInfo($locale, $table, $table_id);

        if(isset($url) && !is_null($url)) $currentURL->url = $url;
        if(isset($is301) && !is_null($is301)) $currentURL->is_301 = $is301;
        if(isset($is404) && !is_null($is404)) $currentURL->is_404 = $is404;
        if(isset($urlRedirect) && !is_null($urlRedirect)) $currentURL->url_redirect = $urlRedirect;
        //Log::debug('updateCurrentUrl > '.$url.'|'.$is301.'|'.$is404.'|'.$urlRedirect);
        $currentURL->save();

        return true;
    }

    /**
     * Check if insert or update url by resource.
     *
     * @param string $url
     * @param string $locale
     * @param string $table
     * @param integer $table_id
     * @param boolean $set_404
     * @return boolean
    */
    public function checkInsertUpdateUrl($locale, $table, $table_id, $url, $is301=false, $is404=false, $urlRedirect=null){
        
        $currentURL = $this->getUrlByInfo($locale, $table, $table_id);
        
        if($url != $currentURL->url){
            $start = new \DateTime($currentURL->updated_at);
            $end = new \DateTime();

            //determine what interval should be used - can change to weeks, months, etc
            $interval = new \DateInterval('PT1H');

            //create periods every hour between the two dates
            $periods = new \DatePeriod($start, $interval, $end);

            if(iterator_count($periods) >= 24){
                //Log::debug('checkInsertUpdateUrl > difftime >24 [UPD+INS]');
                // update old
                $this->updateCurrentUrl($locale, $table, $table_id, $currentURL->url, true, false, $url);

                // insert new
                $this->insertNewUrl($locale, $table, $table_id, $url, $is301, $is404, $urlRedirect);
                
            }else{
                //Log::debug('checkInsertUpdateUrl > difftime <24 [UPD]');
                // update
                $this->updateCurrentUrl($locale, $table, $table_id, $url, $is301, $is404, $urlRedirect);
            }

            return true;

        }else{
            return false;

        }
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
     * Create blade view if not already exists in giving directory.
     *
     * @param string $filename
     * @param string $dir
     * @return array
    */
    public function createViewsIfNotExists($filename, $dir = null){
        $filenameWithDir = $filename;
        if(!is_null($dir)) $filenameWithDir = $dir .'/'. $filename;
        
        if(!view()->exists($filenameWithDir)){
            fopen(base_path('resources/views/'.$filenameWithDir), 'w');
        }
        return;
    }

    /**
     * Convert array request with Stepanenko3\NovaJson\JSON fields in standard array
     * es INPUT: 
     * [ "name" => "prova", "title->it" => "prova titolo1", "title->en"=>null, "title->de"=>null ]
     * 
     * es OUTPUT: 
     * [ "name" => "prova", "title" => ["it"=>"prova titolo1", "en"=>null, "de"=>null], ]
     *
     * @param array $arInput
     * @return array
    */
    public function convertArrayRequest($arInput){
        $arOut = array();
        $arTmp = array();

        foreach($arInput as $key => $value){
            $pos = strpos($key, '->');
            if($pos === false){
                $arOut[$key] = $value;
            }else{
                $keyTmp = substr($key, 0, $pos);
                $subkeyTmp = substr($key, $pos+2);
                $valueTmp = $value;

                if(!in_array($keyTmp, array_keys($arTmp))){
                    $arTmp[$keyTmp] = [$subkeyTmp => $valueTmp];
                }else{
                    $valueTmp2 = $arTmp[$keyTmp];
                    $valueTmp2[$subkeyTmp] = $valueTmp;
                    $arTmp[$keyTmp] = $valueTmp2;
                }    
            }
        }

        return array_merge($arOut, $arTmp);
    }

    /**
     * Display data in input in different type
     *
     * @param array $data
     * @param string $typeView
     * @return string
    */
    public function displayData($data, $typeView){
        
        $outData='';
        if($typeView=='table'){
            $outData.='<table style="width:100%;border:1px solid">';
            foreach($data as $dataItem){
                $outData.='<tr>';
                $outData.='<td>';
                $outData.=strtoupper($dataItem['lang']);
                $outData.='</td>';
                $outData.='<td>';
                $outData.=$dataItem['url'];
                $outData.='</td>';
                $outData.='<td>';
                $outData.='<a href="'.$dataItem['lang'].'" target="_blank">APRI</a>';
                $outData.='</td>';
                $outData.='</tr>';
            }
            $outData.='</table>';
        }

        return $outData;
    }

    /**
     * Get the Nova Custom Fields List giving data from model CoreCustomField.
     *
     * @param  \App\Models\Core\CoreCustomField  $cf
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