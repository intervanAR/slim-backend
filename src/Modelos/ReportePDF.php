<?php

namespace Backend\Modelos;

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Test Image
 * @author Nicola Asuni
 * @since 2008-03-04
 */


class ReportePDF extends \TCPDF {


	public $img_file = null;	

	public $rep_w;
	public $rep_h;

    //Page header
    public function Header() {
        // get the current page break margin
        $bMargin = $this->getBreakMargin();
        // get current auto-page-break mode
        $auto_page_break = $this->AutoPageBreak;
        // disable auto-page-break
        $this->SetAutoPageBreak(false, 0);
        // set bacground image
        $this->Image($this->img_file, 0, 0, $this->rep_w, $this->rep_h , '', '', '', false, 300, '', false, false, 0);
        // restore auto-page-break status
        $this->SetAutoPageBreak($auto_page_break, $bMargin);
        // set the starting point for the page content
        $this->setPageMark();
    }


	public function __construct($img_bk,$report_w,$report_h,$orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
		$this->img_file = $img_bk;
		$this->rep_w = $report_w;
		$this->rep_h = $report_h; 

		// set document information
		$this->SetCreator(PDF_CREATOR);
		$this->SetAuthor('Intervan S.C.');
		//$this->SetTitle('Ticket');		
		//$pdf->SetSubject('Comprobante de Operación');
		//$pdf->SetKeywords('Oficina Virtual, Gestionar, Intervan');


		// set header and footer fonts
		$this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

		// set default monospaced font
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->SetHeaderMargin(0);
		$this->SetFooterMargin(0);

		// remove default footer
		$this->setPrintFooter(false);

		// set auto page breaks
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		    require_once(dirname(__FILE__).'/lang/eng.php');

		}
	}

	public function print( $data ){

		foreach ($data as $key => $text) 
		{
			if(isset($text["font-family"])){
/*
			public function SetFont($family, $style='', $size=null, $fontfile='', $subset='default', $out=true) {|

*/				$f_family = $text["font-family"];
				$f_style = isset($text["font-style"]) ?  $text["font-style"] : '';
				$f_size =  isset($text["font-size"]) ? $text["font-size"] : '';
				$f_file = isset($text["font-file"]) ? $text["font-file"] :'' ;
				$f_subset = isset($text["font-subset"]) ? $text["font-subset"] : 'default';
				$this->SetFont($f_family,$f_style,$f_size,$f_file,$f_subset);
			}
			# code...

			if(isset($text["set-x"]) && isset($text["set-y"]) ){
/*
			public function SetXY
*/
				$set_x = $text["set-x"];
				$set_y = $text["set-y"];
				$set_rtloff = isset($text["set_rtloff"]) ? $text["set_rtloff"] : false;
				$this->SetXY($set_x,$set_y,$set_rtloff);
			}
			# code...
			if(isset($text["text"]) ){
/*
	public function Text($x, $y, $txt, $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false)

*/				$txt = $text["text"];
				$txt_x = isset($text["text-x"]) ? $text["text-x"] : '';
				$txt_y = isset($text["text-y"]) ? $text["text-y"] : '';
				$txt_fstroke = isset($text["text-fstroke"])? $text["text-fstroke"] : false;
				$txt_fclip = isset($text["text-fclip"]) ? $text["text-fclip"] :false;
				$txt_ffill = isset($text["text-ffill"]) ? $text["text-ffill"] : true;
				$txt_border = isset($text["text-border"]) ? $text["text-border"] :0;
				$txt_ln = isset($text["text-ln"]) ? $text["text-ln"] : 0;
				$txt_align = isset($text["text-align"]) ? $text["text-align"] : '';
				$txt_fill = isset($text["text-fill"]) ? $text["text-fill"] : false;
				$txt_link = isset($text["text-link"]) ? $text["text-link"] : '';
				$txt_stretch = isset($text["text-link"]) ? $text["text-link"] : '';
				$this->Text($txt_x, $txt_y, $txt , $txt_fstroke, $txt_fclip, $txt_ffill, $txt_border, $txt_ln, $txt_align, $txt_fill, $txt_link, $txt_stretch);
			}


			if(isset($text["multi-text"]) ){
				$txt =$text["multi-text"];
				$mul_w= isset($text["multi-w"]) ? $text["multi-w"] : '';
				$mul_h= isset($text["multi-h"]) ? $text["multi-h"] : '';
				$mul_b= isset($text["multi-b"]) ? $text["multi-b"] : 0;
				$mul_al= isset($text["multi-al"]) ? $text["multi-al"] : '';
				$mul_x= isset($text["multi-x"]) ? $text["multi-x"] : '';
				$mul_y= isset($text["multi-y"]) ? $text["multi-y"] : '';
				$this->MultiCell($mul_w, $mul_h, $txt, $mul_b, $mul_al, 0 , 0, $mul_x, $mul_y , true);;
			}
/*                    ["bc1d-x"=>116 , "bc1d-y"=>168,"bc1d-text"=>$row["COD_BARRA_NRO"],
                      "bc1d-w"=>80 , "bc1d-h"=>15,"bc1d"=>"I25"],  
  */
                        //$pdf->write1DBarcode('1234567', 'I25', '', '', '', 18, 0.4, $style, 'N');
			if(isset($text["bc1d-text"]) ){
				$txt =$text["bc1d-text"];
				$bc1d_w= isset($text["bc1d-w"]) ? $text["bc1d-w"] : '';
				$bc1d_h= isset($text["bc1d-h"]) ? $text["bc1d-h"] : '';
				$bc1d_x= isset($text["bc1d-x"]) ? $text["bc1d-x"] : '';
				$bc1d_y= isset($text["bc1d-y"]) ? $text["bc1d-y"] : '';
				$bc1d= isset($text["bc1d"]) ? $text["bc1d"] : "";
				$bc1d_s= isset($text["bc1d-s"]) ? $text["bc1d-s"] : "";
				$bc1d_r= isset($text["bc1d-r"]) ? $text["bc1d-r"] : "";
				$bc1d_a= isset($text["bc1d-a"]) ? $text["bc1d-a"] : "N";
				$this->write1DBarcode($txt, $bc1d, $bc1d_x, $bc1d_y, $bc1d_w, $bc1d_h,$bc1d_r, $bc1d_s, $bc1d_a);
			}



		}
	}
}