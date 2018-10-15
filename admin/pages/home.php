<?php
// PHP Survey All Rights Reserved
// A software product of NetArt Media, All Rights Reserved
// Find out more about our products and services on:
// http://www.netartmedia.net
?><?php
if(!defined('IN_SCRIPT')) die("");
?>

<?php
$surveys = simplexml_load_file($this->data_file);
$total = $surveys->xpath("/surveys/survey");

$this->SetAdminHeader($this->texts["dashboard"]);
?>

<div class="col-lg-4 col-sm-6">
	<div class="card">
		<div class="content">
			<div class="row">
				<div class="col-xs-4">
					<div class="icon-big icon-warning text-center">
						<i class="ti-notepad"></i>
					</div>
				</div>
				<div class="col-xs-8">
					<div class="numbers">
						<p><?php echo $this->texts["surveys"];?></p>
						<?php echo count($total);?>
					</div>
				</div>
			</div>
			<div class="footer">
				<hr />
				<div class="stats">
					<i class="ti-arrow-right"></i> <a href="index.php?page=surveys"><?php echo $this->texts["see_all"];?></a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-lg-4 col-sm-6">
	<div class="card">
		<div class="content">
			<div class="row">
				<div class="col-xs-4">
					<div class="icon-big icon-success text-center">
						<i class="ti-write"></i>
					</div>
				</div>
				<div class="col-xs-8">
					<div class="numbers">
						<p><?php echo $this->texts["completed_surveys"];?></p>
						<?php
						$total_completed=$this->count_all("../".$this->data_folder);
						echo $total_completed;

						?>

					</div>
				</div>
			</div>
			<div class="footer">
				<hr />
				<div class="stats">
					<i class="ti-arrow-right"></i> <a href="index.php?page=completed_surveys"><?php echo $this->texts["see_all"];?></a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="col-lg-4 col-sm-6">
	<div class="card">
		<div class="content">
			<div class="row">
				<div class="col-xs-5">
					<div class="icon-big icon-info text-center">
						<i class="ti-menu-alt"></i>
					</div>
				</div>
				<div class="col-xs-7">
					<div class="numbers">
						<p><?php echo $this->texts["total_questions"];?></p>
						<?php echo substr_count(file_get_contents($this->data_file), ';;;');?>
					</div>
				</div>
			</div>
			<div class="footer">
				<hr />
				<div class="stats">
					<i class="ti-arrow-right"></i> <a href="index.php?page=surveys"><?php echo $this->texts["edit_your_surveys"];?></a>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="clearfix"></div>



 <div class="col-md-12">

	<div class="card">

		<br/>



		<div class="clearfix"></div>

		<div class="header">
			<h4 class="title"><?php  echo $this->texts["your_latest_serveys"];?></h4>
		</div>

		<br/>
		<div class="table-responsive table-wrap">
			<table class="table table-striped">
			  <thead>
				<tr>
				  <th width="80"><?php echo $this->texts["edit"];?></th>
				  <th><?php echo $this->texts["survey_name"];?></th>

				  <th width="190"><?php echo $this->texts["total_questions"];?></th>
				  <th width="190"><?php echo $this->texts["total_completed"];?></th>

				</tr>
			  </thead>
		  <tbody>
		  <?php
			$all_surveys = simplexml_load_file($this->data_file);

			$surveys = array_reverse($all_surveys->xpath('survey'));
			$i=count($surveys)-1;

			foreach ($surveys as $survey)
			{
				if($i>=3) break;
				?>
				<tr>
					<td><a href="index.php?page=edit&id=<?php echo $i;?>"><img src="images/edit-icon.gif"/></a></td>

					<td><?php echo $survey->name;?></td>


					<td><a class="underline-link" href="index.php?page=edit&id=<?php echo $i;?>"><?php echo substr_count($survey->questions, ';;;');?></a></td>

					<td>
						<a class="underline-link" href="index.php?page=completed_surveys&survey_id=<?php echo $survey->id;?>"><?php
							echo $this->count_survey_files("../".$this->data_folder."/".$survey->id);
						?></a>
					</td>

				</tr>
				<?php
				$i--;
			}

		  ?>
			<tr>
				<td colspan="5">
					<i class="ti-arrow-right"></i> <a href="index.php?page=surveys"><?php echo $this->texts["see_all"];?></a>
				</td>
			</tr>
		  </tbody>
		</table>
	  </div>



	  <div class="clearfix"></div>


	</div>
</div>

<?php
if(isset($_REQUEST["login"])&&$_REQUEST["login"]=="1")
{
	if(file_exists("../data/login.xml"))
	{
		$login_data = simplexml_load_file("../data/login.xml");

		if($total_completed>$login_data->info[0]->total_completed)
		{
			?>
				<script>document.getElementById("top_notification").innerHTML="<i class=\"ti-bell\"></i> <a href=\"index.php?page=completed_surveys\"><?php echo ($total_completed - intval($login_data->info[0]->total_completed) )." ".$this->texts["new_completed"];?></a>";</script>
			<?php
		}

		$login_data->info[0]->last_login=time();
		$login_data->info[0]->total_completed=$total_completed;
		$login_data->asXML("../data/login.xml");
	}
}

?>
