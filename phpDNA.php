<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
function executeWebDNATemplate($file,$params=null)
{
  $sockLocation="/var/lib/apache2/fcgid/sock/20343.2";
  //$sockLocation="/home/tuseroni/testSocket";
  if($params==null)
  {
    $params=array();
  }
  $paramString="";
  $params=array_merge($_GET,$params);
  $paramKeyPairs=array();
  foreach($params as $key=>$val)
  {
    $paramKeyPairs[]=urlencode($key)."=".urlencode($val);
  }
  $paramString=implode($paramKeyPairs,"&");
  error_reporting(E_ALL); 
  ini_set('display_errors', 1);
  $scriptLocation=$file;
  $env='env -i SERVER_SIGNATURE="<address>Apache/2.4.10 (Debian) Server at localhost Port 80</address>" \
  HTTP_AUTHORIZATION="" \
  HTTP_USER_AGENT="Mozilla/5.0 (X11; Linux x86_64; rv:45.0) Gecko/20100101 Firefox/45.0" \
  SERVER_PORT="80" \
  HTTP_HOST="localhost" \
  DOCUMENT_ROOT="/var/www/html" \
  SCRIPT_FILENAME="/var/www/html/'.$scriptLocation.'" \
  REQUEST_URI="'.$scriptLocation.'" \
  SCRIPT_NAME="'.$scriptLocation.'" \
  SCRIPT_URI="http://localhost/'.$scriptLocation.'" \
  HTTP_CONNECTION="keep-alive" \
  REMOTE_PORT="53503" \
  PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin" \
  SCRIPT_URL="'.$scriptLocation.'" \
  CONTEXT_PREFIX="" \
  PWD="/var/www/html" \
  SERVER_ADMIN="webmaster@localhost" \
  REQUEST_SCHEME="http" \
  HTTP_ACCEPT_LANGUAGE="en-US,en;q=0.5" \
  HTTP_ACCEPT="text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
  REMOTE_ADDR="::1" \
  SHLVL="1" \
  SERVER_NAME="localhost" \
  SERVER_SOFTWARE="Apache/2.4.10 (Debian)" \
  QUERY_STRING="'.$paramString.'" \
  SERVER_ADDR="::1" \
  GATEWAY_INTERFACE="CGI/1.1" \
  SERVER_PROTOCOL="HTTP/1.1" \
  HTTP_ACCEPT_ENCODING="gzip, deflate" \
  REQUEST_METHOD="GET" \
  HTTP_COOKIE="Configuration=block" \
  CONTEXT_DOCUMENT_ROOT="/var/www/html/" \
  _="/usr/bin/cgi-fcgi" \
  cgi-fcgi -bind -connect '.$sockLocation.' /var/www/html/WebDNA/WebDNA.fcgi';
  //echo "env".$env;
  $output=shell_exec($env);
  $request=explode("\r\n\r\n",$output);
  //print_r($request);
  $header=explode("\r\n",$request[0]);
  $body=$request[1];
  $req=array("header"=>$header,"body"=>$body);
  return $req;
  //echo substr($output,strpos($output,"\r\n\r\n"));
}
$regex = '#\[php]((?:[^[]|\[(?!/?php])|(?R))+)\[/php]#';
$totalTime=0;
$evalNum=0;
function parseTagsRecursive($input)
{
    
    global $regex,$totalTime,$evalNum;
    if (is_array($input)) 
    {
	$funcString='ob_start();'.$input[1].';$page = ob_get_contents();ob_end_clean();return $page;';
	//echo $funcString
        //$input = eval("return ".$input[1].";");
        //$startTime=xdebug_time_index();
        $input = eval($funcString);
        //$stopTime=xdebug_time_index();
        //$totalTime+=($stopTime-$startTime);
        //$evalNum++;
    }

    return preg_replace_callback($regex, 'parseTagsRecursive', $input);
}


function parseTemplate($file,$params=null)
{
  global $totalTime,$evalNum;
  $phpRegex="/{(.*)}/";
  $ret=executeWebDNATemplate($file,$params);
  $output = parseTagsRecursive($ret["body"]); 
  //echo "tot: ".$totalTime." avg: ".($totalTime/$evalNum);
  //return exec($matches)
  return $output;
  //return $ret;
}
function executeWebDNA($webDNAText,$params=null)
{

$scriptLocation="output.dna";
$file = fopen($scriptLocation,"w");
fwrite($file,$webDNAText);
fclose($file);
return executeWebDNATemplate($scriptLocation,$params);
//echo substr($output,strpos($output,"\r\n\r\n"));
//
} 
?>