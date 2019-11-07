<?php
session_start();
$_SESSION['exam']=$_POST['exam'];
$_SESSION['class']=$_POST['class'];
$_SESSION['term']=$_POST['term'];
$_SESSION['year']=$_POST['year'];
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
        $this->SetFont('Arial','B','50');
        if($database->checkWatermark()){
          $this->watermark($key->sName,45,11);
        }else{
          continue;
        }
        $this->SetFont('Arial','B','10');
      }

    }
    function getImage($url){
      return $url;
    }
    function head($db,$reg){
      $db->query("SELECT * FROM student WHERE regNo=:regNo AND status=:status");
      $db->bind(':regNo',$reg);
      $db->bind(':status',1);
      $db->execute();
      if($student=$db->single()){
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
      $_SESSION['school_name']=$key->sName;
      // $this->watermark($text,22.5,40);
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
    function footer(){
      $this->SetY(-15);
      $this->SetFont('Arial','','10');
      // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0);
      $this->SetX(-50);
      $this->Cell(0,0,'Generated By '.$_SESSION['role'],0,0);
    }
    function headerTable($reg){
    $this->SetFont('Arial','',10);
      $database=new Database();
      $database->query("SELECT * FROM student WHERE regNo=:regNo AND status=:status");
      $database->bind(':regNo',$reg);
      $database->bind(':status',1);
      $database->execute();
      if($student=$database->single()){
        $this->SetTextColor(255,0,0);
        $this->SetFont('Arial','IB','8');
        $this->Cell(10,5,'NAME:',0,0,'C');
        $this->Cell(30,5,$student->names,0,0,'C');
        $_SESSION['class']=$student->class;$_SESSION['stream']=$student->stream;

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
    function viewTable($db){
      $db->query('SELECT * FROM student WHERE Class=:class ORDER BY regNo DESC');
      $db->bind(':class',$_SESSION['class']);
      $stat=$db->resultSet();
      foreach($stat as $key){
        $_SESSION['regNo']=$key->regNo;
        $this->Cell(0,0,$this->head($db,$key->regNo),0,0,'C');
        $_SESSION['pregNo']=$key->regNo;
        $this->Ln(2);
        $this->Cell(0,0,$this->headerTable($key->regNo),0,0,'C');
        $this->Ln(2);
        $statement=$db->displayApproved('subject');
        $this->Cell(30,5,'Subject',1,0,'C');
        //check if there is cat one done
        $c1=$db->checkCat('CAT 1',$_SESSION['term'],$_SESSION['year']);
        $c2=$db->checkCat('CAT 2',$_SESSION['term'],$_SESSION['year']);
      if($c1){
        $this->Cell(20,5,'CAT 1',1,0,'C');
        $ca1=true;
      }else{
       // $this->Cell(20,5,'',1,0,'C');
      }
     if($c2){
        $this->Cell(20,5,'CAT 2',1,0,'C');
         $ca2=true;
      }else{
       // $this->Cell(20,5,'',1,0,'C');
      }if($c1 && $c2){
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
       // $totalMarks=$totalMarks+$key->marks;
       $mark=$db->displayResults($_SESSION['pregNo'],$_SESSION['exam'],$_SESSION['year'],$_SESSION['term'],$key->subjectName);
       if($mark==0){
         //do nothing
         continue;
       }else{
         $this->Cell(30,7,$key->subjectName,1,0,'L');
           $count=$count+1;
       }
      //  $this->Cell(30,7,$key->subjectName,1,0,'L');
         $db=new Database();
      $cat1=$db->displayCat1($_SESSION['pregNo'],'CAT 1',$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      $cat2=$db->displayCat2($_SESSION['pregNo'],'CAT 2',$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      if($c1){
      $this->Cell(20,7,$cat1,1,0,'C');
      }else{
        //$this->Cell(20,5,'',1,0,'C');
      }if($c2){
      $this->Cell(20,7,$cat2,1,0,'C');
      }else{
        //$this->Cell(20,5,'',1,0,'C');
      }if($c1 && $c2){
        $l=20;
      }  else if($c1){
        $l=28;
      }else if($c2){
        $l=28;
      }
      else{
        $l=40;
      }
      //$mark=$db->displayResults($_SESSION['pregNo'],$_SESSION['exam'],$_SESSION['year'],$_SESSION['term'],$key->subjectName);
      if($mark=="0"){
        $mrk="--";
      }else{
        // $count=$count+1;
        $mrk=$mark;
      }
      $this->Cell($l,7,$mrk,1,0,'C');
        if($c1 && $c2){
          if($cat1=="--" && $cat2=="--"){
            $average=$mark;
          }else if($cat1=="--"){
            $ca1=0;
            $average=round(($ca1+$cat2+$mark)/2);
          }else if($cat2=="--"){
            $ca2=0;
            $average=round(($cat1+$ca2+$mark)/2);
          }
          else{
            $average=round(($cat1+$cat2+$mark)/3);
          }
        }  else if($c1){
            if($cat1=="--"){
              $average=round((0+$mark)/2);
            }else{
              $average=round(($cat1+$mark)/2);
            }
        }else if($c2){
          $average=round(($cat2+$mark)/2);
        }
        else{
          $average=$mark;
          $length=25;
        }if($average=="0"){
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
        $regNumber=$db->getRegNumber($key->id);
        $subject=$key->subjectName;
        $class=$_SESSION['class'];
        $stream=$_SESSION['stream'];
        $this->Cell(40,7,$db->getTeacher($class,$stream,$subject),1,0,'C');
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
        if($divisor==0){
          $mean=round($totalMarks/1);
        }else{
          $mean=round($totalMarks/$divisor);
        }
       $gr=$db->displayGrade($_SESSION['pregNo'],$_SESSION['exam'],$_SESSION['year'],$_SESSION['term']);
        $this->SetTextColor(0,0,255);
        $this->SetFont('Arial','B',8);
        $this->Cell(30,7,"Grade : ".$gr,1,0,'C');
        $year=date('Y');
      $pts=$db->displayPoints($_SESSION['pregNo'],$_SESSION['exam'],$_SESSION['year'],$_SESSION['term']);
      $this->Cell(30,7,"Total Points: ". $pts,1,0,'C');
        // $this->Cell(50,7,'Class Mean',1,0,'C');
        // $this->Cell(40,7,$db->getMean($_SESSION['class'],$_SESSION['stream'],$_SESSION['term'],$year,$_SESSION['exam']),1,0,'C');
        $this->Cell(60,7,"Total Subjects: ".$divisor,1,0,'C');
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
        $this->Cell(70,30,'Official Stamp   Date: '.date('y-M-d'),1,0,'C');
          $this->Ln();
        $this->Cell(40,20,"Principal's Comments",1,0,'C');
        $this->Cell(30,20,"___________________",1,0,'C');
        $this->Cell(50,20,'Signature:.....................................',1,0,'C');
        $this->Cell(70,30,'Official Stamp   Date: '.date('y-M-d'),1,0,'C');
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
          $db->bind(':class',$_SESSION['class']);
        $db->bind(':year',date('Y'));
        $reg=$db->resultSet();
        $count=0;
        foreach($reg as $regNo){
          $db->query("SELECT SUM(marks) FROM examResults WHERE Class=:class");
            $db->bind(':class',$_SESSION['class']);
          $db->execute();
          $sum=$db->fetchColumn();
          if($sum>$amount){
            //$count=$count+1;
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
        $this->Cell(30,5,$db->getPosition($_SESSION['regNo'],$_SESSION['exam'],$_SESSION['class'],$_SESSION['term'],$_SESSION['year']).' Out of '.$numbers,1,0,'C');
        $this->SetTextColor(255,0,0);
        $this->Cell(70,5,'Stream Position',1,0,'C');
        $this->SetTextColor(0,0,0);
        $this->Cell(50,5,$db->getStreamPosition($_SESSION['regNo'],$_SESSION['exam'],$_SESSION['class'],$_SESSION['stream'],$_SESSION['term'],$_SESSION['year']).' Out of '.$num,1,0,'C');
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
        $this->SetTextColor(0,0,0);
        $this->Cell(70,5,"Report Form Seen By: ".$db->getParent($_SESSION['regNo']),0,0,'L');
        $this->Cell(80,5,"\tSignature_________________________",0,'L');
        $this->Cell(90,5,"\tDate_________________________",0,'L');
        $this->Ln(100);
      }
    }
  }
  $pdf=new myClass();
$pdf->AddPage('p','A4',0);
  $pdf->viewTable($db);
  $pdf->output();
}else{  header("Location: index.php");}
?>