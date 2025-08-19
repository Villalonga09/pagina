<?php
require_once APP_PATH . "/Core/Controller.php";
require_once APP_PATH . "/Core/View.php";
require_once APP_PATH . "/Core/Auth.php";
require_once APP_PATH . "/Core/CSRF.php";
require_once APP_PATH . "/Core/Utils.php";
require_once APP_PATH . "/Models/User.php";

class AuthController extends Controller {
  private function rateKey() { return 'login_attempts_' . Utils::clientIp(); }
  public function loginForm() {
    Auth::start();
    View::render('admin/login.php', [], null);
  }
  public function login() {
    CSRF::validate();
    Auth::start();
    $key = $this->rateKey();
    $_SESSION[$key] = $_SESSION[$key] ?? ['count'=>0,'time'=>time()];
    $window = 15*60;
    if ($_SESSION[$key]['count'] >= 5 && (time() - $_SESSION[$key]['time']) < $window) {
      die("Demasiados intentos. Intente luego.");
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $u = (new User())->findByEmail($email);
    if ($u && password_verify($password, $u['password_hash'])) {
      Auth::login(['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email']]);
      $_SESSION[$key] = ['count'=>0,'time'=>time()];
      $this->redirect('/admin');
    } else {
      $_SESSION[$key]['count'] += 1;
      $_SESSION[$key]['time'] = time();
      View::render('admin/login.php', ['error'=>'Credenciales inválidas.'], null);
    }
  }
  public function logout() {
    Auth::logout();
    $this->redirect('/admin/login');
  }
  public function seedAdmin() {
    $token = $_GET['token'] ?? '';
    $expected = $_ENV['ADMIN_SETUP_TOKEN'] ?? '';
    if (!$expected || $token !== $expected) {
      http_response_code(403);
      echo "Forbidden";
      return;
    }
    $email = $_GET['email'] ?? ($_ENV['ADMIN_SEED_EMAIL'] ?? '');
    $password = $_GET['password'] ?? ($_ENV['ADMIN_SEED_PASSWORD'] ?? '');
    $name = $_GET['name'] ?? ($_ENV['ADMIN_SEED_NAME'] ?? 'Admin');

    if (!$email || !$password) {
      echo "Missing email or password";
      return;
    }

    $u = (new User())->findByEmail($email);
    if ($u) { echo "User already exists (id={$u['id']})"; return; }
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]);
    (new User())->create($name, $email, $hash, 'admin');
    echo "OK: admin created with email {$email}";
  }

}
