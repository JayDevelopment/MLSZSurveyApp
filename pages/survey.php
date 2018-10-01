<?php
// PHP Survey, http://www.netartmedia.net/php-survey
// A software product of NetArt Media, All Rights Reserved
// Find out more about our products and services on:
// http://www.netartmedia.net
// Released under the MIT license

if(!defined('IN_SCRIPT')) die("");

$id=$_REQUEST["id"];

$this->check_id($id);

$show_survey_form=true;

$xml = simplexml_load_file($this->data_file);

$nodes = $xml->xpath('//surveys/survey/id[.="'.$id.'"]/parent::*');
$survey = $nodes[0];

?>
<div class="container">
<div class="block-wrap">


	<?php
	if(isset($_REQUEST["proceed_submit"]))
	{

		if($this->settings["website"]["use_captcha_images"]=="1" && ( (md5($_POST['captcha_code']) != $_SESSION['code'])|| trim($_POST['captcha_code']) == "" ) )
		{
			?>
			<h2 class="custom-color"><?php echo $this->texts["wrong_captcha"];?></h2>
			<br/>
			<script>
			document.getElementById("captcha_code").focus();
			</script>
			<?php
		}
		else
		{

			if(!file_exists("data/".$survey->id))
			{
				if(!mkdir("data/".$survey->id))
				{

				}
			}

			$survey_result_file="data/".$survey->id."/".md5($survey->id.$this->salt)."_".time().".xml";
			if(!file_exists($survey_result_file))
			{
				file_put_contents($survey_result_file, "<results></results>");
			}

			$survey_results = simplexml_load_file($survey_result_file);
			$survey_result = $survey_results->addChild('result');
			$survey_result->addChild('name', (isset($_POST["name"])?$this->filter_data($_POST["name"]):"") );
			$survey_result->addChild('email', (isset($_POST["email"])?$this->filter_data($_POST["email"]):"")  );
			$survey_result->addChild('phone', (isset($_POST["phone"])?$this->filter_data($_POST["phone"]):"") );

			$s_questions=explode(";;;",stripslashes($survey->questions));
			$question_counter=0;

			$survey_data="";
			$survey_email="";

			foreach($s_questions as $question)
			{
				$question_items=explode("---",$question);
				if(sizeof($question_items) != 3) continue;
				$survey_data.=$question_items[1]."###".(isset($_POST["survey_question_".$question_counter])?$this->filter_data($_POST["survey_question_".$question_counter]):"")."@@@";
				$survey_email.=$question_items[1].": ".(isset($_POST["survey_question_".$question_counter])?$this->filter_data($_POST["survey_question_".$question_counter]):"")."\n";
				$question_counter++;
			}

			$survey_result->addChild('data', $survey_data);


			$survey_results->asXML($survey_result_file);


			?>
			<br/>
			<h2 class="custom-color"><?php echo $this->texts["survey_thank_you"];?></h2>
			<br/>
			<br/>
			<br/>
			<?php

			if($this->settings["website"]["send_notifications"]=="1")
			{
				mail
				(
					$this->settings["website"]["admin_email"],
					$this->texts["new_completed_survey"]." - ".$survey->name,
					$survey_email
				);
			}

			$show_survey_form=false;
		}
	}
	else
	{
	?>
		<h2 class="custom-color"><?php echo $survey->name;?></h2>
		<i><?php echo $survey->description;?></i>
	<?php
	}


	if($show_survey_form)
	{
	?>
		<form action="index.php" method="post"   enctype="multipart/form-data">
		<input type="hidden" name="page" value="survey"/>
		<input type="hidden" name="proceed_submit" value="1"/>
		<input type="hidden" name="id" value="<?php echo $id;?>"/>



		<div class="clearfix"></div>


		<input type="hidden" name="survey_questions" id="survey_questions" value="<?php  echo $survey->questions;?>"/>
		<br/>
		<br/>

		<?php
$s_questions=explode(";;;",stripslashes($survey->questions));
$question_counter=0;
		foreach($s_questions as $question)
		{
			if(trim($question)=="") continue;
			$question_items=explode("---",$question);
			if(sizeof($question_items) != 3) continue;
			?>
		<?php if($_GET['question'] == $question_counter+1){ ?>


			<div class="survey-question custom-color"><?php echo ($question_counter+1);?>. <?php echo $question_items[1]?></div>

			<?php

			if($question_items[0]=="Text")
			{
			?>
				<input required name="survey_question_<?php echo $question_counter;?>" value="<?php if(isset($_POST["survey_question_".$question_counter])) echo strip_tags($_POST["survey_question_".$question_counter]);?>" type="text" class="form-control survey-field border-input"  placeholder=""/>
			<?php
			}
			else
			if($question_items[0]=="Text area")
			{
			?>
				<textarea name="survey_question_<?php echo $question_counter;?>" class="form-control survey-field border-input"><?php if(isset($_POST["survey_question_".$question_counter])) echo strip_tags($_POST["survey_question_".$question_counter]);?></textarea>
			<?php
			}
			else
			if($question_items[0]=="Drop down")
			{
				echo '<select required name="survey_question_'.$question_counter.'" class="form-control border-input survey-field">';

				if(trim($question_items[2])!="")
				{
					$possible_values=explode("@@@",$question_items[2]);
					foreach($possible_values as $value)
					{
						echo '<option '.(isset($_POST["survey_question_".$question_counter])&&$_POST["survey_question_".$question_counter]==$value?"selected":"").'>'.$value.'</option>';
					}
				}

				echo '</select>';
			}
			else
			if($question_items[0]=="Checkbox")
			{
				if(trim($question_items[2])!="")
				{
					$possible_values=explode("@@@",$question_items[2]);
					foreach($possible_values as $value)
					{
						echo '<input type="checkbox" value="'.$value.'" name="survey_question_'.$question_counter.'" '.(isset($_POST["survey_question_".$question_counter])&&$_POST["survey_question_".$question_counter]==$value?"checked":"").' class="survey-check"/> '.$value.'';
					}
				}
			}
			else
			if($question_items[0]=="Radio button")
			{
				if(trim($question_items[2])!="")
				{
					$possible_values=explode("@@@",$question_items[2]);
					foreach($possible_values as $value)
					{
						echo '<input type="radio" required value="'.$value.'" name="survey_question_'.$question_counter.'" '.(isset($_POST["survey_question_".$question_counter])&&$_POST["survey_question_".$question_counter]==$value?"checked":"").' class=""/> '.$value.' &nbsp;&nbsp;';
					}
				}
			}
			?>
			<div class="clearfix"></div>

			<br/>
		<?php

		}
		$question_counter++;
		}
		?>
	<a style="inline"href="#">&laquo; Previous</a>
	<a style="inline"href="#"> Next &raquo;</a>
		<div class="clearfix"></div>
<?php if ($_GET['question'] > count($questions)) { ?>
	<?php
		if($survey->anonymous == "0")
		{
	?>
			<hr/>

			<i><?php echo $this->texts["your_details"];?></i>
			<br/>
			<br/>

			<div class="survey-question custom-color"><?php echo $this->texts["name"];?>(*)</div>

			<input class="form-control survey-field" id="name" <?php if(isset($_POST["name"])) echo "value=\"".strip_tags($_POST["name"])."\"";?> name="name" placeholder="" type="text" required/>
			<br/>

			<div class="survey-question custom-color"><?php echo $this->texts["email"];?>(*)</div>
			<input class="form-control survey-field" id="email" <?php if(isset($_POST["email"])) echo "value=\"".strip_tags($_POST["email"])."\"";?> name="email" placeholder="example@domain.com" type="email" required/>

			<br/>


			<div class="survey-question custom-color"><?php echo $this->texts["phone"];?></div>
			<input class="form-control survey-field" id="phone" <?php if(isset($_POST["phone"])) echo "value=\"".strip_tags($_POST["phone"])."\"";?> name="phone" placeholder="" type="text"/>

			<div class="clearfix"></div>

			<br/>
		<?php
}
		?>

		<?php
		if($this->settings["website"]["use_captcha_images"]=="1")
		{
		?>
			<img src="include/sec_image.php" width="150" height="30"/>
			<br/>
			<input placeholder="" class="form-control survey-field" id="captcha_code" <?php if(isset($_POST["captcha_code"])) echo "value=\"".strip_tags($_POST["captcha_code"])."\"";?> name="captcha_code" type="text" required/>

		<?php
	}
		?>
		<br/>

		<button type="submit" class="btn btn-lg custom-back-color"><?php echo $this->texts["submit"];?></button>

		<div class="clearfix"></div>
		<br/>
		</form>

<?php
}
}
?>

</div>
</div>
<?php
$this->Title($survey->name);
$this->MetaDescription($survey->description);
?>
