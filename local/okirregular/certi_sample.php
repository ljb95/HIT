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
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);
        $this->pdf->SetAutoPageBreak(true, 14);
        $this->pdf->setFontSubsetting(true);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    function Write($vars) {
        // add a page
        $this->pdf->AddPage();

        /* Korean Font
                cid0kr: gothic
                hysmyeongjostdmedium: myeongjo
        */
        $this->pdf->Image($this->_srcdir.'/'.$vars->background,14,17,240,336);

        $utf8text = '제 '.$vars->documentid.' 호';
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 11);
        $this->pdf->SetXY(28, 33);
        $this->pdf->Write(5, $utf8text);

        $utf8text = $vars->cname;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 24);
        $this->pdf->SetXY(22, 65);
        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');

        $utf8text = get_string('certi:name','local_certi').': '.$vars->name;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 14);
        $this->pdf->SetXY(124, 90);
        $this->pdf->Write(5, $utf8text);
        
        $utf8text = $vars->birthday;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 15);
        $this->pdf->SetXY(130, 96);
        $this->pdf->Write(5, $utf8text);
        
        $this->pdf->Image($this->_srcdir.'/'.$vars->seal,80,135,60,60);
        
        $utf8text = $vars->description;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 18);
        $this->pdf->SetXY(42, 131);
        $this->pdf->MultiCell(165, 0, $utf8text, 0, 'L');
        
        $utf8text = $vars->description1;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 18);
        $this->pdf->SetXY(57, 140);
        $this->pdf->MultiCell(165, 0, $utf8text, 0, 'L');
        
        
        
        $utf8text = $vars->issuedate2;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 22);
        $this->pdf->SetXY(22, 175);
        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');
        //$this->pdf->SetFont('bareunbatangm', '', 12);
        
        
        $utf8text = $vars->coursename;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 20);
        $this->pdf->SetXY(25, 165);
        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');

        $utf8text = $vars->issuedate;
        $this->pdf->SetXY(20, 223);
        $this->pdf->MultiCell(170, 0, $utf8text, 0, 'C');

        $this->pdf->Image($this->_srcdir.'/'.$vars->dojang,140,235,30,30);
        $this->pdf->Image($this->_srcdir.'/'.$vars->dojang1,140,185,30,30);

        $utf8text = $vars->author;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 23);
        $this->pdf->SetXY(0, 239);
        $this->pdf->MultiCell(210, 0, $utf8text, 0, 'C');
        
        $utf8text = $vars->author1;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 20);
        $this->pdf->SetXY(0, 190); 
        $this->pdf->MultiCell(210, 0, $utf8text, 0, 'C');
        
        $utf8text = $vars->author2;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 21);
        $this->pdf->SetXY(0, 198); 
        $this->pdf->MultiCell(210, 0, $utf8text, 0, 'C');
        
        $utf8text = $vars->author3;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 16);
        $this->pdf->SetXY(0, 215); 
        $this->pdf->MultiCell(210, 0, $utf8text, 0, 'C');
        
        $utf8text = $vars->author4;
        $this->pdf->SetFont('hysmyeongjostdmedium', 'B', 23);
        $this->pdf->SetXY(0, 248); 
        $this->pdf->MultiCell(210, 0, $utf8text, 0, 'C');
        
        $this->pdf->Image($this->_srcdir.'/'.$vars->logo,95,35,25,25);
        
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

