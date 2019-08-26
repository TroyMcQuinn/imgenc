<?php

$ikey = '[create a long random string here]';

$minw = 1;
$maxw = 2048;

/*----------------------
      FUNCTIONS
----------------------*/

function encrypt($s,$key=''){
  global $ikey;
  $key .= $ikey;
  return openssl_encrypt($s,'AES-256-CBC',md5($key),0,substr(md5($key),0,16));  
}

function decrypt($s,$key=''){
  global $ikey;
  $key .= $ikey;
  return openssl_decrypt($s,'AES-256-CBC',md5($key),0,substr(md5($key),0,16));
}

function limitWidth($w){
  global $minw, $maxw;
  $w = ($w < $minw) ? $minw : $w;
  $w = ($w > $maxw) ? $maxw : $w;
  return intval($w);
}





/*----------------------
      MAIN LOGIC
----------------------*/

if(!empty($_FILES['upload']) && ($_POST['function'] == 'decode')){ // Decoding image back to binary data file
  
  // Get data from image.
  $img_old = imagecreatefrompng($_FILES['upload']['tmp_name']);
  $w = imagesx($img_old);
  $h = imagesy($img_old);
  
  // Convert to a 24-bit true color image (necessary if source image is indexed color)
  $img = imagecreatetruecolor($w,$h);
  imagecopy($img,$img_old,0,0,0,0,$w,$h);
  imagedestroy($img_old);
  
  $d = [
    'r' => [],
    'g' => [],
    'b' => []
  ];
  for($y=0;$y<$h;$y++){
    for($x=0;$x<$w;$x++){
      $rgb = imagecolorat($img,$x,$y);
      $d['r'][] = ($rgb >> 16) & 0xFF;
      $d['g'][] = ($rgb >> 8) & 0xFF;
      $d['b'][] = $rgb & 0xFF;
    }
  }
  imagedestroy($img);
  
  $o = ''; // Output data.
  
  switch($_POST['mode']){
    
    case 'rgb':
    
    foreach($d as $channel){
      foreach($channel as $p){
        $o .= pack('C*',$p);
      }
    }
    
    break;
    
    case 'grey':
    default:
    
    foreach($d['g'] as $p){
      $o .= pack('C*',$p);
    }
    
    break;
    
  }
  
  // Decrypt if password provided.
  if(!empty($_POST['password'])){
    $o = decrypt($o,$_POST['password']);
  }
  
  // Decompress if specified.
  $od = gzdecode($o);
  if(!empty($od)) $o = $od;

  header('Content-Disposition: attachment; filename="'.explode('.',$_FILES['upload']['name'])[0].'.bin"');
  echo $o;
  exit();

} else if(!empty($_FILES['upload'])){ // Encoding binary data as PNG image
  
  // Get data.
  $d = file_get_contents($_FILES['upload']['tmp_name']);
  
  // Compress if specified.
  if(!empty($_POST['compress'])){
    $d = gzencode($d);
  }
  
  // Encrypt if specified.
  if(!empty($_POST['password']) && ($_POST['password'] === $_POST['password_confirm'])){
    $d = encrypt($d,$_POST['password']);
  }
  
  // Convert data to array of byte values.
  $d = unpack('C*',$d);
  
  // Calculate the size of the image by rounding up.
  switch($_POST['mode']){
    
    case 'rgb':
    
      if(empty($_POST['width'])){
        $w = $h = ceil(sqrt(count($d) / 3));
      } else {
        $w = limitWidth($_POST['width']);
        $h = ceil(count($d) / $w / 3);
      }
    
    break;
    
    case 'grey':
    default:
    
      if(empty($_POST['width'])){
        $w = $h = ceil(sqrt(count($d)));
      } else {
         $w = limitWidth($_POST['width']);
         $h = ceil(count($d) / $w);
      }
    
    break;    
    
  }  
  
   // Create the image and set the pixels.
   $img = imagecreatetruecolor($w,$h);
   $pos = 1;
   for($y=0;$y<$h;$y++){
     for($x=0;$x<$w;$x++){

       if(isset($d[$pos])){
       
         // Determine pixel color based on mode.
         switch($_POST['mode']){
           
           case 'rgb':
           
            $r = $d[$pos];
            $g = isset($d[$pos + ($w * $h)]) ? $d[$pos + ($w * $h)] : 0;
            $b = isset($d[$pos + ((($w * $h) * 2))]) ? $d[$pos + ((($w * $h) * 2))] : 0;
            
           break;
           
           case 'grey':
           default:
           
            $r = $g = $b = $d[$pos];
           
           break;
           
         }
         
         $color = imagecolorallocate($img,$r,$g,$b);
         imagesetpixel($img,$x,$y,$color);
         
       }
       $pos++;
       
     }       
   }
   
   header('Content-type: image/png');
   header('Content-Disposition: attachment; filename="'.explode('.',$_FILES['upload']['name'])[0].'.png"');
   imagepng($img);
   exit();
  
}

?>