<?php

function set_admin_mode_from_cookie() {
  if(valid_login($_COOKIE['username'],$_COOKIE['password'])) {
    $GLOBALS['admin_mode'] = true;
  } else {
    $GLOBALS['admin_mode'] = false;
  }
  return $GLOBALS['admin_mode'];
}


function encrypt($data) {
  return md5($data);
}

function valid_login($username, $password) {
  $valid_logins = array('francesca' => 'rosemary',
                        'james'     => 'm047444074',
                       );
  $valid = false;
  foreach($valid_logins as $user => $pass) {
    if($username === $user && $password === encrypt($pass)) {
      $valid = true;
      break;
    }
  }
  return $valid;
}

function print_login_screen() {
  global $login_warning;

  echo "<div style='text-align:center'>";
  echo "<div>$login_warning</div>";
  echo "<form action='$_SERVER[REQUEST_URI]' method='post'>";
  echo "<div>Username: <input type='text' style='width:8em' name='username'></div>";
  echo "<div>Password: <input type='password' style='width:8em' name='password'></div>";
  echo "<div><input type='submit' style='width:7em' value='Login'></div>";
  echo "</div>";
}

// process login
if($_GET['logout'] === 'logout') {
  setcookie('username',false);
  setcookie('password',false);
  $login_warning = "<b>Successfully Logged Out</b><br>";
  $GLOBALS['admin_mode'] = false;
}
elseif($_POST['username'] == '' && $_POST['password'] == '') {
  set_admin_mode_from_cookie();
}
elseif(valid_login($_POST['username'], encrypt($_POST['password']))) {
  setcookie('username',$_POST['username']);
  setcookie('password',encrypt($_POST['password']));
  $GLOBALS['admin_mode'] = true;
}
else {
  $login_warning = "<b>Invalid Username and/or Password</b><br>";
  $GLOBALS['admin_mode'] = false;
}

?>