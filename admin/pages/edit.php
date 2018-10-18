<?php
if(!defined('IN_SCRIPT')) die("");

$id=intval($_REQUEST["id"]);

$this->ms_i($id);
?>

<?php
$show_add_form=true;

$xml = simplexml_load_file($this->data_file);

if(isset($_POST["proceed_delete"])&&$_POST["proceed_delete"]=="1")
{
	$xml->survey[$id]->questions=stripslashes($_POST["survey_questions"]);
	$xml->asXML($this->data_file);

}
else
if(isset($_POST["proceed_save"]))
{
	$xml->survey[$id]->description=stripslashes($_POST["survey_description"]);
	$xml->survey[$id]->name=stripslashes($_POST["survey_name"]);
	//$xml->survey[$id]->questions=stripslashes($_POST["survey_type"]);

	if(isset($_POST["anonymous"]))
	{
		$xml->survey[$id]->anonymous="1";
	}
	else
	{
		$xml->survey[$id]->anonymous="0";
	}

	$survey_questions = stripslashes($_POST["survey_questions"]);

	$questions = explode(';;;',$survey_questions);

	$new_survey_questions="";

	for($i=0;$i<sizeof($questions);$i++)
	{
		if(trim($questions[$i])=="") continue;

		$question_items = explode('---',$questions[$i]);

		$new_survey_questions.=$question_items[0];

		if(isset($_POST["survey_question_".$i]) && $_POST["survey_question_".$i]!="")
		{
			$new_survey_questions.="---".$_POST["survey_question_".$i];
		}
		else
		{
			$new_survey_questions.="---".$question_items[1];
		}

		if(isset($_POST["possible_answers_".$i]) && $_POST["possible_answers_".$i]!="")
		{
			$new_survey_questions.="---".str_replace("\r","",str_replace("\n","@@@",trim($_POST["possible_answers_".$i])));
		}
		else
		{
			$new_survey_questions.="---".$question_items[2];
		}

		$new_survey_questions.=";;;";

	}



	$xml->survey[$id]->questions=$new_survey_questions;


	$xml->asXML($this->data_file);
	echo "<h3>".$this->texts["modifications_saved"]."</h3><br/>";
}

if($show_add_form)
{
?>
 <div class="card">


		<a class="btn btn-default pull-right" href="index.php?page=surveys" style="margin-top:12px;margin-right:12px;"><i class="ti-angle-left"></i> <?php echo $this->texts["go_back"];?></a>
		<div class="clearfix"></div>

		<div class="header">
			<h4 class="title"><?php  echo $this->texts["edit_survey"];?></h4>
		</div>

		<div class="content">

			<form id="survey_create_form" action="index.php" method="post"   enctype="multipart/form-data">
			<input type="hidden" name="page" value="edit"/>
			<input type="hidden" name="proceed_save" value="1"/>
			<input type="hidden" id="proceed_delete" name="proceed_delete" value="0"/>
			<input type="hidden" name="id" value="<?php echo $id;?>"/>


				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label><?php  echo $this->texts["survey_name"];?></label>
							<input type="text" name="survey_name" class="form-control border-input"  value="<?php echo $xml->survey[$id]->name;?>">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<label><?php  echo $this->texts["survey_description"];?></label>
							<textarea type="text" id="survey_description" name="survey_description" class="form-control border-input"><?php echo $xml->survey[$id]->description;?></textarea>
						</div>
					</div>
				</div>


				<div class="row">
					<div class="col-md-12">
						<div class="form-group">

							<input type="checkbox" id="anonymous" value="1" name="anonymous" <?php if($xml->survey[$id]->anonymous=="1") echo "checked";?>/>

							<?php  echo $this->texts["make_anonymous"];?>

						</div>
					</div>
				</div>





			<div class="clearfix"></div>

			<hr/>

			<input type="hidden" name="survey_questions" id="survey_questions" value="<?php  echo $xml->survey[$id]->questions;?>"/>
			<br/>
			<h4 name="edit_form" class="title"><?php  echo $this->texts["edit_survey_questions"];?></h4>
			<br/>
			<table id="survey_questions_table" class="table">

			<tbody id="custom_display">
			<?php
			$custom_amenities=explode(";;;",stripslashes($xml->survey[$id]->questions));
			$line_counter=0;
			foreach($custom_amenities as $question)
			{
				if(trim($question)=="") continue;
				$question_items=explode("---",$question);
				if(sizeof($question_items) != 3) continue;
			?>
			   <tr style="cursor:pointer;" id="line<?php echo $line_counter;?>">
					 <td style="">
					<?php if(isset($_POST['survey_type_'.$line_counter])) $question_items[0] = $_POST['survey_type_'.$line_counter]; ?>
					<input id="survey_type_<?php echo $line_counter;?>" name="survey_type_<?php echo $line_counter;?>" value="<?php $value = (isset($_POST['survey_type_'.$line_counter])) ? $_POST['survey_type_'.$line_counter] : $question_items[0]; echo $value;?>" class="form-control border-input"/>
				</td>
				<td>
					<input value="<?php echo $question_items[1];?>" id="survey_question_<?php echo $line_counter;?>" name="survey_question_<?php echo $line_counter;?>" type="text" class="form-control border-input"  placeholder=""/>
				</td>
				<td>
					<?php
					if(trim($question_items[2])!="")
					{
					?>
						<textarea id="possible_answers_<?php echo $line_counter;?>" name="possible_answers_<?php echo $line_counter;?>" rows="7" type="text" class="form-control border-input"><?php echo str_replace("@@@","\n",$question_items[2]);?></textarea>

					<?php
					}
					?>

				</td>
				<td><a onclick="upNdown('up');" id="up_arrow" class="btn btn-default" style="font-size:2rem;color:black">&uarr;</a></td>
				<td><a onclick="upNdown('down');" id="down_arrow" class="btn btn-default" style="font-size:2rem;color:black">&darr;</a></td>
				<td align="right" valign="top">
					<a href="javascript:RemoveLine('<?php echo $line_counter;?>','<?php echo addslashes($question);?>')"><img src="images/closeicon.png"/></a>
				</td>
			  </tr>
			<?php
				$line_counter++;
			}
			//var_dump($_POST);
			?>
			<style>
				 .selected {background-color: #D3D3D3; color: #fff;font-weight: bold};
			</style>
			<script>

			var index;  // variable to set the selected row index
            function getSelectedRow()
            {
                var table = document.getElementById("survey_questions_table");
                for(var i = 0; i < table.rows.length; i++)
                {
                    table.rows[i].onclick = function()
                    {
                        // clear the selected from the previous selected row
                        // the first time index is undefined
                        if(typeof index !== "undefined"){
                            table.rows[index].classList.toggle("selected");
                        }

                        index = this.rowIndex;
                        this.classList.toggle("selected");

                    };
                }

            }


            getSelectedRow();


            function upNdown(direction)
            {
                var rows = document.getElementById("survey_questions_table").rows,
                    parent = rows[index].parentNode;
                 if(direction === "up")
                 {
                     if(index > 0){
                        parent.insertBefore(rows[index],rows[index - 1]);
                        // when the row go up the index will be equal to index - 1
                        index--;
                    }
                 }

                 if(direction === "down")
                 {
                     if(index < rows.length){
                        parent.insertBefore(rows[index +1],rows[index]);
                        // when the row go down the index will be equal to index + 1
                        index++;
                    }
                 }
            }

			var line_counter=<?php echo $line_counter;?>;

			function AddQuestion()
			{


				var survey_question = document.getElementById("survey_question").value;

				if(survey_question=="")
				{
					alert("<?php echo $this->texts["enter_survey_question"];?>");
					document.getElementById("survey_question").focus();
					return;
				}

				var selected_field_type = document.getElementById("selected_type").value;
				var possible_answers = document.getElementById("possible_answers").value;


				possible_answers=possible_answers.replace(/@@@/gm, " / ");

				var preview_html = "";

				preview_html=
				"<tr id=\"line"+line_counter+"\" class=\"line-custom\">";


				preview_html+="<td>"+survey_question+"</td>";
				preview_html+="<td>"+possible_answers+"</td>";
				preview_html+="<td align=\"right\"><a href=\"javascript:RemoveLine('"+line_counter+"','"+survey_question+"')\"><img src=\"images/closeicon.png\"/></a></td>";

				preview_html+="</tr>";

				document.getElementById("custom_display").innerHTML+=preview_html;

				line_counter++;
				document.getElementById("survey_questions").value+=selected_field_type+"---"+survey_question+"---"+possible_answers+";;;";

				document.getElementById("survey_question").value="";
			}

			function RemoveLine(x,y)
			{
				document.getElementById("line"+x).style.display="none";
				document.getElementById("survey_questions").value=document.getElementById("survey_questions").value.replace(y+";;;", "");
				document.getElementById("proceed_delete").value="1";

				document.getElementById("survey_create_form").submit();
			}

			</script>
			</tbody>
			</table>
			<br/>


			<h4 class="title"><?php  echo $this->texts["add_new_questions"];?></h4>
			<br/>

			<div class="row">

				<div class="col-md-2">
					<div class="form-group">
						<input type="hidden" id="selected_type" name="selected_type" value="Text"/>

						<label><?php echo $this->texts["field_type"];?></label>
						<div class="clearfix"></div>
						<div class="btn-group">

							<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">

							<img src="images/form_textbox.gif"> <?php echo $this->texts["textbox"];?> <span class="caret"></span></a>

							<ul class="dropdown-menu">
								<li><a href="javascript:void(0);"><img src="images/form_combo.gif"/><?php echo $this->texts["dropdown"];?></a></li>
								<li><a href="javascript:void(0);"><img src="images/form_textarea.gif"/><?php echo $this->texts["textarea"];?></a></li>
								<li><a href="javascript:void(0);"><img src="images/form_check.gif"/><?php echo $this->texts["checkbox"];?></a></li>
								<li><a href="javascript:void(0);"><img src="images/form_radio.gif"/><?php echo $this->texts["radiobutton"];?></a></li>
							</ul>
						</div>

						<script>
						$(".dropdown-menu li a").click(function () {
							var selText = $(this).text().trim();

							document.getElementById("selected_type").value=selText;

							if(selText=="Drop down"||selText=="Checkbox"||selText=="Radio button")
							{
								document.getElementById("answers_group").style.display="block";
							}
							else
							{
								document.getElementById("answers_group").style.display="none";
							}

							var imgSource = $(this).find('img').attr('src');
							var img = '<img src="' + imgSource + '"/>';
							$(this).parents('.btn-group').find('.dropdown-toggle').html(img + ' ' + selText + ' <span class="caret"></span>');
						});

						</script>




					</div>
				</div>



				<div class="col-md-5">
					<div class="form-group">
						<label><?php echo $this->texts["question"];?></label>
						<input id="survey_question" name="survey_question" type="text" class="form-control border-input"  placeholder=""/>
					</div>
				</div>


				<div class="col-md-3">
					<div class="form-group no-display" id="answers_group">
						<label><?php echo $this->texts["possible_answers"];?></label>
						<textarea id="possible_answers" name="possible_answers" rows="7" type="text" class="form-control border-input"></textarea>
					</div>
				</div>

				<div class="col-md-1">
					<div class="form-group">
						<br/>
						<a class="btn btn-default btn-wd" href="javascript:AddQuestion()"><?php echo $this->texts["add"];?></a>
					</div>
				</div>

			</div>


			<div class="clearfix"></div>
			<br/>
			<br/>
			<hr/>
			<br/>

			<button type="submit" class="btn btn-primary btn-fill btn-wd"><?php echo $this->texts["save_survey"];?></button>

			<div class="clearfix"></div>
			<br/>
			</form>
		</div>
	</div>
<?php
}
?>
