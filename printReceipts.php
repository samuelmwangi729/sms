<?php
session_start();
if(isset($_SESSION['role'])){
  require_once('Database.php');
  $db=new Database();
	include('include/fpdf.php');
	class myClass extends FPDF{
    public $angle;
    public function Header(){
      $database=new Database();
      $details=$database->getSchool();
      foreach ($details as $key) {
        $this->SetFont('Arial','B','40');
        if($database->checkWatermark()){
          $this->watermark($key->sName,45,85);
        }else{
          continue;
        }
        $this->SetFont('Arial','B','10');
      }

    }
    function watermark($text,$angle,$start){
      $this->SetTextColor(255,200,233);
      $this->Rotate($angle,20,200);
      $this->Text($start,190,$text);
      $this->Rotate(0);
    }
    function Rotate($angle,$x=-1,$y=-1)
    {
      if($x==-1)
        $x=$this->x;
      if($y==-1)
        $y=$this->y;
      if($this->angle!=0)
        $this->_out('Q');
      $this->angle=$angle;
      if($angle!=0)
      {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.2F %.2F %.2F %.2F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
      }
    }
    function _endpage()
    {
      if($this->angle!=0)
      {
        $this->angle=0;
        $this->_out('Q');
      }
      parent::_endpage();
    }
    function head(){
      $db=new Database();
  			$this->SetFont('Arial','',10);
        $detail=$db->getSchool();
        foreach($detail as $key){
			$this->SetFont('Arial','I',10);
      $this->Image('assets/images/'.$key->Image,35,5,0,15);
      $this->SetFont('Courier','I','8');
      $this->Cell(160,5,$key->sName,0,0,'C');
      $this->SetTextColor(255,0,0);
      $this->SetFont('Arial','IB',15);
      $this->Cell(20,8,"Receipt Number:\t".$db->getReceiptNumber($_SESSION['id']),0,0,'C');
      $this->SetTextColor(0,0,0);
      $this->SetFont('Arial','I',10);
      $this->Ln();
      $this->Cell(160,6,"P.O.Box ".$key->pBox." ".$key->pCode." ".$key->pCity." Kenya",0,0,'C');
      $this->Ln();
      $this->Cell(160,5,"Tel :".$key->Phone,0,0,'C');
      $this->Ln();
      $this->Cell(190,0.2,'',1,0,'');
      $this->Ln(0.1);
      $this->Cell(100,5,'Bank: '.$key->bName,0,0,'C');
      $this->Cell(40,5,"",0,0,'C');
      $this->Cell(20,5,'Paid To:'.$db->getName($db->getReg($_SESSION['id'])),0,0,'C');
      $this->Ln(4);
      $this->Cell(100,5,'Account Number: '.$key->aNumber,0,0,'C');
      $this->Cell(40,5,"",0,0,'C');
      $this->Cell(20,5,'Admission Number:'.$db->getReg($_SESSION['id']),0,0,'C');
      $this->SetFont('Courier','B','10');
      //$this->Cell(160,5,"0704922042",0,0,'C');
      $this->Ln(4);
      $this->SetFont('Courier','I','8');
      $this->Cell(100,5,'Payment For Term '.$db->getCurrentTerm(),0,0,'C');
      $this->Cell(40,5,"",0,0,'C');
      $this->Cell(20,5,'Class:'.$db->getClass($db->getReg($_SESSION['id'])).' '.$db->getStream($db->getReg($_SESSION['id'])),0,0,'C');
      $this->Ln();
      $this->SetFont('Courier','B','10');
      $this->Cell(160,5,"",0,0,'C');
      $this->Ln(5);
      $this->SetFont('Arial','B','10');
      	$this->Cell(190,0.2,'',1,1,'');
        $this->Ln(5);
      $this->Cell(180,5,"Cash Receipt as at ".date("D-Y-M-d"),0,0,'C');
      $this->Ln(5);
			}
    }
		function footer(){
			$this->SetY(-15);
			$this->SetFont('Arial','','10');
			// $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0);
			$this->SetX(-50);
			$this->Cell(0,0,'Generated By '.$_SESSION['role'],0,0);
		}
		function viewTable($db){
			$db->query('select * from payments');
      $stat=$db->resultSet();
			foreach($stat as $key){
        $_SESSION['id']=$key->id;
        $this->Cell(0,0,$this->head($db,$key->id),0,0,'C');
				$this->Ln(2);
				$this->SetTextColor(0,0,0);
        $this->SetFont('Courier','I','8');
				$this->Cell(200,5,"This is a proof of that payments have been made for the above named student\t",0,0,'C');
        $this->Ln(10);
        $this->Cell(200,5,"The Amount Paid is 10000 and the current Outstanding school balance is Ksh.".$db->getBalance($db->getReg($_SESSION['id'])),0,0,'C');
        $this->Ln(10);
        $this->Cell(200,5,"The Amount Was paid in Bankers Cheque on ".date('Y-M-d'),0,0,'C');
        $this->Ln(10);
        $this->Cell(200,5,"Received By:.................................",0,0,'R');
        $this->Ln(5);
        $this->Cell(133,5,"Stamp",0,0,'R');
        $this->Cell(65,30,"",1,0,'R');
        $this->Ln(30);
			}
		}
	}
	$pdf=new myClass();
  $pdf->AddPage('L','a5',0);
	$pdf->viewTable($db);
	$pdf->output();
}else{  header("Location: index.php");}
?>
