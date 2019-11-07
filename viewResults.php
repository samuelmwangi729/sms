<?php
session_start();
require_once('Database.php');
$_SESSION['exam']="";
$_SESSION['student']="";
$_SESSION['class']="";
$_SESSION['stream']="";
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
	$_SESSION['exam']=$_POST['exam'];
	$_SESSION['student']=$_POST['student'];
	$_SESSION['class']=$_POST['class'];
	$_SESSION['stream']=$_POST['stream'];
	$_SESSION['year']=$_POST['year'];
}

?>
			<div class="app-content">

						<?php include('include/header.php');?>
				<!-- end: TOP NAVBAR -->
				<div class="main-content" >
					<div class="wrap-content container" id="container">
            Manage Exam Results
						<?php
						if(isset($_GET['Activate'])){
							$id=$_GET['Activate'];
							$db=new Database();
							$db->query("UPDATE examResults SET status=:status WHERE id=:id");
							$db->bind(':status',0);
							$db->bind(':id',$id);
							$status=$db->execute();
							if($status){
								echo "<div class='alert alert-success 'id='alert'>
									<strong>Exam Results Approved!!!</strong>
								</div>";
							}
						}
						if(isset($_GET['Deactivate'])){
							$id=$_GET['Deactivate'];
							$db=new Database();
							$db->query("UPDATE examResults SET status=:status WHERE id=:id");
							$db->bind(':status',1);
							$db->bind(':id',$id);
							$status=$db->execute();
							if($status){
								echo "<div class='alert alert-danger'id='alert'>
									<strong>Exam Results Cancelled!!</strong>
								</div>";
							}else{
								echo "<div class='alert alert-danger'id='alert'>
									<strong>Unknown Error Occurred!!</strong>
								</div>";
							}
						}
						if(isset($_GET['Delete'])){
							$id=$_GET['Delete'];
							$db=new Database();
							$delete=$db->delete('examResults',$id);
							if($delete){
								echo "<div class='alert alert-danger'id='alert'>
									<strong>Exam Results deleted!!</strong>
								</div>";
							}
						}
						?>
						<form method="POST" action="viewResults.php">
							<?php echo $Msg;?>
							<fieldset>
								<legend style="border:0px;font-size:30px;"><strong>Please select the Exam To view Results</strong></legend>
								<div class="row">
									<div class="col-sm-2">
										<label for="Subject" class="label-control">Exam</label>&nbsp;<i class="fa fa-list" style="color:red"></i>
										<select name="exam" class="form-control">
											<?php $database=new Database();
											$results=$database->displayApproved('examinations');
											foreach ($results as $key) {
											?>
											<option><?php echo $key->examTitle;?></option>
										<?php }?>
										</select>
									</div>
									<div class="col-sm-2">
										<label for="Subject" class="label-control">Student</label>&nbsp;<i class="fa fa-list" style="color:red"></i>
										<select name="student" class="form-control">
											<?php $database=new Database();
											$results=$database->displayApproved('student');
											foreach ($results as $key) {
											?>
											<option><?php echo $key->regNo;?></option>
										<?php }?>
										</select>
									</div>
									<div class="col-sm-2">
										<label for="Subject" class="label-control">Class</label>&nbsp;<i class="fa fa-list" style="color:red"></i>
										<select name="class" class="form-control">
											<?php $database=new Database();
											$results=$database->displayApproved('classes');
											foreach ($results as $key) {
											?>
											<option><?php echo $key->form;?></option>
										<?php }?>
										</select>
									</div>
									<div class="col-sm-2">
										<label for="Subject" class="label-control">Stream</label>&nbsp;<i class="fa fa-list" style="color:red"></i>
										<select name="stream" class="form-control">
											<?php $database=new Database();
											$results=$database->displayApproved('stream');
											foreach ($results as $key) {
											?>
											<option><?php echo $key->streamName;?></option>
										<?php }?>
											<option><?php echo "All Stream";?></option>
										</select>
									</div>
									<div class="col-sm-2">
										<label for="Subject" class="label-control">Year</label>&nbsp;<i class="fa fa-list" style="color:red"></i>
										<select name="year" class="form-control">
											<option><?php echo "2023"?></option>
											<option><?php echo "2022"?></option>
											<option><?php echo "2021"?></option>
											<option><?php echo "2020"?></option>
											<option><?php echo "2019"?></option>
										</select>
									</div>
									<div class="col-sm-2">
										<br>
										<button class="badge badge-success fa fa-upload" style="margin-top:10px;" name="sub">&nbsp;&nbsp;Select Details</button>
									</div>
								</div>
							</fieldset>
						</form>
            <div class="table table-responsive">
    					<table class="table table-striped table-codensed table-hover table-bordered">
    						<thead class="text-center">
    							<tr>
										<td>S/N</td>
										<td>Names</td>
    								<td>Year</td>
    								<td>Exam</td>
    								<td>Subject</td>
    								<td>Marks</td>
    								<td>Grade</td>
    								<td colspan="2">Status</td>
    							</tr>
    						</thead>
    						<tbody>
								<tr>
									<?php
									$database=new Database();
									if($_SESSION['exam']=="" || $_SESSION['student']=="" || $_SESSION['class']=="" || $_SESSION['stream']==""){
										$database->query("SELECT * FROM examResults ORDER BY id ASC");
									}else{
										$database->query("SELECT * FROM examResults   WHERE exam=:exam AND studentReg=:reg AND Class=:class AND Stream=:stream AND Year=:year  ORDER BY id ASC");
										$database->bind(':exam',$_SESSION['exam']);
										$database->bind(':reg',$_SESSION['student']);
										$database->bind(':class',$_SESSION['class']);
										$database->bind(':stream',$_SESSION['stream']);
										$database->bind(':year',$_SESSION['year']);
									}
								  $results=$database->resultSet();
									if($database->rowCount()==0){
										echo "<div class='alert alert-danger'id='alert'>
											<strong>No record Found!!</strong>
										</div>";
									}else{
									foreach ($results as $key) {
										$status="";
									?>
											<td><?php echo $key->studentReg;?></td>
											<td><?php
											$db=new Database();
											$names=$db->getName($key->studentReg);
											echo $names;
											?></td>
											<td><?php echo $key->Year." Term ".$key->term?></td>
											<td><?php echo $key->exam;?></td>
											<td><?php echo $key->subject;?></td>
											<td><?php echo $key->marks;?></td>
										<td><?php
										$category=$db->getCategory($key->subject);
										$Grade=$db->getGrade($category,$key->marks);
										echo $Grade;
										?></td>
										<td><?php
										if($key->status==1){
											echo "<button class='badge badge-danger'>Cancelled</button>";
										}else{
												echo "<button class='badge badge-success'>Approved</button>";
										}
										?></td>
										<?php if(isset($_POST['sub'])){
											echo "<td><a href='viewIndResults.php?View={$key->id}' target='_blank' class='badge badge-warning'><i class='fa fa-eye'>&nbsp;View</i></a></td>";
											}?>
										</tr>
								<?php }}?>
    						 </tbody>
    					 </table>
    				</div>
						<!-- start: PAGE TITLE -->
						<!-- end: PAGE TITLE -->
						<!-- start: BASIC EXAMPLE -->
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
