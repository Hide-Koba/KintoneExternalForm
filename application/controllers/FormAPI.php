<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FormAPI extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index(){
        $subdomain = 'study-au';
        $api_key = 'f2b7RCRubRApp26JzMdXUfTXZcw6slHRBG3yIQTW';
        $appID = 42;
        
        //$j_input = file_get_contents('php://input');
        //$parsed_input = json_decode($j_input,true);
        var_dump($_POST);
        $options = array(
            'http'=>array(
                'method'=>'POST',
                'header'=> "X-Cybozu-API-Token:". $api_key ."\r\n".'Content-Type: application/json',
                'ignore_errors'=>true,
                'content'=>json_encode(
                    array(
                        'app'=>$appID,
                        'record'=>array(
                            'URL'=>array(
                                'value'=>$_POST['URL']
                            ),
                            'Device_Type'=>array(
                                'value'=>$_POST['Device_Type']
                            ),
                            'Browser_Type'=>array(
                                'value'=>$_POST['Browser_Type']
                            )
                        )
                    )
                ),
            ),
        );
        
        $context = stream_context_create( $options );
        //var_dump($context);
        // サーバーに接続してデータを送信
        $url =  'https://'. $subdomain .'.cybozu.com/k/v1/record.json';
        $contents = json_decode(file_get_contents( $url, FALSE, $context ),true);
        var_dump($contents);
    }
}