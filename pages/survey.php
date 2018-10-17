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
function show_text($question_items, $question_counter) {
	echo '<br><input id="',$question_counter,'" name="survey_question_', $question_counter,'" value="',$_SESSION["survey_question_".$question_counter],'" type="text" class="form-control survey-field border-input" placeholder="" required/><br>';
}
function show_textarea($question_items, $question_counter) {
	echo '<br><textarea id="',$question_counter,'" name="survey_question_', $question_counter,'" value="',$_SESSION["survey_question_".$question_counter],'" class="form-control survey-field border-input" required>'.$_SESSION["survey_question_".$question_counter].'</textarea><br>';
}
function show_checkbox($question_items, $question_counter) {
	//print_r($_SESSION);
	if(trim($question_items[2])!="")
	{
		$possible_values=explode("@@@",$question_items[2]);
		foreach($possible_values as $n=>$value)
		{
			$isChecked = (isset($_SESSION["survey_question_".$question_counter]) && $_SESSION["survey_question_".$question_counter][$n]==1);
			echo "<br><input class=\"survey-check\" id=\"{$question_counter}\" type=\"checkbox\" name=\"survey_question[{$n}]\"   value=\"1\" {$isChecked}> {$value} <br>";
		}
	}
	echo '
	<br>
	<br>
	<textarea name="survey_question_textarea_',$question_counter,'" style="float:center;width:55%;margin:0em 5em 0em 1em;padding:1em 1em" placeholder="Jegyzetek">'.$_SESSION["survey_question_textarea_".$question_counter].'</textarea>';
}
function show_radio($question_items, $question_counter) {
	if(trim($question_items[2])!="")
	{
		$possible_values=explode("@@@",$question_items[2]);
		foreach($possible_values as $value)
		{
			$isChecked = (isset($_SESSION["survey_question_".$question_counter]) && $_SESSION["survey_question_".$question_counter]==$value);
			echo '
			<br>
			<input id="'.
			$question_counter.
			'"'.
			'type="radio" value="'.
			$value.
			'" name="survey_question_'.$question_counter.'" '.
			($isChecked?'checked':'').
			' class="" required /> '.
			$value.
			'<br>'.
			' &nbsp;&nbsp;';
		}
	}
	echo '
	<br>
	<textarea name="survey_question_textarea_',$question_counter,'" style="float:center;width:55%;margin:1.4em 5em 0em 0em;padding:1em 1em" placeholder="Jegyzetek">'.$_SESSION["survey_question_textarea_".$question_counter].'</textarea>';
}
function show_select($question_items, $question_counter) {
	echo '<br><select id="', $question_counter, '" name="survey_question_', $question_counter, '" class="form-control border-input survey-field" required>';
	if(trim($question_items[2])!="")
	{
		//The @@@ is because of how the data is stored in the XML
		$possible_values=explode("@@@",$question_items[2]);
		foreach($possible_values as $value)
		{
			$isSelected = (isset($_SESSION["survey_question_".$question_counter]) && $_SESSION["survey_question_".$question_counter]==$value);
			echo '<option ',($isSelected?'selected':''),'>',$value,'</option>';
		}
	}
	echo '</select><br>';
	echo '
	<br>
	<textarea name="survey_question_textarea_',$question_counter,'" style="float:center;width:55%;margin:0em 5em 0em 1em;padding:1em 1em" placeholder="Jegyzetek">'.$_SESSION["survey_question_textarea_".$question_counter].'</textarea>';
}

	// ini_set('display_errors', 1);
	// ini_set('display_startup_errors', 1);
	// error_reporting(E_ALL);

	if(!isset($_SESSION)) { $_SESSION = []; }

	if(!empty($_POST))
	{
		if($this->settings["website"]["use_captcha_images"]=="1" &&
		 ( (md5($_POST['captcha_code']) != $_SESSION['code'])||
		  trim($_POST['captcha_code']) == "" ) )
		{
			?>
			<!-- <h2 class="custom-color"><?php //echo $this->texts["wrong_captcha"];?></h2> -->
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
				$survey_data.=$question_items[1].
				"###".(isset($_POST["survey_question_".
				$question_counter])?$this->filter_data($_POST["survey_question_".$question_counter]):"")."@@@";
				$survey_email.=$question_items[1].": ".
				(isset($_POST["survey_question_".
				$question_counter])?$this->filter_data($_POST["survey_question_".
				$question_counter]):"")."\n";
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
	$s_questions=explode(";;;",stripslashes($survey->questions));
	if($show_survey_form)
	{
	?>
		<form action="<?php
		($_GET['question'] == count($s_questions)) ? 'index.php' : ''
		?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="page" value="survey"/>
		<?php if($_GET['question'] == count($s_questions)) {  ?>
	<?php } ?>
		<input type="hidden" name="id" value="<?php echo $id;?>"/>



		<div class="clearfix"></div>


		<input type="hidden" name="survey_questions" id="survey_questions" value="<?php  echo $survey->questions;?>"/>
		<br/>
		<br/>

		<?php
		//Looping through the questions, which come from an XML file stored in /data
		$question_counter=0;
		foreach($s_questions as $question)
		{
			if(trim($question)=="") continue;
			$question_items = explode("---",$question);
			if(sizeof($question_items) != 3) continue;
		// This is so just one question appears per page
			if($_GET['question'] == $question_counter+1){
      ?>

			<div class="survey-question custom-color"><?php echo ($question_counter+1);?>. <?php echo $question_items[1]?></div>

			<?php
			// $question_items[0] is where the type of input is stored
			//echo 'actual type which is show and processing: ', $question_items[0];
			switch ($question_items[0]) {
				case 'Text':
					show_text($question_items, $question_counter);//separate code
				break;
				case 'Text area':
					show_textarea($question_items, $question_counter);// but it is text too
				break;
				case 'Checkbox':
					show_checkbox($question_items, $question_counter);
				break;
				case 'Radio button':
					show_radio($question_items, $question_counter);
				break;
				case 'Drop down':
					show_select($question_items, $question_counter);
				break;
			}
			?>
			<div class="clearfix"></div>

			<br/>
		<?php
		}
		// FOR LATER
		// foreach($_SESSION["survey_question"] as $question_counter=>$question_item){
		// 		foreach($question_item as $answer_type=>$answer){
		//
		// 			$_POST["survey_question"][$question_counter][$answer_type] = $answer;
		// 		}
		// }


//print_r($_POST);

		// if (!empty($_POST["survey_question_".$question_counter])) {
		// 	foreach($_POST["survey_question"][$question_counter] as $answer_type=>$answer){
		// 			$_SESSION["survey_question"][$question_counter][$answer_type] = $answer;
		// 	}
		// 	//echo '<br>type: ', $question_items[0], ' saveKey: ', " survey_question_".$question_counter, ' saveVal: ', $_POST["survey_question_".$question_counter];
		// 	// if(isset($_POST["survey_question"][$question_counter]["simple"])) {
		// 	// 	$_SESSION["survey_question"][$question_counter]["simple"] = strip_tags($_POST["survey_question_".$question_counter]);
		// 	// }
		// }
		if (!empty($_POST["survey_question_".$question_counter])) {
			//echo '<br>type: ', $question_items[0], ' saveKey: ', " survey_question_".$question_counter, ' saveVal: ', $_POST["survey_question_".$question_counter];
			if(isset($_POST["survey_question_".$question_counter])) {
				$_SESSION["survey_question_".$question_counter] = strip_tags($_POST["survey_question_".$question_counter]);
			}
		}
		if (!empty($_POST["survey_question_textarea_".$question_counter])) {
			if(isset($_POST["survey_question_textarea_".$question_counter])) {
				$_SESSION["survey_question_textarea_".$question_counter] = strip_tags($_POST["survey_question_textarea_".$question_counter]);
			}
		}
		$question_counter++;
  }
			//var_dump($_POST);
			//var_dump($_SESSION);
?>
<?php $end_survey = count($s_questions);
			$current_question = $_GET['question'];
?>
<input formaction="index.php?page=survey&id=<?php
echo $survey->id?>&question=<?php
echo ($current_question -1)?>"
id="previous_button" type="submit" value="&laquo; Előző" style="inline;<?php
if($_GET['question'] == '1') echo 'display:none'?>
" class="btn btn-default">

<input formaction="index.php?page=survey&id=<?php
echo $survey->id?>&question=<?php
echo ($current_question +1)?>"
id="next_button" type="submit" value="<?php
$next_finish = ($current_question != $end_survey-1) ? 'Következő' : 'Befejez'; echo $next_finish?> &raquo;" style="inline;<?php
 if($current_question == $end_survey) echo 'display:none'?>
 " class="btn btn-default"/>

	<div><p style="float:right;<?php
	 if($current_question == $end_survey)echo 'display:none'?>
	 "><i>question <?php echo $current_question?> of <?php echo $end_survey -1?></i></p></div>
		<div class="clearfix"></div>
<?php
if ($current_question == $end_survey) { ?>
<?php
if($survey->anonymous == "0") {
?>
			<hr/>

			<i><?php echo $this->texts["your_details"];?></i>
			<br/>
			<br/>

			<div class="survey-question custom-color"><?php
			echo $this->texts["name"];?>(*)</div>

			<input class="form-control survey-field" id="name" <?php
			if(isset($_POST["name"])) echo "value=\"".strip_tags($_POST["name"])."\"";?> name="name" placeholder="" type="text" required/>
			<br/>

			<div class="survey-question custom-color"><?php
			echo $this->texts["email"];?>(*)</div>
			<input class="form-control survey-field" id="email" <?php
			if(isset($_POST["email"])) echo "value=\"".strip_tags($_POST["email"])."\"";?> name="email" placeholder="example@domain.com" type="email" required/>

			<br/>


			<div class="survey-question custom-color"><?php
			echo $this->texts["phone"];?></div>
			<input class="form-control survey-field" id="phone" <?php
			if(isset($_POST["phone"])) echo "value=\"".strip_tags($_POST["phone"])."\"";?> name="phone" placeholder="" type="text"/>

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
			<input placeholder="" class="form-control survey-field" id="captcha_code" <?php
			if(isset($_POST["captcha_code"])) echo "value=\"".strip_tags($_POST["captcha_code"])."\"";?> name="captcha_code" type="text" required/>

		<?php
	}

		?>
		<br/>

		<button type="submit" class="btn btn-lg custom-back-color"><?php
		echo $this->texts["submit"];?></button>

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
