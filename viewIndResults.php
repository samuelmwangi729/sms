<?php
session_start();
$_SESSION['View']=$_GET['View'];
if(isset($_SESSION['role'])){
  require_once('Database.php');
  $db=new Database();
	include('include/fpdf.php');
	//include('include/rotation.php');
	class myClass extends FPDF{
    public $angle;
		function head($db,$id){
      $database=new Database();
      $regNo=$database->getRegNumber($id);
      $_SESSION['regNo']=$regNo;
      $database->query("SELECT * FROM student WHERE regNo=:regNo AND status=:status");
      $database->bind(':regNo',$regNo);
      $database->bind(':status',1);
      $database->execute();
      if($student=$database->single()){
        $image=$student->photo;
      }
      $detail=$db->getSchool();
      foreach($detail as $key){
      $this->SetFont('Arial','',10);
      $this->Image('assets/images/'.$key->Image,10,10,0,20);
      $this->SetFont('Courier','B','20');
      $this->Cell(200,10,$key->sName,0,0,'C');
      //$this->Image('Student/'.$image,180,10,20,20);
      $this->Ln(9);
      $this->SetFont('Courier','B','10');
      $this->Cell(200,5,"Tel :".$key->Phone,0,0,'C');
      $this->Ln(5);
      $this->SetFont('Courier','B','10');
      $this->Cell(200,5,"P.O.Box ".$key->pBox." ".$key->pCode." ".$key->pCity." Kenya",0,0,'C');
      $this->Ln(5);
      $this->SetFont('Arial','B','10');
      $this->Cell(200,5,"STUDENT REPORT FORM",0,0,'C');
      $this->Ln();
      $this->Cell(200,5,$_SESSION['exam'],0,0,'C');
      $this->Ln(10);
      $database=new Database();
      $details=$database->getSchool();
      foreach ($details as $key) {
        $this->SetFont('Arial','B','50');
        if($database->checkWatermark()){
          $this->watermark($key->sName,30,11);
        }else{
          continue;
        }
        $this->SetFont('Arial','B','10');
      }
      // $this->watermark($text,22.5,40);
    }
		}
    function watermark($text,$angle,$start){
      $this->SetTextColor(186,189,182);
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
		function footer(){
			$this->SetY(-15);
			$this->SetFont('Arial','','10');
      $this->Cell(0,10,'=======================================================================================',0,0);
      $this->Ln(3);
			$this->Cell(0,10,'The School holds the right to edit the contents of this report form when deemed necessary',0,0,'C');
			$this->SetX(-40);
      $this->Ln();
      $this->SetTextColor(125,125,125);
			$this->Cell(0,0,'Generated By '.$_SESSION['role'],0,0);
		}
		function headerTable($id){
      $database=new Database();
      $regNo=$database->getRegNumber($id);
      $_SESSION['regNo']=$regNo;
      $database->query("SELECT * FROM student WHERE regNo=:regNo AND status=:status");
      $database->bind(':regNo',$regNo);
      $database->bind(':status',1);
      $database->execute();
      if($student=$database->single()){
        $this->SetTextColor(255,0,0);
  			$this->SetFont('Arial','IB','8');
  			$this->Cell(10,5,'NAME:',0,0,'C');
  			$this->Cell(30,5,$student->names,0,0,'C');
        $_SESSION['class']=$student->class;
        $_SESSION['stream']=$student->stream;
  			$this->Cell(200,5,'CLASS:  '.$student->class.' '.$student->stream,0,0,'C');
        $this->Ln(8);
  			$this->Cell(190,0.1,'',1,0,'C');
  			$this->Ln(5);
        $this->Cell(10,5,'Term: '.$_SESSION['term'],0,0,'C');
  			$this->Cell(50,5,'Year: '.$_SESSION['year'],0,0,'C');
  			$this->Cell(70,5,'Admission Number : '.$student->regNo,0,0,'C');
        $status="";
        if($student->status==1){
          $status="Active";
        }else{
        $status="Suspended";
        }
        $this->SetTextColor(0,0,255);
        $this->Cell(90,5,'Current Status : '.$status,0,0,'C');
        $this->SetTextColor(255,0,0);
        $this->Ln(5);
  			$this->Cell(190,0.1,'',1,0,'C');
        $this->Ln(10);
        $this->SetTextColor(255,0,0);
      }
		}
		function viewTable($db,$id){
			$this->SetFont('Arial','',8);
      //get the current exam,year,term
			$statement=$db->displayApproved('subject');
      $this->Cell(30,5,'Subject',1,0,'C');
      //check if there is cat one done
      $c1=$db->checkCat('CAT 1',$_SESSION['term'],$_SESSION['year']);
      $c2=$db->checkCat('CAT 2',$_SESSION['term'],$_SESSION['year']);
      if($c1){
        $this->Cell(20,5,'CAT 1',1,0,'C');
        $ca1=true;
      }else{
        //
      }
     if($c2){
        $this->Cell(20,5,'CAT 2',1,0,'C');
         $ca2=true;
      }else{
        //
      }
      if($c1 && $c2){
        $l=20;
      }  else if($c1){
        $l=28;
      }else if($c2){
        $l=28;
      }
      else{
        $l=40;
      }
      $this->Cell($l,5,"End Term",1,0,'C');
      $this->Cell($l,5,"Average",1,0,'C');
      $this->Cell(15,5,"Grade",1,0,'C');
      $this->Cell(40,5,"Initials",1,0,'C');
      $this->Cell(30,5,"Remarks",1,0,'C');
      $this->Ln();
      $totalMarks=0;
      $count=1;
    foreach ($statement as $key) {
      $mark=$db->displayResults($db->getRegNumber($id),$_SESSION['exam'],$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      if($mark==0){
        //do nothing
        continue;
      }else{
        $this->Cell(30,7,$key->subjectName,1,0,'L');
          $count=$count+1;
      }
      $db=new Database();
      $cat1=$db->displayCat1($db->getRegNumber($id),'CAT 1',$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      $cat2=$db->displayCat2($db->getRegNumber($id),'CAT 2',$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      if($c1){
      $this->Cell(20,7,$cat1,1,0,'C');
      }else{
        //$this->Cell(20,5,'',1,0,'C');
      }if($c2){
      $this->Cell(20,7,$cat2,1,0,'C');
      }else{
        //$this->Cell(20,5,'',1,0,'C');
      }
      if($c1 && $c2){
        $l=20;
      }  else if($c1){
        $l=28;
      }else if($c2){
        $l=28;
      }
      else{
        $l=40;
      }
      //$mark=$db->displayResults($db->getRegNumber($id),$_SESSION['exam'],$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      if($mark=="0"){
        $mrk="--";
      }else{
        $mrk=$mark;
      }
      if($mark=="--"){
        //pass
      }else{
      //  $count=$count+1;
      }
      if($mrk==0){
        //pass
      }else{
        $this->Cell($l,7,$mrk,1,0,'C');
      if($c1 && $c2){

        $average=round(($cat1+$cat2+$mark)/3);
      }  else if($c1){
        $average=round(($cat1+$mark)/2);
      }else if($c2){
        $average=round(($cat2+$mark)/2);
      }
      else{
        $average=$mark;
        $length=25;
      }
      if($average=="0"){
        $avg="--";
      }else{
        $avg=$average;
      }
      $this->Cell($l,7,$avg,1,0,'C');
      $totalMarks=$totalMarks+$average;
      if($avg=="--"){
        $this->Cell(15,7,"--",1,0,'C');
        $this->Cell(40,7,"--",1,0,'C');
        $this->Cell(30,7,"--",1,0,'C');
      }else{
      $category=$db->getCategory($key->subjectName);
      $grade=$db->getGrade($category,$average);
      $this->Cell(15,7,$grade,1,0,'C');
    }
      $regNumber=$db->getRegNumber($id);
      $this->Cell(40,7,$db->getTeacher($_SESSION['class'],$_SESSION['stream'],$key->subjectName),1,0,'C');
      $comment="";
      if($grade=='A'|| $grade=='A-'){
        $comment="Excellent";
      }elseif ($grade=='B-' || $grade=='B'||$grade=='B+') {
        $comment="Good. Work Harder";
      }elseif ($grade=="C-" || $grade=="C" || $grade=="C+") {
        $comment="Average";
      }elseif ($grade=="D-"||$grade=="D"||$grade=="D+") {
        $comment="Aim Higher";
      }else{
        $comment="Poor. Aim Higher";
      }
      $this->SetFont('Arial','B',8);
      $this->SetTextColor(0,0,255);
      $this->Cell(30,7,$comment,1,0,'L');
    }
      $this->SetTextColor(255,0,0);
      $this->SetFont('Arial','',10);
      $this->Ln();
    }
      $this->Ln();
      $this->SetTextColor(255,0,0);
      $this->Cell(40,7,"Total Marks:",1,0,'C');
      $this->Cell(30,7,$totalMarks,1,0,'C');
      $Grade='';
      if($count==1){
        $Mean=$totalMarks;
      }
      $divisor=$count-1;
      $mean=round($totalMarks/$divisor);
      $gr=$db->displayGrade($_SESSION['pregNo'],$_SESSION['exam'],$_SESSION['year'],$_SESSION['term']);
      $this->SetTextColor(0,0,255);
      $this->SetFont('Arial','B',8);
      $this->Cell(30,7,"Grade: ".$gr,1,0,'C');
      $pts=$db->displayPoints($_SESSION['pregNo'],$_SESSION['exam'],$_SESSION['year'],$_SESSION['term']);
      $this->Cell(50,7,"Total Points: ". $pts,1,0,'C');
      $this->Cell(40,7," Total Subjects: ".($count-1),1,0,'C');
      $this->SetFont('Arial','',8);
      $this->Ln(10);
      $this->Cell(40,20,'Class Teachers Comments',1,0,'C');
      $classTeacherComment="";
      if($Grade=='A'|| $Grade=='A-'){
        $classTeacherComment="Excellent";
      }elseif ($Grade=='B-' || $Grade=='B'||$Grade=='B+') {
        $classTeacherComment="Good. Work Harder";
      }elseif ($Grade=="C-" || $Grade=="C" || $Grade=="C+") {
        $classTeacherComment="Average";
      }elseif ($Grade=="D-"||$Grade=="D"||$Grade=="D+") {
        $classTeacherComment="Aim Higher";
      }else{
        $classTeacherComment="Poor. Aim Higher";
      }
      $this->Cell(30,20,$classTeacherComment,1,0,'C');
      $this->Cell(50,20,'Signature:.....................................',1,0,'C');
      $this->Cell(70,30,'Official Stamp   Date: '.date('Y-M-d'),1,0,'C');
        $this->Ln();
      $this->Cell(40,20,"Principal's Comments",1,0,'C');
      $this->Cell(30,20,"___________________",1,0,'C');
      $this->Cell(50,20,'Signature:.....................................',1,0,'C');
      $this->Cell(70,30,'Official Stamp   Date: '.date('Y-M-d'),1,0,'C');
        $this->SetTextColor(0,0,0);
        $this->Ln();
      $this->Cell(40,5,"Class Position",1,0,'C');
      $db->query("SELECT SUM(marks) FROM examResults WHERE class=:class AND Year=:year AND studentReg=:studentReg");
      $db->bind(':class',$_SESSION['class']);
      $db->bind(':year',date('Y'));
      $db->bind(':studentReg',$_SESSION['regNo']);
      $db->execute();
      $amount=$db->fetchColumn();
      $db->query("SELECT * FROM student WHERE  class=:class AND Year=:year");
      $db->bind(':class',1);
      $db->bind(':year',date('Y'));
      $reg=$db->resultSet();
      $count=0;
      foreach($reg as $regNo){
        $db->query("SELECT SUM(marks) FROM examResults WHERE Class=:class");
        $db->bind(':class',1);
        $db->execute();
        $sum=$db->fetchColumn();
        if($sum>$amount){
          // $count=$count+1;
        }
      }
      $db->query("SELECT count(id) FROM student WHERE  class=:class AND Year=:year");
      $db->bind(':class',$_SESSION['class']);
      $db->bind(':year',date('Y'));
      $db->execute();
      $numbers=$db->fetchColumn();
      $db->query("SELECT count(id) FROM student WHERE  class=:class AND stream=:stream AND Year=:year");
      $db->bind(':class',$_SESSION['class']);
      $db->bind(':stream',$_SESSION['stream']);
      $db->bind(':year',date('Y'));
      $db->execute();
      $num=$db->fetchColumn();
      $this->SetTextColor(0,0,255);
      $this->Cell(30,5,$db->getPosition($regNumber,$_SESSION['exam'],$_SESSION['class'],$_SESSION['term'],$_SESSION['year']).' Out of '.$numbers,1,0,'C');
      $this->SetTextColor(255,0,0);
      $this->Cell(70,5,'Stream Position',1,0,'C');
      $this->SetTextColor(0,0,0);
      $this->Cell(50,5,$db->getStreamPosition($regNumber,$_SESSION['exam'],$_SESSION['class'],$_SESSION['stream'],$_SESSION['term'],$_SESSION['year']).' Out of '.$num,1,0,'C');
      $this->SetTextColor(0,0,0);
      $this->SetFont('Arial','B',8);
      $this->Ln();
      $this->Cell(190,5,"The School Closes today on ".date('Y-m-d')." And we Resume back to School on ".$db->getOpeningDate().". The student Has An outstanding balance of ".$db->getBalance($_SESSION['regNo'])." Which",0,0,'L');
      $this->Ln(5);
      $db=new Database();
      $term=$db->getNextTerm();
      $results=$db->displaynTFees($_SESSION['class'],$term);
      $total=0;
      foreach ($results as $key) {
          $total=$total+$key->amount;
        $status="";
      }
      $this->Cell(190,5," should be cleared upon reporting. The next term fees is ".$total." Ksh",0,0,'L');
      $this->Ln(10);
      $this->Cell(70,5,"Report Form Seen By: ".$db->getParent($_SESSION['regNo']),0,0,'L');
      $this->Cell(80,5,"\tSignature_________________________",0,'L');
      $this->Cell(90,5,"\tDate_________________________",0,'L');
    //  $this->drawGraph();
		}

	}
	$pdf=new myClass();
	$pdf->AliasNbPages();
	$pdf->AddPage('p','A4',0);
	$pdf->head($db,$_SESSION['View']);
	$pdf->headerTable($_SESSION['View']);
	$pdf->viewTable($db,$_SESSION['View']);
	$pdf->output();
}else{  header("Location: index.php");}
?>
