<?php
session_start();
$Msg="";
require_once('Database.php');
$id=$_GET['Edit'];
if(isset($_SESSION['role'])){
	include('head.php');
?>
	<body>
		<div id="app">
<?php
//perform access management
if($_SESSION['role']=="Administrator"){
	include('include/sidebar.php');
}else if($_SESSION['role']=="teacher"){
	include('include/tsidebar.php');
}else if($_SESSION['role']=="Accountant"){
	include('include/asidebar.php');
}else{
	echo "";
}
if(isset($_POST['sub'])){
	$names=strip_tags($_POST['names']);
	$class=strip_tags($_POST['class']);
	$gender=$_POST['gender'];
	$stream=$_POST['stream'];
	$regNumber=$_POST['regNumber'];
	$KCPE=$_POST['kcpe'];
	$parent=$_POST['parent'];
	$tId=$_POST['id'];
	$dorm=$_POST['dorm'];
	$fees=$_POST['fees'];
	//passport image upload
	$file=$_FILES['logo']['name'];
	$tmpName=$_FILES['logo']['tmp_name'];
	if($file==""){
		$fname="default.png";
	}else{
		$fname=$file;
	}
	move_uploaded_file($tmpName, "Student/$fname");
	$db=new Database();
	$db->query("UPDATE student SET names=:names,parent=:parent,class=:class,stream=:stream,regNo=:regNumber,KCPE=:KCPE,gender=:gender,dorm=:dorm,photo=:passportName, fees=:fees WHERE id=:id");
	$db->bind(':names',$names);
	$db->bind(':parent',$parent);
	$db->bind(':class',$class);
	$db->bind(':stream',$stream);
	$db->bind(':regNumber',$regNumber);
	$db->bind(':KCPE',$KCPE);
	$db->bind(':gender',$gender);
	$db->bind(':dorm',$dorm);
	$db->bind(':fees',$fees);
	$db->bind(':passportName',$fname);
  $db->bind(':id',$tId);
	if($db->execute()){
			echo "<script>window.open('viewStudent.php','_self');</script>";
	}else{
		$Msg="<div class='alert alert-danger'id='alert'>
			<strong>Student Details Not Updated</strong>
		</div>";
	}
}

?>
			<div class="app-content">

						<?php include('include/header.php');?>

				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<span style="font-size:20px"><span class="text-primary">Edit Student Details</span></span>
					<div class="container">
            <?php
            $db=new Database();
            $db->query("select * from student where id=:id");
            $db->bind(':id',$id);
            if($results=$db->single()){
							$_SESSION['regNo']=$results->regNo;
							$_SESSION['kcpe']=$results->KCPE;
            ?>
            <div style="float:right">
              <img src="<?php echo "Student/".$results->photo;?>" width="150px" height="150px" style="border-radius:100px">
            </div>
					<form method="POST" action="editStudent.php" enctype="multipart/form-data">
						<?php echo $Msg;?>
						<div class="row">
							<div class="col-sm-3">
                	<input type="hidden" class="form-control" name="id" value="<?php echo $results->id;?>"/>
								<label for="names" class="control-label">Enter the names</label>&nbsp;<i class="fa fa-tags"style="color:red"></i>
								<input type="text" class="form-control" name="names" value="<?php echo $results->names;?>" required/>
							</div>
							<div class="col-sm-3">
								<label for="email" class="label-control">class</label>&nbsp;<i class="fa fa-university"style="color:red"></i>
								<input type="number" class="form-control" name="class" value="<?php echo $results->class;?>" required/>
							</div>
							<div class="col-sm-3">
								<label for="email" class="label-control">Stream</label>&nbsp;<i class="fa fa-arrow-up"style="color:red"></i>
								<select name="stream" class="form-control">
									<?php $database=new Database();
									$resultss=$database->displayApproved('stream');
									foreach ($resultss as $key) {
									?>
									<option><?php echo $key->streamName;?></option>
								<?php }?>
								</select>
							</div>
							<div class="col-sm-3">
								<label for="gender" class="label-control">Gender</label>&nbsp;<i class="fa fa-users"style="color:red"></i>
								<select name="gender" class="form-control">
									<option><?php echo $results->gender;?></option>
									<option>M</option>
									<option>F</option>
								</select>
							</div>
							<div class="col-sm-3">
								<label for="Employee Number" class="label-control">Registration Number</label>&nbsp;<i class="fas fa-tags"style="color:red"></i>
								<!--[generated by the system]-->
								<input type="text" class="form-control" name="regNumber" value="<?php echo $_SESSION['regNo'];?>" required/>
							</div>
							<div class="col-sm-3">
								<label for="Employee Number" class="label-control">KCPE MARKS</label>&nbsp;<i class="fas fa-tags"style="color:red"></i>
								<!--[generated by the system]-->
								<input type="text" class="form-control" name="kcpe" value="<?php echo $_SESSION['kcpe'];?>"/>
							</div>
							<div class="col-sm-3">
								<label for="Designation" class="label-control">Parent</label>&nbsp;<i class="fa fa-list"style="color:red"></i>
								<!--[to be fetched form the database]-->
								<select name="parent" class="form-control">
									<?php $database=new Database();
									$resuls=$database->displayParent('teacher');
									foreach ($resuls as $key) {
									?>
									<option><?php echo $key->names;?></option>
								<?php }?>
								</select>
							</div>
							<div class="col-sm-3">
								<label for="Department" class="label-control">Dormitory</label>&nbsp;<i class="fa fa-bank"style="color:red"></i>
								<select name="dorm" class="form-control">
									<option>--N/A--</option>
									<?php $database=new Database();
									$results1=$database->displayTable('dorm');
									foreach ($results1 as $key) {
									?>
									<option><?php echo $key->dormName;?></option>
								<?php }?>
								</select>
							</div>
							<div class="col-sm-3">
								<label for="" class="label-control">Passport Photo</label>&nbsp;<i class="fas fa-image" style="color:red"></i>
								<input type="file" name="logo" value="<?php echo $key->photo;?>"/>
							</div>
							<div class="col-sm-3">
								<label for="Employee Number" class="label-control">School Fees</label>&nbsp;<i class="fas fa-tags"style="color:red"></i>
								<!--[generated by the system]-->
								<input type="text" class="form-control" name="fees" value="<?php echo $results->fees;?>" required/>
							</div>
						</div>
          <?php
         }
          ?>
						<button class="btn btn-success btn-raised" name="sub" type="submit" style="margin-top:20px">Update Details</button>
					</form>
				</div>
				</div>
			</div>
			<!-- start: FOOTER -->
	<?php include('include/footer.php');?>
		</div>
		<!-- start: MAIN JAVASCRIPTS -->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="vendor/modernizr/modernizr.js"></script>
		<script src="vendor/jquery-cookie/jquery.cookie.js"></script>
		<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
		<script src="vendor/switchery/switchery.min.js"></script>
		<script src="assets/js/main.js"></script>

		<script>
			jQuery(document).ready(function() {
				Main.init();
				FormElements.init();
			});
		</script>
		<!-- end: JavaScript Event Handlers for this page -->
		<!-- end: CLIP-TWO JAVASCRIPTS -->
	</body>
</html>
<?php }else{
	header("location: index.php");
}
