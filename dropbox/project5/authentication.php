<?php

error_reporting(E_ALL);
enable_implicit_flush();
set_time_limit(0);

require_once("DropboxClient.php");

$dropbox = new DropboxClient(array(
	'app_key' => "",      
	'app_secret' => "",   
	'app_full_access' => false,
),'en');


$access_token = load_token("access");
if(!empty($access_token)) {
	$dropbox->SetAccessToken($access_token);
}
elseif(!empty($_GET['auth_callback'])) 
{
	$request_token = load_token($_GET['oauth_token']);
	if(empty($request_token)) die('Request token not found!');
	
	$access_token = $dropbox->GetAccessToken($request_token);	
	store_token($access_token, "access");
	delete_token($_GET['oauth_token']);
}

if(!$dropbox->IsAuthorized())
{
	$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
	$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
	$request_token = $dropbox->GetRequestToken();
	store_token($request_token, $request_token['t']);
	die("Authentication required. <a href='$auth_url'>Click here.</a>");
}

/*
$files = $dropbox->GetFiles("",false);

echo "<ul>";
if(!empty($files)) {
	foreach ($files as $key => $value) {
		echo "<li>";
		$thumbnail = base64_encode($dropbox->GetThumbnail($value->path));
		echo "<img src=\"data:image/jpeg;base64,$thumbnail\" />";
		echo "$key";
		echo "<input type='button' value='Delete' />";
		echo "</li>";
	}
*/	
	
	//print_r(array_keys($files));
       
//    $file = reset($files);
//	$test_file = "test_download_".basename($file->path);
	
//	echo "<img src='".$dropbox->GetLink($file,false)."'/></br>";
	
/*	echo "$test_file</br>";
	echo "\r\n\r\n<b>Downloading $file->path:</b>\r\n";
	print_r($dropbox->DownloadFile($file, $test_file));
		
	echo "\r\n\r\n<b>Uploading $test_file:</b>\r\n";
	print_r($dropbox->UploadFile($test_file));
	echo "\r\n done!";		
*/
//}
//echo "</ul>";

/*	
echo "\r\n\r\n<b>Searching for JPG files:</b>\r\n";	
$jpg_files = $dropbox->Search("/", ".jpg", 5);
if(empty($jpg_files))
	echo "Nothing found.";
else {
	print_r($jpg_files);
	$jpg_file = reset($jpg_files);

	echo "\r\n\r\n<b>Thumbnail of $jpg_file->path:</b>\r\n";	
	$img_data = base64_encode($dropbox->GetThumbnail($jpg_file->path));
	echo "<img src=\"data:image/jpeg;base64,$img_data\" alt=\"Generating PDF thumbnail failed!\" style=\"border: 1px solid black;\" />";
}
*/

function store_token($token, $name)
{
	if(!file_put_contents("tokens/$name.token", serialize($token)))
		die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
}

function load_token($name)
{
	if(!file_exists("tokens/$name.token")) return null;
	return @unserialize(@file_get_contents("tokens/$name.token"));
}

function delete_token($name)
{
	@unlink("tokens/$name.token");
}

function enable_implicit_flush()
{
	@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
	ob_implicit_flush(1);
	echo "<!-- ".str_repeat(' ', 2000)." -->";
}