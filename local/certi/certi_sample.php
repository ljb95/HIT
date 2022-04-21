<?php

class completionPDF {

    protected $_srcdir;
    protected $pdf;

    public function __construct($srcdir, $page = 1) {
        $this->_srcdir = $srcdir;

        // initiate PDF
        if (is_null($this->pdf)) {
            $this->pdf = new FPDI();
        }

        $this->pdf->setPageUnit('mm'); // milimeter
//        $this->pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
        $this->pdf->SetAutoPageBreak(true, -35);
        $this->pdf->setFontSubsetting(true);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        $this->pdf->setCellHeightRatio(2);
    }

    function Write($vars) {
        // add a page
        $this->pdf->AddPage();
        $fontname = TCPDF_FONTS::addTTFfont('../../lib/tcpdf/fonts/gung.TTF');

        /* Korean Font
                cid0kr: gothic
                hysmyeongjostdmedium: myeongjo
        */
        $this->pdf->Image($this->_srcdir.'/'.$vars->background,0,1,524,790);
//        $this->pdf->Image($this->_srcdir.'/'.$vars->background,0,-1.3,500,740);
        //$this->pdf->Image($vars->background,7.5,15,480,660,'', '', '', false, 300, '', false, false, 0);

        $utf8text = '제 '.$vars->documentid.' 호';
        $this->pdf->SetFont($fontname, 'B', 12);
        $this->pdf->SetXY(20, 62);
        $this->pdf->Write(5, $utf8text);

        $utf8text = '수료증';
        $this->pdf->SetFont($fontname, 'B', 32);
        $this->pdf->SetXY(20, 80);
        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');

        $utf8text = '소      속 : '.$vars->sosok;
        $this->pdf->SetFont($fontname, 'B', 16);
        $this->pdf->SetXY(22, 110);
        $this->pdf->Write(5, $utf8text);
        
        $utf8text = get_string('certi:name','local_certi').' : '.$vars->name;
        $this->pdf->SetFont($fontname, 'B', 16);
        $this->pdf->SetXY(22, 117.5);
        $this->pdf->Write(5, $utf8text);
        
        $utf8text = $vars->desc1.$vars->desc3;
        $this->pdf->SetFont($fontname, 'B', 16);
        $this->pdf->SetXY(22, 125);
        $this->pdf->Write(5, $utf8text);
        
        $utf8text = $vars->desc2.$vars->desc4;
        $this->pdf->SetFont($fontname, 'B', 16);
        $this->pdf->SetXY(22, 132.5);
        $this->pdf->Write(5, $utf8text);
        /*
        $utf8text = '생년월일 : '.$vars->birthday;
        $this->pdf->SetXY(124, 96);
        $this->pdf->Write(5, $utf8text);
        */
        $utf8text = $vars->description;
        $this->pdf->SetFont($fontname, 'B', 18);
        $this->pdf->SetXY(23, 167);
        $this->pdf->MultiCell(165, 0, $utf8text, 0, 'L');

        //$this->pdf->SetFont('bareunbatangm', '', 12);
//        $utf8text = $vars->coursename;
//        $this->pdf->SetFont($fontname, 'B', 15);
//        $this->pdf->SetXY(20, 179);
//        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');

        $utf8text = $vars->issuedate;
        $this->pdf->SetFont($fontname, 'B', 20);
        $this->pdf->SetXY(20, 225);
        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');

        $this->pdf->Image($this->_srcdir.'/'.$vars->dojang,120,237,30,30);

//        $utf8text = $vars->author;
//        $this->pdf->SetFont($fontname, 'B', 23);
//        $this->pdf->SetXY(0, 249);
//        $this->pdf->MultiCell(210, 0, $utf8text, 0, 'C');
    }
    
    function Output($filename, $mod='D')
    {
        $this->pdf->Output($filename, $mod);
    }

}
/* Destination where to send the document. It can take one of the following values:
	I: send the file inline to the browser. The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.
	D: send to the browser and force a file download with the name given by name.
	F: save to a local file with the name given by name (may include a path).
	S: return the document as a string. name is ignored.
*/

