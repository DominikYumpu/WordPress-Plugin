<div class="wrap">
	<h2>Yumpu.com ePapers</h2>
	<?php if($this->yumpu_error_message) { ?>
		<div id="message" class="error">
			<p><?php echo $this->yumpu_error_message; ?></p>
		</div>
	<?php } ?>
	
	<?php if($this->yumpu_success_message) { ?>
		<div id="message" class="updated">
			<p><?php echo $this->yumpu_success_message; ?>
				<!-- Dominik -->
				<?php

					try {
						session_start();

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_HTTPHEADER,
							array(
								'X-ACCESS-TOKEN: ' . $_SESSION['user_access_token']
							)
						);
						curl_setopt($ch, CURLOPT_URL, 'http://api.yumpu.com/2.0/document/progress.json?id=' . $_SESSION["progress_id"]);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
						$url_response = curl_exec($ch);
						curl_close($ch);

						if (isset($url_response) && !empty($url_response)) {
							$url_response = json_decode($url_response, TRUE);
							if (isset($url_response['state']) && $url_response['state'] == 'success' && isset($url_response['document'][0]['id']) && is_numeric($url_response['document'][0]['id'])) {
								$Reload = false;
								$_SESSION["progress_id"] = "";
							} else {
								$Reload = true;
							}
						}
					} catch(Exception $e){
						echo $e->getMessage();
					}

				?>

				<script type="text/javascript">

					document.addEventListener("DOMContentLoaded", function(event) {
						refreshFirst();
					});

					function refreshFirst(){
						t = setTimeout(refreshSite, 5000);
					}

					function refreshSite(){
						var Reload = "<?php echo $Reload; ?>";
						if (Reload == true) {
							parent.window.location.reload(true);
							clearTimeout(t);
						} else {
							var thisReload = true;
						}
					}
				</script>
			</p>

		</div>
	<?php } ?>
	
	
	<table id="dataTable" class="widefat ">
		<thead>
			<tr>
				<!-- Dominik Cover "<th>ID</th>" -->
				<th></th> <!--Dominik Cover-->
				<th>Title</th>
				<th>Shortcode</th>
				<th>State</th>
				<th>Visibility</th>
				<th>Created</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php $count = 0;?>
			<?php foreach($this->epapers as $ePaper) { ?>
				<tr class="<?php echo (++$count % 2) ? "" : "alternate"; ?>">
					<!-- Dominik Cover  "<td><?php echo $ePaper->getId(); ?></td>" -->
					<td width="60" align="center"><img src="<?php echo $ePaper->getImage_Small(); ?>" alt="<?php echo $ePaper->getTitle(); ?>" height="42" width="32"></td>
					<td><?php echo $ePaper->getTitle(); ?></td>

					<?php
						$myShortcode = "";
						if ($ePaper->getEpaper_id() > 0) {
							$myShortcode = '[YUMPU epaper_id='.$ePaper->getEpaper_id().' width=&quot;512&quot; height=&quot;384&quot;]';
						}
					?>

					<td>
						<form>
							<div class="input-group">
								<input type="text" style="width: 400px; background-color:white" class="form-control"
									   value="<?php echo $myShortcode; ?>" id="copytext" readonly">
							</div>
						</form>
					</td>

					<td id="myStatus"><?php if ($ePaper->getStatus() == "progress") :?><span class="dashicons dashicons-yes"></span><?php else: ?> <span class="dashicons dashicons-external"></span> <?php endif ?></td>
					<td><?php echo $ePaper->getPrivacy_Mode(); ?></td>
					<td><?php echo $ePaper->getCreate_Date(); ?></td>
					<!-- <td><button type="button" title="Edit on Yumpu"><a class="dashicons dashicons-admin-generic" href="<?php echo "https://www.yumpu.com/de/account/magazines/edit/".$ePaper->getEpaper_id(); ?>" ></a></button></td> -->
					<td><button type="button" ><a href="<?php echo "https://www.yumpu.com/de/account/magazines/edit/".$ePaper->getEpaper_id(); ?>" style="text-decoration: none;color: black;">Edit at Yumpu</a></button></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>

	<p style="clear:both;padding-bottom:20px;"></p>
	<h3>Create E-Paper</h3>
	<form style="margin-left: 30px;" id="api_token_form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="yumpu_action" value="create_epaper">
        <table class="form-table">
            <tbody>
                <tr>
					<th>Title:</th>
					<td>
						<input type="text" class="regular-text" name="yc_title" id="yc_title" value=""/>
					</td>
				</tr>
                <tr>
                    <th>Description:</th>
                    <td>
                        <textarea rows="5" cols="50" class="regular-text" name="yc_description" id="yc_description"></textarea>
                    </td>
                </tr>
                <tr>
                    <th>PDF-File:</th>
                    <td>
                        <input type="file" class="regular-text" name="yc_file" id="yc_file" value=""> <?php submit_button('Upload PDF','primary', 'submit',false, array('id'=>"upload_form") );?>
                    </td>
                </tr>
            </tbody>
        </table>                 
    </form>
</div>

<link rel="stylesheet" href="<?php echo plugins_url( 'misc/DataTables-1.10.2/media/css/jquery.dataTables.css', dirname(__FILE__) );?>">
<script src="<?php echo plugins_url( 'misc/DataTables-1.10.2/media/js/jquery.dataTables.min.js', dirname(__FILE__) );?>"></script>
<script>
	$=jQuery.noConflict();
	$('#dataTable').dataTable({
		"aaSorting": [[ 0, "desc" ]]
    });


	window.onload = function(){
		try {

			$(document.body).on('click', 'input', function() {
				if(this.id == 'copytext') {
					if (copyToClipboard(this) == true) {
						alert("Successfully copied shortcode to clipboard");
					}
				}
			});
		} catch(err) {
			alert(err.message);
		}
	};


	function copyToClipboard(elem) {
		// create hidden text element, if it doesn't already exist
		var targetId = "_hiddenCopyText_";
		var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
		var origSelectionStart, origSelectionEnd;
		if (isInput) {
			// can just use the original source element for the selection and copy
			target = elem;
			origSelectionStart = elem.selectionStart;
			origSelectionEnd = elem.selectionEnd;
		} else {
			// must use a temporary form element for the selection and copy
			target = document.getElementById(targetId);
			if (!target) {
				var target = document.createElement("textarea");
				target.style.position = "absolute";
				target.style.left = "-9999px";
				target.style.top = "0";
				target.id = targetId;
				document.body.appendChild(target);
			}
			target.textContent = elem.textContent;
		}
		// select the content
		var currentFocus = document.activeElement;
		target.focus();
		target.setSelectionRange(0, target.value.length);

		// copy the selection
		var succeed;
		try {
			succeed = document.execCommand("copy");
		} catch(e) {
			succeed = false;
		}
		// restore original focus
		if (currentFocus && typeof currentFocus.focus === "function") {
			currentFocus.focus();
		}

		if (isInput) {
			// restore prior selection
			elem.setSelectionRange(origSelectionStart, origSelectionEnd);
		} else {
			// clear temporary content
			target.textContent = "";
		}
		return succeed;
	}


</script>
