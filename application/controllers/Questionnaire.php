<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Questionnaire extends CI_Controller {

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
	public function index()
	{
        $this->load->Model('KintoneComm','KC');
        $this->KC->setId(APP_ID);//Set AppID
        $this->KC->setAPICom('[API Token]',APP_ID,'[Subdomain]');//Set API Token, AppID, Subdomain
        $field_data = $this->KC->genFormData($this->KC->getLayout(),$this->KC->getFormDetails());
        
        $kt_data = array();
        if (isset($_REQUEST['id'])){
            $kt_data = json_decode($this->KC->getKTData($_REQUEST['id']),true);
        }else{
            //$kt_data = array('kterror'=>'No ID');
        }
        //Combert Form data
        $this->load->Model('KintoneData','KD');
        $subtableHeader = $this->KD->detectSubTables($field_data);
        $subtableData = $this->KD->formupSubTableData($subtableHeader,$kt_data);

        $this->load->helper('url');
        $data = array(
            'field_data'=>$field_data,
            'kt_data'=>$kt_data,
            'subtablesheader'=>$subtableHeader,
            'uuid'=>$_REQUEST['id'],
            'subtabledata'=>$subtableData,
            'base_url'=>base_url()
        );
        $this->load->view('form_view4',$data);
	}
    
    public function save(){
        $this->load->Model('KintoneComm','KC');
        $this->KC->setId(APP_ID);//Set AppID
        $this->KC->setAPICom('[API Token]',APP_ID,'[Subdomain]');//Set API Token, AppID, Subdomain
        
        //Prepare Data
        $data = $_POST;
        
        //Resolve id in request
        $uuid = '';
        if (isset($_REQUEST['id'])){
            //id ID exists -> settle ID
            $uuid = $_REQUEST['id'];
            $data['UUID'] = $uuid;
        }else{
            //Nothing, If addtional action for UUID, add here
        }


        $result = $this->KC->postRecord4q($data);//Save into Kintone
        if (($uuid==='')&&(isset($result['id']))){
            $uuid = $this->KC->findUUIDById($result['id']);
        }
        $this->load->helper('url');
        redirect('Questionnaire?id='.$uuid);
    }
    
    public function submit(){
        $this->load->Model('KintoneComm','KC');
        $this->KC->setId(APP_ID);//Set AppID
        $this->KC->setAPICom('[API Token]',APP_ID,'[Subdomain]');//Set API Token, AppID, Subdomain
        
        $data = $_POST;
        if (isset($_REQUEST['id'])){
            //id ID exists -> settle ID
            $uuid = $_REQUEST['id'];
            $data['UUID'] = $uuid;
        }else{
            //Nothing, If addtional action for UUID, add here
        }
        $result = $this->KC->postRecord4q($data);//Save into Kintone
        
        //Send completion notice email
        $mail_body = $this->formupMail($data);

        //Load plugin for sendmail
        include ('qdmail.php');

        $result = qd_send_mail('html','[Sender_email]','[mail title]',$mail_body,'[Receipent Address]');//Input sender mail address, Title, Receiver email address to blank

        $this->load->helper('url');
        redirect('Questionnaire/submit_screen?id='.$uuid);
    }
    
    public function submit_screen(){
        $this->load->view('success_q');
    }
    
    public function formupMail($data){
        $this->KC->setId(APP_ID);//Set AppID
        $this->KC->setAPICom('[API Token]',APP_ID,'[Subdomain]');//Set API Token, AppID, Subdomain
        $layout = $this->KC->getLayout();
        $form_data= $this->KC->getFormDetails();
        $field_data = $this->KC->genFormData($layout,$form_data);
        
        $this->load->Model('KintoneData','KD');
        
        //Formup mail template
        $datas = $this->KD->formMailData($field_data,$data);
        
        //Extract mail body
        $mail_body = $this->extractMailBodyS($datas);
        
        return $mail_body;
    }
    public function extractMailBodyS($datas){
        $mail_body = '';
        $mail_body .= '';//Input mail body
        $name = '';//Prepare name of input subject
        foreach ($datas as $header=>$values){
            if (is_array($values)){
            }else{
                if ($header==="Name of Institute"){
                    if ($values!==""){
                        $name = $values;
                    }
                }
            }
        }
        
        $mail_body = $name . $mail_body . PHP_EOL .  ' Form_ID: ' . $_GET['id'];
        
        return $mail_body;
    }
}
