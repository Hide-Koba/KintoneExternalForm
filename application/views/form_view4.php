<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Page Title Here</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script>
        $(function () {
            <?php
            foreach($subtablesheader as $subtable){
                ?>
            $(document).on('click', '.<?php echo $subtable;?>_Add_Button', function(){
                console.log('add sequence start');
                //alert('<?php echo $subtable;?> CLICK!!!');
                var cnt = ($(".<?php echo $subtable;?>_Add_Button").length-1);
                console.log('<?php echo $subtable;?>_Add_Button: '+cnt);
                var original = $('#<?php echo $subtable;?>_' + cnt + '_');
                console.log(original);
                cnt++;
                original
                    .clone()
                    .hide()
                    .insertAfter(original)
                    .attr('id', '<?php echo $subtable;?>_' + cnt + '_') // クローンのid属性を変更。
                    //.find(\"input[type='radio'][checked]"\).prop('checked', true)
                    .end() // 一度適用する
                    .find('input, textarea, select, button').each(function(idx, obj) {
                    console.log($(obj).attr('id'));
                    $(obj).attr({
                        id: $(obj).attr('id').replace(/\[\d{0,3}\]+$/, '\[' + cnt + '\]'),
                        name: $(obj).attr('name').replace(/\[\d{0,3}\]+$/, '\[' + cnt + '\]')
                    });
                    if ($(obj).attr('type') == 'text') {
                        $(obj).val('');
                    }
                });

                // clone取得
                var clone = $('#<?php echo $subtable;?>_' + cnt + '_');
                clone.children('span.close').show();
                clone.show();
                console.log('add sequence end');
                console.log('');

                  // originalラジオボタン復元
                  //original.find(\"input[name='sex\\[\" + originCnt + \"\\]'][value='\" + originVal + \"']\").prop('checked', true);
            });
            $(document).on('click', '.<?php echo $subtable;?>_close', function(){
                //alert('close clicked!!');
                console.log('delete sequence start');
                var deleteTargetID = $(this).parent().parent().parent().parent().attr("id");
                var originalID = '<?php echo $subtable;?>_0_';
                if (deleteTargetID===originalID){//0番目のフォームは消さない
                    return false;
                }
                var removeObj = $(this).parent().parent().parent().parent();
                removeObj.fadeOut('fast', function() {
                    removeObj.remove();
                    // 番号振り直し
                    var frm_cnt = 0;
                    $(".<?php echo $subtable;?>").each(function(index, formObj) {
                        $(formObj)
                            .attr('id', '<?php echo $subtable;?>_' + frm_cnt + '_') // id属性を変更。
                            .find('input, textarea, select, button').each(function(idx, obj) {
                                $(obj).attr({
                                    id: $(obj).attr('id').replace(/\[\d{0,3}\]+$/, '\[' + frm_cnt + '\]'),
                                    name: $(obj).attr('name').replace(/\[\d{0,3}\]+$/, '\[' + frm_cnt + '\]')
                                });
                            });
                        frm_cnt++;
                    });
                });
                console.log('delete sequence end');
                console.log('');
            });
                <?php
            }
            ?>
        });
    </script>
</head>
<body>
<div id="container">
    <?php
        //フィールドの最大カウントを取得
        $field_max_count = 0;
        foreach($field_data as $line){
            if (count($line['fields'])>$field_max_count) $field_max_count = count($line['fields']);
        }

        //echo $field_max_count;
        $label_count=0;
    ?>
	<h1>School Questionnaire</h1>
    
    <?php
    $label_count=0;
    ?>

	<div id="body">
        <form action='' method='post'>
            <table border="1" width="100%">
                <?php
                    //echo '<pre>';
                    //var_dump($field_data);
                    //echo '</pre>';
                    foreach($field_data as $line){
                        
                        echo '<tr>';
                        if ($line['type']==='SUBTABLE'){
                            $marge_count = $field_max_count;
                            //echo '<td colspan="'.$marge_count.'">';
                            echo '<td>';
                            echo '<input type="hidden" value="SUBTALBE" name="'.$line['code'].'[type]" />';
                            //echo '<div width="100%" class="'.$line['code'].'_0_" id="'.$line['code'].'_0_">';
                            echo '<table class="'.$line['code'].'" id="'.$line['code'].'_0_"><tr><td>';
                            echo '<button name="delete_button" id="'.$line['code'].'_delete_Button[0]" type="button" width="'.$merge_percentage.'%" class="'.$line['code'].'_close" title="Close" style"display:none;float:left;"> 削除 </button></td></tr>';
                            foreach($line['fields'] as $column){
                                echo '<tr><td>'.$column['label'].'</td>';
                                if ($column['type']!=='LABEL'){
                                    switch($column['type']){
                                        case 'DATE':
                                        case 'SINGLE_LINE_TEXT':
                                            echo '<td>';
                                            echo '<input type="text" name="'.$line['code'].'['.$column['code'].'][0]" id="'.$column['code'].'[0]"/>';
                                            echo '</td>';
                                            echo '</tr>';
                                            break;

                                        case 'DROP_DOWN':
                                            echo '<td>';
                                            //echo '<input type="hidden" value="'.$column['label'].'">';
                                            echo '<select name="'.$line['code'].'['.$column['code'].'][0]" id="'.$column['code'].'[0]" />';
                                            foreach($column['options'] as $each_option){
                                                echo '<option value="'.$each_option.'">'.$each_option.'</option>';
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            break;
                                        default:
                                            break;
                                    }
                                }

                            }

                            echo '<tr><td><button id="'.$line['code'].'_Add_Button[0]" name="add_button" type="button" width="'.$merge_percentage.'%" class="'.$line['code'].'_Add_Button" title="Add" style"display:none;float:left;"> 追加 </button></td></tr></table>';
                            
                            echo '</div>';
                            echo '</td>';
                            echo '</tr>';
                        }else{
                            ////////////////////////////////////////////////////////////////
                            //SUBTABLE以外のフォーム構成要素
                            ////////////////////////////////////////////////////////////////
                            foreach($line['fields'] as $column){
                                $column_count = (count($line['fields']));
                                $marge_count = $field_max_count/$column_count;
                                //echo '<pre>';
                                //var_dump($column);
                                //echo '</pre>';
                                //echo '<td colspan="'.$marge_count.'">'.$column['label'].'</td>';
                                echo '<td>'.$column['label'].'</td>';
                                if ($column['type']==='LABEL'){
                                    continue;
                                }
                                switch($column['type']){
                                    case 'DATE':
                                    case 'SINGLE_LINE_TEXT':
                                        echo '<td>';
                                        //echo '<input type="text" name="'.$column['code'].'" /><input type="hidden" value="'.$column['label'].'">';
                                        if ($kt_data['records'][0][$column['code']]['value']===null){
                                            echo '<input type="text" name="'.$column['code'].'" value="" />';
                                        }else{
                                            echo '<input type="text" name="'.$column['code'].'" value="'.$kt_data['records'][0][$column['code']]['value'].'" />';
                                        }
                                        
                                        echo '</td>';
                                        break;

                                    case 'DROP_DOWN':
                                        echo '<td>';
                                        echo '<select name="'.$column['code'].'" />';
                                        foreach($column['options'] as $each_option){
                                            if ($kt_data['records'][0][$column['code']]['value']===null){
                                                echo '<option value="'.$each_option.'">'.$each_option.'</option>';
                                            }elseif($each_option===$kt_data['records'][0][$column['code']]['value']){
                                                echo '<option value="'.$each_option.'" selected>'.$each_option.'</option>';
                                            }else{
                                                echo '<option value="'.$each_option.'">'.$each_option.'</option>';
                                            }
                                            //echo '<option value="'.$each_option.'" selected>'.$each_option.'</option>';
                                        }
                                        echo '</td>';
                                        break;
                                        
                                    case 'RADIO_BUTTON':
                                        echo '<td>';
                                        foreach($column['options'] as $each_option){
                                            if ($kt_data['records'][0][$column['code']]['value']===null){
                                                echo '<input type="radio" name="'.$column['code'].'" value="'.$each_option.'">'.$each_option.'<br/>';
                                            }elseif ($kt_data['records'][0][$column['code']]['value']===$each_option){
                                                echo '<input type="radio" name="'.$column['code'].'" value="'.$each_option.'" checked>'.$each_option.'<br/>';
                                            }else{
                                                echo '<input type="radio" name="'.$column['code'].'" value="'.$each_option.'">'.$each_option.'<br/>';
                                            }
                                            //echo '<input type="radio" name="'.$column['code'].'" value="'.$each_option.'" checked>'.$each_option;
                                        }
                                        echo '</td>';
                                        break;
                                        
                                    case 'MULTI_LINE_TEXT':
                                        echo '<td>';
                                        if ($kt_data['records'][0][$column['code']]['value']===null){
                                            echo '<textarea name="'.$column['code'].'" rows="4" cols="40"></textarea>';
                                        }else{
                                            echo '<textarea name="'.$column['code'].'" rows="4" cols="40">'.$kt_data['records'][0][$column['code']]['value'].'</textarea>';
                                        }
                                        
                                        echo '</td>';
                                        break;
                                        
                                    case 'CHECK_BOX':
                                        echo '<td>';
                                        foreach($column['options'] as $each_option){
                                            if ($kt_data['records'][0][$column['code']]['value']===null){
                                                echo '<input type="checkbox" name="'.$column['code'].'[]" value="'.$each_option.'">'.$each_option.'<br/>';
                                            }else if ((array_search($each_option,$kt_data['records'][0][$column['code']]['value']))!==false){
                                                echo '<input type="checkbox" name="'.$column['code'].'[]" value="'.$each_option.'" checked>'.$each_option.'<br/>'; 
                                            }else{
                                                echo '<input type="checkbox" name="'.$column['code'].'[]" value="'.$each_option.'">'.$each_option.'<br/>';
                                            }
                                            //echo '<input type="checkbox" name="'.$column['code'].'[]" value="'.$each_option.'" checked>'.$each_option;
                                        }
                                        echo '</td>';
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }

                        echo '</tr>';
                        $cnt++;
                    }
                ?>
                <tr>
                
                    <td>Save</td><td>
                    <?php
                        if ($uuid===null){
                            echo '<button class="submit" data-action="'.$base_url.'index.php/Questionnaire/save">Save</button></td>';
                        }else{
                            echo '<button class="submit" data-action="'.$base_url.'index.php/Questionnaire/save?id='.$uuid.'">Save</button></td>';
                        }
                    ?>
                    
                </tr>
                <tr>
                    <td>Submit</td>
                    <td>
                    <?php
                        if ($uuid===null){
                            echo '<button class="submit" data-action="'.$base_url.'index.php/Questionnaire/submit">Submit</button>';
                        }else{
                            echo '<button class="submit" data-action="'.$base_url.'index.php/Questionnaire/submit?id='.$uuid.'">Submit</button>';
                        }
                    ?>
                    
                    </td>
                </tr>
            </table>
            
            </form>
	</div>
</div>
<script>
    $('.submit').click(function(){
        $(this).parents('form').attr('action', $(this).data('action'));
        $(this).parents('form').submit();
    });
</script>
<script>
    //$(window).load(function(){
    $(document).ready(function(){
    <?php
    if (isset($kt_data)){
        $subtable = $this->KD->setup_subtable_data($kt_data);
        //echo '<pre>';
        //var_dump($subtable);
        //echo '</pre>';
        $j=0;
        //var_dump($subtable);
        foreach($subtable as $key=>$value){
            //外部ループ。各サブテーブルの処理
            $table_name = $key;
            $type = $value[0]['type']['value'];
            //var_dump($type);
            
            foreach($value as $k=>$v){
                //内部ループ。各テーブルの１行
                //初回はフォームを追加しない
                if ($j!==0){
                    //echo "$('#".$table_name."_Add_Button[0]').click();\n";
                    echo "document.getElementById('".$table_name."_Add_Button[0]').click();";
                    //echo "$('.".$table_name."_Add_Button[0]').click();\n";
                    //echo "$(document).on('click', '.".$table_name."_Add_Button', function() {alert('クリック！');});";
                    echo 'console.log("clicked");';
                }
                foreach($v[0]['value'] as $column_code=>$column_data){
                    //各行内の項目の処理
                    $column_type = $type[$column_code];
                    switch($column_type){
                        case 'DROP_DOWN':
                            echo "$('select[name=\"".$table_name."[".$column_code."][".$j."]\"]').val('".$column_data."');\n";
                            break;
                        case 'SINGLE_LINE_TEXT':
                        case 'DATE':
                            //echo "$(':text[id="Table_Text[0]"]').val("testDEF");"
                            //最後の最後で日付のデコード。YYYY-MM-DDをDD/MM/YYYYへ
                            if (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}/",$column_data)){
                                $column_data = $column_data;
                            }
                            echo "$(':text[name=\"".$table_name."[".$column_code."][".$j."]\"]').val('".$column_data."');\n";
                            break;
                        default:
                            break;
                    }
                }
                
                
                $j++;
                
            }
            $j=0;
        }
    ?>
        
    <?php
    }
    ?>
    });
</script>
</body>
</html>