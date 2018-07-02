<?php
class KintoneData extends CI_Model{
    public function formMailData($template,$input){
        
        //echo '<pre>';
        //var_dump($template);
        //echo '</pre>';
        $data = array();
        $lcnt=0;
        foreach($template as $line){
            if ($line['type']==='ROW'){
                foreach($line['fields'] as $each){
                    //var_dump($each);
                    if ($each['type']==='LABEL'){//ただのラベルの場合はそのまま流す
                        $data[$each['label']] = $each['label'];
                    }else{//データの場合はかみ合わせを行う
                        $data[$each['label']] = $input[$each['code']];
                    }
                }
            }elseif($line['type']==='SUBTABLE'){
                //Subtableを切り出す
                $putvar  = $input[$line['code']];
                unset($putvar['type']);
                //var_dump($line);
                $data[$line['code']] = $this->subtablesortformail($putvar,$line);
            }
            $lcnt=0;
        }
        
        //echo '<pre>';
        //var_dump($data);
        //echo '</pre>';
        
        return $data;
    }
    
    public function subtablesortformail($data,$template){
        //echo '<pre>';
        //var_dump($data);
        //echo '</pre>';
        //echo '<pre>';
        //var_dump($template);
        //echo '</pre>';
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
                //$new_data[$cnt]['value'][$keys[$i]] = $v;
                $new_data[$cnt]['value'][$template['fields'][$i]['label']] = $v;
                $i++;
            }
            $cnt++;
            $i=0;
        }
        //var_dump($new_data);
        
        return $new_data;
    }
    public function subtableSort($data){
        //echo '<pre>';
        //var_dump($data);
        //echo '<pre>';
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
                $new_data[$cnt]['value'][$keys[$i]] = $v;
                $i++;
            }
            $cnt++;
            $i=0;
        }
        //毎回[0]にKintoneのフォーム型情報が入るので配列を詰める.流れてくるのはサブテーブルの１行ごとのデータなので、[0], [1]の２行のみ。これのヘッダー情報だけを差し替える
        $new_data['type'] = $new_data[0];
        $new_data[0]= $new_data[1];
        unset($new_data[1]);
        
        //echo '<pre>';
        //var_dump($new_data);
        //echo '</pre>';
        
        return $new_data;
    }

    public function setup_subtable_data($data){
        $subtable_data = array();

        foreach ($data['records'][0] as $key => $var){
            //テーブルタイプをチェックして
            if ((isset($var['type']))&&($var['type']==='SUBTABLE')){
                //echo '<pre>';
                //var_dump($var['value']);
                //echo '</pre>';
                $i=0;
                foreach($var['value'] as $value){
                    $subtable_data[$key][$i] = $this->subtableSort($value['value']);
                    $i++;
                }
            }
        }
        
        //echo '<pre>';
        //var_dump($subtable_data);
        //echo '<pre>';
        
        return $subtable_data;
    }
    
    public function detectSubTables($fields){
        $retvar = array();
        
        foreach($fields as $line){
            if ($line['type']==='SUBTABLE'){
                array_push($retvar,$line['code']);
            }
        }
        
        return $retvar;
        
    }
    public function formupSubTableData($header,$data){
        $retvar = array();
        
        for ($i=0;$i<count($header);$i++){
            array_push($retvar,json_encode($data['records']['0'][$header[$i]]['value']));
        }
        
        return $retvar;
    }
}
?>