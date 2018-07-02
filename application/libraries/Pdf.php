<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//TCPDF
require_once dirname(__FILE__) . '/tcpdf/tcpdf.php';

//FDPI
require_once dirname(__FILE__) . '/fpdi/fpdi.php';

//fpdi/Fpdi.php

class Pdf extends TCPDF
{
    function __construct()
    {
        parent::__construct();
    }
}

//This file and TCPDF library implementations are based on https://github.com/bcit-ci/CodeIgniter/wiki/TCPDF-Integration
//Then download fdpi2.0 from official site and implement in
/* End of file Pdf.php */
/* Location: ./application/libraries/Pdf.php */