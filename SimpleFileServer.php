<?php
/**
 * Simple File Server
 *
 * @author      JoeyHwong <JoeyHwong@hotmail.com>
 * @version     1.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
 
header('Content-Type: text/html; charset=utf-8');
ob_start();
session_start();
$config_file = "./config.json";
$php_self = substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],'/')+1);
$obj = json_decode(file_get_contents($config_file), true);
if (!is_array($obj)) die ('Fail to load your config !!');
$filefolder = $obj['filefolder']['PATH'];
$sitetitle = $obj['site']['title'];
$user = $obj['admin']['user'];
$pass = $obj['admin']['password'];
$mail = $obj['admin']['mail'];
$safe_num = $obj['admin']['safe_num'];
$developerweb = $obj['admin']['developer_website'];
$meurl = $_SERVER['PHP_SELF'];
$me = explode('/',$meurl);
$me = end($me);

if(isset($_REQUEST['op'])){
	$op = $_REQUEST['op'];
}else{
	$op = 'root';
}

if(isset($_REQUEST['folder'])){
	$folder = $_REQUEST['folder'];
}else{
	$folder = '';
}
$arr = str_split($folder);
if($arr[count($arr)-1]!=='/'){
    $folder .= '/';
}
while (preg_match('/\.\.\//',$folder)) $folder = preg_replace('/\.\.\//','/',$folder);
while (preg_match('/\/\//',$folder)) $folder = preg_replace('/\/\//','/',$folder);
if ($folder == ''){
    $folder = $filefolder;
}elseif ($filefolder != ''){
    if (!@ereg($filefolder,$folder)){
        $folder = $filefolder;
    }  
}
$ufolder = $folder;

if(@$_SESSION['error'] > $safe_num && $safe_num !== 0){
	printerror('Sorry, Maximum number of allowable login has been exceeded.');
}

if (@$_COOKIE['user'] != $user || @$_COOKIE['pass'] != md5($pass)){
	if (@$_REQUEST['user'] == $user && @$_REQUEST['pass'] == $pass){
	    setcookie('user',$user,time()+60*60*24*1);
	    setcookie('pass',md5($pass),time()+60*60*24*1);
	} else {
		if (@$_REQUEST['user'] == $user || @$_REQUEST['pass']) $er = true;
		login(@$er);
	}
}


/****************************************************************/

function maintop($title,$showtop = true){
    global $developerweb,$meurl,$me,$sitetitle,$lastsess,$login,$viewing,$iftop,$user,$pass,$password,$debug,$issuper;
    echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'>\n<html>\n<head>\n"
        ."<title>$sitetitle - $title</title>\n"
        ."</head>\n"
        ."<body>\n"
        ."<style>\n*{font-family:Verdana;}.tips{overflow:auto;margin:8px 0}td{font-size:13px;}span{margin-bottom:8px;}h2{margin:6px 0;}a:visited{color:#333;}a:hover {color:#666;}a:link {color:#333;}a:active {color:#666;}table,form{width:700px !important;max-width:700px !important;}textarea{font-family:'Yahei Consolas Hybrid',Consolas,Verdana, Tahoma, Arial, Helvetica,'Microsoft Yahei', sans-serif;font-size:14px;border:1px solid #ccc;margin:5px 0;padding:8px;line-height:18px;width:680px;max-width:680px;}input.button{margin:10px 0;font-size:13px;*font-size:90%;*overflow:visible;padding:4px 10px;;color:#fff !important;color: white !important;*color:#fff !important;border:1px solid #fff;border:0 rgba(0,0,0,0);background-color:#666;text-decoration:none;}input.button:hover{filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#00000000', endColorstr='#1a000000', GradientType=0);background-image:-webkit-gradient(linear,0 0,0 100%,from(transparent),color-stop(40%,rgba(0,0,0,.05)),to(rgba(0,0,0,.1)));background-image:-webkit-linear-gradient(transparent,rgba(0,0,0,.05) 40%,rgba(0,0,0,.1));background-image:-moz-linear-gradient(top,rgba(0,0,0,.05) 0,rgba(0,0,0,.1));background-image:-o-linear-gradient(transparent,rgba(0,0,0,.05) 40%,rgba(0,0,0,.1));background-image:linear-gradient(transparent,rgba(0,0,0,.05) 40%,rgba(0,0,0,.1));text-decoration: none}input.buuton:active{box-shadow:0 0 0 1px rgba(0,0,0,.15) inset,0 0 6px rgba(0,0,0,.2) inset}input.text,select,option,.upload{border: 1px solid #999;margin:6px 1px;padding:5px;;font-size:12px;}body{;background-color:#ededed;margin: 0px 0px 10px;}.title{font-weight: bold; FONT-SIZE: 12px;text-align: center;}.error{font-size:10pt;color:#AA2222;text-align:left}.menu{position:fixed;margin:20px;font-size:13px;padding:5px;}.menu li{list-style-type:square;margin-bottom:8px;}.menu a{text-decoration:none;margin-right:8px;}.menu a:hover{color:#707070;}.table{background-color:#777;color:#fff;}.mytable tr:hover{background:#ededed;color:#707070;font-size:13px;}.table:hover{background-color:#777 !important;color:#fff !important}tr{height:26px;}.upload{width:400px;}\n</style>\n";
    if($_REQUEST['op']!=='root'){
    	$back = "<li><a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back to ".$_SESSION['folder']."</a></li>\n";
    }else{
    	$back = '';
    }
    if ($showtop){
        echo "<div class=\"menu\">\n"
	    	."<li><a href=\"".$developerweb."\">Blog</a></li>\n"	
	    	."<li><a href=\"".$meurl."?op=root\">Root</a></li>\n"
            .$back
            ."<li><a href=\"".$meurl."?op=up\">Upload</a></li>\n"
            ."<li><a href=\"".$meurl."?op=cr\">Create</a></li>\n"
            ."<li><a href=\"".$meurl."?op=sqlb\">MySQL Backup</a></li>\n"
	    	."<li><a href=\"".$meurl."?op=ftpa\">FTP Backup</a></li>\n"
            ."<li><a href=\"".$meurl."?op=killme&dename=".$me."&folder=./\" onclick=\"return confirm('Are you sure you want to end all？');\">Kill me</a></li>\n"
            ."<li><a href=\"".$meurl."?op=logout\">logout</a></li>\n"
            ."</div>";
    }
    if ($viewing == ""){
        echo "<table cellpadding=10 cellspacing=10 bgcolor=#ededed align=center><tr><td>\n"
            ."<table cellpadding=1 cellspacing=1><tr><td>\n"
            ."<table cellpadding=5 cellspacing=5 bgcolor='white'><tr><td>\n";
    }else{
        echo "<table cellpadding=7 cellspacing=7 bgcolor='white'><tr><td>\n";
    }
    echo "<h2>$sitetitle <small>- $title</small></h2>\n";
}


/****************************************************************/

function login($er=false){
    global $sitetitle, $meurl,$op,$safe_num,$mail;
    setcookie("user","",time()-60*60*24*1);
    setcookie("pass","",time()-60*60*24*1);
    maintop("login",false);

    if ($er){ 
        if (isset($_SESSION['error'])){
            $_SESSION['error']++;
            if($_SESSION['error'] > $safe_num && $safe_num !== 0){
                @mail($mail,'Warning messages from '.$sitetitle.' ：Someone is trying malicious access！','<br>IP：'.$_SERVER['REMOTE_ADDR'],'From: '.$meurl);
                echo ('<span class="error">ERROR: Malicious Access！</span>');
                exit;
            }
        }else{
            $_SESSION['error'] = 1;
        }
        echo "<span class=error>ERROR: ID or password is not correct ！</span><br>\n"; 
    }

    echo "<form action=\"".$meurl."?op=".$op."\" method=\"post\">\n"
        ."<input type=\"text\" name=\"user\" border=\"0\" class=\"text\" value=\"".@$user."\"  placeholder=\"username\">\n"
        ."<input type=\"password\" name=\"pass\" border=\"0\" class=\"text\" value=\"".@$pass."\" placeholder=\"password\"><br>\n"
        ."<input type=\"submit\" name=\"submitButtonName\" value=\"login\" border=\"0\" class=\"button\">\n"
        ."</form>\n";
    mainbottom();
}


/****************************************************************/

function root(){
    global $meurl ,$folder, $ufolder, $filefolder, $HTTP_HOST, $config_file, $php_self;
    maintop("Root");
    echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=100% class='mytable'><form method='post'>\n";

    $content1 = "";
    $content2 = "";

    $count = "0";
    $folder = iconv("UTF-8", "GBK", $folder);
    $style = opendir($folder);
    $a=1;
    $b=1;

    if ($folder){
        $_SESSION['folder']=$ufolder;
    }

    while($stylesheet = readdir($style)){
    	if ($stylesheet !== "." && $stylesheet !== ".." && $stylesheet !== $php_self && $stylesheet !== basename($config_file) ){
	        if (is_dir($folder.$stylesheet) && is_readable($folder.$stylesheet)){
	            $sstylesheet = $stylesheet;
	            $stylesheet = iconv("GBK", "UTF-8", $stylesheet);
	            $ulfolder = $folder;
	            $folder = iconv("GBK", "UTF-8", $folder);
	            $content1[$a] = "<tr width=100%><td><input name='select_item[d][$stylesheet]' type='checkbox' id='$stylesheet' onclick='One($stylesheet)' class=\"checkbox\" value='".$folder.$stylesheet."' /></td>\n"
	                           ."<td><a href=\"".$meurl."?op=root&folder=".$folder.$stylesheet."/\">".$stylesheet."</a></td>\n"
	                           ."<td>".Size(dirSize($folder.$stylesheet))."</td>"
	                           ."<td><a href=\"".$meurl."?op=root&folder=".htmlspecialchars($folder.$stylesheet)."/\">Enter</a></td>\n"
	                           ."<td><a href=\"".$meurl."?op=ren&file=".htmlspecialchars($stylesheet)."&folder=$folder\">Rename</a></td>\n"
	                           ."<td><a href=\"".$folder.$stylesheet."\" target='_blank'>View</a></td>\n"
	                           ."<td>".substr(sprintf('%o',fileperms($ulfolder.$sstylesheet)), -3)."</td></tr>\n";
	            $a++;
	            $folder = iconv("UTF-8", "GBK", $folder);
	        }elseif(!is_dir($folder.$stylesheet) && is_readable($folder.$stylesheet)){ 
		        if(preg_match ("/.zip$/i", $folder.$stylesheet)){
		            $sstylesheet = $stylesheet;
		            $ulfolder = $folder;
		            $stylesheet = iconv("GBK", "UTF-8", $stylesheet);
		            $folder = iconv("GBK", "UTF-8", $folder);
		            $content2[$b] = "<tr width=100%><td><input name='select_item[f][$stylesheet]' type='checkbox' id='$stylesheet' class=\"checkbox\" value='".$folder.$stylesheet."' /></td>\n"
		                           ."<td><a href=\"".$folder.$stylesheet."\" target='_blank'>".$stylesheet."</a></td>\n"
		                           ."<td>".Size(filesize($ufolder.$sstylesheet))."</td>"
		                           ."<td></td>\n"
		                           ."<td><a href=\"".$meurl."?op=ren&file=".htmlspecialchars($stylesheet)."&folder=$folder\">Rename</a></td>\n"
		                           ."<td><a href=\"".$meurl."?op=unz&dename=".htmlspecialchars($stylesheet)."&folder=$folder\">Unzip</a></td>\n"
		                           ."<td>".substr(sprintf('%o',fileperms($ulfolder.$sstylesheet)), -3)."</a></td></tr>\n";
		            $b++;
		            $folder = iconv("UTF-8", "GBK", $folder);
		        }else{
		            $sstylesheet = $stylesheet;
		            $ulfolder = $folder;
		            $stylesheet = iconv("GBK", "UTF-8", $stylesheet);
		            $folder = iconv("GBK", "UTF-8", $folder);
		            $content2[$b] = "<tr width=100%><td><input name='select_item[f][$stylesheet]' type='checkbox' id='$stylesheet' class=\"checkbox\" value='".$folder.$stylesheet."' /></td>\n"
		                           ."<td><a href=\"".$folder.$stylesheet."\" target='_blank'>".$stylesheet."</a></td>\n"
		                           ."<td>".Size(filesize($ufolder.$sstylesheet))."</td>"
		                           ."<td><a href=\"".$meurl."?op=edit&fename=".htmlspecialchars($stylesheet)."&folder=$folder\">Edit</a></td>\n"
		                           ."<td><a href=\"".$meurl."?op=ren&file=".htmlspecialchars($stylesheet)."&folder=$folder\">Rename</a></td>\n"
		                           ."<td><a href=\"".$folder.$stylesheet."\" target='_blank'>View</a></td>\n"
		                           ."<td>".substr(sprintf('%o',fileperms($ulfolder.$sstylesheet)), -3)."</a></td></tr>\n";
		            $b++;
		            $folder = iconv("UTF-8", "GBK", $folder);
		        }
	    	}
    	$count++;
    	} 
	}
    closedir($style);

    echo "Current directory: $ufolder\n"
        ."<div style=\"position:fixed;bottom:0;margin-left:2px;\"><input type=\"checkbox\" id=\"check\" onclick=\"Check()\"> <input class='button' name='action' type='submit' value='move' /> <input class='button' name='action' type='submit' value='copy' /> <input class='button' name='action' type='submit' onclick=\"return confirm('Click OK, the selected file will create as Backup-time.zip！')\"  value='zip' /> <input class='button' name='action' type='submit' onclick=\"return confirm('Are you sure you to delete the selected file?')\" value='delete' /> <input class='button' name='action' type='submit' onclick=\"var t=document.getElementById('chmod').value;return confirm('Modify the permissions of these files'+t+'？If it is a folder, the operating will be do for all contents！')\" value='permissions' /> <input type=\"text\" class=\"text\" stlye=\"vertical-align:text-top;\" size=\"3\" id=\"chmod\" name=\"chmod\" value=\"0755\"></div>"
        ."<br>File Numbers: " . $count . "<br><br>";

    echo "<tr class='table' width=100%>"
        ."<script>function Check(){
            var collid = document.getElementById(\"check\")
            var coll = document.getElementsByTagName('input')
            if (collid.checked){
                for(var i = 0; i < coll.length; i++)
                    coll[i].checked = true;
            }else{
                for(var i = 0; i < coll.length; i++)
                    coll[i].checked = false;
            }
         }</script>"
       ."<td width=20></td>\n"
       ."<td>FileName</td>\n"
       ."<td width=65>Size</td>\n"
       ."<td width=45>Enter</td>\n"
       ."<td width=55>Rename</td>\n"
       ."<td width=45>View</td>\n"
       ."<td width=30>Permissions</td>\n"
       ."</tr>";
    if($ufolder!=="./"){
        $count = substr_count($ufolder,"/");
        $last = explode('/', $ufolder);
        $i = 1;
        $back = ".";
        while($i < $count-1){
              $back = $back."/".$last[$i];
              $i++;
        }
        echo "<tr width=100%><td></td><td><a href=\"".$meurl."?op=root&folder=".$back."/"."\">parent directory</a></td><td></td><td></td><td></td><td></td><td></td></tr>";
    }
    for ($a=1; $a<count($content1)+1;$a++){
        $tcoloring   = ($a % 2) ? '#DEDEDE' : '#ededed';
        if(empty($content1)){
        }else{
            echo @$content1[$a];
        }
    }

    for ($b=1; $b<count($content2)+1;$b++){
        $tcoloring   = ($a++ % 2) ? '#DEDEDE' : '#ededed';
        echo @$content2[$b];
    }

    echo "</table></form>";
    mainbottom();
}


/****************************************************************/

function dirSize($directoty){
	$dir_size=0;

	if($dir_handle=@opendir($directoty)){
    	while($filename=readdir($dir_handle)){
    		$subFile=$directoty.DIRECTORY_SEPARATOR.$filename;
    		if($filename=='.'||$filename=='..'){
    			continue;
    		}elseif (is_dir($subFile))
    		{
    			$dir_size+=dirSize($subFile);
    		}elseif (is_file($subFile)){
    			$dir_size+=filesize($subFile);
    		}
    	}
    	closedir($dir_handle);
    }
    return ($dir_size);
}


/****************************************************************/

function Size($size){
    if($size < 1024){
        $filesize = $size;
    }elseif($size > 1024 and $size < 1024*1024){
        $count1 = round($size/1024,1);
        $filesize = $count1."k";
    }elseif($size > 1024*1024 and $size < 1024*1024*1024){
        $count1 = round($size/1024/1024,1);
        $filesize = $count1."M";
    }elseif($size > 1024*1024*1024 and $size < 1024*1024*1024*1024){
        $count1 = round($size/1024/1024/1024,1);
        $filesize = $count1."G";
    }elseif($size > 1024*1024*1024*1024){
        $count1 = round($size/1024/1024/1024/1024,1);
        $filesize = $count1."T";
    }
    return $filesize;
}


/****************************************************************/

function curl_get_contents($url){   
    $ch = curl_init();   
    curl_setopt($ch, CURLOPT_URL, $url);            
    //curl_setopt($ch,CURLOPT_HEADER,1);           
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);         
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   
    $r = curl_exec($ch);   
    curl_close($ch);   
    return $r;   
}


/****************************************************************/

function up(){
    global $sitetitle, $meurl, $folder, $content, $filefolder;
    maintop("Upload");

    echo "<FORM ENCTYPE=\"multipart/form-data\" ACTION=\"".$meurl."?op=upload\" METHOD=\"POST\">\n"
        ."<h3>basic upload</h3>You can upload files up to ".ini_get('upload_max_filesize')."<br><input type=\"File\" name=\"upfile[]\" multiple size=\"20\">\n"
        ."<input type=\"text\" name=\"ndir\" value=\"".$_SESSION["folder"]."\" class=\"upload\">\n";

    echo $content
        ."</select><br>"
        ."<input type=\"submit\" value=\"upload\" class=\"button\">\n"
        ."<script>function UpCheck(){if(document.getElementById(\"unzip\").checked){document.getElementById(\"deluzip\").disabled=false}else{document.getElementById(\"deluzip\").disabled=true}}</script>"
        ."<input type=\"checkbox\" name=\"unzip\" id=\"unzip\" value=\"checkbox\" onclick=\"UpCheck()\" checked><label for=\"unzip\"><abbr title='Extracting Zip file uploaded'>Unzip</abbr></labal> "
        ."<input type=\"checkbox\" name=\"delzip\" id=\"deluzip\"value=\"checkbox\"><label for=\"deluzip\"><abbr title='Delete the raw Zip file'>Delete</abbr></labal>"
        ."</form>\n";
    echo "<h3>Remote upload</h3>What's the meanning of Remote upload ? <br>".$sitetitle." also allow upload your file via url.<br>Similar to wget function in linux.<br><br><form action=\"".$meurl."?op=yupload\" method=\"POST\"><input name=\"url\" size=\"85\" type=\"text\" class=\"text\" placeholder=\"Input your url...\"/> <input type=\"text\" class=\"text\" size=\"20\" name=\"ndir\" value=\"".$_SESSION["folder"]."\">"
         ."<input name=\"submit\" value=\"upload\" type=\"submit\" class=\"button\"/>\n"
         ."<script>function Check(){if(document.getElementById(\"un\").checked){document.getElementById(\"del\").disabled=false}else{document.getElementById(\"del\").disabled=true}}</script>"
         ."<input type=\"checkbox\" name=\"unzip\" id=\"un\" value=\"checkbox\" onclick=\"Check()\" checked><label for=\"un\"><abbr title='Extracting Zip file uploaded'>Unzip</abbr></labal> "
         ."<input type=\"checkbox\" name=\"delzip\" id=\"del\"value=\"checkbox\"><label for=\"del\"><abbr title='Delete the raw Zip file'>Delete</abbr></labal></form>";
	echo "<h3>Server transfer</h3>What's the meanning of Server transfer ?<br>".$sitetitle." also allow upload your file via absolute address in our server.<br>Similar to ln function in linux.<br><br><form action=\"".$meurl."?op=bupload\" method=\"POST\"><input name=\"url\" size=\"85\" type=\"text\" class=\"text\" placeholder=\"Input a absolute dir...\"/> <input type=\"text\" class=\"text\" size=\"20\" name=\"ndir\" value=\"".$_SESSION["folder"]."\">"
	."<input name=\"submit\" value=\"upload\" type=\"submit\" class=\"button\"/>\n"
	."</form>\n";
    mainbottom();
}


/****************************************************************/

function bupload($filepath, $folder){
	global $meurl;
     $nfolder = $folder;
     $filepath = iconv("UTF-8", "GBK", $filepath);
     $folder = iconv("UTF-8", "GBK", $folder);
     $stime = date('Y-m-d');
     $stime = str_replace("\n","",$stime);
     $stime = iconv("UTF-8", "GBK",$stime);
     if($filepath!==""){
		set_time_limit (24 * 60 * 60); 
	    if (!file_exists($folder)){
	    	mkdir($folder, 0755);		
	    }
		$newfname = $folder . $stime . '_' . basename($filepath);
		if (!file_exists($filepath)){
			echo "file un exists";
		}else{
			symlink($filepath, $newfname);
			maintop("Server transfer");
			echo "Upload ".basename($filepath)." succeed<br>\n";
			echo "Now, you can <a href=\"".$meurl."?op=root&folder=".$folder."\">access file dir</a> or <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>  or <a href=\"".$meurl."?op=up\">Upload more</a>\n";
		}
		mainbottom();
		return true;
	}else{
		printerror ('File address can not be empty');
	}
}


/****************************************************************/

function yupload($url, $folder, $unzip, $delzip){
	global $meurl;
    $nfolder = $folder;
    $url = iconv("UTF-8", "GBK", $url);
    $folder = iconv("UTF-8", "GBK", $folder);
    $stime = date('Y-m-d');
    $stime = str_replace("\n","",$stime);
    $stime = iconv("UTF-8", "GBK",$stime);
    if($url!==""){
        set_time_limit (24 * 60 * 60); 
  	    if (!file_exists($folder)){
    	    mkdir($folder, 0755);
	    }
		$newfname = $folder . $stime . '_' . basename($url); 
    	if(function_exists('curl_init')){
        	$file = curl_get_contents($url);
    		file_put_contents($newfname,$file);
    	}else{
        	$file = fopen ($url, "rb"); 
        	if ($file){ 
            	$newf = fopen ($newfname, "wb");
        	if ($newf) 
            	while (!feof($file)){ 
            		fwrite($newf, fread($file, 1024 * 8), 1024 * 8); 
            	}
        	}
        	if ($file){
            	fclose($file); 
        	}
       		if ($newf){
            	fclose($newf);
        	}
    	}
    	maintop("Remote upload");
    	echo "Upload ".basename($url)." succeed<br>\n";
    	$end = explode('.', basename($url));
    	if(end($end)=="zip" && isset($unzip) && $unzip == "checkbox"){
        	if(class_exists('ZipArchive')){
           		$zip = new ZipArchive();
            	if ($zip->open($folder.basename($url)) === TRUE){
                	$zip->extractTo($folder);
               		$zip->close();
                	echo basename($nurl)." has been extracted to ".$nfolder."<br>";
                	if(isset($delzip) && $delzip == "checkbox"){
            	    	if(unlink($folder.basename($url))){
            	       		echo basename($url)." delete succeed<br>";
                    	}else{
            	        	echo basename($url)." delete failed<br>";
                		}
                   		echo "Now, you can <a href=\"".$meurl."?op=root&folder=".$folder."\">access file dir</a> or <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>  or <a href=\"".$meurl."?op=up\">Upload more</a>\n";
                	}
            	}else{
                	echo('<span class="error">Unable to extract：'.$nfolder.basename($nurl).'</span><br>');
            	}
        	}else{
        		echo('<span class="error">PHP on this server does not support ZipArchive, unable to extract the zip files!</span><br>');
        	}
    	}else{
    		echo "Now, you can <a href=\"".$meurl."?op=root&folder=".$nfolder."\">access file dir</a> or <a href=\"".$meurl."?op=edit&fename=".basename($url)."&folder=".$nfolder."\">Edit your file</a> or <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>  or <a href=\"".$meurl."?op=up\">Upload more</a>\n";
    	}
    	mainbottom();
    	return true;
    }else{
	    printerror ('File address can not be empty');
    }
}


/****************************************************************/

function upload($upfile,$ndir,$unzip,$delzip){
    global $meurl, $folder;
    $nfolder = $folder;
    $nndir = $ndir;
    $ndir = iconv("UTF-8", "GBK", $ndir);
    if (!$upfile){
        printerror("You have not selected file yet！");
    }elseif($upfile){ 
  	    maintop("Upload");
	  	if (!file_exists($ndir)){
	    	mkdir($ndir, 0755);
	    }
	    $i = 1;
	    while (count($upfile['name']) >= $i){
	    	$dir = iconv("UTF-8", "GBK", $nndir.$upfile['name'][$i-1]);
	        if(@copy($upfile['tmp_name'][$i-1],$dir)){
	            echo "Upload ".$nndir.$upfile['name'][$i-1]." succeed\n<br>";
	            $end = explode('.', $upfile['name'][$i-1]);
	            if(end($end)=="zip" && isset($unzip) && $unzip == "checkbox"){
	            	if(class_exists('ZipArchive')){
	                    $zip = new ZipArchive();
	                    if ($zip->open($dir) === TRUE){
	                        $zip->extractTo($ndir);
	                        $zip->close();
	                        echo $upfile['name'][$i-1]." has been extracted to ".$nndir."<br>";
	                        if(isset($delzip) && $delzip == "checkbox"){
	            	            if(unlink($dir.$upfile['name'][$i-1])){
	            	                echo $upfile['name'][$i-1]." delete succeed<br>";
	                            }else{
	                                echo $upfile['name'][$i-1].("<span class=\"error\">delete failed</span><br>");
	                            }
	                        }
	                    }else{
	                        echo("<span class=\"error\">>Unable to extract: ".$nndir.$upfile['name'][$i-1]."</span><br>");
	                    }
	                }else{
	            	    echo("<span class=\"error\">PHP on this server does not support ZipArchive, unable to extract the zip files!</span><br>");
	                }
	            }
	        }else{
	            echo("<span class=\"error\">Upload ".$upfile['name'][$i-1]." failed</span><br>");
	        }
	        $i++;
	    }
	    echo "Now, you can <a href=\"".$meurl."?op=root&folder=".$folder."\">access file dir</a> or <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>  or <a href=\"".$meurl."?op=up\">Upload more</a>\n";
	    mainbottom();
	}else{
	    printerror("You have not selected file yet！");
	}
}


/****************************************************************/

function unz($dename){
    global $meurl, $folder, $content, $filefolder;
    if (!$dename == ""){
        maintop("Unzip");
        echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
            ."<span class=error>**WARNING: You will extract ".$folder.$dename.". **</span ><br><br>\n"
            ."<form ENCTYPE=\"multipart/form-data\" action=\"".$meurl."?op=unzip\">Extract to..."
            ."<input type=\"text\" name=\"ndir\" class=\"text\" value=\"".$_SESSION['folder']."\">";
        echo $content
            ."</select>"
            ."<br><br>Sure to unzip ".$folder.$dename."?<br><br>\n"
            ."<input type=\"hidden\" name=\"op\" value=\"unzip\">\n"
            ."<input type=\"hidden\" name=\"dename\" value=\"".$dename."\">\n"
            ."<input type=\"hidden\" name=\"folder\" value=\"".$folder."\">\n"
            ."<input type=\"submit\" value=\"unzip\" class=\"button\"><input type=\"checkbox\" name=\"del\" id=\"del\"value=\"del\"><label for=\"del\">Delete the raw Zip file</label><br><br>\n"
            ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n"
            ."</table>\n";
        mainbottom();
    }else{
        root();
    }
}


/****************************************************************/

function unzip($dename,$ndir,$del){
    global $meurl, $folder;
    $nndir = $ndir;
    $nfolder = $folder;
    $ndename = $dename;
    $dename = iconv("UTF-8", "GBK", $dename);
    $folder = iconv("UTF-8", "GBK", $folder);
    $ndir = iconv("UTF-8", "GBK", $ndir);
    if (!$dename == ""){
        if (!file_exists($ndir)){
    	    mkdir($ndir, 0755);
        }
        if(class_exists('ZipArchive')){
            $zip = new ZipArchive();
            if ($zip->open($folder.$dename) === TRUE){
                $zip->extractTo($ndir);
                $zip->close();
                maintop("Unzip");
                echo $dename." has been extracted to $nndir<br>";
                if($del=='del'){
                	unlink($folder.$dename);
                	echo $ndename." has been deleted<br>";
                }
                echo "<a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
                mainbottom();
            }else{
                printerror('Unable to extract: '.$nfolder.$ndename);
            }
        }else{
        	printerror('PHP on this server does not support ZipArchive, unable to extract the zip files!');
        }
    }else{
        root();
    }
}


/****************************************************************/

function deltree($pathdir){  
	if(is_empty_dir($pathdir)){
		rmdir($pathdir);
	}else{
        $d=dir($pathdir);  
        while($a=$d->read()){  
	        if(is_file($pathdir.'/'.$a) && ($a!='.') && ($a!='..')){
	        	unlink($pathdir.'/'.$a);
	        }  
	        if(is_dir($pathdir.'/'.$a) && ($a!='.') && ($a!='..')){
	            if(!is_empty_dir($pathdir.'/'.$a)){
	            	deltree($pathdir.'/'.$a);  
	            }  
	            if(is_empty_dir($pathdir.'/'.$a)){
	            	rmdir($pathdir.'/'.$a);
	            }
	        }  
       }  
       $d->close();  
    }  
}  


/****************************************************************/

function is_empty_dir($pathdir){
    $d=opendir($pathdir);  
    $i=0;  
    while($a=readdir($d)){  
        $i++;  
    }  
    closedir($d);  
    if($i>2){
    	return false;
    }else return true;  
}


/****************************************************************/

function edit($fename){
    global $meurl,$folder;
    $file = iconv("UTF-8", "GBK", $folder.$fename);
    if (file_exists($folder.$fename)){
        maintop("Edit");
        echo $folder.$fename;
        $contents = file_get_contents($file);
        if(function_exists('mb_detect_encoding')){
            $encode = mb_detect_encoding($contents);
        }else{
            $encode = 'UTF-8';
        }
        if($encode!=="UTF-8" && !empty($encode)){
            $contents = iconv("UTF-8", $encode, $contents);
        }
        echo "<form action=\"".$meurl."?op=save&encode=".$encode."\" method=\"post\">\n"
            ."<textarea rows=\"24\" name=\"ncontent\">\n";

        echo htmlspecialchars($contents);
        echo "</textarea>\n"
            ."<br>\n"
            ."<input type=\"hidden\" name=\"folder\" value=\"".$folder."\">\n"
            ."<input type=\"hidden\" name=\"fename\" value=\"".$fename."\">\n"
            ."<input type=\"submit\" value=\"save\" class=\"button\"> <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n"
            ."</form>\n";
        mainbottom();
    }else{
        root();
    }
}


/****************************************************************/

function save($ncontent, $fename, $encode){
    global $meurl,$folder;
    if (!$fename == ""){
	    maintop("Edit");
	    $file = iconv("UTF-8", "GBK", $folder.$fename);
	    $ydata = stripslashes($ncontent);
	    if($encode!=="UTF-8"){
	    	$ydata = iconv($encode, "UTF-8", $ydata);
	    }

	    if(file_put_contents($file, $ydata)){
	        echo "<a href=\"".$folder.$fename."\" target=\"_blank\">".$folder.$fename."</a> Saved successfully!\n"
	            ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a> or <a href=\"".$meurl."?op=edit&fename=".$fename."&folder=".$folder."\">Continue editing</a>\n";
	        $fp = null;
	    }else{
	        echo "<span class='error'>File save error!</span>\n"
	        ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
	    }
	    mainbottom();
    }else{
    	root();
    }
}


/****************************************************************/

function cr(){
    global $meurl, $folder, $content, $filefolder;
    maintop("Create");
    if (!$content == ""){ echo "Please input the fileName\n"; }
    echo "<form action=\"".$meurl."?op=create\" method=\"post\">\n"
        ."<label for=\"nfname\">FileName：</label><br><input type=\"text\" size=\"20\" id=\"nfname\" name=\"nfname\" class=\"text\"><br>\n"
        ."<label for=\"ndir\">Target directory: </label><br><input type=\"text\" class=\"text\" id=\"ndir\" name=\"ndir\" value=\"".$_SESSION['folder']."\">";
    echo $content
        ."<br>";

    echo "<select name=\"isfolder\"><option value=\"1\" checked>Folder</option>\n"
        ."<option value=\"0\" checked>File</option></select><br>\n"
        ."<input type=\"hidden\" name=\"folder\" value=\"$folder\">\n"
        ."<input type=\"submit\" value=\"create\" class=\"button\">  <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n"
        ."</form>\n";
    mainbottom();
}


/****************************************************************/

function create($nfname, $isfolder, $ndir){
    global $meurl, $folder;
    if (!$nfname == ""){
        maintop("Create");
        $ndir = iconv("UTF-8", "GBK", $ndir);
        $nfname = iconv("UTF-8", "GBK", $nfname);
	    if ($isfolder == 1){
	        if(@mkdir($ndir."/".$nfname, 0755)){
	        	$ndir = iconv("GBK", "UTF-8", $ndir);
	        	$nfname = iconv("GBK", "UTF-8", $nfname);
	            echo "Your directory<a href=\"".$meurl."?op=root&folder=./".$nfname."/\">".$ndir.$nfname."/</a> has been created successfully.\n"
	            ."<br><a href=\"".$meurl."?op=root&folder=".$ndir.$nfname."/\">Enter</a> | <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
	        }else{
	        	$ndir = iconv("GBK", "UTF-8", $ndir);
	        	$nfname = iconv("GBK", "UTF-8", $nfname);
	            echo "<span class='error'>Your directory ".$ndir."".$nfname." can not be created. Please check if permissions has already been set whether or directory have already exists!</span>\n";
	        }
	    }else{
	        if(@fopen($ndir."/".$nfname, "w")){
	        	$ndir = iconv("GBK", "UTF-8", $ndir);
	        	$nfname = iconv("GBK", "UTF-8", $nfname);
	            echo "Your file <a href=\"".$meurl."?op=viewframe&file=".$nfname."&folder=$ndir\">".$ndir.$nfname."</a> has been created successfully!\n"
	                ."<br><a href=\"".$meurl."?op=edit&fename=".$nfname."&folder=".$ndir."\">Edit</a> | <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
	        }else{
	        	$ndir = iconv("GBK", "UTF-8", $ndir);
	        	$nfname = iconv("GBK", "UTF-8", $nfname);
	            echo "<span class='error'>your file ".$ndir.$nfname." can not be created. Please check if permissions has already been set whether or file have already exists!</span> <a onclick=\"history.go(-1);\" style=\"cursor:pointer\">Back</a>\n";
	        }
	    }
	    mainbottom();
    }else{
    	cr();
    }
}


/****************************************************************/

function ren($file){
    global $meurl,$folder,$ufolder;
    $ufile = $file;
    if (!$file == ""){
        maintop("Rename");
        echo "<form action=\"".$meurl."?op=rename\" method=\"post\">\n"
            ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n"
            ."Rename ".$ufolder.$ufile;
        echo "</table><br>\n"
            ."<input type=\"hidden\" name=\"rename\" value=\"".$ufile."\">\n"
            ."<input type=\"hidden\" name=\"folder\" value=\"".$ufolder."\">\n"
            ."FileName:<br><input class=\"text\" type=\"text\" size=\"20\" name=\"nrename\" value=\"$ufile\">\n"
            ."<input type=\"Submit\" value=\"rename\" class=\"button\"></form>\n";
        mainbottom();
    }else{
        root();
    }
}


/****************************************************************/

function renam($rename, $nrename, $folder){
    global $meurl,$folder;
    if (!$rename == ""){
        $loc1 = iconv("UTF-8", "GBK", "$folder".$rename); 
        $loc2 = iconv("UTF-8", "GBK", "$folder".$nrename);
        if(rename($loc1,$loc2)){
        	maintop("Rename");
            echo "Your file ".$folder.$rename." has been renamed as ".$folder.$nrename."</a>\n"
            ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
            mainbottom();
        }else{
            printerror("Rename Error !");
        }
    }else{
    	root();
    }
}


/****************************************************************/

function movall($file, $ndir, $folder){
    global $meurl,$folder;
    if (!$file == ""){
        maintop("Move All");
        $arr = str_split($ndir);
        if($arr[count($arr)-1]!=='/'){
            $ndir .= '/';
        }
        $nndir = $ndir;
        $nfolder = $folder;
    	$file = iconv("UTF-8", "GBK",$file);
    	$ndir = iconv("UTF-8", "GBK",$ndir);
    	$folder = iconv("UTF-8", "GBK",$folder);
        if (!file_exists($ndir)){
    	    mkdir($ndir, 0755);
        }
        $file = explode(',',$file);
        foreach ($file as $v){
	        if (file_exists($ndir.$v)){
	        	@unlink($ndir.$v);
	        	if (@rename($folder.$v, $ndir.$v)){
	        		$v = iconv("GBK", "UTF-8",$v);
	    	        echo $nndir.$v." have been replaced by ".$nfolder.$v." <br>";
	            }else{
	            	$v = iconv("GBK", "UTF-8",$v);
	                echo "<span class='error'>Unable rename to ".$nfolder.$v.'，please check the permissions of your file </span><br>';
	            }
	        }elseif (@rename($folder.$v, $ndir.$v)){
	        	$v = iconv("GBK", "UTF-8",$v);
	            echo $nfolder.$v." have been moved to ".$nndir.$v.'<br>';
	        }else{
	        	$v = iconv("GBK", "UTF-8",$v);
	            echo "<span class='error'>Unable move to ".$nfolder.$v.', please check the permissions of your file</span><br>';
	        }
        }
	    echo "Your can <a href=\"".$meurl."?op=root&folder=".$nndir."\">go to the folder and view your Files</a> or <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">back</a>\n";
	    mainbottom();
    }else{
    	root();
    }
}


/****************************************************************/

function tocopy($file, $ndir, $folder){
    global $meurl,$folder;
    if (!$file == ""){
        maintop("Copy");
        $nndir = $ndir;
        $nfolder = $folder;
    	$file = iconv("UTF-8", "GBK",$file);
    	$ndir = iconv("UTF-8", "GBK",$ndir);
    	$folder = iconv("UTF-8", "GBK",$folder);
        if (!file_exists($ndir)){
    	    mkdir($ndir, 0755);
        }
        $file = explode(',',$file);
        foreach ($file as $v){
	        if (file_exists($ndir.$v)){
	        	@unlink($ndir.$v);
	        	if (@copy($folder.$v, $ndir.$v)){
	        		$v = iconv("GBK", "UTF-8",$v);
	    	        echo $nndir.$v." have been replaced by ".$nfolder.$v." <br>";
	            }else{
	            	$v = iconv("GBK", "UTF-8",$v);
	                echo "<span class='error'>Unable copy ".$nfolder.$v.', please check the permissions of your file</span><br>';
	            }
	        }elseif (@copy($folder.$v, $ndir.$v)){
	        	$v = iconv("GBK", "UTF-8",$v);
	            echo $nfolder.$v." have been copied to ".$nndir.$v.'<br>';
	        }else{
	        	$v = iconv("GBK", "UTF-8",$v);
	            echo "<span class='error'>Unable copy ".$nfolder.$v.'please check the permissions of your file</span><br>';
	        }
    	}
    	echo "you can <a href=\"".$meurl."?op=root&folder=".$nndir."\">go to the folder and view your Files</a> or <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
    	mainbottom();
    }else{
    	root();
    }
}


/****************************************************************/
/* function logout()                                            */
/*                                                              */
/* Logs the user out and kills cookies                          */
/****************************************************************/

function logout(){
    global $meurl,$login;
    setcookie("user","",time()-60*60*24*1);
    setcookie("pass","",time()-60*60*24*1);

    maintop("Logout",false);
    echo "Your have been logout."
        ."<br><br>"
        ."<a href=".$meurl."?op=root>Click here to re-login.</a>";
    mainbottom();
}


/****************************************************************/
/* function mainbottom()                                        */
/*                                                              */
/****************************************************************/

function mainbottom(){
	global $developerweb, $sitetitle;
    echo "</table></table>\n"
        ."\n<div style='text-align:center'>"
        ."power by <a href='".$developerweb."'>".$sitetitle."</a> Version 1.0.0 </div></table></table></body>\n"
        ."</html>\n";
    exit;
}


/****************************************************************/
/* function sqlb()                                              */
/*                                                              */
/* First step to backup sql.                                    */
/****************************************************************/

function sqlb(){
	global $meurl;
    maintop("Sql backup");
    echo @$content 
        ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\"></table>\n<div class=\"tips\"><span>It will be exported and compressed to intomysql.zip! Such as the existence of the file, the file will be overwritten!</span></div><form action=\"".$meurl."?op=sqlbackup\" method=\"POST\">\n<label for=\"ip\">Host:  </label><input id=\"ip\" name=\"ip\" size=\"30\" class=\"text\"/><br><label for=\"sql\">dbname:  </label><input id=\"sql\" name=\"sql\" size=\"30\" class=\"text\"/><br><label for=\"username\">dbuser:  </label><input id=\"username\" name=\"username\" size=\"30\" class=\"text\"/><br><label for=\"password\">dbpasswd:  </label><input id=\"password\" name=\"password\" size=\"30\" class=\"text\"/><br><select id=\"chset\" style=\"display:none;\"><option id=\utf8\">utf8</option></select><input name=\"submit\" class=\"button\" value=\"backup\" type=\"submit\" /></form>\n";
    mainbottom();
}


/****************************************************************/

function sqlbackup($ip,$sql,$username,$password){
	global $meurl;
    if(class_exists('ZipArchive')){
	    maintop("MySQL Backup");
	    $database=$sql;
	    $options=array(
	        'hostname' => $ip,
	        'charset' => 'utf8',
	        'filename' => $database.'.sql',
	        'username' => $username,
	        'password' => $password
	    );
	    mysql_connect($options['hostname'],$options['username'],$options['password'])or die("Fail to connect DB!");
	    mysql_select_db($database) or die("Database name error!");
	    mysql_query("SET NAMES '{$options['charset']}'");
	    $tables = list_tables($database);
	    $filename = sprintf($options['filename'],$database);
	    $fp = fopen($filename, 'w');
	    foreach ($tables as $table){
	        dump_table($table, $fp);
	    }
	    fclose($fp);
	    if (file_exists('mysql.zip')){
	    	unlink('mysql.zip'); 
	    }
	    $file_name=$options['filename'];
	    $zip = new ZipArchive;
	    $res = $zip->open('mysql.zip', ZipArchive::CREATE);
	    if ($res === TRUE){
	        $zip->addfile($file_name);
	        $zip->close();
	        unlink($file_name);
	    echo 'Database Export and compression jobs done!'
	        ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
	    }else{
	        printerror('Unable to compress files!');
	    }
	    exit;
	    mainbottom();
	}else{
	   	printerror('PHP on this server does not support ZipArchive, unable to extract the zip files!');
	}
}


/****************************************************************/

function list_tables($database){
    $rs = mysql_query("SHOW TABLES FROM $database");
    $tables = array();
    while ($row = mysql_fetch_row($rs)){
        $tables[] = $row[0];
    }
    mysql_free_result($rs);
    return $tables;
}


/****************************************************************/

function dump_table($table, $fp = null){
    $need_close = false;
    if (is_null($fp)){
        $fp = fopen($table . '.sql', 'w');
        $need_close = true;
    }
	$a=mysql_query("show create table `{$table}`");
	$row=mysql_fetch_assoc($a);fwrite($fp,$row['Create Table'].';');
    $rs = mysql_query("SELECT * FROM `{$table}`");
    while ($row = mysql_fetch_row($rs)){
        fwrite($fp, get_insert_sql($table, $row));
    }
    mysql_free_result($rs);
    if ($need_close){
        fclose($fp);
    }
}


/****************************************************************/

function get_insert_sql($table, $row){
    $sql = "INSERT INTO `{$table}` VALUES (";
    $values = array();
    foreach ($row as $value){
        $values[] = "'" . mysql_real_escape_string($value) . "'";
    }
    $sql .= implode(', ', $values) . ");";
    return $sql;
}


/****************************************************************/

function killme($dename){
    global $folder;
    if (!$dename == ""){
        if(unlink($folder.$dename)){
        	maintop("Kill");
            echo "Succesed "
                ." <a href=".$folder.">Back</a>\n";
            mainbottom();
        }else{
            printerror("Error, Please check the permissions!");
        }    
    }else{
        root();
    }
}


/****************************************************************/
/* function ftpa()                                              */
/*                                                              */
/* First step to backup sql.                                    */
/****************************************************************/

function ftpa(){
	global $meurl;
    maintop("FTP Backup");
    echo @$content
        ."<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\"></table>\n<div class=\"tips\"><span>Upload files to a ftp address! </span></div>\n<form action=\"".$meurl."?op=ftpall\" method=\"POST\"><label for=\"ftpip\">FTP address:  </label><input id=\"ftpip\" name=\"ftpip\" size=\"30\" class=\"text\" value=\"127.0.0.1:21\"/><br><label for=\"ftpuser\">FTP user:  </label><input id=\"ftpuser\" name=\"ftpuser\" size=\"30\" class=\"text\"/><br><label for=\"ftppass\">FTP passwd:  </label><input id=\"ftppass\" name=\"ftppass\" size=\"30\" class=\"text\"/><br><label for=\"goto\">Stored Path:  </label><input id=\"goto\" name=\"goto\" size=\"30\" class=\"text\" value=\"./htdocs/\"/><br><label for=\"ftpfile\">Upload files:  </label><input id=\"ftpfile\" name=\"ftpfile\" size=\"30\" class=\"text\" value=\"allbackup.zip\"/><br><input name=\"submit\" class=\"button\" value=\"remote upload\" type=\"submit\" /><input type=\"checkbox\" name=\"del\" id=\"del\"value=\"checkbox\"><label for=\"del\"><abbr title='Delete the local files after upload'>Delete</abbr></label></form>\n";
    mainbottom();
}


/****************************************************************/
/* function ftpall()                                         */
/*                                                              */
/* Second step in backup sql.                                   */
/****************************************************************/

function ftpall($ftpip,$ftpuser,$ftppass,$ftpdir,$ftpfile,$del){
	global $meurl;
	$ftpfile = iconv("UTF-8", "GBK", $ftpfile);
    maintop("FTP Upload");
    $ftpip=explode(':', $ftpip);
    $ftp_server=$ftpip['0'];
    $ftp_user_name=$ftpuser;
    $ftp_user_pass=$ftppass;
    if(empty($ftpip['1'])){
    	$ftp_port='21';
    }else{
    	$ftp_port=$ftpip['1'];
    }
    $ftp_put_dir=$ftpdir;
    $ffile=$ftpfile;

    $ftp_conn_id = ftp_connect($ftp_server,$ftp_port);
    $ftp_login_result = ftp_login($ftp_conn_id, $ftp_user_name, $ftp_user_pass);

    if((!$ftp_conn_id) || (!$ftp_login_result)){
        echo "Failed to connect the ftp server";
        exit;
    }else{
        ftp_pasv ($ftp_conn_id,true);
        ftp_chdir($ftp_conn_id, $ftp_put_dir);
        $ffile=explode(',', $ffile);
        foreach ($ffile as $v){
        	$ftp_upload = ftp_put($ftp_conn_id,$v,$v, FTP_BINARY);
        	if ($del == 'del'){
        		unlink('./'.$v);
        	}
        }
        ftp_close($ftp_conn_id); 
    }

    $ftpfile = iconv("GBK", "UTF-8", $ftpfile);
    echo "Upload ".$ftpfile." succeed\n"
        ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
    mainbottom();
}


/****************************************************************/

function printerror($error){
    maintop("Error");
    echo "<span class=error>ERROR: \n".$error."\n</span>"
        ." <a onclick=\"history.go(-1);\" style=\"cursor:pointer\">Back</a>\n";
    mainbottom();
}


/****************************************************************/

function deleteall($dename){
    if (!$dename == ""){
    	$udename = $dename;
    	$dename = iconv("UTF-8", "GBK",$dename);
        if (is_dir($dename)){
            if(is_empty_dir($dename)){ 
                rmdir($dename);
                echo "<span>".$udename." have been deleted</span><br>";
            }else{
                deltree($dename);
                rmdir($dename);
                echo "<span>".$udename." have been deleted</span><br>";
            }
        }else{
            if(@unlink($dename)){
                echo '<span>'.$udename." have been deleted</span><br>";
            }else{
                echo("<span class='error'>Unable to delete ".$udename."！</span><br>");
            }
        }
    }
}

if(@$_POST['action']=='delete'){
    if(isset($_POST['select_item'])){
    	maintop("Delete");
        if(@$_POST['select_item']['d']){
            foreach($_POST['select_item']['d'] as $val){
                deleteall($val);
            }
        }
        if(@$_POST['select_item']['f']){
            foreach($_POST['select_item']['f'] as $val){
                if(deleteall($val)){}
            }
        }
        echo "<a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
        mainbottom();
    }else{
        printerror("You have not selected file yet！");
    }
}

if(@$_POST['action']=='move'){
    if(isset($_POST['select_item'])){
    	maintop("Move All");
    	$file = '';
        if(@$_POST['select_item']['d']){
            foreach($_POST['select_item']['d'] as $key => $val){
                $file = $file.$key.',';
            }
        }
        if(@$_POST['select_item']['f']){
            foreach($_POST['select_item']['f'] as $key => $val){
                $file = $file.$key.',';
            }
        }
        $file = substr($file,0,-1);
    	echo "<form action=\"".$meurl."?op=movall\" method=\"post\">";
    	echo '<input type="hidden" name="file" value="'.$file.'"><input type="hidden" name="folder" value="'.$_SESSION['folder'].'">You will move the following files to：'
    	    ."<input type=\"text\" class=\"text\" name=\"ndir\" value=\"".$_SESSION['folder']."\">\n"
    	    ."<div class='tips'>".$file."</div>";
        echo "<input type=\"submit\" value=\"move\" border=\"0\" class=\"button\"> <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
        mainbottom();
    }else{
        printerror("You have not selected file yet！");
    }
}

if(@$_POST['action']=='copy'){
    if(isset($_POST['select_item'])){
    	maintop("copy");
    	$file = '';
        if(@$_POST['select_item']['d']){
            foreach($_POST['select_item']['d'] as $key => $val){
                $file = $file.$key.',';
            }
        }
        if(@$_POST['select_item']['f']){
            foreach($_POST['select_item']['f'] as $key => $val){
                $file = $file.$key.',';
            }
        }
        $file = substr($file,0,-1);
    	echo "<form action=\"".$meurl."?op=copy\" method=\"post\">";
    	echo '<input type="hidden" name="file" value="'.$file.'"><input type="hidden" name="folder" value="'.$_SESSION['folder'].'">You will copy the following files to：'
    	    ."<input type=\"text\" class=\"text\" name=\"ndir\" value=\"".$_SESSION['folder']."\">\n"
    	    ."<div class='tips'>".$file."</div>";
        echo "<input type=\"submit\" value=\"copy\" border=\"0\" class=\"button\"> <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
        mainbottom();
    }else{
        printerror("You have not selected file yet！");
    }
}

if(@$_POST['action']=='zip'){
    if(isset($_POST['select_item'])){
    	if(class_exists('ZipArchive')){
    		maintop("Directory compression");
	        class Zipper extends ZipArchive {
	            public function addDir($path){
	                if(@$_POST['select_item']['d']){
	                    foreach($_POST['select_item']['d'] as $key => $val){
	                    	$val = substr($val,2);
	                    	$val = iconv("UTF-8", "GBK",$val);
	                    	$this->addDir2($val);
	                    }
	                }
	                if(@$_POST['select_item']['f']){
	                    foreach($_POST['select_item']['f'] as $key => $val){
	                    	$val = substr($val,2);
	                    	echo $val.'<br>';
	                        $this->addFile($val);
	                    }
	                	$this->deleteName('./');
	                }
	            }
	            public function addDir2($path){
	                $nval = iconv("GBK", "UTF-8",$path);
	                echo $nval.'<br>';
	                $this->addEmptyDir($path);
	                $dr = opendir($path);
	                $i=0;
	                while (($file = readdir($dr)) !== false){
	            	    if($file!=='.' && $file!=='..'){
	            	        $nodes[$i] = $path.'/'.$file;
	            	        $i++;
	                    }
	                }
	                closedir($dr);
	                foreach ($nodes as $node){
	                	$nnode = iconv("GBK", "UTF-8",$node);
	                    echo $nnode . '<br>';
	                    if (is_dir($node)){
	                        $this->addDir2($node);
	                    }elseif(is_file($node)){
	                        $this->addFile($node);
	                    }
	                }
	            }
	        }
        	$zip = new Zipper;
        	$time = date('D-d-M-g-h',$_SERVER['REQUEST_TIME']);
        	$res = $zip->open($_SESSION['folder'].'Backup-'.$time.'.zip', ZipArchive::CREATE);
	        if ($res === TRUE){
	        	$f = substr($_SESSION['folder'], 0, -1);
	            $zip->addDir($f);
	            $zip->close();
	            echo "Complete,file is saved as Backup-".$time.".zip<br>You can <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">View it </a> or <a href=\"".$meurl."?op=root\">Back</a>\n";
	        }else{
	            echo '<span class="error">Compression failed!</span>'
	                ." <a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
	        }
	        mainbottom();
	    }else{
	    	printerror('PHP on this server does not support ZipArchive, unable to extract the zip files!');
	    }
    }else{
        printerror("You have not selected file yet！");
    }
}

if(@$_POST['action']=='permissions'){
    if(isset($_POST['select_item'])){
    	maintop("Permissions modification");
    	$chmod = octdec($_REQUEST['chmod']);
        function ChmodMine($file, $chmod){
        	$nfile = $file;
        	$file = iconv("UTF-8", "GBK",$file);
        	if(is_file($file)){
                if(@chmod($file, $chmod)){
                	echo $nfile.' Permissions changed successfully<br>';
                }else{
                	echo '<span class="error">'.$nfile.' Permissions modification fails</span><br>';
                }
        	}elseif(is_dir($file)){
                if(@chmod($file, $chmod)){
                	echo $nfile.' Permissions changed successfully<br>';
                }else{
                	echo '<span class="error">'.$nfile.' Permissions modification fails</span><br>';
                }
        		$foldersAndFiles = @scandir($file);
        		$entries = @array_slice($foldersAndFiles, 2);
        		foreach($entries as $entry){
        			$nentry = iconv("GBK", "UTF-8",$entry);
        			ChmodMine($nfile.'/'.$nentry, $chmod);
        		}
        	}else{
        		echo '<span class="error">'.$nfile.' Not Exists</span><br>';
        	}
        }
        if(@$_POST['select_item']['d']){
            foreach($_POST['select_item']['d'] as $val){
                ChmodMine($val,$chmod);
            }
        }
        if(@$_POST['select_item']['f']){
            foreach($_POST['select_item']['f'] as $val){
                ChmodMine($val,$chmod);
            }
        }
        echo "<a href=\"".$meurl."?op=root&folder=".$_SESSION['folder']."\">Back</a>\n";
        mainbottom();
    }else{
        printerror("You have not selected file yet！");
    }
}

/****************************************************************/
/* function switch()                                            */
/*                                                              */
/* Switches functions.                                          */
/* Recieves $op() and switches to it                            *.
/****************************************************************/

switch($op){

    case "root":
    root();
    break;

    case "up":
    up();
    break;
    case "bupload":
    if(!isset($_REQUEST['url'])){
	printerror('You have not selected file yet！！');
    }elseif(isset($_REQUEST['ndir'])){
	    bupload($_REQUEST['url'], $_REQUEST['ndir']);
    }else{
	    bupload($_REQUEST['url'], './');
    }
    break;

    case "yupload":
    if(!isset($_REQUEST['url'])){
    	printerror('You have not selected file yet！！');
    }elseif(isset($_REQUEST['ndir'])){
        yupload($_REQUEST['url'], $_REQUEST['ndir'], @$_REQUEST['unzip'] ,@$_REQUEST['delzip']);
    }else{
    	yupload($_REQUEST['url'], './', @$_REQUEST['unzip'] ,@$_REQUEST['delzip']);
    }
    break;

    case "upload":
    if(!isset($_FILES['upfile'])){
    	printerror('You have not selected file yet！！');
    }elseif(isset($_REQUEST['ndir'])){
        upload($_FILES['upfile'], $_REQUEST['ndir'], @$_REQUEST['unzip'] ,@$_REQUEST['delzip']);
    }else{
    	upload($_FILES['upfile'], './', @$_REQUEST['unzip'] ,@$_REQUEST['delzip']);
    }
    break;

    case "unz":
    unz($_REQUEST['dename']);
    break;

    case "unzip":
    unzip($_REQUEST['dename'],$_REQUEST['ndir'],@$_REQUEST['del']);
    break;

    case "sqlb":
    sqlb();
    break;

    case "sqlbackup":
    sqlbackup($_POST['ip'], $_POST['sql'], $_POST['username'], $_POST['password']);
    break;

    case "ftpa":
    ftpa();
    break;

    case "ftpall":
    ftpall($_POST['ftpip'], $_POST['ftpuser'], $_POST['ftppass'], $_POST['goto'], $_POST['ftpfile'], $_POST['del']);
    break;

    case "edit":
    edit($_REQUEST['fename']);
    break;

    case "save":
    save($_REQUEST['ncontent'], $_REQUEST['fename'], $_REQUEST['encode']);
    break;

    case "cr":
    cr();
    break;

    case "create":
    create($_REQUEST['nfname'], $_REQUEST['isfolder'], $_REQUEST['ndir']);
    break;

    case "ren":
    ren($_REQUEST['file']);
    break;

    case "rename":
    renam($_REQUEST['rename'], $_REQUEST['nrename'], $folder);
    break;

    case "movall":
    movall(@$_REQUEST['file'], @$_REQUEST['ndir'], $folder);
    break;

    case "copy":
    tocopy(@$_REQUEST['file'], @$_REQUEST['ndir'], $folder);
    break;

    case "printerror":
    printerror($error);
    break;

    case "logout":
    logout();
    break;   

    case "z":
    z($_REQUEST['dename'],$_REQUEST['folder']);
    break;

    case "zip":
    zip($_REQUEST['dename'],$_REQUEST['folder']);
    break;

    case "killme":
    killme($_REQUEST['dename']);
    break;

    default:
    root();
    break;
}

?>
