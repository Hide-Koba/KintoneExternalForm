<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

class KintoneComm extends CI_Model{
    
    public $id = 0;

    private $login_key = '';
    public $subdomain = '';
    public $apiToken = '';
    
    public $form_template = array(
        'type'=>'',
        'value'=>'',
        'option'=>array(),
        'code'=>''
    );
    
    public function __construct(){
        parent::__construct();
        $this->login_key = 'systeminfo@study-au.com:Homestay1994';/*★*/
        $this->subdomain = 'study-au';/*★*/
    }
    public function setId($id){
        $this->id = $id;
    }
    public function setApiToken($token=''){
        if ($token===''){
            return false;
        }
        $this->apiToken=$token;
    }
    
    

    public function getLayout(){
        $options = array(
            'http'=>array(
                'method'=>'GET',
                'header'=> "Host: ".$this->subdomain.".cybozu.com:433\r\n"
                ."X-Cybozu-Authorization: ". base64_encode($this->login_key)
                ."\r\n".'Content-Type: application/json'."\r\n",
              'content'=>json_encode(
                array(
                    'app'=>$this->id,
                )
              ),
            'ignore_errors'=>true
            )
        );
        $context = stream_context_create( $options );
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/app/form/layout.json';
        
        $layout_raw = file_get_contents( $url, FALSE, $context );
        //var_dump($layout_raw);
        $layout = json_decode($layout_raw,true);
        
        return $layout;
    }
    
    public function getFormDetails(){
        $options = array(
            'http'=>array(
                'method'=>'GET',
                'header'=> "Host: ".$this->subdomain.".cybozu.com:433\r\n"
                ."X-Cybozu-Authorization: ". base64_encode($this->login_key)
                ."\r\n".'Content-Type: application/json'."\r\n",
              'content'=>json_encode(
                array(
                    'app'=>$this->id,
                )
              ),
            'ignore_errors'=>true
            )
        );
        $context = stream_context_create( $options );
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/form.json';
        
        $field_details = file_get_contents( $url, FALSE, $context );
        $details = json_decode($field_details,true);
        
        return $details;
    }
    
    public function genFormData($layout,$data){
        //echo '<pre>';
        //var_dump($data);
        //echo '</pre>';
        
        $cnt=0;
        //echo count ($layout);
        $record = true;
        $form_layout = array();
        $lineCnt = 0;
        $j=0;
        
        
        //開始前に全行検査を行って、もし制御セクションがあるのであれば全行を表示しない。
        //制御セクションがない場合はすべての行を表示する
        foreach($layout['layout'] as $line){
            if (isset($line['fields'])){
                if ($line['fields'][0]['type']==='LABEL'){
                    if (preg_match('/\WSection_Start\W/', $line['fields'][0]['label'], $m)){$record = false; continue;}
                }
            }
        }
        
        //設定引き上げセクション開始
        foreach($layout['layout'] as $line){
            //echo '<pre>';
            //var_dump($line);
            //echo '</pre>';
            //制御判定部分. RECのON/OFFをスイッチする
            if (isset($line['fields'])){
                if ($line['fields'][0]['type']==='LABEL'){
                    //echo $line['fields'][0]['label'].'<br />';
                    if (preg_match('/\WSection_Start\W/', $line['fields'][0]['label'], $m)){$record = true; continue;}
                    if (preg_match('/\WSection_END\W/', $line['fields'][0]['label'], $m)){$record = false; continue;}
                }
            }
            
            if (($record)){
            //if (($record)||($line['fields'][0]['code']==='UUID')){
                foreach ($line['fields'] as $each){
                    //各行にあるフィールドレイアウトのコードから
                    //echo '<pre>';
                    //var_dump($each);
                    //echo '</pre>';
                    if (isset($each['code'])){
                        foreach($data['properties'] as $d){
                            //echo '<pre>';
                            //var_dump($d);
                            //echo '</pre>';
                            //echo $each['code'];
                            //echo '<br />';
                            if (array_search($each['code'],$d)){//通常の項目の場合
                                //echo '<pre>';
                                //var_dump($d);
                                //echo '</pre>';
                                
                                $line['fields'][$j]['label'] = $d['label'];
                                
                                //Radio, Dropdownの対応
                                if (isset($d['options'])){
                                    $line['fields'][$j]['options'] = $d['options'];
                                }
                                if (isset($d['defaultValue'])){
                                    $line['fields'][$j]['defaultValue'] = $d['defaultValue'];
                                }
                                continue;
                            }elseif($d['type']==='SUBTABLE'){//もし検索対象のデータがサブテーブルを持っていた場合はもう一段ネストする必要がある
                                
                                foreach($d['fields'] as $sub_item){
                                    
                                    if (array_search($each['code'],$sub_item)){
                                        //echo array_search($each['code'],$sub_item);
                                        //var_dump($line['fields'][$j]['options']);
                                        
                                        $line['fields'][$j]['label'] = $sub_item['label'];

                                        //サブテーブル内でのRadio, Dropdownの対応
                                        if (isset($sub_item['options'])){
                                            $line['fields'][$j]['options'] = $sub_item['options'];
                                        }                                        
                                        if (isset($sub_item['defaultValue'])){
                                            $line['fields'][$j]['defaultValue'] = $sub_item['defaultValue'];
                                        }
                                        
                                    }
                                }
                                continue;
                            }
                        }
                    }
                    $j++;
                }
                
                $form_layout[$lineCnt] = $line;
                //echo $lineCnt.'<br>';
                //echo '<pre>';
                //var_dump($form_layout[$lineCnt]);
                //echo '</pre>';
                $j=0;
                $lineCnt++;
            }
            
            $cnt++;
            
        }
        
        //echo '<pre>';
        //var_dump($form_layout);
        //echo '</pre>';
        
        return $form_layout;
    }
    
    public function genFormHtml($structure){
        //echo '<pre>';
        //var_dump($structure);
        //echo '</pre>';
        $html = array();
        $cnt = 0;
        $j = 0;
        foreach($structure as $line){
            if (!isset($html[$cnt][$j])){
                $html[$cnt][$j]='';
            }
            if ($line['type']==='SUBTABLE'){
                
                $html[$cnt][$j].='<div class="'.$line['code'].'" id="'.$line['code'].'">';
                foreach($line['fields'] as $item){
                    switch($item['type']){
                        case 'LABEL':
                            $html[$cnt][$j] = $item['label'];
                            break;
                        case 'DATE':
                        case 'SINGLE_LINE_TEXT':
                            $html[$cnt][$j] .= $item['label'].'<input type="text" name="'.$line['code'].'\['.$item['code'].'\]" />';
                            break;
                        case 'DROP_DOWN':
                            $html[$cnt][$j] .= $item['label'].'<select name="'.$item['code'].'" />';
                            foreach($item['options'] as $each_option){
                                $html[$cnt][$j] .= '<option value="'.$each_option.'">'.$each_option.'</option>';
                            }
                            $html[$cnt][$j].= '</select>';
                            break;
                        case 'RADIO_BUTTON':
                            $html[$cnt][$j] .= $item['label'];
                            foreach($item['options'] as $each_option){
                                //$html[$cnt][$j] .= '<option value="'.$each_option.'">'.$each_option.'</option>';
                                $html[$cnt][$j] .= '<input type="radio" name="'.$item['code'].'" value="'.$each_option.'">'.$each_option;
                            }
                            break;
                        case 'MULTI_LINE_TEXT':
                            $html[$cnt][$j] .= $item['label'].'<textarea name="'.$item['code'].'" rows="4" cols="40"></textarea>';
                            break;
                        default:
                            break;
                    }
                    
                    $j++;
                }
                $html[$cnt][$j].='</div>';
            }else{
                foreach($line['fields'] as $item){
                    switch($item['type']){
                        case 'LABEL':
                            $html[$cnt][$j] = $item['label'];
                            break;
                        case 'DATE':
                        case 'SINGLE_LINE_TEXT':
                            $html[$cnt][$j] .= $item['label'].'<input type="text" name="'.$item['code'].'" />';
                            break;
                        case 'DROP_DOWN':
                            $html[$cnt][$j] .= $item['label'].'<select name="'.$item['code'].'" />';
                            foreach($item['options'] as $each_option){
                                $html[$cnt][$j] .= '<option value="'.$each_option.'">'.$each_option.'</option>';
                            }
                            $html[$cnt][$j].= '</select>';
                            break;
                        case 'RADIO_BUTTON':
                            $html[$cnt][$j] .= $item['label'];
                            foreach($item['options'] as $each_option){
                                //$html[$cnt][$j] .= '<option value="'.$each_option.'">'.$each_option.'</option>';
                                $html[$cnt][$j] .= '<input type="radio" name="'.$item['code'].'" value="'.$each_option.'">'.$each_option;
                            }
                            break;
                        case 'MULTI_LINE_TEXT':
                            $html[$cnt][$j] .= $item['label'].'<textarea name="'.$item['code'].'" rows="4" cols="40"></textarea>';
                            break;

                        default:
                            break;
                    }
                    $j++;
                }
            }
            $j=0;
            $cnt++;
        }
        
        return $html;
    }
    
    private $api_key='';
    private $app_id = 0;
    private $app_record = array();
    
    public function setAPICom($key='',$id=0,$subdomain=''){
        if ($key===''){
            return false;
        }
        
        $this->api_key = $key;
        $this->app_id = $id;
        $this->subdomain = $subdomain;
        
        return true;
    }
    
    public function send_to_Student($data){
        $app_id = 14;/*★*/
        $api_token = 'vEOZrzpbBMPZ17ao0RZcIPYihSnqlxEe3gJFwBSY';/*★*/
        $sub_domain = 'gb5tm';/*★*/
        $options = array(
            'http'=>array(
                'method'=>'POST',//今回は追加
                'header'=> "X-Cybozu-API-Token:". $this->api_token ."\r\n".'Content-Type: application/json',
                'content'=>json_encode(
                    array(
                        'app'=>"$app_id",
                        'record'=>$data
                        )
                    )
                )
        );
        //echo '<pre>';
        //var_dump($options);
        //echo '</pre>';
        
        $context = stream_context_create( $options );
        // サーバに接続してデータを貰う
        $url =  'https://'. $sub_domain .'.cybozu.com/k/v1/record.json';
        //$contents = file_get_contents( 'https://'. SUB_DOMAIN .'.cybozu.com/k/v1/record.json?app='. APP_NO , FALSE, $context );
        $contents = file_get_contents( $url, FALSE, $context );
        
        return $contents;
        
    }
    
    public function subtableSort($data){
        //var_dump($data);
        //変数の縦横を変換。この段階ではまだ各キーが連想配列ではない
        $sorted = call_user_func_array('array_map',array_merge(array(null), $data ));

        //連想配列にするために各キーを取得
        $keys = array();
        foreach($data as $key=>$var){
            array_push($keys,$key);
        }

        //キー名をつけて新しい変数にコピー & 合成
        $cnt = 0;
        $i=0;
        $new_data = array();
        foreach($sorted as $key=>$var){
            foreach($var as $k=>$v){
                $temp = array($keys[$i]=>$v);
                if (preg_match("/^[0-9]{1,2}(\/|-)[0-9]{1,2}(\/|-)[0-9]{4}/",$v)){
                    //$v = $this->dateConvertToKT($v);
                }
                $new_data[$cnt]['value'][$keys[$i]] = $v;
                $i++;
            }
            $cnt++;
            $i=0;
        }
        
        return $new_data;
    }
    
    public function dateConvertToKT($date){
        //日付処理 DD/MM/YYYYをYYYY-MM-DDへ
        $calender = explode("/",$date);
        $date = $calender[2].'-'.$calender[1].'-'.$calender[0];
        return $date;
    }
    
    public function dateConvertToForm($date){
         //日付処理 YYYY-MM-DDをDD/MM/YYYYへ
        $calender = explode("-",$date);
        $date = $calender[2].'/'.$calender[1].'/'.$calender[0];
        return $date;
    }
    
    public function postRecord($data){
        //その場で組み立てること！！！！！何故か…うーん…(´・ω・｀)
        
        //var_dump($this->app_record);
        //$record = json_encode($this->app_record);
        //$record = $this->app_record;
        unset($data['mode']);
        unset($data['0']);
        
        //echo "test<br />";
        //アップデート用のデータ用意
        $cnt = 0;
        //echo '<pre>';
        //var_dump($data);
        //echo '</pre>';
        foreach($data as $key=>$val){
            if ((isset($val['type']))&&($val['type']==='SUBTALBE')){
                unset($val['type']);
                $dat[$key] = array('value'=>$this->subtableSort($val));
            }else{
                if (!is_array($val)){
                    if (preg_match("/^[0-9]{1,2}(\/|-)[0-9]{1,2}(\/|-)[0-9]{4}/",$val)){
                        //$val = $this->dateConvertToKT($val);
                    }
                }
                $dat[$key] = array('value'=>$val);
            }
            
            $cnt++;
        }
        
        if (isset($data['UUID'])){
            //レコードがあるかどうかをUUIDから判定, IDを取得
            $result = json_decode($this->getKTData($data['UUID']),true);
        }else{
            $result = array();
        }
        
        
        //var_dump($result);
        //echo "result:".$result;
        //echo count($result['records']);
        if (count($result['records'])===0){
            //echo "no record";//新しいレコードとして登録

            $options = array(
                'http'=>array(
                    'method'=>'POST',
                    'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                    'ignore_errors'=>true,
                    'content'=>json_encode(
                        array(
                            'app'=>$this->app_id,
                            'record'=>$dat
                        )
                    ),
                ),
            );
        }else{
            //echo "there is some";
            //IDを取得してアップデーととして登録！
            $this_id = $result['records'][0]['$id']['value'];
            $options = array(
                'http'=>array(
                    'method'=>'PUT',
                    'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                    'ignore_errors'=>true,
                    'content'=>json_encode(
                        array(
                            'app'=>$this->app_id,
                            'id'=>$this_id,
                            'record'=>$dat
                        )
                    ),
                ),
            );
        }
        
        
        
        //echo '<pre>';
        //var_dump($options);
        //echo '</pre>';
        //exit();
        $context = stream_context_create( $options );
        //var_dump($context);
        // サーバーに接続してデータを送信
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/record.json';
        $contents = json_decode(file_get_contents( $url, FALSE, $context ),true);
        
        echo '<pre>';
        var_dump($contents);
        echo '</pre>';
        //exit();
        return $contents;
    }

    public function postRecord4q($data){
        //その場で組み立てること！！！！！何故か…うーん…(´・ω・｀)
        
        //var_dump($this->app_record);
        //$record = json_encode($this->app_record);
        //$record = $this->app_record;
        unset($data['mode']);
        unset($data['0']);
        
        //echo "test<br />";
        //アップデート用のデータ用意
        $cnt = 0;
        //echo '<pre>';
        //var_dump($data);
        //echo '</pre>';
        foreach($data as $key=>$val){
            if ((isset($val['type']))&&($val['type']==='SUBTALBE')){
                unset($val['type']);
                $dat[$key] = array('value'=>$this->subtableSort($val));
            }else{
                if (!is_array($val)){
                    if (preg_match("/^[0-9]{1,2}(\/|-)[0-9]{1,2}(\/|-)[0-9]{4}/",$val)){
                        //$val = $this->dateConvertToKT($val);
                    }
                }
                $dat[$key] = array('value'=>$val);
            }
            
            $cnt++;
        }
        
        if (isset($data['UUID'])){
            //レコードがあるかどうかをUUIDから判定, IDを取得
            $result = json_decode($this->getKTData($data['UUID']),true);
        }else{
            $result = array();
        }
        
        
        //var_dump($result);
        //echo "result:".$result;
        //echo count($result['records']);
        if (count($result['records'])===0){
            //echo "no record";//新しいレコードとして登録
            $uuid = uniqid().'-'.uniqid().'-'.uniqid().'-'.uniqid().uniqid();
            $dat['UUID']['value'] = $uuid;
            $options = array(
                'http'=>array(
                    'method'=>'POST',
                    'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                    'ignore_errors'=>true,
                    'content'=>json_encode(
                        array(
                            'app'=>$this->app_id,
                            'record'=>$dat
                        )
                    ),
                ),
            );
        }else{
            //echo "there is some";
            //IDを取得してアップデーととして登録！
            $this_id = $result['records'][0]['$id']['value'];
            $options = array(
                'http'=>array(
                    'method'=>'PUT',
                    'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                    'ignore_errors'=>true,
                    'content'=>json_encode(
                        array(
                            'app'=>$this->app_id,
                            'id'=>$this_id,
                            'record'=>$dat
                        )
                    ),
                ),
            );
        }
        
        
        
        //echo '<pre>';
        //var_dump($options);
        //echo '</pre>';
        //exit();
        $context = stream_context_create( $options );
        //var_dump($context);
        // サーバーに接続してデータを送信
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/record.json';
        $contents = json_decode(file_get_contents( $url, FALSE, $context ),true);
        
        echo '<pre>';
        var_dump($contents);
        echo '</pre>';
        //exit();
        return $contents;
    }
    
    public function getKTData($id){
        $str = 'UUID = "'.$id.'"';
        $options = array(
            'http'=>array(
                'method'=>'GET',
                'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                'ignore_errors'=>true,
                'content'=>json_encode(
                    array(
                        'app'=>$this->app_id,
                        'query'=>$str
                    )
                ),
            ),
        );
        //echo '<pre>';
        //var_dump($options);
        //echo '</pre>';
        $context = stream_context_create( $options );
        //var_dump($context);
        // サーバーに接続してデータを送信
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/records.json';
        $contents = file_get_contents( $url, FALSE, $context );
        
        return $contents;
    }

    public function getKTDataByQuery($query){
        $options = array(
            'http'=>array(
                'method'=>'GET',
                'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                'ignore_errors'=>true,
                'content'=>json_encode(
                    array(
                        'app'=>$this->app_id,
                        'query'=>$query
                    )
                ),
            ),
        );
        echo '<pre>';
        var_dump($options);
        echo '</pre>';
        $context = stream_context_create( $options );
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/records.json';
        $contents = file_get_contents( $url, FALSE, $context );
        
        return $contents;
    }

    public function findAll(){
        $options = array(
            'http'=>array(
                'method'=>'GET',
                'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                'ignore_errors'=>true,
                'content'=>json_encode(
                    array(
                        'app'=>$this->app_id,
                    )
                ),
            ),
        );
        echo '<pre>';
        var_dump($options);
        echo '</pre>';
        $context = stream_context_create( $options );
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/records.json';
        $contents = file_get_contents( $url, FALSE, $context );
        
        return $contents;
    }

    public function findUUIDById($id){
        $options = array(
            'http'=>array(
                'method'=>'GET',
                'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                'ignore_errors'=>true,
                'content'=>json_encode(
                    array(
                        'app'=>$this->app_id,
                        'id'=>$id,
                    )
                ),
            ),
        );
        $context = stream_context_create( $options );
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/record.json';
        $contents = json_decode(file_get_contents( $url, FALSE, $context ),true);

        return $contents['record']['UUID']['value'];
    }

    public function getAllKTDataByQuery($query,$limit=500){
        
        $url =  'https://'. $this->subdomain .'.cybozu.com/k/v1/records.json';
        $offset = 0;
        $retvar = array();
        $retrievecount = 0;
        do{
            $options = array(
                'http'=>array(
                    'method'=>'GET',
                    'header'=> "X-Cybozu-API-Token:". $this->api_key ."\r\n".'Content-Type: application/json',
                    'ignore_errors'=>true,
                    'content'=>json_encode(
                        array(
                            'app'=>$this->app_id,
                            'query'=>$query.'limit '.$limit.' offset '.$offset,
                        )
                    ),
                ),
            );
            $context = stream_context_create( $options );
            $contents = json_decode(file_get_contents( $url, FALSE, $context ),true);
            for ($cnt=0;$cnt<count($contents['records']);$cnt++){
            //while(list($each) = each($contents['records'])) {
            //foreach ($contents['records'] as $each){
                array_push($retvar,$contents['records'][$cnt]);
                unset($contents['records'][$cnt]);
            }
            $offset+=count($contents['records']);
            $retrievecount = count($contents['records']);
        }while($retrievecount===$limit);
        return $retvar;
    }
}
?>