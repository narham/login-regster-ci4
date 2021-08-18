<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class Auth extends BaseController
{
	private $authModel;
	private $session;

	public function __construct()
	{
		$this->authModel = new \App\Models\AuthM();
		$this->session =  \Config\Services::session();
	}

	// Menampilkan Form register
	public function register()
	{
		# code...
		return view('Auth/register');
	}

	public function proses_daftar()
	{

		if (!$this->validate([
			'username' => [
				'rules' => 'required|min_length[4]|max_length[20]|is_unique[user.username]',
				'errors' => [
					'required' => '{field} Harus diisi',
					'min_length' => '{field} Minimal 4 Karakter',
					'max_length' => '{field} Maksimal 20 Karakter',
					'is_unique' => 'Username sudah digunakan sebelumnya'
				]
			],
			'password' => [
				'rules' => 'required|min_length[4]|max_length[50]',
				'errors' => [
					'required' => '{field} Harus diisi',
					'min_length' => '{field} Minimal 4 Karakter',
					'max_length' => '{field} Maksimal 50 Karakter',
				]
			],
			'cpassword' => [
				'rules' => 'matches[password]',
				'errors' => [
					'matches' => 'Konfirmasi Password tidak sesuai dengan password',
				]
			],
			'email' => [
				'rules' => 'required|valid_email|is_unique[user.email]',
				'errors' => [
					'required' => '{field} Harus diisi',
					'valid_email' => 'Gunakan {field} Yang Valid',
					'is_unique' => '{field} Sudah terdaftar',

				]
			],
		])) {
			session()->setFlashdata('error', $this->validator->listErrors());
			return redirect()->back()->withInput();
		}
		// ==========================
		$this->authModel->insert([
			'username' => $this->request->getVar('username'),
			'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
			'email' => $this->request->getVar('email')

			// =================================
		]);
		return redirect()->to('/login');
	}

	public function index()
	{
		# code...

		return view('Auth/login');
	}

	public function proses_login()
	{
		# code...

		$email = $this->request->getvar('email');
		$password = $this->request->getvar('password');

		// Cek Data
		$cekUser = $this->authModel->where(['email' => $email,])->first();

		// Jika ditemukan
		if ($cekUser) {
			if (password_verify($password, $cekUser->password)) {
				session()->set([
					'username' => $cekUser->username,
					'nama' => $cekUser->nama,
					'level' => $cekUser->level,
					'foto' => $cekUser->foto,
					'logged_in' => TRUE
				]);
				// dd($cekUser);
				if ($cekUser->level == 1) {
					# code...
					return redirect()->to('/admin');
				} elseif ($cekUser->level == 2) {
					# code...
					return redirect()->to('/user');
				} else {
					# code...

					session()->setFlashdata('error', 'Akun anda belum di Aktifasi');
					return redirect()->back();
				}
			} else {
				session()->setFlashdata('error', 'Username & Password Salah');
				return redirect()->back();
			}
		} else {
			session()->setFlashdata('error', 'Username & Password Salah');
			return redirect()->back();
		}

		// dd($cekUser);

	}

	public function logout()
	{
		# Keluar dariSystem
		session()->destroy();
		return redirect()->to('/login');
		// $this->authModel->logout();
	}
}