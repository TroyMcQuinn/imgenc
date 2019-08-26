<?php require('./process.php');?>
<html>

  <head>
    <title>Encode data as image</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
  </head>
  
  <body>
    
    <form id="main_form" name="main_form" action="" method="post" enctype="multipart/form-data">
    
      <label for="function">Function:</label><br />
      <input type="radio" name="function" value="encode" checked="checked" /><label for="function">encode</label>
      <input type="radio" name="function" value="decode" /><label for="function">decode</label>
      
      <br /><br />
    
      <label for="upload">Upload file:</label><br />
      <input type="file" name="upload" />
      
      <br /><br />
    
      <label for="mode">Color mode:</label><br />
      <input type="radio" name="mode" value="grey" checked="checked" /><label for="mode">greyscale</label>
      <input type="radio" name="mode" value="rgb" /><label for="mode">RGB</label>
      
      <br /><br />
      
      <label for="width">Width (for encoding only; leave blank for a square image):</label><br />
      <input type="number" name="width" value="" min="1" max="2048" /> px
      
      <br /><br />
      
      <input type="checkbox" name="compress" /><label for="compress">compressed?</input>
      
      <br /><br />
      
      <label for="password">Encryption / decryption password (leave blank for none)</label><br />
      
      <input type="password" name="password" value="" /><label for="password">Password</label>
      <br />
      <input type="password" name="password_confirm" value="" /><label for="password_confirm">Confirm password (req'd for encoding only)</label>
      
      <br /><br />
      
      <input type="submit" name="submit" value="Get data encoded as a PNG image" />
    
    </form>
  
  
  </body>

</html>