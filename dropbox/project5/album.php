<?php
	require_once("authentication.php");
?>

<html>
	<head>
		<title>
			Dropbox photos application
		</title>
		<link rel="stylesheet" type="text/css" href="album.css">
		<script type="text/javascript">
			function setMainImage(imageName) {
				document.getElementById("main_img").src = imageName;
			}
		</script>
	</head>
	<body>
	
		<div class="header" > 
			<h2>Dropbox Images</h2>
		</div>
	
		<?php
			if(isset($_FILES['userfile'])) {
				$fileName = $_FILES['userfile']['name'];
				$tmpFileName = $_FILES['userfile']['tmp_name'];
				$fileType = pathinfo($fileName,PATHINFO_EXTENSION);
				if($fileType != "jpg") {
					echo "Please selecta a *.jpg file";
				} else {
					$dropbox->UploadFile($tmpFileName,$fileName);
				}
			}
			if(isset($_GET["deleteFile"])) {
                $dropbox->Delete($_GET["deleteFile"]);
			}
		?>
		
		<div id="upload" class="upload" >
		<form enctype="multipart/form-data" action="album.php" method="POST">
			<input type="hidden" name="MAX FILE SIZE" value="3000000" />
			Upload file : <input name="userfile" type="file" class="custom-file-input" />
			<input type="submit" value="Upload" class="custom_button" />
		</form>
		</div>
	
		<?php
			$files = $dropbox->GetFiles("",false);
			
			if(!empty($files)) {
				echo "<div class='list_div' >";
				echo "<form method='post'  >";
				echo "<ul>";			
				foreach ($files as $key => $value) {
					echo "<li>";
					$thumbnail = base64_encode($dropbox->GetThumbnail($value->path));
					echo "<img class='thumbnail' src=\"data:image/jpeg;base64,$thumbnail\" />";				
					echo "<input type='submit' class='custom_button right' value='Delete' formaction='album.php?deleteFile=" . urlencode($key) . "' /><br/>";
					echo "<a href=\"javascript:setMainImage('".$dropbox->GetLink($value,false)."')\" >$key</a>";
                    
					echo "</li>";
				}			
				echo "</ul>";
				echo "</form>";				
				echo "</div>";
			}
		?>
		
		<div class="main_img_div" >
			<img id="main_img" class="main_image" />
		</div>	
	
	</body>
</html>


	